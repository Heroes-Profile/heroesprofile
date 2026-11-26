<template>
  <div>
  <!-- Chart.js sizes the canvas from this wrapper; it must be relative, and anything else in it absolutely positioned -->
  <div class="relative">
    <canvas ref="canvas" width="1500" height="750"></canvas>
    <!-- The hover tooltip as page text rather than drawn on the canvas, so it's as sharp as the rest of the page and
         uses the site's fonts (canvas text blurs on standard-resolution screens). Above the point, kept inside the chart. -->
    <div
      v-if="tip.visible"
      class="pointer-events-none absolute z-10 bg-surface-4 rounded px-3 py-2.5 text-sm text-white whitespace-nowrap shadow-[0_0.25rem_1rem_rgba(0,0,0,0.4)]"
      :style="{ left: tip.x + 'px', top: tip.y + 'px', transform: tip.transform }"
    >
      <div v-if="tip.title" class="font-bold mb-1">{{ tip.title }}</div>
      <div v-for="(line, index) in tip.lines" :key="index">{{ line }}</div>
    </div>
  </div>
  <!-- When the event labels don't fit across the plot, the lines are numbered and named here -->
  <ol v-if="compactMarkers && visibleMarkers.length" class="mt-3 flex flex-col gap-1 text-xs text-subtext">
    <li v-for="(marker, index) in visibleMarkers" :key="marker.label"><span class="font-bold text-[#f87171] mr-2">{{ index + 1 }}</span>{{ marker.text }}</li>
  </ol>
  </div>
</template>

<script>
import { siteLocale } from '../../utils/translate';
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'LineChart',
  emits: ['pan'],
  components: {},
  props: {
    data: Array,
    dataAttribute: String,
    title: String,
    pointRadius: { type: Number, default: 0 },
    pointHoverRadius: { type: Number, default: 0 },
    // Expected value for the last point, drawn dashed from the point before it
    projected: { type: Number, default: null },
    projectedLabel: { type: String, default: 'Expected' },
    // Dashed vertical lines: [{ label: x label to sit on, text }]
    markers: { type: Array, default: () => [] },
    // A 0-100% y axis with % ticks
    percent: { type: Boolean, default: false },
    // Dim the x labels of points with no value
    dimEmpty: { type: Boolean, default: false },
    // Show only this many points, ending before endIndex; changing endIndex slides the chart
    visibleCount: { type: Number, default: null },
    endIndex: { type: Number, default: null },
    // Width to height on desktop; phones keep their taller plot
    aspectRatio: { type: Number, default: 2 },
    // The season chart's look: a line per point, alternating y bands, y labels on the right
    gridded: { type: Boolean, default: false },
    // A row of each point's value of this attribute above the plot, named by topLabel
    topAttribute: { type: String, default: null },
    topLabel: { type: String, default: '' },
    // Names the value in the tooltip ("HP Quick Match MMR: 2,706") in place of the chart title
    tooltipLabel: { type: String, default: null },
  },
  data() {
    return {
      chart: null,
      phone: false,
      // The event labels don't fit across the plot: numbered lines, named under the chart
      compactMarkers: false,
      tip: { visible: false, x: 0, y: 0, transform: '', title: '', lines: [] },
    };
  },
  computed: {
    // Markers whose month is on the chart
    visibleMarkers() {
      const labels = (this.data || []).map(item => item.x_label);
      return this.markers.filter(marker => labels.includes(marker.label));
    },
  },
  mounted() {
    const markerLines = {
      id: 'markerLines',
      afterDatasetsDraw: (chart) => {
        const visible = this.visibleMarkers;
        const { top, bottom, right } = chart.chartArea;
        const ctx = chart.ctx;
        ctx.save();
        ctx.strokeStyle = '#f87171';
        ctx.fillStyle = '#f87171';
        ctx.lineWidth = 2;
        ctx.font = 'bold 14px sans-serif';
        // Full labels while they fit: two rows, none overlapping its row's neighbour or running past the plot;
        // otherwise numbers, named in a list under the chart
        const spans = visible.map(marker => {
          const x = chart.scales.x.getPixelForValue(chart.data.labels.indexOf(marker.label));
          const width = ctx.measureText(marker.text).width;
          const start = x + 6 + width > right ? x - 6 - width : x + 6;
          return [start, start + width];
        });
        const fits = spans.every(([start], i) => start >= chart.chartArea.left && (i < 2 || spans[i - 2][1] + 8 <= start));
        if (this.compactMarkers === fits) {
          this.compactMarkers = !fits;
        }
        visible.forEach((marker, i) => {
          const x = chart.scales.x.getPixelForValue(chart.data.labels.indexOf(marker.label));
          ctx.setLineDash([6, 4]);
          ctx.beginPath();
          ctx.moveTo(x, top);
          ctx.lineTo(x, bottom);
          ctx.stroke();
          ctx.setLineDash([]);
          // Alternate rows so neighbouring labels don't overlap; flip left near the right edge
          const text = this.compactMarkers ? String(i + 1) : marker.text;
          const width = ctx.measureText(text).width;
          const textX = x + 6 + width > right ? x - 6 - width : x + 6;
          ctx.fillText(text, textX, top + 16 + (this.compactMarkers ? 0 : (i % 2) * 18));
        });
        ctx.restore();
      },
    };

    const dim = index => this.dimEmpty && this.data[index]?.[this.dataAttribute] == null;

    // Shading drawn under the line: every other y band, and the full column of each empty point
    const backdrop = {
      id: 'backdrop',
      beforeDatasetsDraw: (chart) => {
        const { left, right, top, bottom } = chart.chartArea;
        const ctx = chart.ctx;
        ctx.save();
        ctx.beginPath();
        // The season chart's plot has slightly rounded corners, like the rank chart
        ctx.roundRect(left, top, right - left, bottom - top, this.gridded ? 6 : 0);
        ctx.clip();
        if (this.gridded) {
          const ticks = chart.scales.y.ticks;
          ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
          for (let i = ticks.length - 1; i > 0; i -= 2) {
            const y1 = chart.scales.y.getPixelForValue(ticks[i].value);
            const y2 = chart.scales.y.getPixelForValue(ticks[i - 1].value);
            ctx.fillRect(left, y1, right - left, y2 - y1);
          }
        }
        if (this.dimEmpty) {
          // Each run of empty points, from the last point with a value to the next one
          ctx.fillStyle = 'rgba(0, 0, 0, 0.25)';
          const empty = index => this.data[index]?.[this.dataAttribute] == null;
          for (let index = 0; index < this.data.length; index++) {
            if (!empty(index) || (index > 0 && empty(index - 1))) {
              continue;
            }
            let last = index;
            while (last + 1 < this.data.length && empty(last + 1)) {
              last++;
            }
            const x1 = chart.scales.x.getPixelForValue(index > 0 ? index - 1 : index - 0.5);
            const x2 = chart.scales.x.getPixelForValue(last + 1 < this.data.length ? last + 1 : last + 0.5);
            ctx.fillRect(x1, top, x2 - x1, bottom - top);
            ctx.save();
            ctx.fillStyle = '#a1a9b5';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(this.$t('No data'), (x1 + x2) / 2, top + 18);
            ctx.restore();
          }
        }
        ctx.restore();
      },
    };
    // The line's shaded area keeps to the same rounded corners
    const roundedPlot = {
      id: 'roundedPlot',
      beforeDatasetsDraw: (chart) => {
        if (!this.gridded) {
          return;
        }
        const { left, right, top, bottom } = chart.chartArea;
        chart.ctx.save();
        chart.ctx.beginPath();
        chart.ctx.roundRect(left, top, right - left, bottom - top, 6);
        chart.ctx.clip();
      },
      afterDatasetsDraw: (chart) => {
        if (this.gridded) {
          chart.ctx.restore();
        }
      },
    };
    const topRow = {
      id: 'topRow',
      afterDraw: (chart) => {
        if (!this.topAttribute) {
          return;
        }
        const { left, right, top } = chart.chartArea;
        const ctx = chart.ctx;
        const y = top / 2;
        ctx.save();
        ctx.font = '11px sans-serif';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#a1a9b5';
        ctx.fillText(this.topLabel, chart.scales.y.left + 4, y);
        ctx.beginPath();
        ctx.rect(left, 0, right - left, top);
        ctx.clip();
        ctx.textAlign = 'center';
        this.data.forEach((item, index) => {
          const value = item[this.topAttribute] ?? 0;
          ctx.fillStyle = value ? '#a1a9b5' : 'rgba(161, 169, 181, 0.4)';
          ctx.fillText(value.toLocaleString(), chart.scales.x.getPixelForValue(index), y);
        });
        ctx.restore();
      },
    };

    const xTicks = this.visibleCount ? {
      // Linear x so the window can sit between points mid-slide
      type: 'linear',
      ...this.windowRange(),
      // A tick on every point in view; Chart.js would start them at the half-step edge
      afterBuildTicks: (axis) => {
        axis.ticks = [];
        for (let value = Math.ceil(axis.min); value <= Math.floor(axis.max); value++) {
          axis.ticks.push({ value });
        }
      },
      ticks: {
        autoSkip: false,
        maxRotation: 0,
        color: (context) => dim(context.tick.value) ? 'rgba(161, 169, 181, 0.4)' : '#a1a9b5',
        font: { size: 11 },
        callback: value => Number.isInteger(value) ? this.data[value]?.x_label ?? '' : '',
      },
    } : {
      // A few flat labels instead of one per point; the tooltip still has the full label
      ticks: {
        display: true,
        color: (context) => dim(context.index) ? 'rgba(161, 169, 181, 0.4)' : '#a1a9b5',
        font: { size: 11 },
        autoSkip: true,
        maxRotation: 0,
        maxTicksLimit: window.matchMedia('(max-width: 767px)').matches ? 3 : 10,
        // Timestamps as a short date in the viewer's locale, e.g. "Mar 4, 2017"; month labels as they are
        callback(value) {
          const label = this.getLabelForValue(value);
          const date = typeof label === 'string' && label.match(/^(\d{4})-(\d{2})-(\d{2})/);
          return date
            ? new Date(date[1], date[2] - 1, date[3]).toLocaleDateString(siteLocale(), { year: 'numeric', month: 'short', day: 'numeric' })
            : label;
        },
      },
    };

    this.phoneQuery = window.matchMedia('(max-width: 767px)');
    this.phone = this.phoneQuery.matches;
    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: 'line',
      plugins: [backdrop, roundedPlot, markerLines, topRow],
      data: {
        labels: this.data.map(item => item.x_label),
        datasets: this.datasets(),
      },
      options: {
        responsive: true,
        // Phones: taller plot, and few flat date labels so they don't crush it
        aspectRatio: this.phoneQuery.matches ? 1.2 : this.aspectRatio,
        // Room for the top row, and for arrows laid over either end
        layout: { padding: this.topAttribute ? { top: 28, left: 20, right: 20 } : 0 },
        // Styled like the Storm League rank chart
        plugins: {
          // The legend only names the dashed projected line; a single line needs none
          legend: {
            display: this.projected !== null,
            labels: { color: '#a1a9b5', boxWidth: 24 },
          },
          tooltip: {
            // Chart.js works out the title and lines; showTooltip lays them out as page text
            enabled: false,
            external: context => this.showTooltip(context),
            displayColors: false,
            callbacks: {
              title: items => this.tooltipTitle(this.visibleCount ? this.data[items[0].parsed.x]?.x_label : items[0].label),
              ...(this.tooltipLabel ? { label: item => `${this.tooltipLabel}: ${item.formattedValue}${this.percent ? '%' : ''}` } : {}),
            },
          },
        },
        scales: {
          x: {
            grid: {
              display: this.gridded,
              color: 'rgba(255, 255, 255, 0.08)',
            },
            border: { display: false },
            ...xTicks,
          },
          y: {
            position: this.gridded ? 'right' : 'left',
            beginAtZero: false,
            // A little room past the top and bottom, so a point at 100% (or 0%) isn't clipped by the plot's
            // edge. Percent charts keep their 0-100 ticks and grid bands; the extra 5% each side is unmarked.
            ...(this.percent
              ? { min: -5, max: 105, afterBuildTicks: scale => { scale.ticks = [0, 20, 40, 60, 80, 100].map(value => ({ value })); } }
              : { grace: '5%' }),
            grid: {
              display: !this.gridded,
              color: 'rgba(255, 255, 255, 0.06)',
            },
            border: { display: false },
            ticks: {
              display: true,
              color: '#a1a9b5',
              font: { size: 11 },
              ...(this.percent ? { callback: value => value + '%' } : {}),
            },
          }
        }
      }
    }));

    // Follow the container as it grows or shrinks: switch between the phone and desktop shapes at the
    // breakpoint, and redraw so a resize Chart.js held back (it waits out animations) is applied
    this.resizeObserver = new ResizeObserver(() => {
      cancelAnimationFrame(this.resizeFrame);
      this.resizeFrame = requestAnimationFrame(() => {
        this.chart.options.aspectRatio = this.phoneQuery.matches ? 1.2 : this.aspectRatio;
        this.chart.resize();
        this.chart.update('none');
      });
    });
    this.resizeObserver.observe(this.$el.parentElement);

    if (this.visibleCount) {
      this.addDrag();
    }
  },
  watch: {
    data() {
      this.refresh();
    },
    projected() {
      this.refresh();
    },
    endIndex() {
      this.slide();
    },
    visibleCount() {
      this.refresh();
    },
  },
  beforeUnmount() {
    this.removeDrag?.();
    cancelAnimationFrame(this.slideFrame);
    cancelAnimationFrame(this.resizeFrame);
    this.resizeObserver?.disconnect();
    this.chart?.destroy();
  },
  methods: {
    refresh() {
      if (!this.chart) {
        return;
      }
      this.chart.data.labels = this.data.map(item => item.x_label);
      this.chart.data.datasets = this.datasets();
      if (this.visibleCount) {
        cancelAnimationFrame(this.slideFrame);
        Object.assign(this.chart.options.scales.x, this.windowRange());
      }
      this.chart.update();
    },
    // Timestamps ("2017-05-22 03:29:30", UTC) in the viewer's locale and timezone; other labels as they are
    // Chart.js's tooltip state as the HTML tooltip: centred above the point, flipped right/left near the
    // chart's sides and below the point near its top, so it stays inside the chart
    showTooltip({ chart, tooltip }) {
      if (!tooltip || tooltip.opacity === 0 || !tooltip.dataPoints?.length) {
        this.tip.visible = false;
        return;
      }
      const x = tooltip.caretX;
      const y = tooltip.caretY;
      const horizontal = x < chart.width * 0.15 ? '0%' : x > chart.width * 0.85 ? '-100%' : '-50%';
      const vertical = y < 70 ? '0.75rem' : 'calc(-100% - 0.75rem)';
      this.tip = {
        visible: true,
        x,
        y,
        transform: `translate(${horizontal}, ${vertical})`,
        title: (tooltip.title || []).join(' '),
        lines: (tooltip.body || []).flatMap(part => part.lines),
      };
    },
    tooltipTitle(label) {
      if (typeof label !== 'string' || !/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(label)) {
        return label;
      }
      return new Date(label.replace(' ', 'T') + 'Z').toLocaleString(siteLocale(), { dateStyle: 'long', timeStyle: 'short' });
    },
    // Half a step either side so the end points aren't cut in half
    windowRange() {
      const end = this.endIndex ?? this.data.length;
      return { min: Math.max(0, end - this.visibleCount) - 0.5, max: end - 0.5 };
    },
    // Dragging a windowed chart sideways moves the window with the pointer; letting go snaps it to whole points
    // and reports the new end (the 'pan' event), which comes back as endIndex and eases into place.
    // Vertical swipes still scroll the page on phones.
    addDrag() {
      const canvas = this.$refs.canvas;
      canvas.style.touchAction = 'pan-y';
      let drag = null;

      const canDrag = () => this.data.length > this.visibleCount;
      const setCursor = () => { canvas.style.cursor = !canDrag() ? '' : drag?.moved ? 'grabbing' : 'grab'; };

      const down = (event) => {
        if (!canDrag() || event.button > 0) {
          return;
        }
        cancelAnimationFrame(this.slideFrame);
        const { left, right } = this.chart.chartArea;
        drag = { x: event.clientX, min: this.chart.options.scales.x.min, perPoint: (right - left) / this.visibleCount, moved: false };
        canvas.setPointerCapture(event.pointerId);
      };
      const move = (event) => {
        if (!drag) {
          setCursor();
          return;
        }
        const dx = event.clientX - drag.x;
        if (!drag.moved && Math.abs(dx) < 4) {
          return;
        }
        drag.moved = true;
        this.tip.visible = false;
        const lowest = -0.5;
        const highest = this.data.length - 0.5 - this.visibleCount;
        const scale = this.chart.options.scales.x;
        scale.min = Math.min(highest, Math.max(lowest, drag.min - dx / drag.perPoint));
        scale.max = scale.min + this.visibleCount;
        this.chart.update('none');
        setCursor();
      };
      const up = () => {
        if (!drag) {
          return;
        }
        const moved = drag.moved;
        drag = null;
        setCursor();
        if (!moved) {
          return;
        }
        const end = Math.round(this.chart.options.scales.x.min + 0.5) + this.visibleCount;
        if (end === (this.endIndex ?? this.data.length)) {
          this.slide();
        } else {
          this.$emit('pan', end);
        }
      };

      canvas.addEventListener('pointerdown', down);
      canvas.addEventListener('pointermove', move);
      canvas.addEventListener('pointerup', up);
      canvas.addEventListener('pointercancel', up);
      this.removeDrag = () => {
        canvas.removeEventListener('pointerdown', down);
        canvas.removeEventListener('pointermove', move);
        canvas.removeEventListener('pointerup', up);
        canvas.removeEventListener('pointercancel', up);
      };
    },
    // Eases the x range to the new window instead of redrawing
    slide() {
      if (!this.chart || !this.visibleCount) {
        return;
      }
      cancelAnimationFrame(this.slideFrame);
      const from = this.chart.options.scales.x.min;
      const to = this.windowRange().min;
      const started = performance.now();
      const step = (now) => {
        const progress = Math.min(1, (now - started) / 300);
        const eased = 1 - Math.pow(1 - progress, 3);
        // Re-read each frame: update() replaces the resolved options object
        const scale = this.chart.options.scales.x;
        scale.min = from + (to - from) * eased;
        scale.max = scale.min + this.visibleCount;
        this.chart.update('none');
        if (progress < 1) {
          this.slideFrame = requestAnimationFrame(step);
        }
      };
      this.slideFrame = requestAnimationFrame(step);
    },
    datasets() {
      const totals = this.data.map(item => item[this.dataAttribute]);
      const datasets = [{
        label: this.title,
        data: this.visibleCount ? totals.map((y, x) => ({ x, y })) : totals,
        backgroundColor: 'rgba(0, 139, 139, 0.35)',
        borderColor: '#008b8b',
        borderWidth: 2,
        fill: 'start',
        pointRadius: this.pointRadius,
        pointHoverRadius: this.pointHoverRadius,
        pointBackgroundColor: '#23252f',
        pointBorderColor: '#008b8b',
      }];

      if (this.projected !== null && totals.length) {
        const last = totals.length - 1;
        const projectedData = totals.map(() => null);
        if (last > 0) {
          projectedData[last - 1] = totals[last - 1];
        }
        projectedData[last] = this.projected;

        datasets.push({
          label: this.projectedLabel,
          data: projectedData,
          backgroundColor: 'transparent',
          borderColor: '#008b8b',
          borderWidth: 2,
          borderDash: [6, 4],
          pointRadius: this.pointRadius,
          pointHoverRadius: this.pointHoverRadius,
          pointBackgroundColor: 'transparent',
        });
      }

      return datasets;
    },
  },
}
</script>
