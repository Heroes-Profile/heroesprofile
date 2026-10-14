<template>
  <div>
    <!-- Level lead -->
    <h2 class="text-xl font-bold mb-2 text-teal">Level lead over the game</h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      :labels="leadMinutes"
      :yLabel="'Levels ahead'"
      :series="[
        { label: 'Your wins', data: lead('you', 'lead_won'), color: 'teal' },
        { label: 'Your losses', data: lead('you', 'lead_lost'), color: 'red' },
        { label: 'Baseline wins', data: lead('baseline', 'lead_won'), color: 'blue' },
        { label: 'Baseline losses', data: lead('baseline', 'lead_lost'), color: 'yellow' },
      ]"
    ></multi-series-chart>

    <!-- Comebacks -->
    <h2 class="text-xl font-bold mb-2 text-teal">Win rate by level state</h2>
    <table class="min-w-0 w-full responsive-table max-w-[1200px] mx-auto mb-8">
      <thead>
        <tr><th>At</th><th>Behind (you)</th><th>Behind (baseline)</th><th>Even (you)</th><th>Even (baseline)</th><th>Ahead (you)</th><th>Ahead (baseline)</th></tr>
      </thead>
      <tbody>
        <tr v-for="minute in [10, 15, 20]" :key="minute">
          <td>{{ minute }} min</td>
          <template v-for="state in ['behind', 'even', 'ahead']" :key="state">
            <td>{{ comeback('you', minute, state) }}</td>
            <td>{{ comeback('baseline', minute, state) }}</td>
          </template>
        </tr>
      </tbody>
    </table>

    <!-- Talent tier windows -->
    <h2 class="text-xl font-bold mb-2 text-teal">Talent tier races</h2>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First to 10'" :value="share(tiers.you.first_to_10, tiers.you.games)" :secondstat="share(tiers.base.first_to_10, tiers.base.games)" :secondcaption="'baseline'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate When First to 10'" :value="percent(tiers.you.win_rate_first_to_10)" :secondstat="percent(tiers.base.win_rate_first_to_10)" :secondcaption="'baseline'" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First to 20'" :value="share(tiers.you.first_to_20, tiers.you.games)" :secondstat="share(tiers.base.first_to_20, tiers.base.games)" :secondcaption="'baseline'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate When First to 20'" :value="percent(tiers.you.win_rate_first_to_20)" :secondstat="percent(tiers.base.win_rate_first_to_20)" :secondcaption="'baseline'" color="yellow"></stat-box>
    </div>

    <!-- Objectives -->
    <h2 class="text-xl font-bold mb-2 text-teal">Objectives, camps and forts</h2>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Objective'" :value="clock(objectives.you.avg_first_objective)" :secondstat="clock(objectives.base.avg_first_objective)" :secondcaption="'baseline'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Merc Camp'" :value="clock(objectives.you.avg_first_camp)" :secondstat="clock(objectives.base.avg_first_camp)" :secondcaption="'baseline'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Fort Taken'" :value="clock(objectives.you.avg_first_fort)" :secondstat="clock(objectives.base.avg_first_fort)" :secondcaption="'baseline'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Win Rate, First Objective'" :value="percent(objectives.you.win_rate_first_objective)" :secondstat="percent(objectives.base.win_rate_first_objective)" :secondcaption="'baseline'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Win Rate, First Camp'" :value="percent(objectives.you.win_rate_first_camp)" :secondstat="percent(objectives.base.win_rate_first_camp)" :secondcaption="'baseline'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Win Rate, First Fort'" :value="percent(objectives.you.win_rate_first_fort)" :secondstat="percent(objectives.base.win_rate_first_fort)" :secondcaption="'baseline'" color="yellow"></stat-box>
    </div>

    <!-- XP sources -->
    <h2 class="text-xl font-bold mb-2 text-teal">Team XP sources at game end</h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      :labels="['Minions', 'Merc camps', 'Structures', 'Heroes', 'Passive']"
      :series="[
        { label: 'Your wins', data: xp('you', true), color: 'teal' },
        { label: 'Your losses', data: xp('you', false), color: 'red' },
        { label: 'Baseline wins', data: xp('baseline', true), color: 'blue' },
        { label: 'Baseline losses', data: xp('baseline', false), color: 'yellow' },
      ]"
    ></multi-series-chart>

    <!-- Takedowns -->
    <h2 class="text-xl font-bold mb-2 text-teal">Takedown participation</h2>
    <div class="flex flex-wrap justify-center gap-4 mb-4">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Participation'" :value="percent(takedowns.you.participation)" :secondstat="percent(takedowns.base.participation)" :secondcaption="'baseline'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Takedowns per Game'" :value="round(takedowns.you.takedowns)" :secondstat="round(takedowns.base.takedowns)" :secondcaption="'baseline'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Solo Kills per Game'" :value="round(takedowns.you.solo_kills, 2)" :secondstat="round(takedowns.base.solo_kills, 2)" :secondcaption="'baseline'" color="teal"></stat-box>
    </div>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      :labels="['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min']"
      :title="'Share of enemy deaths you take part in, by game phase'"
      :yLabel="'%'"
      :series="[
        { label: 'You', data: takedownPhases('you'), color: 'teal' },
        { label: 'Baseline', data: takedownPhases('baseline'), color: 'yellow' },
      ]"
    ></multi-series-chart>
  </div>
</template>

<script>
import analytics from '../../utils/deepAnalyticsFormat.js';

export default {
  name: 'DeepAnalyticsTimelines',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  computed: {
    leadMinutes() {
      const minutes = [...new Set(this.data.level_lead.map(r => r.minute))].sort((a, b) => a - b);
      return minutes.map(m => m + ' min');
    },
    tiers() {
      return { you: this.pick(this.data.tier_windows, 'you'), base: this.pick(this.data.tier_windows, 'baseline') };
    },
    objectives() {
      return { you: this.pick(this.data.objective_timing, 'you'), base: this.pick(this.data.objective_timing, 'baseline') };
    },
    takedowns() {
      return { you: this.pick(this.data.takedowns, 'you'), base: this.pick(this.data.takedowns, 'baseline') };
    },
  },
  methods: {
    lead(who, field) {
      const minutes = [...new Set(this.data.level_lead.map(r => r.minute))].sort((a, b) => a - b);
      return minutes.map(m => {
        const row = this.data.level_lead.find(r => r.who === who && r.minute === m);
        return row ? this.round(row[field], 2) : null;
      });
    },
    comeback(who, minute, state) {
      const row = this.data.comebacks.find(r => r.who === who && r.minute === minute && r.state === state);
      return row ? this.percent(row.win_rate) + ' (' + row.games + ')' : '-';
    },
    xp(who, won) {
      const row = this.data.xp_sources.find(r => r.who === who && r.winner === won);
      return row ? ['minion', 'creep', 'structure', 'hero', 'trickle'].map(k => Math.round(row[k])) : [];
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
