<template>
  <div>
    <infobox class="max-w-[1200px] mx-auto mb-6" :input="'How your games unfold over time: team levels, talent tier races, objectives, experience and kills. Each number is your average over the games matching the filters, shown next to a global sample of all players on the same filters. Team level and experience are shared by the whole team, so most of these reflect your team, not just you.'"></infobox>

    <!-- Level lead -->
    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Level lead over the game
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">Your team's level minus the enemy team's level at each minute, averaged over the games still running at that minute. Above 0 means ahead. Late minutes come from fewer, longer games, so they jump around more.</p></slot>
        </round-image>
      </span>
    </h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      :labels="leadMinutes"
      :yLabel="'Levels ahead'"
      :series="[
        { label: 'Your wins', data: lead('you', 'lead_won'), color: 'teal' },
        { label: 'Your losses', data: lead('you', 'lead_lost'), color: 'red' },
        { label: 'Global wins', data: lead('global', 'lead_won'), color: 'blue' },
        { label: 'Global losses', data: lead('global', 'lead_lost'), color: 'yellow' },
      ]"
    ></multi-series-chart>

    <!-- Comebacks -->
    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Win rate by level state
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">At 10, 15 and 20 minutes each game is marked behind, even or ahead in team level. Each cell is the win rate from that state, with the number of games in brackets. Games that ended before the checkpoint are left out.</p></slot>
        </round-image>
      </span>
    </h2>
    <table class="min-w-0 w-full responsive-table max-w-[1200px] mx-auto mb-8">
      <thead>
        <tr><th>At</th><th>Behind (you)</th><th>Behind (global)</th><th>Even (you)</th><th>Even (global)</th><th>Ahead (you)</th><th>Ahead (global)</th></tr>
      </thead>
      <tbody>
        <tr v-for="minute in [10, 15, 20]" :key="minute">
          <td>{{ minute }} min</td>
          <template v-for="state in ['behind', 'even', 'ahead']" :key="state">
            <td>{{ comeback('you', minute, state) }}</td>
            <td>{{ comeback('global', minute, state) }}</td>
          </template>
        </tr>
      </tbody>
    </table>

    <!-- Talent tier windows -->
    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Talent tier races
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">First to 10 / 20: share of games where your team reached level 10 (or 20) before the enemy; reaching it at the same second does not count. Win %: your win rate in those games.</p></slot>
        </round-image>
      </span>
    </h2>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First to 10'" :value="share(tiers.you.first_to_10, tiers.you.games)" :secondstat="share(tiers.base.first_to_10, tiers.base.games)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First-10 Win %'" :value="percent(tiers.you.win_rate_first_to_10)" :secondstat="percent(tiers.base.win_rate_first_to_10)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First to 20'" :value="share(tiers.you.first_to_20, tiers.you.games)" :secondstat="share(tiers.base.first_to_20, tiers.base.games)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First-20 Win %'" :value="percent(tiers.you.win_rate_first_to_20)" :secondstat="percent(tiers.base.win_rate_first_to_20)" :secondcaption="'global'" color="yellow"></stat-box>
    </div>

    <!-- Objectives -->
    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Objectives, camps and forts
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">Times are the average game time of your team's first map objective, first mercenary camp capture and first enemy fort destroyed, in games where it happened. Win % is your win rate in games where your team got it before the enemy. Objectives differ by map, so filter to one map for a fair comparison.</p></slot>
        </round-image>
      </span>
    </h2>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Objective'" :value="clock(objectives.you.avg_first_objective)" :secondstat="clock(objectives.base.avg_first_objective)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Merc Camp'" :value="clock(objectives.you.avg_first_camp)" :secondstat="clock(objectives.base.avg_first_camp)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Fort Taken'" :value="clock(objectives.you.avg_first_fort)" :secondstat="clock(objectives.base.avg_first_fort)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Obj. Win %'" :value="percent(objectives.you.win_rate_first_objective)" :secondstat="percent(objectives.base.win_rate_first_objective)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Camp Win %'" :value="percent(objectives.you.win_rate_first_camp)" :secondstat="percent(objectives.base.win_rate_first_camp)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Fort Win %'" :value="percent(objectives.you.win_rate_first_fort)" :secondstat="percent(objectives.base.win_rate_first_fort)" :secondcaption="'global'" color="yellow"></stat-box>
    </div>

    <!-- XP sources -->
    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Team XP sources at game end
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">Your team's total experience at the end of the game, split by where it came from. Passive is the experience every team gains automatically over time. Averaged separately for wins and losses.</p></slot>
        </round-image>
      </span>
    </h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      :labels="['Minions', 'Merc camps', 'Structures', 'Heroes', 'Passive']"
      :series="[
        { label: 'Your wins', data: xp('you', true), color: 'teal' },
        { label: 'Your losses', data: xp('you', false), color: 'red' },
        { label: 'Global wins', data: xp('global', true), color: 'blue' },
        { label: 'Global losses', data: xp('global', false), color: 'yellow' },
      ]"
    ></multi-series-chart>

    <!-- Takedowns -->
    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Takedown participation
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">Participation is the share of your team's kills on enemy heroes that you were credited in (kill or assist). Takedowns / Game counts those kills; Solo Kills / Game counts kills where you were the only hero credited. The chart splits participation by game phase.</p></slot>
        </round-image>
      </span>
    </h2>
    <div class="flex flex-wrap justify-center gap-4 mb-4">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Participation'" :value="percent(takedowns.you.participation)" :secondstat="percent(takedowns.base.participation)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Takedowns / Game'" :value="round(takedowns.you.takedowns)" :secondstat="round(takedowns.base.takedowns)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Solo Kills / Game'" :value="round(takedowns.you.solo_kills, 2)" :secondstat="round(takedowns.base.solo_kills, 2)" :secondcaption="'global'" color="teal"></stat-box>
    </div>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      :labels="['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min']"
      :title="'Share of enemy deaths you take part in, by game phase'"
      :yLabel="'%'"
      :series="[
        { label: 'You', data: takedownPhases('you'), color: 'teal' },
        { label: 'Global', data: takedownPhases('global'), color: 'yellow' },
      ]"
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
  computed: {
    leadMinutes() {
      const minutes = [...new Set(this.data.level_lead.map(r => r.minute))].sort((a, b) => a - b);
      return minutes.map(m => m + ' min');
    },
    tiers() {
      return { you: this.pick(this.data.tier_windows, 'you'), base: this.pick(this.data.tier_windows, 'global') };
    },
    objectives() {
      return { you: this.pick(this.data.objective_timing, 'you'), base: this.pick(this.data.objective_timing, 'global') };
    },
    takedowns() {
      return { you: this.pick(this.data.takedowns, 'you'), base: this.pick(this.data.takedowns, 'global') };
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
