<template>
  <!-- Chart.js sizes the canvas from this wrapper; it must be relative and hold only the canvas -->
  <div class="relative">
    <canvas ref="canvas" width="1500" height="750"></canvas>
  </div>
</template>

<script>
import { siteLocale } from '../../utils/translate';
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'LineChart',
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
    };
  },
  mounted() {
    const markerLines = {
      id: 'markerLines',
      afterDatasetsDraw: (chart) => {
        const visible = this.markers.filter(marker => chart.data.labels.includes(marker.label));
        const { top, bottom, right } = chart.chartArea;
        const ctx = chart.ctx;
        ctx.save();
        ctx.strokeStyle = '#f87171';
        ctx.fillStyle = '#f87171';
        ctx.lineWidth = 2;
        ctx.font = 'bold 14px sans-serif';
        visible.forEach((marker, i) => {
          const x = chart.scales.x.getPixelForValue(chart.data.labels.indexOf(marker.label));
          ctx.setLineDash([6, 4]);
          ctx.beginPath();
          ctx.moveTo(x, top);
          ctx.lineTo(x, bottom);
          ctx.stroke();
          ctx.setLineDash([]);
          // Alternate rows so neighbouring labels don't overlap; flip left near the right edge
          const width = ctx.measureText(marker.text).width;
          const textX = x + 6 + width > right ? x - 6 - width : x + 6;
          ctx.fillText(marker.text, textX, top + 16 + (i % 2) * 18);
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
        ctx.rect(left, top, right - left, bottom - top);
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
            ctx.fillStyle = '#9ca3af';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('No data', (x1 + x2) / 2, top + 18);
            ctx.restore();
          }
        }
        ctx.restore();
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
        ctx.fillStyle = '#9ca3af';
        ctx.fillText(this.topLabel, chart.scales.y.left + 4, y);
        ctx.beginPath();
        ctx.rect(left, 0, right - left, top);
        ctx.clip();
        ctx.textAlign = 'center';
        this.data.forEach((item, index) => {
          const value = item[this.topAttribute] ?? 0;
          ctx.fillStyle = value ? '#9ca3af' : 'rgba(156, 163, 175, 0.4)';
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
        color: (context) => dim(context.tick.value) ? 'rgba(156, 163, 175, 0.4)' : '#9ca3af',
        font: { size: 11 },
        callback: value => Number.isInteger(value) ? this.data[value]?.x_label ?? '' : '',
      },
    } : {
      // A few flat labels instead of one per point; the tooltip still has the full label
      ticks: {
        display: true,
        color: (context) => dim(context.index) ? 'rgba(156, 163, 175, 0.4)' : '#9ca3af',
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
    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: 'line',
      plugins: [backdrop, markerLines, topRow],
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
            labels: { color: '#9ca3af', boxWidth: 24 },
          },
          tooltip: {
            backgroundColor: '#393e4d',
            padding: 12,
            displayColors: false,
            callbacks: {
              title: items => this.tooltipTitle(this.visibleCount ? this.data[items[0].parsed.x]?.x_label : items[0].label),
              ...(this.tooltipLabel ? { label: item => `${this.tooltipLabel}: ${item.formattedValue}` } : {}),
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
            ...(this.percent ? { min: 0, max: 100 } : {}),
            grid: {
              display: !this.gridded,
              color: 'rgba(255, 255, 255, 0.06)',
            },
            border: { display: false },
            ticks: {
              display: true,
              color: '#9ca3af',
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
  },
  beforeUnmount() {
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
        pointBackgroundColor: '#1f2229',
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
