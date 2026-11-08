<template>
  <div>
    <p v-if="!rowFor('you', 'all').games" class="text-center text-gray-light mb-8">
      Positioning is worked out overnight, so {{ game ? 'this match appears' : 'your newest games appear' }} here the day after {{ game ? 'it is' : 'they are' }} processed.
    </p>

    <template v-else>
      <!-- The answers first -->
      <div class="flex flex-wrap justify-center gap-4 mb-8">
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Lane Time'" :value="percent(rowFor('you', 'all').lane_share)" :secondstat="percent(rowFor('global', 'all').lane_share)" :secondcaption="'global'" color="teal"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Laning Alone'" :value="percent(rowFor('you', 'all').solo_lane_share)" :secondstat="percent(rowFor('global', 'all').solo_lane_share)" :secondcaption="'global'" color="yellow"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Front Deaths / 10 Min'" :value="orDash(round(fights('you').front_deaths_10, 2))" :secondstat="orDash(round(fights('global').front_deaths_10, 2))" :secondcaption="'global'" color="red"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Objective Arrival'" :value="arrival('you')" :secondstat="arrival('global')" :secondcaption="'global'" color="teal"></stat-box>
      </div>

      <!-- Laning -->
      <analytics-heading :title="'Laning'" :takeaway="laningTakeaway">
        Laning runs all game, so it is measured across the whole game and split into early (to 10:00), mid (10:00-20:00) and late (20:00 on). It uses the game's exact hero positions only, not estimates, and only your "open" time: not in a fight (10 s before its first death to 5 s after its last) and not at base. In a lane means within 8 map units of the lane's minion path. Laning alone means no teammate within 15 units. How far up the lane runs from 0 at your core to 1 at the enemy's, so 0.5 is the middle.
      </analytics-heading>
      <div class="grid md:grid-cols-2 gap-6 mb-4">
        <multi-series-chart type="bar" format="percent" :min="0" :max="100" :labels="phaseLabels" :title="'Share of open time in a lane'" :series="phaseSeries('lane_share')"></multi-series-chart>
        <multi-series-chart type="bar" :horizontal="true" :stacked="true" :min="0" :max="100" format="percent" :labels="whos().map(whoLabel)" :title="'Where your open time goes'" :series="timeSplit"></multi-series-chart>
      </div>
      <div class="grid md:grid-cols-2 gap-6 mb-4">
        <multi-series-chart type="bar" format="percent" :min="0" :max="100" :labels="phaseLabels" :title="'Share of lane time alone'" :series="phaseSeries('solo_lane_share')"></multi-series-chart>
        <multi-series-chart type="bar" format="number" :min="0" :max="1" :reference="0.5" :referenceLabel="'Middle'" :labels="phaseLabels" :title="'How far up the lane (0 your core, 1 theirs)'" :series="phaseSeries('lane_push', 2)"></multi-series-chart>
      </div>
      <analytics-numbers>
        <table class="min-w-0 w-full responsive-table mb-4">
          <thead>
            <tr><th>Phase</th><th v-for="w in whos()" :key="w">{{ whoLabel(w) }}: in lane / at camps / elsewhere</th></tr>
          </thead>
          <tbody>
            <tr v-for="(phase, i) in phases" :key="phase">
              <td>{{ phaseLabels[i] }}</td>
              <td v-for="w in whos()" :key="w">
                {{ percent(rowFor(w, phase).lane_share) }} / {{ percent(rowFor(w, phase).camp_share) }} / {{ percent(rowFor(w, phase).away_share) }}
              </td>
            </tr>
          </tbody>
        </table>
        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr><th>Whole game</th><th v-for="w in whos()" :key="w">{{ whoLabel(w) }}</th></tr>
          </thead>
          <tbody>
            <tr><td>Time in your main lane</td><td v-for="w in whos()" :key="w">{{ percent(rowFor(w, 'all').main_lane_share) }}</td></tr>
            <tr><td>Lane changes per 10 min</td><td v-for="w in whos()" :key="w">{{ orDash(round(rowFor(w, 'all').switches_10, 1)) }}</td></tr>
            <tr><td>Lane time past the middle</td><td v-for="w in whos()" :key="w">{{ percent(rowFor(w, 'all').enemy_side_share) }}</td></tr>
            <tr><td>Main lane</td><td v-for="w in whos()" :key="w">{{ mainLanes(w) }}</td></tr>
          </tbody>
        </table>
      </analytics-numbers>

      <!-- Teamfights -->
      <analytics-heading :title="'Teamfights'" :takeaway="fightTakeaway">
        A fight is two or more hero deaths with no more than 15 s between them. For each fight you were at, your position is your average over the 10 s before its first death, compared with your team's centre along the line towards the enemy team's centre (only teammates at the fight count). Front and back of the team need at least 3 teammates there. Arrived late means you were first within 25 units of where the fight's deaths happened only after it started. Front deaths are times you were your team's furthest-forward death in a fight where your team lost two or more. Positions in fights are mostly estimated from move orders, which players give constantly in fights. Games with fewer than 3 such fights are left out.
      </analytics-heading>
      <div class="grid md:grid-cols-2 gap-6 mb-4">
        <multi-series-chart type="bar" format="percent" :min="0" :max="100" :labels="['Front of your team', 'Back of your team', 'Arrived late']" :title="'Where you are when fights start'" :series="fightSeries"></multi-series-chart>
        <multi-series-chart type="bar" format="number" :labels="['Ahead of your team (units)', 'Front deaths / 10 min']" :title="'How far forward'" :series="forwardSeries"></multi-series-chart>
      </div>
      <p v-if="heroNote" class="text-center text-sm mb-8">{{ heroNote }}</p>

      <!-- Objectives -->
      <analytics-heading :title="'Objectives and camps'" :takeaway="objectiveTakeaway">
        Objective and camp presence: the share of your team's camp captures and enemy structure kills (and of camps alone) you were at, as on the Coaching tab. Arrival: at the ones you were at, how many seconds before the capture you were first within 30 units, looking back up to 60 s. Contesting: the share of the enemy team's camp captures you were within 30 units of, from 15 s before to 5 s after. Map objectives (tributes, shrines, altars) aren't in this yet: the replay doesn't say where or when they start.
      </analytics-heading>
      <multi-series-chart class="mb-8" type="bar" format="percent" :min="0" :max="100" :labels="['Objectives you were at', 'Camps you were at', 'Enemy camps contested']" :title="'Being there'" :series="objectiveSeries"></multi-series-chart>
    </template>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

export default {
  name: 'AnalyticsPositioning',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  data() {
    return {
      phases: ['early', 'mid', 'late'],
      phaseLabels: ['Early (to 10:00)', 'Mid (10-20 min)', 'Late (20:00 on)'],
    };
  },
  computed: {
    timeSplit() {
      const rows = this.whos().map(w => this.rowFor(w, 'all'));
      const pct = v => (v === null || v === undefined ? null : this.round(100 * v, 1));
      return [
        { label: 'In a lane', data: rows.map(r => pct(r.lane_share)), color: 'teal' },
        { label: 'At camps', data: rows.map(r => pct(r.camp_share)), color: 'yellow' },
        { label: 'Elsewhere', data: rows.map(r => pct(r.away_share)), color: 'gray' },
      ];
    },
    fightSeries() {
      return this.whos().map(w => {
        const f = this.fights(w);
        return { label: this.whoLabel(w), data: [f.front_share, f.back_share, f.late_share].map(v => (v === null || v === undefined ? null : this.round(100 * v, 1))), color: this.whoColor(w), n: [f.games, f.games, f.games] };
      });
    },
    forwardSeries() {
      return this.whos().map(w => {
        const f = this.fights(w);
        return { label: this.whoLabel(w), data: [this.round(f.fight_ahead, 1), this.round(f.front_deaths_10, 2)], color: this.whoColor(w), n: [f.games, f.games] };
      });
    },
    objectiveSeries() {
      return this.whos().map(w => {
        const o = this.objectives(w);
        return { label: this.whoLabel(w), data: [o.objective_presence, o.camp_presence, o.contest_share].map(v => (v === null || v === undefined ? null : this.round(100 * v, 1))), color: this.whoColor(w), n: [o.games, o.games, o.games] };
      });
    },
    laningTakeaway() {
      const [you, global] = [this.rowFor('you', 'all'), this.rowFor('global', 'all')];
      if (you.lane_share === null || you.lane_share === undefined) return null;
      return (this.game ? 'This match you spent ' : 'You spend ') + this.percent(you.lane_share) + ' of your open time in a lane'
        + this.versus(100 * you.lane_share, global.lane_share === undefined ? null : 100 * global.lane_share, '%')
        + ', ' + this.percent(you.solo_lane_share) + ' of it alone.';
    },
    fightTakeaway() {
      const [you, global] = [this.fights('you'), this.fights('global')];
      if (!you.games) return null;
      const front = you.front_deaths_10;
      const gFront = global.front_deaths_10;
      const where = you.front_share > you.back_share + 0.05 ? 'more often at the front of your team than the back' : you.back_share > you.front_share + 0.05 ? 'more often at the back of your team than the front' : 'about as often at the front of your team as the back';
      const deaths = front === null || gFront === null || gFront === undefined ? '' : front > gFront * 1.15 ? ', and die at the front more than most players' : front < gFront * 0.85 ? ', and die at the front less than most players' : '';
      return 'When fights start you are ' + where + deaths + '.';
    },
    objectiveTakeaway() {
      const [you, global] = [this.objectives('you'), this.objectives('global')];
      if (you.objective_presence === null || you.objective_presence === undefined) return null;
      return 'You were at ' + this.percent(you.objective_presence) + " of your team's objectives"
        + this.versus(100 * you.objective_presence, global.objective_presence === undefined ? null : 100 * global.objective_presence, '%')
        + (you.objective_lead ? ', getting there ' + Math.round(you.objective_lead) + ' s before the capture on average.' : '.');
    },
    // With one hero: which way being further forward goes with winning on it
    heroNote() {
      const weight = (this.data.weights || []).find(r => r.metric === 'fight_ahead');
      if (!weight || weight.r_win === null) return null;
      const who = this.game ? this.game.hero : 'this hero';
      if (Math.abs(weight.r_win) < 0.05) return 'On ' + who + ', standing further forward or back in fights makes little difference to winning.';
      return 'On ' + who + ', standing further ' + (weight.r_win > 0 ? 'forward' : 'back') + ' in fights goes ' + (Math.abs(weight.r_win) < 0.15 ? 'slightly ' : '') + 'with winning (across our global sample).';
    },
  },
  methods: {
    rowFor(who, phase) {
      return (this.data.laning || []).find(r => r.who === who && r.phase === phase) || {};
    },
    fights(who) {
      return this.pick(this.data.fights, who);
    },
    objectives(who) {
      return this.pick(this.data.objectives, who);
    },
    phaseSeries(field, digits = 1) {
      const percent = field !== 'lane_push';
      return this.whos().map(w => ({
        label: this.whoLabel(w),
        data: this.phases.map(p => {
          const v = this.rowFor(w, p)[field];
          return v === null || v === undefined ? null : percent ? this.round(100 * v, digits) : this.round(v, digits);
        }),
        color: this.whoColor(w),
        n: this.phases.map(p => this.rowFor(w, p).games ?? null),
      }));
    },
    arrival(who) {
      const v = this.objectives(who).objective_lead;
      return v === null || v === undefined ? '-' : Math.round(v) + ' s early';
    },
    mainLanes(who) {
      const rows = (this.data.main_lanes || []).filter(r => r.who === who);
      const total = rows.reduce((sum, r) => sum + Number(r.games), 0);
      if (!total) return '-';
      return rows.sort((a, b) => b.games - a.games).map(r => r.lane_name + ' ' + this.round((100 * r.games) / total) + '%').join(', ');
    },
  },
};
</script>
