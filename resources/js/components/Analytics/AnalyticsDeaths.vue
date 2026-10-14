<template>
  <div>
    <!-- Death map -->
    <div class="mb-8">
      <h2 class="text-xl font-bold mb-2 text-teal">Where you die</h2>
      <div class="flex justify-center mb-2">
        <single-select-filter
          :key="mapsWithDeaths.join()"
          :values="mapsWithDeaths.map(map => ({ code: map, name: mapName(map) }))"
          :text="'Map'"
          :defaultValue="selectedMap"
          :disabledeselectfilters="true"
          @input-changed="selectedMap = $event.value"
        ></single-select-filter>
      </div>
      <analytics-map-view :map-name="mapName(selectedMap)" :layout="layoutFor(selectedMap)" :points="deathPoints" :legend="deathLegend"></analytics-map-view>
    </div>

    <!-- Death context -->
    <h2 class="text-xl font-bold mb-2 text-teal">How you die</h2>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Ganked'" :value="share(you.ganked, you.deaths)" :secondstat="share(base.ganked, base.deaths)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Teamfight'" :value="share(you.teamfight, you.deaths)" :secondstat="share(base.teamfight, base.deaths)" :secondcaption="'global'" color="red"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Level Behind'" :value="share(you.level_behind, you.deaths)" :secondstat="share(base.level_behind, base.deaths)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Enemy Half'" :value="share(you.enemy_half, you.deaths)" :secondstat="share(base.enemy_half, base.deaths)" :secondcaption="'global'" color="teal"></stat-box>
    </div>

    <!-- Death timing -->
    <h2 class="text-xl font-bold mb-2 text-teal">When you die</h2>
    <div class="flex flex-wrap justify-center gap-4 mb-4">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'First Death'" :value="clock(youTiming.avg_first_death)" :secondstat="clock(baseTiming.avg_first_death)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Deathless Games'" :value="percent(youTiming.deathless_share)" :secondstat="percent(baseTiming.deathless_share)" :secondcaption="'global'" color="yellow"></stat-box>
    </div>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      :labels="phaseLabels"
      :title="'Average deaths per game by game phase'"
      :series="[
        { label: 'You', data: phases(youTiming), color: 'teal' },
        { label: 'Global', data: phases(baseTiming), color: 'yellow' },
      ]"
    ></multi-series-chart>

    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      :labels="phaseLabels"
      :title="'Share of deaths on the enemy half of the map, by game phase'"
      :yLabel="'%'"
      :series="[
        { label: 'You', data: overextension('you'), color: 'teal' },
        { label: 'Global', data: overextension('global'), color: 'yellow' },
      ]"
    ></multi-series-chart>

    <!-- Nemesis -->
    <h2 class="text-xl font-bold mb-2 text-teal">Who kills you, and who you kill</h2>
    <div class="flex flex-wrap gap-6 justify-center">
      <div v-for="list in nemesisLists" :key="list.title" class="w-[45%] max-md:w-full">
        <h3 class="mb-2">{{ list.title }}</h3>
        <table class="min-w-0 w-full responsive-table">
          <thead><tr><th>{{ list.column }}</th><th>Times</th></tr></thead>
          <tbody>
            <tr v-for="row in list.rows" :key="list.title + row.label">
              <td>{{ row.label }}</td>
              <td>{{ row.n }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

export default {
  name: 'AnalyticsDeaths',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  data() {
    return {
      selectedMap: null,
      phaseLabels: ['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min'],
    };
  },
  computed: {
    you() {
      return this.pick(this.data.death_context, 'you');
    },
    base() {
      return this.pick(this.data.death_context, 'global');
    },
    youTiming() {
      return this.pick(this.data.death_timing, 'you');
    },
    baseTiming() {
      return this.pick(this.data.death_timing, 'global');
    },
    mapsWithDeaths() {
      const counts = {};
      this.data.death_points.forEach(p => {
        counts[p.game_map] = (counts[p.game_map] || 0) + 1;
      });
      return Object.keys(counts).map(Number).sort((a, b) => counts[b] - counts[a]);
    },
    deathLegend() {
      return [
        { key: 'ganked', label: 'Ganked (1-2 killers)', color: '#008b8b' },
        { key: 'teamfight', label: 'Teamfight (3+)', color: '#b33616' },
        { key: 'none', label: 'No hero killer', color: '#aaa' },
      ];
    },
    deathPoints() {
      return this.data.death_points
        .filter(p => p.game_map === this.selectedMap)
        .map(p => {
          const group = p.killers >= 3 ? 'teamfight' : p.killers >= 1 ? 'ganked' : 'none';
          return {
            x: p.x,
            y: p.y,
            group,
            color: this.deathLegend.find(l => l.key === group).color,
            title: this.clock(p.t_s) + (p.enemy_half ? ' - enemy half' : ''),
          };
        });
    },
    nemesisLists() {
      const heroRows = rows => rows.map(r => ({ label: this.heroName(r.hero_id), n: r.n }));
      const playerRows = rows => rows.map(r => ({ label: r.battletag || 'Hidden player', n: r.n }));
      return [
        { title: 'Heroes that kill you most', column: 'Hero', rows: heroRows(this.data.nemesis_heroes) },
        { title: 'Heroes you kill most', column: 'Hero', rows: heroRows(this.data.victim_heroes) },
        { title: 'Players that kill you most', column: 'Player', rows: playerRows(this.data.nemesis_players) },
        { title: 'Players you kill most', column: 'Player', rows: playerRows(this.data.victim_players) },
      ];
    },
  },
  watch: {
    mapsWithDeaths: {
      immediate: true,
      handler(maps) {
        if (!maps.includes(this.selectedMap)) {
          this.selectedMap = maps[0] ?? null;
        }
      },
    },
  },
  methods: {
    layoutFor(map) {
      return this.data.layout.filter(l => l.game_map === map);
    },
    phases(row) {
      return ['p0', 'p1', 'p2', 'p3', 'p4'].map(p => this.round(row[p], 2));
    },
    overextension(who) {
      return [0, 1, 2, 3, 4].map(phase => {
        const row = this.data.overextension.find(r => r.who === who && r.phase === phase);
        return row && row.deaths ? this.round((100 * row.enemy_half) / row.deaths, 1) : null;
      });
    },
  },
};
</script>
