<template>
  <div>
    <canvas id="myChart" width="1500" height="750"></canvas>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'LineChart',
  components: {},
  props: {
    data: Array,
    dataAttribute: String,  //need to pass in win_rate for seasonal winrate info
  },
  data() {
    return {
      chart: null,
    };
  },
  mounted() {
    const labels = this.data.map(item => item.x_label);
    const totals = this.data.map(item => item[this.dataAttribute]); 

    const ctx = document.getElementById('myChart').getContext('2d');
    this.chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total',
          data: totals,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
          pointRadius: 0,
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
    });
  },
}
</script>
