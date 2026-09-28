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
  },
  data() {
    return {
      chart: null,
    };
  },
  mounted() {    
    const labels = this.data.map(item => item.x_label);
    const totals = this.data.map(item => item[this.dataAttribute]); 

    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: this.title,
          data: totals,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
          pointRadius: this.pointRadius,
          pointHoverRadius: this.pointHoverRadius,
        }]
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
    data(newData) {
      if (!this.chart) {
        return;
      }
      this.chart.data.labels = newData.map(item => item.x_label);
      this.chart.data.datasets[0].data = newData.map(item => item[this.dataAttribute]);
      this.chart.update();
    },
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
}
</script>
