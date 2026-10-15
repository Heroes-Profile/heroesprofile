<template>
  <div>
    <infobox class="mb-6" :input="'Every death you had in your processed games, broken down by where, when and how it happened, and who was involved. Next to most numbers is the same number for a global sample of games under the same filters, so you can see if a habit is yours or just how the game plays. With only a few games, expect big swings.'"></infobox>

    <!-- Death map -->
    <div class="mb-8">
      <h2 class="text-xl font-bold mb-2 text-teal">
        <span class="inline-flex items-center gap-1">
          Where you die
          <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
            <slot><p class="text-sm p-2 text-left">Each dot is one of your deaths, at the exact spot the game recorded (not an estimate). Hover a dot for the game time. You play on both sides across games, so use "enemy half" in the hover rather than the structure colours to tell whose side it was.</p></slot>
          </round-image>
        </span>
      </h2>
      <infobox class="mb-2" :input="'Colour shows how many enemy heroes got credit for the kill: 1-2 is a gank, 3 or more is a teamfight, and grey means no hero got credit (minions, towers, mercenaries or bosses). Hover or click a legend entry to pick out just those deaths. Pick any map; the one you have died on most is selected first.'"></infobox>
      <div class="flex justify-center mb-2">
        <single-select-filter
          :key="mapsWithDeaths.join()"
          :values="mapOptions"
          :text="'Map'"
          :defaultValue="mapName(selectedMap)"
          :disabledeselectfilters="true"
          @input-changed="selectedMap = mapId($event.value)"
        ></single-select-filter>
      </div>
      <analytics-map-view :map-name="mapName(selectedMap)" :layout="layoutFor(selectedMap)" :points="deathPoints" :legend="deathLegend"></analytics-map-view>
    </div>

    <!-- Death context -->
    <h2 class="text-xl font-bold mb-2 text-teal">How you die</h2>
    <infobox class="mb-2" :input="'Each box is the share of all your deaths that fit that description, with the global share beside it. They overlap, so they do not add up to 100%. Ganked: 1-2 enemy heroes got kill credit. Teamfight: 3 or more did. Level Behind / Even / Ahead: your team\'s level compared with the enemy team\'s when you died (deaths in the first seconds, before any level is recorded, count in none of them). Enemy Half: you died closer to the enemy base than to your own.'"></infobox>
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Ganked'" :value="share(you.ganked, you.deaths)" :secondstat="share(base.ganked, base.deaths)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Teamfight'" :value="share(you.teamfight, you.deaths)" :secondstat="share(base.teamfight, base.deaths)" :secondcaption="'global'" color="red"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Enemy Half'" :value="share(you.enemy_half, you.deaths)" :secondstat="share(base.enemy_half, base.deaths)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Level Behind'" :value="share(you.level_behind, you.deaths)" :secondstat="share(base.level_behind, base.deaths)" :secondcaption="'global'" color="red"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Level Even'" :value="share(you.level_even, you.deaths)" :secondstat="share(base.level_even, base.deaths)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Level Ahead'" :value="share(you.level_ahead, you.deaths)" :secondstat="share(base.level_ahead, base.deaths)" :secondcaption="'global'" color="teal"></stat-box>
    </div>

    <!-- Death timing -->
    <h2 class="text-xl font-bold mb-2 text-teal">When you die</h2>
    <infobox class="mb-2" :input="'First Death: the average game time of your first death, counting only games where you died. Deathless Games: the share of your games with zero deaths. The first chart is how many times you die per game in each 5-minute window (games with no deaths count as 0). The second is the share of your deaths in each window that happened on the enemy half of the map; a high late-game number usually means overextending.'"></infobox>
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
    <infobox class="mb-2" :input="'Kill credit includes assists: every enemy credited with a takedown counts, not just the last hit. So one death in a 5-man fight adds 1 to five different heroes. These lists are yours only; there is no global comparison. Players who hide their profile show as Hidden player.'"></infobox>
    <div class="flex flex-wrap gap-6 justify-center">
      <div v-for="list in nemesisLists" :key="list.title" class="w-[45%] max-md:w-full">
        <h3 class="mb-2">{{ list.title }}</h3>
        <table class="min-w-0 w-full responsive-table">
          <thead><tr><th>{{ list.column }}</th><th>
              <span class="inline-flex items-center gap-1">
                Times
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">{{ list.info }}</p></slot>
                </round-image>
              </span>
            </th></tr></thead>
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
    gameMaps: Array,
  },
  data() {
    return {
      selectedMap: null,
      phaseLabels: ['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min'],
    };
  },
  computed: {
    // The site's full map filter list, alphabetical like everywhere else
    mapOptions() {
      return this.gameMaps || [];
    },
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
        { title: 'Heroes that kill you most', column: 'Hero', info: 'How many of your deaths this enemy hero got kill credit for, assists included. Top 15.', rows: heroRows(this.data.nemesis_heroes) },
        { title: 'Heroes you kill most', column: 'Hero', info: 'How many times this hero died with you credited for the kill, assists included. Top 15.', rows: heroRows(this.data.victim_heroes) },
        { title: 'Players that kill you most', column: 'Player', info: 'How many of your deaths this player got kill credit for, assists included. Top 10.', rows: playerRows(this.data.nemesis_players) },
        { title: 'Players you kill most', column: 'Player', info: 'How many times this player died with you credited for the kill, assists included. Top 10.', rows: playerRows(this.data.victim_players) },
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
