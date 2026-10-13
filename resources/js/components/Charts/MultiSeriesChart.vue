<template>
  <div>
    <canvas ref="canvas" width="1500" height="600"></canvas>
  </div>
</template>

<script>
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Like LineChart, but several series on one axis (e.g. you vs baseline).
// series: [{ label, data: [numbers aligned with labels], color: 'teal' | 'yellow' | 'red' | 'blue' }]
const COLORS = {
  teal: '0, 139, 139',
  yellow: '217, 140, 64',
  red: '179, 54, 22',
  blue: '49, 91, 182',
};

export default {
  name: 'MultiSeriesChart',
  props: {
    type: { type: String, default: 'line' },
    labels: Array,
    series: Array,
    title: String,
    yLabel: String,
  },
  data() {
    return {
      chart: null,
    };
  },
  mounted() {
    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: this.type,
      data: { labels: this.labels, datasets: this.datasets() },
      options: {
        responsive: true,
        plugins: {
          title: { display: !!this.title, text: this.title },
        },
        scales: {
          x: { grid: { display: true }, ticks: { display: true } },
          y: {
            beginAtZero: this.type === 'bar',
            grid: { display: true },
            ticks: { display: true },
            title: { display: !!this.yLabel, text: this.yLabel },
          },
        },
      },
    }));
  },
  watch: {
    series() {
      if (!this.chart) {
        return;
      }
      this.chart.data.labels = this.labels;
      this.chart.data.datasets = this.datasets();
      this.chart.update();
    },
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
  methods: {
    datasets() {
      return this.series.map(s => {
        const rgb = COLORS[s.color] || COLORS.teal;
        return {
          label: s.label,
          data: s.data,
          backgroundColor: `rgba(${rgb}, ${this.type === 'bar' ? 0.7 : 0.2})`,
          borderColor: `rgba(${rgb}, 1)`,
          borderWidth: this.type === 'bar' ? 0 : 2,
          pointRadius: 0,
          pointHoverRadius: 3,
          spanGaps: true,
        };
      });
    },
  },
};
</script>
