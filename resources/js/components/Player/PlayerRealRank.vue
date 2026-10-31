<template>
  <panel-box v-if="loading" title="Storm League Rank" :loading="true">
    <skeleton-box class="h-[360px]"></skeleton-box>
  </panel-box>
  <panel-box v-else-if="data" title="Storm League Rank">
    <template #title>
      <round-image size="small" icon="fas fa-info" title="info" popupsize="large">
        <slot>
          <div>
            <p class="max-sm:text-xs">This is the player's real in-game Storm League rank, not an estimate. Blizzard doesn't publish ranks, so we read them off the game itself.</p>
            <p class="max-sm:text-xs mt-2">The "with rank reading" version of the Heroes Profile uploader (Windows, macOS or Linux) has an opt-in "Read ranks from the loading screen" setting. When it's on, the uploader looks at the loading screen of each Storm League game (only the game window, only while the loading screen is up), reads every player's rank badge, and sends those ranks to us along with the game's replay.</p>
            <p class="max-sm:text-xs mt-2">We only keep a rank for a player who is actually in that replay, and only when the uploader is confident the badge belongs to them. Each time the same rank is read again we count it, so "read in 12 games" means twelve separate loading screens showed that rank. A new rank, or a new season, starts a new entry.</p>
            <p class="max-sm:text-xs mt-2">A rank only appears once someone with the setting on has played a game with this player, so it can be out of date. Ranks are as read and can occasionally be wrong.</p>
          </div>
        </slot>
      </round-image>
    </template>

    <template #right>
      <span v-if="data.season" class="text-3xl font-bold text-header">{{ data.season }}</span>
    </template>

    <template v-if="data.current">
      <div v-if="!data.current_season" class="text-sm font-bold uppercase text-subtext">Last season's rank</div>

      <div class="flex flex-wrap items-center gap-4">
        <h3 class="text-2xl font-bold">Rank History</h3>
        <tab-button tab1text="7 days" tab2text="30 days" :ignoreclick="true" @tab-click="setRange"></tab-button>
      </div>

      <div v-if="rankedPeriods.length" class="relative h-[260px]">
        <canvas ref="chart"></canvas>
      </div>
      <div v-else class="text-subtext">Only an unranked badge has been read this season.</div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <stat-box v-if="peakName" class="!m-0 !min-w-0" :tier="peakName" :value-first="true" :title="'Peak'" :value="peakName"></stat-box>
        <stat-box class="!m-0 !min-w-0" :tier="data.current.league == 'unranked' ? null : currentName" :value-first="true" :title="'Current'" :value="currentName"></stat-box>
        <stat-box class="!m-0 !min-w-0" :value-first="true" :title="'Games'" :value="games.toLocaleString('en-US')"></stat-box>
        <stat-box class="!m-0 !min-w-0" :value-first="true" :title="'Last seen'" :value="formatDay(data.current.last_game_date)"></stat-box>
      </div>
    </template>

    <div v-else>No in-game rank has been read for this player yet.</div>

    <details class="bg-surface-3 rounded text-sm w-fit max-w-full group open:w-full">
      <summary class="px-4 py-3 flex gap-4 justify-between items-center cursor-pointer list-none">
        <h3 class="text-lg">How to get your rank on Heroes Profile</h3>
        <i class="fas fa-chevron-down transition-transform group-open:rotate-180"></i>
      </summary>
      <div class="px-6 pb-6 space-y-4">
        <ol class="list-decimal list-inside space-y-1">
          <li>Install the <strong>with rank reading</strong> version of the Heroes Profile uploader for your system: Windows, macOS or Linux.</li>
          <li>In the uploader's Settings, turn on <strong>Read ranks from the loading screen</strong>.</li>
          <li>Play Storm League. Once a game's replay is uploaded, the ranks read from its loading screen show up here, for you and for everyone else in that game.</li>
        </ol>
        <p>Rank reading is off until you turn it on.
          <a class="link" href="/Upload/Uploader#rank-reading">How rank reading works</a>.
        </p>
        <custom-button href="/Upload" text="Get The Uploader" alt="Download the Heroes Profile replay uploader" color="blue"></custom-button>
      </div>
    </details>
  </panel-box>
</template>

<script>
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
import moment from 'moment-timezone';
import { rankName } from '../RealRankBadge.vue';

Chart.register(...registerables);

const LEAGUES = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
const SHORT_NAMES = { bronze: 'Bronze', silver: 'Silver', gold: 'Gold', platinum: 'Plat', diamond: 'Diamond' };

// One step per division, Bronze 5 = 1 up to Diamond 1 = 25, then Master and Grandmaster
function rankValue(rank) {
  if (rank.league == 'grandmaster') {
    return 27;
  }
  if (rank.league == 'master') {
    return 26;
  }
  const league = LEAGUES.indexOf(rank.league);
  return league < 0 || !rank.division ? null : league * 5 + (6 - rank.division);
}

function stepLabel(value) {
  if (value == 27) {
    return 'GM';
  }
  if (value == 26) {
    return 'Master';
  }
  return SHORT_NAMES[LEAGUES[Math.floor((value - 1) / 5)]] + ' ' + (5 - (value - 1) % 5);
}

export default {
  name: 'PlayerRealRank',
  emits: ['loaded'],
  props: {
    blizzid: [String, Number],
    region: [String, Number],
  },
  data(){
    return {
      data: null,
      loading: true,
      days: 7,
      chart: null,
    }
  },
  mounted() {
    this.getData();
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
  computed: {
    rankedPeriods(){
      return (this.data?.periods || []).filter(period => rankValue(period) !== null);
    },
    peakName(){
      const peak = this.rankedPeriods.reduce((best, period) => (!best || rankValue(period) > rankValue(best) ? period : best), null);
      return peak ? rankName(peak) : null;
    },
    currentName(){
      return this.data?.current ? rankName(this.data.current) : null;
    },
    games(){
      return (this.data?.periods || []).reduce((total, period) => total + period.read_count, 0);
    },
    // Each day of the range, ending on the last day a rank was read, with the rank read latest that day
    chartDays(){
      if(!this.data?.current){
        return [];
      }
      const end = this.localDay(this.data.current.last_game_date);
      const days = [];
      for(let offset = this.days - 1; offset >= 0; offset--){
        const day = end.clone().subtract(offset, 'days');
        const period = this.rankedPeriods
          .filter(period => !this.localDay(period.first_game_date).isAfter(day) && !this.localDay(period.last_game_date).isBefore(day))
          .reduce((latest, period) => (!latest || period.last_game_date > latest.last_game_date ? period : latest), null);
        days.push({ day, rank: period, value: period ? rankValue(period) : null });
      }
      return days;
    },
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/player/realrank", {
          blizz_id: this.blizzid,
          region: this.region,
        });
        this.data = response.data;
        this.$emit('loaded', this.data);
      }catch(error){
        //Do something here
      }
      this.loading = false;
      this.$nextTick(() => this.drawChart());
    },
    setRange(side){
      this.days = side == 'left' ? 7 : 30;
      this.$nextTick(() => this.drawChart());
    },
    localDay(dateString){
      return moment.tz(dateString, 'UTC').tz(moment.tz.guess()).startOf('day');
    },
    formatDay(dateString){
      return moment.tz(dateString, 'UTC').tz(moment.tz.guess()).format('MMM D, YYYY');
    },
    drawChart(){
      this.chart?.destroy();
      this.chart = null;
      const days = this.chartDays;
      const values = days.map(day => day.value).filter(value => value !== null);
      if(!values.length || !this.$refs.chart){
        return;
      }

      // Whole leagues, so the bands read Plat 1 to Plat 5 rather than a single row
      const low = Math.min(...values);
      const high = Math.max(...values);
      const min = low > 25 ? low : Math.floor((low - 1) / 5) * 5 + 1;
      const max = high > 25 ? high : Math.ceil(high / 5) * 5;

      const rankBands = {
        id: 'rankBands',
        beforeDraw(chart) {
          // Under the rank labels too, out to the canvas edge
          const { ctx, width, chartArea: { left }, scales: { y } } = chart;
          ctx.save();
          for(let value = min; value <= max; value++){
            const top = y.getPixelForValue(value + 0.5);
            ctx.fillStyle = (max - value) % 2 == 0 ? '#303340' : '#272a34';
            ctx.fillRect(left, top, width - left, y.getPixelForValue(value - 0.5) - top);
          }
          ctx.restore();
        },
      };

      this.chart = markRaw(new Chart(this.$refs.chart.getContext('2d'), {
        type: 'line',
        plugins: [rankBands],
        data: {
          labels: days.map(day => day.day.format('DD MMM.')),
          datasets: [{
            data: days.map(day => day.value),
            borderColor: '#008b8b',
            borderWidth: 2,
            backgroundColor: 'rgba(0, 139, 139, 0.35)',
            fill: 'start',
            spanGaps: true,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: '#1f2229',
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
                title: (items) => days[items[0].dataIndex].day.format('MMM D, YYYY'),
                label: (item) => rankName(days[item.dataIndex].rank),
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
              min: min - 0.5,
              max: max + 0.5,
              grid: { display: false },
              border: { display: false },
              afterBuildTicks: (axis) => {
                axis.ticks = [];
                for(let value = min; value <= max; value++){
                  axis.ticks.push({ value });
                }
              },
              ticks: { padding: 16, color: '#9ca3af', callback: (value) => stepLabel(value) },
            },
          },
        },
      }));
    },
  }
}
</script>
