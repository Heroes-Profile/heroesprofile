<template>
  <!-- Shaped like the card with no rank read: its box and the "How to get your rank" button -->
  <panel-box v-if="loading" :inner="true" :title="$t('Storm League Rank')" :loading="true">
    <skeleton-box class="flex-1 min-h-[24.125rem]"></skeleton-box>
    <skeleton-box class="h-[3.25rem] w-[26rem] max-w-full"></skeleton-box>
  </panel-box>
  <panel-box v-else-if="data" :inner="true" :title="$t('Storm League Rank')">
    <template #title>
      <round-image size="small" icon="fas fa-info" title="info" popupsize="large">
        <slot>
          <div>
            <p class="max-sm:text-xs">{{ $t("This is the player's real in-game Storm League rank, not an estimate. Blizzard doesn't publish ranks, so we read them off the game itself.") }}</p>
            <p class="max-sm:text-xs mt-2">{{ $t(`The "with rank reading" version of the Heroes Profile uploader (Windows, macOS or Linux) has an opt-in "Read ranks from the loading screen" setting. When it's on, the uploader looks at the loading screen of each Storm League game (only the game window, only while the loading screen is up), reads every player's rank badge, and sends those ranks to us along with the game's replay.`) }}</p>
            <p class="max-sm:text-xs mt-2">{{ $t('We only keep a rank for a player who is actually in that replay, and only when the uploader is confident the badge belongs to them. Each time the same rank is read again we count it, so "read in 12 games" means twelve separate loading screens showed that rank. A new rank, or a new season, starts a new entry.') }}</p>
            <p class="max-sm:text-xs mt-2">{{ $t('A rank only appears once someone with the setting on has played a game with this player, so it can be out of date. Ranks are as read and can occasionally be wrong.') }}</p>
          </div>
        </slot>
      </round-image>
    </template>

    <template #right>
      <span v-if="rangeLabel" class="text-3xl font-bold text-header">{{ rangeLabel }}</span>
    </template>

    <template v-if="data.current">
      <div v-if="!data.current_season" class="text-sm font-bold uppercase text-subtext">{{ $t("Last season's rank") }}</div>

      <div class="flex flex-wrap items-center gap-4">
        <h3 class="text-2xl font-bold">{{ $t('Rank History') }}</h3>
        <tab-button tab1text="7 days" tab2text="30 days" tab3text="This Season" overridedefaultside="third" :ignoreclick="true" @tab-click="setRange"></tab-button>
      </div>

      <div v-if="rankedPeriods.length" class="relative h-[18.25rem]">
        <tab-button v-if="!oneDay" class="absolute z-[1] -translate-y-[calc(100%+0.25rem)] !p-0.5 !rounded-[0.25rem] [&_a]:!rounded-[0.1875rem] [&_a]:!text-xs [&_a]:!px-1.5 [&_a]:!py-0.5" :style="{ left: plot.left + 'px', top: plot.top + 'px' }" :small="true" tab1text="Day's Peak" tab2text="End of Day" overridedefaultside="right" :ignoreclick="true" @tab-click="setDayRank"></tab-button>
        <canvas ref="chart"></canvas>
      </div>
      <div v-else class="text-subtext">{{ $t('Only an unranked badge has been read this season.') }}</div>
      <p v-if="rankedPeriods.length && oneDay" class="-mt-2 text-sm text-subtext">{{ $t("The chart fills in once a rank has been read on more than one day.") }}</p>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <stat-box v-if="peak" class="!m-0 !min-w-0" :tier="rankName(peak)" :value-first="true" :title="'Peak'" :value="localRankName(peak)"></stat-box>
        <stat-box class="!m-0 !min-w-0" :tier="data.current.league == 'unranked' ? null : rankName(data.current)" :value-first="true" :title="'Current'" :value="localRankName(data.current)"></stat-box>
        <stat-box class="!m-0 !min-w-0" :value-first="true" :title="'Games'" :value="games.toLocaleString('en-US')"></stat-box>
        <stat-box class="!m-0 !min-w-0" :value-first="true" :title="'Last seen'" :value="formatDay(data.current.last_game_date)"></stat-box>
      </div>
    </template>

    <!-- Holds the chart's place and grows with the card, so nothing is left empty under the dropdown -->
    <div v-else class="flex-1 min-h-[24.125rem] rounded-control bg-surface-3 flex items-center justify-center p-8 text-center text-xl text-subtext/50">{{ $t('No in-game rank has been read for this player yet.') }}</div>

    <!-- Opens over the page, like the leaderboard's requirements, so the cards below don't move -->
    <div class="relative w-fit max-w-full">
      <button type="button" class="bg-surface-3 rounded px-4 py-3 flex gap-4 justify-between items-center text-left" :aria-expanded="showRankHelp" @click="showRankHelp = !showRankHelp">
        <h3 class="text-xl">{{ $t('How to get your rank on Heroes Profile') }}</h3>
        <basil-icon name="arrow" :class="['transition-transform', showRankHelp ? '-rotate-90' : 'rotate-90']"></basil-icon>
      </button>
      <div v-if="showRankHelp" class="absolute z-40 left-0 top-full mt-2 w-[40rem] max-w-[calc(100vw-2rem)] max-h-[75vh] overflow-y-auto bg-surface-3 rounded p-6 shadow-lg space-y-4 text-sm">
        <ol class="list-decimal list-inside space-y-1">
          <li>{{ $t('Install the') }} <strong>{{ $t('with rank reading') }}</strong> {{ $t('version of the Heroes Profile uploader for your system: Windows, macOS or Linux.') }}</li>
          <li>{{ $t("In the uploader's Settings, turn on") }} <strong>{{ $t('Read ranks from the loading screen') }}</strong>.</li>
          <li>{{ $t("Play Storm League. Once a game's replay is uploaded, the ranks read from its loading screen show up here, for you and for everyone else in that game.") }}</li>
        </ol>
        <p>{{ $t('Rank reading is off until you turn it on.') }}
          <a class="link" href="/Upload/Uploader#rank-reading">{{ $t('How rank reading works') }}</a>.
        </p>
        <custom-button href="/Upload" text="Get The Uploader" alt="Download the Heroes Profile replay uploader" color="blue"></custom-button>
      </div>
    </div>
  </panel-box>
</template>

<script>
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
import { toFont } from 'chart.js/helpers';
import moment from 'moment-timezone';
import { rankName, localRankName } from '../RealRankBadge.vue';

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

// Grandmasters by ladder position, #1 best; a missing position ranks last
function isBetter(rank, than) {
  if (rankValue(rank) != rankValue(than)) {
    return rankValue(rank) > rankValue(than);
  }
  return rank.league == 'grandmaster' && (rank.ladder_position || Infinity) < (than.ladder_position || Infinity);
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
      // A number of days, or 'season'
      range: 'season',
      // Which rank stands for a day: 'peak' or 'end'
      dayRank: 'end',
      // The "How to get your rank" panel
      showRankHelp: false,
      chart: null,
      // Where the plot starts, so the toggle sits just above its top-left corner
      plot: { left: 0, top: 0 },
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
    peak(){
      return this.rankedPeriods.reduce((best, period) => (!best || isBetter(period, best) ? period : best), null);
    },
    // The header follows the toggle: the season, or the days the chart covers
    rangeLabel(){
      const span = this.chartWindow;
      if(this.range == 'season' || !span){
        return this.data?.season;
      }
      const first = span.start;
      const last = span.end;
      return first.format(first.year() == last.year() ? 'MMM D' : 'MMM D, YYYY') + ' – ' + last.format('MMM D, YYYY');
    },
    // Every rank this season was read on the same day, so there's no history to draw yet
    oneDay(){
      const days = new Set(this.rankedPeriods.flatMap(period => [period.first_game_date, period.last_game_date].map(date => this.localDay(date).format('YYYY-MM-DD'))));
      return days.size == 1;
    },
    games(){
      return (this.data?.periods || []).reduce((total, period) => total + period.read_count, 0);
    },
    // The days the chart covers, ending on the last day a rank was read; start and end are local midnights
    chartWindow(){
      if(!this.data?.current){
        return null;
      }
      const end = this.localDay(this.data.current.last_game_date);
      // This season: from the first rank read this season
      const first = this.rankedPeriods.reduce((earliest, period) => (!earliest || period.first_game_date < earliest ? period.first_game_date : earliest), null);
      const count = this.range == 'season' ? (first ? end.diff(this.localDay(first), 'days') + 1 : 1) : this.range;
      return { start: end.clone().subtract(count - 1, 'days'), end };
    },
    // Each day of the window with the best rank held that day, or the one it ended on; days with no rank read are gaps
    chartDays(){
      const span = this.chartWindow;
      if(!span){
        return [];
      }
      const days = [];
      for(let day = span.start.clone(); !day.isAfter(span.end); day = day.clone().add(1, 'day')){
        const rank = this.rankedPeriods
          .filter(period => !this.localDay(period.first_game_date).isAfter(day) && !this.localDay(period.last_game_date).isBefore(day))
          .reduce((chosen, period) => {
            if(!chosen){
              return period;
            }
            return (this.dayRank == 'peak' ? isBetter(period, chosen) : period.last_game_date > chosen.last_game_date) ? period : chosen;
          }, null);
        days.push({ day, rank });
      }
      return days;
    },
  },
  methods: {
    rankName,
    localRankName,
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
      this.range = { left: 7, right: 30, third: 'season' }[side];
      this.$nextTick(() => this.drawChart());
    },
    setDayRank(side){
      this.dayRank = side == 'left' ? 'peak' : 'end';
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
      const ranked = this.chartDays.filter(day => day.rank);
      if(!ranked.length || !this.$refs.chart){
        return;
      }

      // Above Master, a step per ladder position from the worst one in range up to the best, so #8 to #4 shows as a climb.
      // A Grandmaster read without a position sits on the worst one.
      const positions = ranked.filter(day => day.rank.league == 'grandmaster' && day.rank.ladder_position).map(day => day.rank.ladder_position);
      const worst = positions.length ? Math.max(...positions) : null;
      // A single position has nothing to group, so it gets a plain "GM #5" label and no bracket
      const bracket = positions.length && Math.min(...positions) < worst;
      const stepValue = (rank) => (rank.league == 'grandmaster' && rank.ladder_position ? 27 + worst - rank.ladder_position : rankValue(rank));
      const label = (value) => (value >= 27 && worst ? (bracket ? String(worst - (value - 27)) : 'GM #' + worst) : stepLabel(value));
      const days = this.chartDays.map(day => ({ ...day, value: day.rank ? stepValue(day.rank) : null }));
      // A lone day sits in the middle, with an empty day either side
      if(days.length == 1){
        days.unshift({ day: null, rank: null, value: null });
        days.push({ day: null, rank: null, value: null });
      }
      const values = days.map(day => day.value).filter(value => value !== null);

      // Only the ranks the player had, so one division up or down is easy to see
      const min = Math.min(...values);
      const max = Math.max(...values);
      const steps = max - min + 1;

      // The plot as a slightly rounded box, edge to edge, so it lines up with the boxes below
      const clipPlot = (chart) => {
        const { ctx, width, chartArea: { top, bottom } } = chart;
        ctx.beginPath();
        ctx.roundRect(0, top, width, bottom - top, 6);
        ctx.clip();
      };

      const rankBands = {
        id: 'rankBands',
        beforeDraw(chart) {
          if(steps > 12){
            return;
          }
          const { ctx, width, scales: { y } } = chart;
          ctx.save();
          clipPlot(chart);
          for(let value = min; value <= max; value++){
            const top = y.getPixelForValue(value + 0.5);
            ctx.fillStyle = (max - value) % 2 == 0 ? '#303340' : '#272a34';
            ctx.fillRect(0, top, width, y.getPixelForValue(value - 0.5) - top);
          }
          ctx.restore();
        },
      };

      // The ladder positions read as one group: a bracket beside them and "GM" centred on it
      // The area under the line keeps to the same rounded corners
      const roundedPlot = {
        id: 'roundedPlot',
        beforeDatasetsDraw(chart) {
          chart.ctx.save();
          clipPlot(chart);
        },
        afterDatasetsDraw(chart) {
          chart.ctx.restore();
        },
      };

      const gmTop = max;
      const gmBottom = Math.max(min, 27);
      const gmGroup = {
        id: 'gmGroup',
        afterDraw(chart) {
          if(!bracket || gmTop < 27){
            return;
          }
          const { ctx, scales: { y } } = chart;
          const top = y.getPixelForValue(gmTop + 0.5) + 6;
          const bottom = y.getPixelForValue(gmBottom - 0.5) - 6;
          ctx.save();
          // Just past the widest position number; the axis itself is as wide as "Master"
          ctx.font = toFont(y.options.ticks.font).string;
          const x = y.left + 16 + ctx.measureText('999').width + 16;
          ctx.strokeStyle = '#9ca3af';
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(x - 4, top);
          ctx.lineTo(x, top);
          ctx.lineTo(x, bottom);
          ctx.lineTo(x - 4, bottom);
          ctx.stroke();
          ctx.fillStyle = '#9ca3af';
          ctx.textAlign = 'left';
          ctx.textBaseline = 'middle';
          ctx.fillText('GM', x + 6, (top + bottom) / 2);
          ctx.restore();
        },
      };

      this.chart = markRaw(new Chart(this.$refs.chart.getContext('2d'), {
        type: 'line',
        plugins: [rankBands, roundedPlot, gmGroup, { id: 'plotCorner', afterLayout: (chart) => { this.plot = { left: 0, top: chart.chartArea.top }; } }],
        data: {
          labels: days.map(day => (day.day ? day.day.format('DD MMM.') : '')),
          datasets: [{
            data: days.map(day => day.value),
            borderColor: '#008b8b',
            borderWidth: 2,
            backgroundColor: 'rgba(0, 139, 139, 0.35)',
            fill: 'start',
            spanGaps: true,
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
          // Room above for the Day's Peak / End of Day toggle, and right of the axis for the GM group label
          layout: { padding: { top: 28, right: bracket && max >= 27 ? 36 : 0 } },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#393e4d',
              padding: 12,
              displayColors: false,
              callbacks: {
                title: (items) => days[items[0].dataIndex].day.format('MMM D, YYYY'),
                label: (item) => localRankName(days[item.dataIndex].rank),
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
              // A label per step while they fit; a long range gets Chart.js's own spacing
              afterBuildTicks: (axis) => {
                if(steps > 12){
                  axis.ticks = axis.ticks.filter(tick => Number.isInteger(tick.value) && tick.value >= min && tick.value <= max);
                  return;
                }
                axis.ticks = [];
                for(let value = min; value <= max; value++){
                  axis.ticks.push({ value });
                }
              },
              ticks: { padding: 16, color: '#9ca3af', precision: 0, callback: (value) => label(value) },
            },
          },
        },
      }));
    },
  }
}
</script>
