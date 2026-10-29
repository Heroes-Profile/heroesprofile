<template>
  <div>
    <canvas ref="canvas" width="1500" height="750"></canvas>
  </div>
</template>

<script>
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

    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: 'line',
      plugins: [markerLines],
      data: {
        labels: this.data.map(item => item.x_label),
        datasets: this.datasets(),
      },
      options: {
        responsive: true,
        // Phones: taller plot, and few flat date labels so they don't crush it
        aspectRatio: window.matchMedia('(max-width: 767px)').matches ? 1.2 : 2,
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
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
            },
            border: { display: false },
            ticks: {
              display: true,
              color: '#9ca3af',
              font: { size: 11 },
              //maxTicksLimit: 10, // Limit the number of x-axis ticks to 100
              ...(window.matchMedia('(max-width: 767px)').matches ? {
                maxTicksLimit: 3,
                maxRotation: 0,
                // Date only; the time makes neighbouring labels overlap
                callback(value) {
                  const label = this.getLabelForValue(value);
                  return typeof label === 'string' ? label.slice(0, 10) : label;
                },
              } : {}),
            },
          },
          y: {
            beginAtZero: false,
            grid: {
              color: 'rgba(255, 255, 255, 0.06)',
            },
            border: { display: false },
            ticks: {
              display: true,
              color: '#9ca3af',
              font: { size: 11 },
            },
          }
        }
      }
    }));
  },
  watch: {
    data() {
      this.refresh();
    },
    projected() {
      this.refresh();
    },
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
  methods: {
    refresh() {
      if (!this.chart) {
        return;
      }
      this.chart.data.labels = this.data.map(item => item.x_label);
      this.chart.data.datasets = this.datasets();
      this.chart.update();
    },
    datasets() {
      const totals = this.data.map(item => item[this.dataAttribute]);
      const datasets = [{
        label: this.title,
        data: totals,
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
