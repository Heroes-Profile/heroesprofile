<template>
  <div>
    <h2 class="text-xl font-bold mb-2 text-teal">Activity over the game</h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-2"
      :labels="minutes.map(m => m + ' min')"
      :yLabel="'Ability commands per minute'"
      :series="[
        { label: 'You', data: apm('you'), color: 'teal' },
        { label: 'Baseline', data: apm('baseline'), color: 'yellow' },
      ]"
    ></multi-series-chart>
    <p class="text-center text-sm mb-8 text-gray-light">Counts ability and attack orders, not mouse movement.</p>

    <h2 class="text-xl font-bold mb-2 text-teal">When you pick talents</h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-2"
      type="bar"
      :labels="tierLabels"
      :yLabel="'Game time (seconds)'"
      :series="[
        { label: 'You', data: talents('you'), color: 'teal' },
        { label: 'Baseline', data: talents('baseline'), color: 'yellow' },
      ]"
    ></multi-series-chart>
    <p class="text-center text-sm mb-8 text-gray-light">Average game time of each pick. Choose a hero in the filters to compare like for like.</p>

    <h2 class="text-xl font-bold mb-2 text-teal">Ability usage</h2>
    <p v-if="!abilityHeroes.length" class="text-center text-gray-light">No ability data for these filters yet.</p>
    <div v-for="hero in abilityHeroes" :key="hero.id" class="max-w-[1200px] mx-auto mb-6">
      <h3 class="text-lg font-bold mb-1">{{ heroName(hero.id) }} <span class="text-sm text-gray-light">({{ hero.games }} games)</span></h3>
      <table class="min-w-0 w-full responsive-table">
        <thead>
          <tr>
            <th>Ability</th>
            <th>Presses per game</th>
            <th>Casts per game</th>
            <th>Baseline presses</th>
            <th>Baseline casts</th>
            <th>Games used in</th>
            <th>Baseline games used in</th>
            <th>First use</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="row in hero.abilities" :key="row.ability_id">
            <tr class="cursor-pointer" :class="{ 'font-bold': isSelected(hero.id, row) }" @click="selectAbility(hero.id, row)">
              <td>
                <div class="flex items-center gap-2">
                  <img v-if="row.icon" :src="`/images/talents/${row.icon}`" :alt="row.name" class="w-8 h-8" />
                  <span>{{ row.name }} <span class="text-sm text-gray-light">{{ slotLabel(row.slot) }}</span></span>
                </div>
              </td>
              <td>{{ perGame(row.you) }}</td>
              <td>{{ castsPerGame(row.you) }}</td>
              <td>{{ perGame(row.baseline) }}</td>
              <td>{{ castsPerGame(row.baseline) }}</td>
              <td>{{ row.you ? share(row.you.games_used, row.you.games) : '-' }}</td>
              <td>{{ row.baseline ? share(row.baseline.games_used, row.baseline.games) : '-' }}</td>
              <td>{{ row.you ? clock(row.you.avg_first_use) : '-' }}</td>
            </tr>
            <!-- The clicked ability's graph opens right under it; clicking the row again closes it -->
            <tr v-if="isSelected(hero.id, row)">
              <td colspan="8">
                <loading-component v-if="timelineLoading"></loading-component>
                <p v-else-if="timelineError" class="text-center text-gray-light">{{ timelineError }}</p>
                <p v-else-if="timeline && !timelineHasData" class="text-center text-gray-light">Per-minute data for this ability is still being filled in for your games. Check back soon.</p>
                <template v-else-if="timeline">
                  <multi-series-chart
                    :labels="timelineMinutes.map(m => m + ' min')"
                    :yLabel="'Per game'"
                    :y2Label="'Deaths / takedowns per game'"
                    :series="timelineSeries"
                  ></multi-series-chart>
                  <p class="text-center text-sm text-gray-light">{{ row.name }} over the game. Click a legend item to show or hide it, e.g. your deaths and takedowns.</p>
                </template>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <p class="text-center text-sm mb-8 text-gray-light">
      Click an ability for its usage over the game. Presses count every key press; casts drop repeat presses within 2 seconds and presses while the ability is still on its base cooldown (with slack for cooldown reduction), so abilities that reset early can be undercounted.<span v-if="moreHeroes"> Showing your {{ abilityHeroes.length }} most played heroes; choose a hero in the filters to see another.</span>
    </p>
  </div>
</template>

<script>
import analytics from '../../utils/deepAnalyticsFormat.js';

export default {
  name: 'DeepAnalyticsActivity',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
    // The page's current filters as sent to /Analytics/Data
    filterParams: Object,
  },
  data() {
    return {
      tierLabels: ['Level 1', 'Level 4', 'Level 7', 'Level 10', 'Level 13', 'Level 16', 'Level 20'],
      slotOrder: ['Basic', 'Trait', 'Heroic', 'Activable', 'Mount', 'Hearth'],
      maxHeroes: 5,
      selected: null,
      timeline: null,
      timelineLoading: false,
      timelineError: '',
      timelineCache: {},
    };
  },
  computed: {
    minutes() {
      return [...new Set(this.data.apm.map(r => r.minute))].sort((a, b) => a - b);
    },
    // Your heroes, most played first, each with its abilities paired you / baseline
    heroesPlayed() {
      return (this.data.hero_games || [])
        .filter(r => r.who === 'you')
        .sort((a, b) => b.games - a.games)
        .map(r => ({ id: r.hero_id, games: r.games }));
    },
    abilityHeroes() {
      const rows = this.data.ability_usage || [];
      return this.heroesPlayed.slice(0, this.maxHeroes).map(hero => {
        const byAbility = {};
        rows.filter(r => r.hero_id === hero.id).forEach(r => {
          byAbility[r.ability_id] = byAbility[r.ability_id] || { ability_id: r.ability_id, name: r.name, slot: r.slot, icon: r.icon };
          byAbility[r.ability_id][r.who] = r;
        });
        const abilities = Object.values(byAbility)
          .filter(a => a.you)
          .sort((a, b) => this.slotRank(a.slot) - this.slotRank(b.slot) || b.you.uses - a.you.uses);
        return { ...hero, abilities };
      }).filter(hero => hero.abilities.length);
    },
    timelineMinutes() {
      return [...new Set((this.timeline || []).map(r => r.minute))].sort((a, b) => a - b);
    },
    // Games loaded before per-minute counts existed have none, which reads as all zeros
    timelineHasData() {
      return (this.timeline || []).some(r => Number(r.presses) > 0);
    },
    timelineSeries() {
      const line = (who, field) => this.timelineMinutes.map(m => {
        const row = this.timeline.find(r => r.who === who && r.minute === m);
        return row && row[field] !== null ? this.round(row[field], 2) : null;
      });
      return [
        { label: 'Your presses', data: line('you', 'presses'), color: 'teal' },
        { label: 'Your casts', data: line('you', 'casts'), color: 'teal', dashed: true },
        { label: 'Baseline presses', data: line('baseline', 'presses'), color: 'yellow' },
        { label: 'Baseline casts', data: line('baseline', 'casts'), color: 'yellow', dashed: true },
        { label: 'Your deaths', data: line('you', 'deaths'), color: 'red', axis: 'right', hidden: true },
        { label: 'Your takedowns', data: line('you', 'takedowns'), color: 'blue', axis: 'right', hidden: true },
      ];
    },
    moreHeroes() {
      return this.heroesPlayed.length > this.maxHeroes;
    },
  },
  methods: {
    apm(who) {
      return this.minutes.map(m => {
        const row = this.data.apm.find(r => r.who === who && r.minute === m);
        return row ? this.round(row.commands, 1) : null;
      });
    },
    slotBase(slot) {
      return (slot || '').replace(' (sub)', '');
    },
    slotRank(slot) {
      const rank = this.slotOrder.indexOf(this.slotBase(slot));
      return rank === -1 ? this.slotOrder.length : rank;
    },
    slotLabel(slot) {
      const base = this.slotBase(slot);
      return base === 'Activable' ? 'Active' : base;
    },
    castsPerGame(row) {
      // Games loaded before casts were recorded have none; only show casts once every game has them
      return row && row.games && row.casts_games === row.games_used ? this.round(row.casts / row.games, 1) : '-';
    },
    isSelected(heroId, row) {
      return this.selected && this.selected.hero === heroId && this.selected.ability === row.ability_id;
    },
    async selectAbility(heroId, row) {
      if (this.isSelected(heroId, row)) {
        this.selected = null;
        return;
      }
      this.selected = { hero: heroId, ability: row.ability_id, name: row.name };
      const params = { ...this.filterParams, ability_hero: heroId, ability_id: row.ability_id };
      const key = JSON.stringify(params);
      this.timelineError = '';
      if (this.timelineCache[key]) {
        this.timeline = this.timelineCache[key];
        return;
      }
      this.timeline = null;
      this.timelineLoading = true;
      try {
        const response = await this.$axios.post('/Analytics/Data/ability', params);
        this.timelineCache[key] = response.data.timeline;
        if (this.isSelected(heroId, row)) {
          this.timeline = response.data.timeline;
        }
      } catch (e) {
        this.timelineError = 'Could not load this ability. Please try again in a minute.';
      } finally {
        this.timelineLoading = false;
      }
    },
    perGame(row) {
      return row && row.games ? this.round(row.uses / row.games, 1) : '-';
    },
    talents(who) {
      return [0, 1, 2, 3, 4, 5, 6].map(tier => {
        const row = this.data.talent_timing.find(r => r.who === who && r.tier === tier);
        return row ? Math.round(row.avg_seconds) : null;
      });
    },
  },
};
</script>
