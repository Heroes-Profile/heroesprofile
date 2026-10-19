<template>
  <div>
    <!-- The answers first -->
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Most Played Map'" :value="mapsPlayed.length ? mapName(mapsPlayed[0]) : '-'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Maps with Positions'" :value="mapsPlayed.length" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Most Time In'" :value="mainLane ? mainLane.label + ' (' + mainLane.value + '%)' : '-'" color="teal"></stat-box>
    </div>

    <!-- Heatmaps -->
    <analytics-heading :title="'Where you are on the map'" :takeaway="mapTakeaway">
      Two heatmaps of your games on the selected map. A replay only records your hero's real position every several seconds, plus the spots where you died or picked up a regen globe. Most other recorded points are estimates: the spot you clicked to move or aim an ability. The first map uses only real positions, so it shows where your hero actually was. The second uses only the click targets. Each side of the map has its own heatmaps, since you start in a different base; the map with the most data is selected first.
    </analytics-heading>
    <analytics-map-list v-if="selectedMap === null" class="mb-8" unit="positions" :rows="positionsByMap" :prompt="'Pick a map to see where you are on it. Everything on this tab then shows that map only.'" @pick="$emit('pick-map', $event)"></analytics-map-list>
    <template v-else>
    <p v-if="(selectedMaps || []).length > 1" class="text-center text-sm mb-2">Showing {{ mapName(selectedMap) }}, your most played of the maps you picked.</p>
    <div class="flex flex-wrap justify-center items-center gap-4 mb-4">
      <multi-tab-button :options="teamOptions" v-model="team"></multi-tab-button>
    </div>
    <div class="grid md:grid-cols-2 gap-6 mb-8">
      <div>
        <analytics-heading :title="'Where you stand'">
          Each square is 4 by 4 map units; brighter means more real positions recorded there across your games. Real positions are sparse, so quick stretches like walking between lanes show up less.
        </analytics-heading>
        <analytics-map-view :map-name="mapName(selectedMap)" :layout="layout" :heatmap="cells"></analytics-map-view>
      </div>
      <div>
        <analytics-heading :title="'Where you click'">
          Built only from estimated points: the target spots of your move and ability orders, at most one per second. It shows where you send your hero and aim, not where your hero stood.
        </analytics-heading>
        <analytics-map-view :map-name="mapName(selectedMap)" :layout="layout" :heatmap="clickCells" heat-color="#f2b134" :heat-labels="['Fewer clicks', 'Most clicks']" heat-unit="clicks"></analytics-map-view>
      </div>
    </div>

    <!-- Rough lane split: the map's height cut into thirds -->
    <analytics-heading :title="'Lane split'" :takeaway="laneTakeaway">
      Share of your real positions in each third of the selected map, on the selected side. The map is cut into three equal horizontal bands between its highest and lowest structures and camps. The bands don't follow the real lane paths, so treat this as a rough guide on three-lane maps.
    </analytics-heading>
    <multi-series-chart type="bar" :horizontal="true" :stacked="true" :min="0" :max="100" format="percent" :labels="['You']" :title="'Share of your time in each third'" :series="laneSplit"></multi-series-chart>
    </template>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

export default {
  name: 'AnalyticsPositions',
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
      team: 0,
    };
  },
  computed: {
    positionCounts() {
      const counts = {};
      this.data.heatmap.filter(c => !c.est).forEach(c => {
        counts[c.game_map] = (counts[c.game_map] || 0) + c.n;
      });
      return counts;
    },
    mapsPlayed() {
      return Object.keys(this.positionCounts).map(Number).sort((a, b) => this.positionCounts[b] - this.positionCounts[a]);
    },
    positionsByMap() {
      return this.mapsPlayed.map(id => ({ name: this.mapName(id), n: this.positionCounts[id] }));
    },
    // The map the heatmaps draw: the one the page is filtered to, or with several, your most played of them
    selectedMap() {
      const picked = (this.selectedMaps || []).map(name => this.mapId(name)).filter(id => id !== null);
      if (picked.length === 1) return picked[0];
      if (picked.length > 1) return this.mapsPlayed.find(id => picked.includes(id)) ?? picked[0];
      return null;
    },
    layout() {
      return this.data.layout.filter(l => l.game_map === this.selectedMap);
    },
    // Which team's structures sit on the left, from the map's layout; null if we can't tell
    leftTeam() {
      const avgX = team => {
        const xs = this.layout.filter(l => l.team === team && (l.kind === 'town' || l.kind === 'building')).map(l => l.x);
        return xs.length ? xs.reduce((sum, x) => sum + x, 0) / xs.length : null;
      };
      const [a, b] = [avgX(0), avgX(1)];
      if (a === null || b === null || a === b) return null;
      return a < b ? 0 : 1;
    },
    teamOptions() {
      if (this.leftTeam === null) {
        return [
          { key: 0, label: 'As Team 1' },
          { key: 1, label: 'As Team 2' },
        ];
      }
      return [
        { key: this.leftTeam, label: 'Left base' },
        { key: 1 - this.leftTeam, label: 'Right base' },
      ];
    },
    cells() {
      return this.data.heatmap.filter(c => c.game_map === this.selectedMap && c.team === this.team && !c.est);
    },
    clickCells() {
      return this.data.heatmap.filter(c => c.game_map === this.selectedMap && c.team === this.team && c.est);
    },
    lanes() {
      const ys = this.layout.map(l => l.y);
      const total = this.cells.reduce((sum, c) => sum + c.n, 0);
      if (ys.length === 0 || total === 0) {
        return null;
      }
      const min = Math.min(...ys), max = Math.max(...ys);
      const third = (max - min) / 3;
      const count = test => this.cells.filter(c => test(c.y)).reduce((sum, c) => sum + c.n, 0);
      const pct = n => this.round((100 * n) / total, 1);
      return {
        top: pct(count(y => y >= max - third)),
        middle: pct(count(y => y < max - third && y >= min + third)),
        bottom: pct(count(y => y < min + third)),
      };
    },
    laneSplit() {
      const l = this.lanes || {};
      return [
        { label: 'Top', data: [l.top ?? null], color: 'teal' },
        { label: 'Middle', data: [l.middle ?? null], color: 'yellow' },
        { label: 'Bottom', data: [l.bottom ?? null], color: 'red' },
      ];
    },
    mainLane() {
      if (!this.lanes) return null;
      return [
        { label: 'Top', value: this.lanes.top },
        { label: 'Middle', value: this.lanes.middle },
        { label: 'Bottom', value: this.lanes.bottom },
      ].sort((a, b) => b.value - a.value)[0];
    },
    mapTakeaway() {
      if (!this.mapsPlayed.length) return 'No recorded positions yet.';
      return this.selectedMap === null ? 'You have the most recorded positions on ' + this.mapName(this.mapsPlayed[0]) + '.' : null;
    },
    laneTakeaway() {
      if (!this.mainLane) return null;
      return 'On ' + this.mapName(this.selectedMap) + ' you spend most of your time in the ' + this.mainLane.label.toLowerCase() + ' third (' + this.mainLane.value + '%).';
    },
  },
};
</script>
