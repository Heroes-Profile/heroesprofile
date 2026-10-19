<template>
  <div>
    <!-- The answers first -->
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Deaths per Game'" :value="orDash(round(deathsPerGame(youTiming), 2))" :secondstat="orDash(round(deathsPerGame(baseTiming), 2))" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Deathless Games'" :value="percent(youTiming.deathless_share)" :secondstat="percent(baseTiming.deathless_share)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'First Death'" :value="clock(youTiming.avg_first_death)" :secondstat="clock(baseTiming.avg_first_death)" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'On Enemy Half'" :value="share(you.enemy_half, you.deaths)" :secondstat="share(base.enemy_half, base.deaths)" :secondcaption="'global'" color="red"></stat-box>
    </div>

    <!-- Death map -->
    <div class="mb-8">
      <analytics-heading :title="'Where you die'" :takeaway="mapTakeaway">
        Each dot is one of your deaths, at the exact spot the game recorded (not an estimate). Colour shows how many enemy heroes got credit for the kill: 1-2 is a gank, 3 or more is a teamfight, and grey means no hero got credit (minions, towers, mercenaries or bosses). Hover or click a legend entry to pick out just those deaths. You play on both sides across games, so use "enemy half" in a dot's hover rather than the structure colours to tell whose side it was.
      </analytics-heading>
      <template v-if="shownMap !== null">
        <p v-if="(selectedMaps || []).length > 1" class="text-center text-sm mb-2">Showing {{ mapName(shownMap) }}, where you die most of the maps you picked.</p>
        <analytics-map-view :map-name="mapName(shownMap)" :layout="layoutFor(shownMap)" :points="deathPoints" :legend="deathLegend"></analytics-map-view>
      </template>
      <analytics-map-list v-else unit="deaths" :rows="deathsByMap" :prompt="'Pick a map to see where you die on it. Everything on this tab then shows that map only.'" @pick="$emit('pick-map', $event)"></analytics-map-list>
    </div>

    <!-- Death context -->
    <analytics-heading :title="'How you die'" :takeaway="contextTakeaway">
      Who got the kill: Ganked is 1-2 enemy heroes with kill credit, Teamfight is 3 or more, No hero is minions, towers, mercenaries or bosses. Your team's level: your team's level compared with the enemy team's when you died; deaths in the first seconds, before any level is recorded, count in none of them. Each bar is all your deaths, with the global sample under it.
    </analytics-heading>
    <div class="grid md:grid-cols-2 gap-6 mb-8">
      <multi-series-chart type="bar" :horizontal="true" :stacked="true" :min="0" :max="100" format="percent" :labels="['You', 'Global']" :title="'Who got the kill'" :series="killerSplit"></multi-series-chart>
      <multi-series-chart type="bar" :horizontal="true" :stacked="true" :min="0" :max="100" format="percent" :labels="['You', 'Global']" :title="levelTitle" :series="levelSplit"></multi-series-chart>
    </div>

    <!-- Death timing -->
    <analytics-heading :title="'When you die'" :takeaway="timingTakeaway">
      The first chart is how many times you die per game in each 5-minute window (games with no deaths count as 0). The second is the share of your deaths in each window that happened on the enemy half of the map; a high late-game number usually means overextending. First Death above is the average game time of your first death, counting only games where you died.
    </analytics-heading>
    <div class="grid md:grid-cols-2 gap-6 mb-8">
      <multi-series-chart
        type="bar"
        :labels="phaseLabels"
        :title="'Deaths per game'"
        :series="[
          { label: 'You', data: phases(youTiming), color: 'teal', n: phaseGames(youTiming) },
          { label: 'Global', data: phases(baseTiming), color: 'yellow', n: phaseGames(baseTiming) },
        ]"
      ></multi-series-chart>
      <multi-series-chart
        type="bar"
        format="percent"
        :labels="phaseLabels"
        :title="'Deaths on the enemy half'"
        :series="[
          { label: 'You', data: overextension('you'), color: 'teal' },
          { label: 'Global', data: overextension('global'), color: 'yellow' },
        ]"
      ></multi-series-chart>
    </div>

    <!-- Nemesis -->
    <analytics-heading :title="'Who kills you, and who you kill'" :takeaway="nemesisTakeaway">
      Kill credit includes assists: every enemy credited with a takedown counts, not just the last hit, so one death in a 5-man fight adds 1 to five different heroes. These lists are yours only; there is no global comparison. Players who hide their profile show as Hidden player.
    </analytics-heading>
    <div class="grid md:grid-cols-2 gap-6">
      <multi-series-chart type="bar" :horizontal="true" :labels="nemesisChart.labels" :title="'Heroes that kill you most'" :series="nemesisChart.series"></multi-series-chart>
      <multi-series-chart type="bar" :horizontal="true" :labels="victimChart.labels" :title="'Heroes you kill most'" :series="victimChart.series"></multi-series-chart>
    </div>
    <analytics-numbers :label="'Show players, and the full lists'">
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
    </analytics-numbers>
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
    // The page's Map filter (map names)
    selectedMaps: { type: Array, default: () => [] },
  },
  emits: ['pick-map'],
  data() {
    return {
      phaseLabels: ['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min'],
    };
  },
  computed: {
    // The map the death map draws: the one the page is filtered to, or with several, the one you die on most
    shownMap() {
      const picked = (this.selectedMaps || []).map(name => this.mapId(name)).filter(id => id !== null);
      if (picked.length === 1) return picked[0];
      if (picked.length > 1) return this.mapsWithDeaths.find(id => picked.includes(id)) ?? picked[0];
      return null;
    },
    deathsByMap() {
      const counts = {};
      this.data.death_points.forEach(p => {
        counts[p.game_map] = (counts[p.game_map] || 0) + 1;
      });
      return this.mapsWithDeaths.map(id => ({ name: this.mapName(id), n: counts[id] }));
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
        .filter(p => p.game_map === this.shownMap)
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
    killerSplit() {
      const row = r => [r.ganked, r.teamfight, r.no_hero_killer].map(n => (r.deaths ? this.round((100 * Number(n)) / Number(r.deaths), 1) : null));
      const [you, base] = [row(this.you), row(this.base)];
      return [
        { label: 'Ganked (1-2)', data: [you[0], base[0]], color: 'teal' },
        { label: 'Teamfight (3+)', data: [you[1], base[1]], color: 'red' },
        { label: 'No hero', data: [you[2], base[2]], color: 'gray' },
      ];
    },
    levelSplit() {
      const row = r => {
        const total = Number(r.level_behind || 0) + Number(r.level_even || 0) + Number(r.level_ahead || 0);
        return [r.level_behind, r.level_even, r.level_ahead].map(n => (total ? this.round((100 * Number(n)) / total, 1) : null));
      };
      const [you, base] = [row(this.you), row(this.base)];
      return [
        { label: 'Behind', data: [you[0], base[0]], color: 'red' },
        { label: 'Even', data: [you[1], base[1]], color: 'yellow' },
        { label: 'Ahead', data: [you[2], base[2]], color: 'teal' },
      ];
    },
    levelTitle() {
      return "Your team's level when you died";
    },
    nemesisChart() {
      const rows = (this.data.nemesis_heroes || []).slice(0, 10);
      return { labels: rows.map(r => this.heroName(r.hero_id)), series: [{ label: 'Your deaths', data: rows.map(r => r.n), color: 'red' }] };
    },
    victimChart() {
      const rows = (this.data.victim_heroes || []).slice(0, 10);
      return { labels: rows.map(r => this.heroName(r.hero_id)), series: [{ label: 'Your kills', data: rows.map(r => r.n), color: 'teal' }] };
    },
    mapTakeaway() {
      if (!this.mapsWithDeaths.length) return 'No deaths with a position yet.';
      return this.shownMap === null ? 'You die most on ' + this.mapName(this.mapsWithDeaths[0]) + '.' : null;
    },
    contextTakeaway() {
      if (!this.you.deaths) return null;
      const ganked = (100 * Number(this.you.ganked)) / Number(this.you.deaths);
      const global = this.base.deaths ? (100 * Number(this.base.ganked)) / Number(this.base.deaths) : null;
      return this.round(ganked) + '% of your deaths are ganks by 1-2 heroes' + this.versus(ganked, global, '%') + '.';
    },
    timingTakeaway() {
      const you = this.phases(this.youTiming);
      if (!you.some(v => v)) return null;
      const worst = you.indexOf(Math.max(...you));
      return 'Your deadliest stretch is ' + this.phaseLabels[worst] + ' (' + you[worst] + ' deaths per game).';
    },
    nemesisTakeaway() {
      const top = (this.data.nemesis_heroes || [])[0];
      const victim = (this.data.victim_heroes || [])[0];
      if (!top) return null;
      return this.heroName(top.hero_id) + ' has killed you most (' + top.n + ' times)' + (victim ? '; you kill ' + this.heroName(victim.hero_id) + ' most (' + victim.n + ').' : '.');
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
  methods: {
    layoutFor(map) {
      return this.data.layout.filter(l => l.game_map === map);
    },
    deathsPerGame(row) {
      return row.games ? ['p0', 'p1', 'p2', 'p3', 'p4'].reduce((sum, p) => sum + Number(row[p] || 0), 0) : null;
    },
    phaseGames(row) {
      return [0, 1, 2, 3, 4].map(() => row.games ?? null);
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
