<template>
  <!-- Win rate for each hour of the day in the viewer's own time, from the same UTC hour-of-week counts as the week days.
       Drawn like the Storm League Rank chart. -->
  <div class="relative flex-1 min-h-[18.25rem]">
    <canvas ref="chart"></canvas>
  </div>
</template>

<script>
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
import moment from 'moment-timezone';

Chart.register(...registerables);

export default {
  name: 'PlayerTimeOfDayWinRates',
  props: {
    // { utcHourOfWeek: { wins, losses } }, as for PlayerWeekdayWinRates
    data: Object,
  },
  data() {
    return {
      chart: null,
    };
  },
  mounted() {
    this.drawChart();
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
  watch: {
    data() {
      this.drawChart();
    },
  },
  computed: {
    hours() {
      const totals = Array.from({ length: 24 }, () => ({ wins: 0, losses: 0 }));
      const offsetHours = Math.round(moment.tz(moment.tz.guess()).utcOffset() / 60);
      for (const [bucket, stat] of Object.entries(this.data || {})) {
        const localHour = (((parseInt(bucket) + offsetHours) % 24) + 24) % 24;
        totals[localHour].wins += stat.wins || 0;
        totals[localHour].losses += stat.losses || 0;
      }
      return totals.map((hour, index) => {
        const games = hour.wins + hour.losses;
        return { index, games, winRate: games ? (hour.wins / games) * 100 : null };
      });
    },
  },
  methods: {
    hourLabel(hour) {
      return moment({ hour }).format('h A');
    },
    drawChart() {
      this.chart?.destroy();
      this.chart = null;
      if (!this.$refs.chart) {
        return;
      }
      const hours = this.hours;
      const rates = hours.filter(hour => hour.winRate !== null).map(hour => hour.winRate);
      // A little room above and below the hours played, in whole 5% steps
      const min = rates.length ? Math.max(0, Math.floor((Math.min(...rates) - 5) / 5) * 5) : 0;
      const max = rates.length ? Math.min(100, Math.ceil((Math.max(...rates) + 5) / 5) * 5) : 100;

      // The plot as a slightly rounded box, edge to edge like the rank chart
      const backdrop = {
        id: 'backdrop',
        beforeDraw(chart) {
          const { ctx, width, chartArea: { top, bottom } } = chart;
          ctx.save();
          ctx.beginPath();
          ctx.roundRect(0, top, width, bottom - top, 6);
          ctx.fillStyle = '#303340';
          ctx.fill();
          ctx.restore();
        },
      };

      this.chart = markRaw(new Chart(this.$refs.chart.getContext('2d'), {
        type: 'line',
        plugins: [backdrop],
        data: {
          labels: hours.map(hour => this.hourLabel(hour.index)),
          datasets: [{
            data: hours.map(hour => hour.winRate),
            borderColor: '#008b8b',
            borderWidth: 2,
            backgroundColor: 'rgba(0, 139, 139, 0.35)',
            fill: 'start',
            spanGaps: true,
            tension: 0.25,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#1f2229',
            pointHoverBackgroundColor: '#008b8b',
            pointBorderColor: '#008b8b',
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#393e4d',
              padding: 12,
              displayColors: false,
              callbacks: {
                title: (items) => this.hourLabel(hours[items[0].dataIndex].index) + ' – ' + this.hourLabel((hours[items[0].dataIndex].index + 1) % 24),
                label: (item) => this.$t('Win Rate') + ': ' + hours[item.dataIndex].winRate.toFixed(2) + '%',
                afterLabel: (item) => this.$t('Games: :value', { value: hours[item.dataIndex].games.toLocaleString('en-US') }),
              },
            },
          },
          scales: {
            x: {
              grid: { display: false },
              border: { display: false },
              ticks: { color: '#9ca3af', font: { size: 11 }, maxRotation: 0, maxTicksLimit: window.matchMedia('(max-width: 767px)').matches ? 4 : 8 },
            },
            y: {
              position: 'right',
              min,
              max,
              grid: { color: 'rgba(156, 163, 175, 0.12)' },
              border: { display: false },
              ticks: { padding: 12, color: '#9ca3af', stepSize: 5, callback: (value) => value + '%' },
            },
          },
        },
      }));
    },
  },
};
</script>
