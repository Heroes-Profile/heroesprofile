<template>
  <div>
    <!-- The answers first -->
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First to 10'" :value="share(tiers.you.first_to_10, tiers.you.games)" :secondstat="share(tiers.base.first_to_10, tiers.base.games)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Comeback Win %'" :value="comebackRate('you', 15, 'behind')" :secondstat="comebackRate('global', 15, 'behind')" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Lead at 15 Min'" :value="signed(leadAt('you', 15, 'lead'))" :secondstat="signed(leadAt('global', 15, 'lead'))" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Objective Win %'" :value="percent(objectives.you.win_rate_first_objective)" :secondstat="percent(objectives.base.win_rate_first_objective)" :secondcaption="'global'" color="yellow"></stat-box>
    </div>

    <!-- Level lead -->
    <analytics-heading :title="'Level lead over the game'" :takeaway="leadTakeaway">
      How your games unfold over time: team levels, talent tier races, objectives, experience and kills. Each number is your average over the games matching the filters, next to a global sample of all players on the same filters. Team level and experience are shared by the whole team, so most of these reflect your team, not just you.
      <br /><br />
      This chart is your team's level minus the enemy team's level at each minute, averaged over the games still running at that minute. Above 0 means ahead. Late minutes come from fewer, longer games, so they jump around more.
    </analytics-heading>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      :labels="leadMinutes"
      :yLabel="'Levels ahead'"
      :reference="0"
      :referenceLabel="'Even'"
      :series="leadSeries"
    ></multi-series-chart>

    <!-- Comebacks -->
    <analytics-heading :title="'Win rate by level state'" :takeaway="comebackTakeaway">
      At 10, 15 and 20 minutes each game is marked behind, even or ahead in team level. Each bar is the win rate from that state; hover for the number of games. Games that ended before the checkpoint are left out.
    </analytics-heading>
    <multi-series-chart
      class="max-w-[1200px] mx-auto"
      type="bar"
      format="percent"
      :reference="50"
      :referenceLabel="'50%'"
      :labels="comebackChart.labels"
      :series="comebackChart.series"
    ></multi-series-chart>
    <analytics-numbers>
      <table class="min-w-0 w-full responsive-table">
        <thead>
          <tr><th>At</th><th>Behind (you)</th><th>Behind (global)</th><th>Even (you)</th><th>Even (global)</th><th>Ahead (you)</th><th>Ahead (global)</th></tr>
        </thead>
        <tbody>
          <tr v-for="minute in checkpoints" :key="minute">
            <td>{{ minute }} min</td>
            <template v-for="state in states" :key="state">
              <td>{{ comeback('you', minute, state) }}</td>
              <td>{{ comeback('global', minute, state) }}</td>
            </template>
          </tr>
        </tbody>
      </table>
    </analytics-numbers>

    <!-- Talent tier windows -->
    <analytics-heading :title="'Talent tier races'" :takeaway="tierTakeaway">
      First to 10 / 20: share of games where your team reached level 10 (or 20) before the enemy; reaching it at the same second does not count. Win %: your win rate in those games. Time ahead: how long your team had the level before the enemy got it (or the game ended), averaged over games where your team reached it; negative means the enemy usually got there first.
    </analytics-heading>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First to 10'" :value="share(tiers.you.first_to_10, tiers.you.games)" :secondstat="share(tiers.base.first_to_10, tiers.base.games)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First-10 Win %'" :value="percent(tiers.you.win_rate_first_to_10)" :secondstat="percent(tiers.base.win_rate_first_to_10)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Ahead to 10'" :value="signedClock(tiers.you.avg_seconds_ahead_10)" :secondstat="signedClock(tiers.base.avg_seconds_ahead_10)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First to 20'" :value="share(tiers.you.first_to_20, tiers.you.games)" :secondstat="share(tiers.base.first_to_20, tiers.base.games)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First-20 Win %'" :value="percent(tiers.you.win_rate_first_to_20)" :secondstat="percent(tiers.base.win_rate_first_to_20)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Ahead to 20'" :value="signedClock(tiers.you.avg_seconds_ahead_20)" :secondstat="signedClock(tiers.base.avg_seconds_ahead_20)" :secondcaption="'global'" color="teal"></stat-box>
    </div>

    <!-- Objectives -->
    <analytics-heading :title="'Objectives, camps and forts'" :takeaway="objectiveTakeaway">
      Times are the average game time of your team's first map objective, first mercenary camp capture and first enemy fort destroyed, in games where it happened. Win % is your win rate in games where your team got it before the enemy. Objectives differ by map, so filter to one map for a fair comparison.
    </analytics-heading>
    <div class="grid md:grid-cols-2 gap-6 mb-8">
      <multi-series-chart type="bar" format="mmss" :labels="objectiveLabels" :title="'When your team gets it first'" :yLabel="'Game time'" :series="objectiveTimes"></multi-series-chart>
      <multi-series-chart type="bar" format="percent" :reference="50" :referenceLabel="'50%'" :labels="objectiveLabels" :title="'Win % when your team gets it first'" :series="objectiveWins"></multi-series-chart>
    </div>

    <!-- XP sources -->
    <analytics-heading :title="'Team XP sources at game end'" :takeaway="xpTakeaway">
      Your team's total experience at the end of the game, split by where it came from. Passive is the experience every team gains automatically over time. Averaged separately for wins and losses.
    </analytics-heading>
    <div class="grid md:grid-cols-2 gap-6 mb-8">
      <multi-series-chart type="bar" :labels="xpLabels" :title="'Wins'" :yLabel="'Experience'" :series="xpWon"></multi-series-chart>
      <multi-series-chart type="bar" :labels="xpLabels" :title="'Losses'" :yLabel="'Experience'" :series="xpLost"></multi-series-chart>
    </div>

    <!-- Takedowns -->
    <analytics-heading :title="'Takedown participation'" :takeaway="takedownTakeaway">
      Participation is the share of your team's kills on enemy heroes that you were credited in (kill or assist). Takedowns / Game counts those kills; Solo Kills / Game counts kills where you were the only hero credited. The chart splits participation by game phase.
    </analytics-heading>
    <div class="flex flex-wrap justify-center gap-4 mb-4">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Participation'" :value="percent(takedowns.you.participation)" :secondstat="percent(takedowns.base.participation)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Takedowns/Game'" :value="round(takedowns.you.takedowns)" :secondstat="round(takedowns.base.takedowns)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Solo Kills/Game'" :value="round(takedowns.you.solo_kills, 2)" :secondstat="round(takedowns.base.solo_kills, 2)" :secondcaption="'global'" color="teal"></stat-box>
    </div>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      format="percent"
      :labels="phaseLabels"
      :title="'Share of enemy deaths you take part in, by game phase'"
      :series="takedownSeries"
    ></multi-series-chart>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

export default {
  name: 'AnalyticsTimelines',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  data() {
    return {
      checkpoints: [10, 15, 20],
      states: ['behind', 'even', 'ahead'],
      objectiveLabels: ['Objective', 'Merc camp', 'Fort'],
      xpLabels: ['Minions', 'Merc camps', 'Structures', 'Heroes', 'Passive'],
      phaseLabels: ['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min'],
    };
  },
  computed: {
    minuteList() {
      return [...new Set(this.data.level_lead.map(r => r.minute))].sort((a, b) => a - b);
    },
    leadMinutes() {
      return this.minuteList.map(m => m + ' min');
    },
    leadSeries() {
      return [
        { label: 'Your wins', data: this.lead('you', 'lead_won'), color: 'teal' },
        { label: 'Your losses', data: this.lead('you', 'lead_lost'), color: 'teal', dashed: true },
        { label: 'Global wins', data: this.lead('global', 'lead_won'), color: 'yellow' },
        { label: 'Global losses', data: this.lead('global', 'lead_lost'), color: 'yellow', dashed: true },
      ];
    },
    comebackChart() {
      const labels = [];
      const you = { data: [], n: [] };
      const global = { data: [], n: [] };
      this.checkpoints.forEach(minute => {
        this.states.forEach(state => {
          labels.push(state.charAt(0).toUpperCase() + state.slice(1) + ' at ' + minute);
          [['you', you], ['global', global]].forEach(([who, out]) => {
            const row = this.comebackRow(who, minute, state);
            out.data.push(row ? this.round(row.win_rate * 100, 1) : null);
            out.n.push(row ? row.games : null);
          });
        });
      });
      return {
        labels,
        series: [
          { label: 'You', data: you.data, n: you.n, color: 'teal' },
          { label: 'Global', data: global.data, n: global.n, color: 'yellow' },
        ],
      };
    },
    tiers() {
      return { you: this.pick(this.data.tier_windows, 'you'), base: this.pick(this.data.tier_windows, 'global') };
    },
    objectives() {
      return { you: this.pick(this.data.objective_timing, 'you'), base: this.pick(this.data.objective_timing, 'global') };
    },
    objectiveTimes() {
      const row = r => [r.avg_first_objective, r.avg_first_camp, r.avg_first_fort].map(v => this.round(v, 0));
      return [
        { label: 'You', data: row(this.objectives.you), color: 'teal' },
        { label: 'Global', data: row(this.objectives.base), color: 'yellow' },
      ];
    },
    objectiveWins() {
      const row = r => [r.win_rate_first_objective, r.win_rate_first_camp, r.win_rate_first_fort].map(v => (v === null || v === undefined ? null : this.round(v * 100, 1)));
      return [
        { label: 'You', data: row(this.objectives.you), color: 'teal' },
        { label: 'Global', data: row(this.objectives.base), color: 'yellow' },
      ];
    },
    xpWon() {
      return this.xpSeries(true);
    },
    xpLost() {
      return this.xpSeries(false);
    },
    takedowns() {
      return { you: this.pick(this.data.takedowns, 'you'), base: this.pick(this.data.takedowns, 'global') };
    },
    takedownSeries() {
      return [
        { label: 'You', data: this.takedownPhases('you'), color: 'teal' },
        { label: 'Global', data: this.takedownPhases('global'), color: 'yellow' },
      ];
    },
    leadTakeaway() {
      const won = this.leadAt('you', 15, 'lead_won');
      const lost = this.leadAt('you', 15, 'lead_lost');
      if (won === null && lost === null) return null;
      const part = (v, what) => (v === null ? null : what + ' your team is ' + Math.abs(v) + (v < 0 ? ' levels behind' : ' levels ahead'));
      return 'At 15 min, ' + [part(won, 'in wins'), part(lost, 'in losses')].filter(Boolean).join('; ') + '.';
    },
    comebackTakeaway() {
      const you = this.comebackRow('you', 15, 'behind');
      if (!you) return null;
      const base = this.comebackRow('global', 15, 'behind');
      const mine = 100 * you.win_rate;
      return 'When behind at 15 min you still win ' + this.round(mine) + '% of games' + this.versus(mine, base ? 100 * base.win_rate : null, '%') + '.';
    },
    tierTakeaway() {
      const { you, base } = this.tiers;
      if (!you.games) return null;
      const mine = (100 * you.first_to_10) / you.games;
      const global = base.games ? (100 * base.first_to_10) / base.games : null;
      return 'Your team hits level 10 first in ' + this.round(mine) + '% of games' + this.versus(mine, global, '%') + (you.win_rate_first_to_10 !== null && you.win_rate_first_to_10 !== undefined ? ', and wins ' + this.percent(you.win_rate_first_to_10) + ' of those.' : '.');
    },
    objectiveTakeaway() {
      const rates = this.objectiveWins[0].data;
      const best = rates.reduce((b, v, i) => (v !== null && (b === -1 || v > rates[b]) ? i : b), -1);
      if (best === -1) return null;
      const names = ['the first objective', 'the first merc camp', 'the first fort'];
      return 'Getting ' + names[best] + ' before the enemy matters most for you: you win ' + rates[best] + '% of those games.';
    },
    xpTakeaway() {
      const won = this.xp('you', true);
      const lost = this.xp('you', false);
      if (!won.length || !lost.length) return null;
      // Passive is the same for both teams, so leave it out
      let gap = -1;
      [0, 1, 2, 3].forEach(i => {
        if (gap === -1 || won[i] - lost[i] > won[gap] - lost[gap]) gap = i;
      });
      return 'Your wins and losses differ most in XP from ' + this.xpLabels[gap].toLowerCase() + ' (' + won[gap].toLocaleString('en-US') + ' vs ' + lost[gap].toLocaleString('en-US') + ').';
    },
    takedownTakeaway() {
      const mine = this.takedowns.you.participation;
      if (mine === null || mine === undefined) return null;
      const global = this.takedowns.base.participation;
      return 'You take part in ' + this.percent(mine) + " of your team's kills" + this.versus(100 * mine, global === null || global === undefined ? null : 100 * global, '%') + '.';
    },
  },
  methods: {
    lead(who, field) {
      return this.minuteList.map(m => this.leadAt(who, m, field));
    },
    leadAt(who, minute, field) {
      const row = this.data.level_lead.find(r => r.who === who && r.minute === minute);
      return row ? this.round(row[field], 2) : null;
    },
    signed(value) {
      return value === null ? '-' : (value > 0 ? '+' : '') + value;
    },
    // clock() with a sign, for leads that can be negative
    comebackRow(who, minute, state) {
      return this.data.comebacks.find(r => r.who === who && r.minute === minute && r.state === state);
    },
    comeback(who, minute, state) {
      const row = this.comebackRow(who, minute, state);
      return row ? this.percent(row.win_rate) + ' (' + row.games + ')' : '-';
    },
    comebackRate(who, minute, state) {
      const row = this.comebackRow(who, minute, state);
      return row ? this.percent(row.win_rate) : '-';
    },
    xp(who, won) {
      const row = this.data.xp_sources.find(r => r.who === who && r.winner === won);
      return row ? ['minion', 'creep', 'structure', 'hero', 'trickle'].map(k => Math.round(row[k])) : [];
    },
    xpGames(who, won) {
      const row = this.data.xp_sources.find(r => r.who === who && r.winner === won);
      return this.xpLabels.map(() => (row ? row.games : null));
    },
    xpSeries(won) {
      return [
        { label: 'You', data: this.xp('you', won), color: 'teal', n: this.xpGames('you', won) },
        { label: 'Global', data: this.xp('global', won), color: 'yellow', n: this.xpGames('global', won) },
      ];
    },
    takedownPhases(who) {
      return [0, 1, 2, 3, 4].map(phase => {
        const row = this.data.takedown_phases.find(r => r.who === who && r.phase === phase);
        return row && row.enemy_deaths ? this.round((100 * row.takedowns) / row.enemy_deaths, 1) : null;
      });
    },
  },
};
</script>
