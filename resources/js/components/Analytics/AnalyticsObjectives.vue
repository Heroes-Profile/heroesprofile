<template>
  <div>
    <p v-if="!takes('you').length" class="text-center text-gray-light mb-8">
      Objectives and bosses are worked out overnight, so {{ game ? 'this match appears' : 'your newest games appear' }} here the day after {{ game ? 'it is' : 'they are' }} processed.
      Only maps with a boss camp have boss takes.
    </p>

    <template v-else>
      <!-- The answers first -->
      <div class="flex flex-wrap justify-center gap-4 mb-8">
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'At Your Boss Takes'" :value="percent(share('you', true, 'present'))" :secondstat="percent(share('global', true, 'present'))" :secondcaption="'global'" color="teal"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Missed Boss Takes'" :value="percent(share('you', true, 'elsewhere'))" :secondstat="percent(share('global', true, 'elsewhere'))" :secondcaption="'global'" color="red"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Enemy Bosses Contested'" :value="percent(share('you', false, 'present'))" :secondstat="percent(share('global', false, 'present'))" :secondcaption="'global'" color="yellow"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="game ? 'Bosses, Yours - Theirs' : 'Bosses / Game, Yours - Theirs'" :value="bossCount('you')" :secondstat="bossCount('global')" :secondcaption="'global'" color="teal"></stat-box>
      </div>

      <!-- Boss takes -->
      <analytics-heading :title="'Boss takes'" :takeaway="takeTakeaway">
        For each boss camp capture, where you were over the 20 s before it. At the take: within 15 units of the camp. Dead: you died in the minute before. On the map objective: near a map objective that was up then (or, in games processed before objectives were in the replay, a map objective happening within 30 s while you were far from the boss). Defending a lane: in a lane on your own half, shown on its own because sometimes that's worth more than the boss and sometimes it isn't. Elsewhere: anything else. The win rate is of the games those takes were in.
      </analytics-heading>
      <div class="grid md:grid-cols-2 gap-6 mb-4">
        <multi-series-chart type="bar" format="percent" :min="0" :max="100" :labels="statusLabels" :title="'Where you were when your team took the boss'" :series="statusSeries(true)"></multi-series-chart>
        <multi-series-chart type="bar" format="percent" :min="0" :max="100" :labels="statusLabels" :title="'Where you were when the enemy took the boss'" :series="statusSeries(false)"></multi-series-chart>
      </div>
      <analytics-numbers>
        <table class="min-w-0 w-full responsive-table mb-4">
          <thead>
            <tr><th>{{ $t('Boss take') }}</th><th>{{ $t('Where you were') }}</th><th v-for="w in whos()" :key="w">{{ whoLabel(w) }}: share / win rate (takes)</th></tr>
          </thead>
          <tbody>
            <template v-for="own in [true, false]" :key="'own' + own">
              <tr v-for="(status, i) in statuses" :key="own + status">
                <td>{{ own ? 'Your team\'s' : 'Enemy\'s' }}</td>
                <td>{{ statusLabels[i] }}</td>
                <td v-for="w in whos()" :key="w">{{ percent(share(w, own, status)) }} / {{ percent(row(w, own, status).win_rate) }} ({{ orDash(row(w, own, status).takes) }})</td>
              </tr>
            </template>
          </tbody>
        </table>
      </analytics-numbers>

      <!-- First boss -->
      <analytics-heading :title="'The first boss'" :takeaway="firstTakeaway">
        Games by which team took the first boss and in which 3-minute stretch, and the share of those games won. Taking a boss is partly a result of being ahead, so a high win rate after an early boss is not proof that the boss won the game.
      </analytics-heading>
      <multi-series-chart class="mb-8" type="bar" format="percent" :min="0" :max="100" :labels="minuteLabels" :title="'Win rate when your team took the first boss, by game time'" :series="firstSeries"></multi-series-chart>

      <!-- Map objectives -->
      <analytics-heading :title="'Map objectives'" :takeaway="objectiveTakeaway">
        Only games processed with map objectives in the replay. Contested objectives (Immortals, tributes, shrines, seeds, payloads, capture points...): whether you were within 20 units of it while it was up. Defending: the enemy's pushing objective (a won Immortal, Dragon Knight, Garden Terror, Punisher, cavalry...), whether you were within 25 units of it at any of its recorded positions. Escorting is the same for your team's.
      </analytics-heading>
      <p v-if="!(data.presence || []).some(r => r.who === 'you')" class="text-center text-gray-light mb-8">
        None of {{ game ? 'this match\'s' : 'your' }} games have map objectives recorded yet. Games processed from now on do; older ones can be updated from the notice at the top of the page.
      </p>
      <template v-else>
        <multi-series-chart class="mb-4" type="bar" format="percent" :min="0" :max="100" :labels="sideLabels" :title="'Being there'" :series="presenceSeries"></multi-series-chart>
        <analytics-numbers>
          <table class="min-w-0 w-full responsive-table mb-8">
            <thead>
              <tr><th>{{ $t('Objective') }}</th><th v-for="w in whos()" :key="w">{{ whoLabel(w) }}: there / win when there / win when not ({{ 'n' }})</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in presenceRows" :key="r.side + r.name">
                <td>{{ sideLabels[sides.indexOf(r.side)] }}: {{ r.label }}</td>
                <td v-for="w in whos()" :key="w">
                  {{ percent(presence(w, r.side, r.name).present_share) }} / {{ percent(presence(w, r.side, r.name).win_present) }} / {{ percent(presence(w, r.side, r.name).win_absent) }}
                  ({{ orDash(presence(w, r.side, r.name).objectives) }})
                </td>
              </tr>
            </tbody>
          </table>
        </analytics-numbers>
      </template>
    </template>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';
import { objectiveLabel } from '../../utils/objectiveUnits.js';

// Boss takes and map objectives (nightly boss_takes and objective_presence)
export default {
  name: 'AnalyticsObjectives',
  mixins: [analytics],
  props: {
    data: Object,
  },
  data() {
    return {
      statuses: ['present', 'dead', 'objective', 'lane', 'elsewhere'],
      statusLabels: ['At the take', 'Dead', 'On the map objective', 'Defending a lane', 'Elsewhere'],
      sides: ['attack', 'defend', 'escort'],
      sideLabels: ['Contested', 'Defending', 'Escorting'],
    };
  },
  computed: {
    firstMinutes() {
      const minutes = new Set((this.data.first_boss || []).filter(r => r.own).map(r => Number(r.minute)));
      return [...minutes].sort((a, b) => a - b);
    },
    minuteLabels() {
      return this.firstMinutes.map(m => m + ':00-' + (m + 3) + ':00');
    },
    firstSeries() {
      return this.whos().map(w => ({
        label: this.whoLabel(w),
        data: this.firstMinutes.map(m => {
          const r = this.first(w, m);
          return r.win_rate === undefined || r.win_rate === null ? null : this.round(100 * r.win_rate, 1);
        }),
        color: this.whoColor(w),
        n: this.firstMinutes.map(m => this.first(w, m).games ?? null),
      }));
    },
    presenceRows() {
      const seen = new Map();
      (this.data.presence || []).forEach(r => seen.set(r.side + r.name, { side: r.side, name: r.name, label: objectiveLabel(r.name) }));
      return [...seen.values()].sort((a, b) => this.sides.indexOf(a.side) - this.sides.indexOf(b.side) || a.label.localeCompare(b.label));
    },
    presenceSeries() {
      return this.whos().map(w => ({
        label: this.whoLabel(w),
        data: this.sides.map(side => {
          const rows = (this.data.presence || []).filter(r => r.who === w && r.side === side);
          const n = rows.reduce((sum, r) => sum + Number(r.objectives), 0);
          return n ? this.round((100 * rows.reduce((sum, r) => sum + r.present_share * r.objectives, 0)) / n, 1) : null;
        }),
        color: this.whoColor(w),
      }));
    },
    takeTakeaway() {
      const at = this.share('you', true, 'present');
      if (at === null) return null;
      const missed = this.share('you', true, 'elsewhere');
      const lane = this.share('you', true, 'lane');
      return (this.game ? 'You were at ' : 'You are at ') + this.percent(at) + " of your team's boss takes"
        + this.versus(100 * at, this.share('global', true, 'present') === null ? null : 100 * this.share('global', true, 'present'), '%')
        + (missed ? ', elsewhere for ' + this.percent(missed) : '')
        + (lane ? ' and defending a lane for ' + this.percent(lane) : '') + '.';
    },
    firstTakeaway() {
      const yours = (this.data.first_boss || []).filter(r => r.who === 'you');
      const own = yours.filter(r => r.own).reduce((sum, r) => sum + Number(r.games), 0);
      const total = yours.reduce((sum, r) => sum + Number(r.games), 0);
      if (!total) return null;
      if (this.game) return own ? 'Your team took the first boss this match.' : 'The enemy took the first boss this match.';
      return 'Your team took the first boss in ' + this.percent(own / total) + ' of your games with one.';
    },
    objectiveTakeaway() {
      const series = this.presenceSeries.find(s => s.label === this.whoLabel('you'));
      if (!series || series.data[0] === null) return null;
      return (this.game ? 'You were at ' : 'You are at ') + series.data[0] + '% of the contested map objectives'
        + (series.data[1] !== null ? ' and ' + series.data[1] + '% of the enemy pushes you could defend.' : '.');
    },
  },
  methods: {
    takes(who) {
      return (this.data.takes || []).filter(r => r.who === who);
    },
    row(who, own, status) {
      return this.takes(who).find(r => r.own === own && r.status === status) || {};
    },
    share(who, own, status) {
      const rows = this.takes(who).filter(r => r.own === own);
      const total = rows.reduce((sum, r) => sum + Number(r.takes), 0);
      return total ? Number(this.row(who, own, status).takes || 0) / total : null;
    },
    statusSeries(own) {
      return this.whos().map(w => ({
        label: this.whoLabel(w),
        data: this.statuses.map(s => {
          const v = this.share(w, own, s);
          return v === null ? null : this.round(100 * v, 1);
        }),
        color: this.whoColor(w),
      }));
    },
    first(who, minute) {
      return (this.data.first_boss || []).find(r => r.who === who && r.own && Number(r.minute) === minute) || {};
    },
    bossCount(who) {
      const r = this.pick(this.data.per_game, who);
      if (!r.games) return '-';
      return this.round(r.own_takes, 1) + ' - ' + this.round(r.enemy_takes, 1);
    },
    presence(who, side, name) {
      return (this.data.presence || []).find(r => r.who === who && r.side === side && r.name === name) || {};
    },
  },
};
</script>
