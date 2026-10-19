<template>
  <div class="w-full max-w-[1500px] mx-auto">
    <canvas ref="chartCanvas" class="w-full"></canvas>
  </div>
</template>

<script>
import { toRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'DualLineChart',
  components: {},
  props: {
    data: Object,
    dataAttribute: String,  //need to pass in win_rate for seasonal winrate info
    winner: Number,
    // Optional, by team (0, 1): colours (hex) and names. Without them the winner is teal and the loser pink.
    teamColors: Array,
    teamNames: Array,
  },
  data() {
    return {
      chart: null,
    };
  },
  mounted() {
    const labels = Array.isArray(this.data.x_axis_time) ? this.data.x_axis_time : Object.values(this.data.x_axis_time);


    let team_one_dataset = Array.isArray(this.data.team_one_differences) ? this.data.team_one_differences : Object.values(this.data.team_one_differences);
    let team_two_dataset = Array.isArray(this.data.team_two_differences) ? this.data.team_two_differences : Object.values(this.data.team_two_differences);

    let team_one_level = Array.isArray(this.data.team_one_level) ? this.data.team_one_level : Object.values(this.data.team_one_level);
    let team_two_level = Array.isArray(this.data.team_two_level) ? this.data.team_two_level : Object.values(this.data.team_two_level);



    const winnerTeam = this.winner == 0 ? 0 : 1;
    const teamName = team => (this.teamNames ? this.teamNames[team] : 'Team ' + (team + 1));
    const colors = this.teamColors
      ? [this.rgba(this.teamColors[winnerTeam]), this.rgba(this.teamColors[1 - winnerTeam])]
      : ['75, 192, 192', '255, 99, 132'];

    let winnerExpLabel = teamName(winnerTeam) + ' Exp. Lead';
    let loserExpLabel = teamName(1 - winnerTeam) + ' Exp. Lead';

    let winnerExperienceDataset = this.winner == 0 ? team_one_dataset : team_two_dataset;
    let loserExperienceDataset = this.winner == 1 ?  team_one_dataset : team_two_dataset ;


    let winnerLevelLabel = teamName(winnerTeam) + ' Level';
    let loserLevelLabel = teamName(1 - winnerTeam) + ' Level';

    let winnerLevel = this.winner == 0 ? team_one_level : team_two_level;
    let loserLevel = this.winner == 1 ?  team_one_level : team_two_level ;


    const ctx = this.$refs.chartCanvas.getContext('2d');
    this.chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: winnerExpLabel,
            data: winnerExperienceDataset,
            backgroundColor: `rgba(${colors[0]}, 0.5)`,
            borderColor: `rgba(${colors[0]}, 1)`,
            borderWidth: 1,
            pointRadius: 0,
            fill: true,
            yAxisID: 'y',
          },
          {
            label: loserExpLabel,
            data: loserExperienceDataset,
            backgroundColor: `rgba(${colors[1]}, 0.5)`,
            borderColor: `rgba(${colors[1]}, 1)`,
            borderWidth: 1,
            pointRadius: 0,
            fill: true,
            yAxisID: 'y',
          },
          {
            label: winnerLevelLabel,
            data: winnerLevel,
            backgroundColor: `rgba(${colors[0]}, 1)`,
            borderColor: `rgba(${colors[0]}, 1)`,
            borderWidth: 1,
            pointRadius: 0,
            yAxisID: 'y1',
                  tension: 0.2
          },
          {
            label: loserLevelLabel,
            data: loserLevel,
            backgroundColor: `rgba(${colors[1]}, 1)`,
            borderColor: `rgba(${colors[1]}, 1)`,
            borderWidth: 1,
            pointRadius: 0,
            yAxisID: 'y1',
                  tension: 0.2
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
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
            beginAtZero: true,
            grid: {
              display: true,
            },
            ticks: {
              display: true,
            },
            title: { display: true, text: 'Experience' },
          },
          // Levels on their own scale, on the right so the two don't read as one
          y1: {
            position: 'right',
            beginAtZero: true,
            grid: {
              display: false,
            },
            ticks: {
              display: true,
            },
            title: { display: true, text: 'Level' },
          },
        },
      },
    });

    // Provide predictable chart height while still allowing responsive width.
    this.$refs.chartCanvas.style.height = '400px';
    if (window.innerWidth < 768) {
      this.$refs.chartCanvas.style.height = '260px';
    }
  },
  watch: {
    // Redraw when given new data (the replay viewer grows the lines as its game clock runs)
    data(newData) {
      // Chart.js breaks when updated through Vue's reactive wrapper, so work on the raw chart
      const chart = toRaw(this.chart);
      if (!chart) return;
      const list = value => (Array.isArray(value) ? value : Object.values(value));
      const teamOne = list(newData.team_one_differences);
      const teamTwo = list(newData.team_two_differences);
      const levelOne = list(newData.team_one_level);
      const levelTwo = list(newData.team_two_level);
      const [winnerExp, loserExp, winnerLevel, loserLevel] = this.winner == 0
        ? [teamOne, teamTwo, levelOne, levelTwo]
        : [teamTwo, teamOne, levelTwo, levelOne];
      chart.data.labels = list(newData.x_axis_time);
      [winnerExp, loserExp, winnerLevel, loserLevel].forEach((values, i) => {
        chart.data.datasets[i].data = values;
      });
      chart.update('none');
    },
  },
  methods: {
    // '#315bb6' -> '49, 91, 182'
    rgba(hex) {
      return [1, 3, 5].map(i => parseInt(hex.substr(i, 2), 16)).join(', ');
    },
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy();
    }
  },
};
</script>
