<template>
  <div>
    <canvas id="myChart" width="400" height="400"></canvas>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

export default {
  name: 'BarChart',
  components: {
  },
  props: {
    data: Array,
  },
  data(){
    return {
      chart: null,
    }
  },
  created(){
  },
  mounted() {
    console.log(this.data);

    this.chartRef = this.$refs.chartRef;

    const labels = this.data.map(item => item.accountLevel);
    const totals = this.data.map(item => item.total);

    const ctx = document.getElementById('myChart').getContext('2d');
    this.chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total',
          data: totals,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>