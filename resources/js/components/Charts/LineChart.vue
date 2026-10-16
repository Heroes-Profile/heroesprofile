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
  },
  data() {
    return {
      chart: null,
    };
  },
  mounted() {    
    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: this.data.map(item => item.x_label),
        datasets: this.datasets(),
      },
      options: {
        responsive: true,
        scales: {
          x: {
            grid: {
              display: true, 
            },
            ticks: {
              display: true, 
              //maxTicksLimit: 10, // Limit the number of x-axis ticks to 100
            },
          },
          y: {
            beginAtZero: false,
            grid: {
              display: true,
            },
            ticks: {
              display: true, 
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
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
        pointRadius: this.pointRadius,
        pointHoverRadius: this.pointHoverRadius,
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
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
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
