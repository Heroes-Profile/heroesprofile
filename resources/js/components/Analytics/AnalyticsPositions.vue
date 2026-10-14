<template>
  <div>
    <h2 class="text-xl font-bold mb-2 text-teal">Where you spend your time</h2>
    <div class="flex flex-wrap justify-center items-center gap-4 mb-2">
      <single-select-filter
        :key="mapsPlayed.join()"
        :values="mapsPlayed.map(map => ({ code: map, name: mapName(map) }))"
        :text="'Map'"
        :defaultValue="selectedMap"
        :disabledeselectfilters="true"
        @input-changed="selectedMap = $event.value"
      ></single-select-filter>
      <multi-tab-button :options="teamOptions" v-model="team"></multi-tab-button>
    </div>
    <analytics-map-view :map-name="mapName(selectedMap)" :layout="layout" :heatmap="cells"></analytics-map-view>

    <!-- Rough lane split: the map's height cut into thirds -->
    <div class="flex flex-wrap justify-center gap-4 mt-6">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Top third'" :value="lanes.top" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Middle third'" :value="lanes.middle" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Bottom third'" :value="lanes.bottom" color="teal"></stat-box>
    </div>
    <p class="text-center text-sm mt-2 text-gray-light">
      From the game's own position updates, which come every several seconds. Lane split is approximate.
    </p>

    <h2 class="text-xl font-bold mt-8 mb-2 text-teal">Where you click</h2>
    <analytics-map-view :map-name="mapName(selectedMap)" :layout="layout" :heatmap="clickCells" heat-color="#f2b134"></analytics-map-view>
    <p class="text-center text-sm mt-2 text-gray-light">
      Move and ability target points, at most one per second.
    </p>
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
  },
  data() {
    return {
      selectedMap: null,
      team: 0,
      teamOptions: [
        { key: 0, label: 'As Team 1' },
        { key: 1, label: 'As Team 2' },
      ],
    };
  },
  computed: {
    mapsPlayed() {
      const counts = {};
      this.data.heatmap.filter(c => !c.est).forEach(c => {
        counts[c.game_map] = (counts[c.game_map] || 0) + c.n;
      });
      return Object.keys(counts).map(Number).sort((a, b) => counts[b] - counts[a]);
    },
    layout() {
      return this.data.layout.filter(l => l.game_map === this.selectedMap);
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
        return { top: '-', middle: '-', bottom: '-' };
      }
      const min = Math.min(...ys), max = Math.max(...ys);
      const third = (max - min) / 3;
      const count = test => this.cells.filter(c => test(c.y)).reduce((sum, c) => sum + c.n, 0);
      return {
        top: this.share(count(y => y >= max - third), total),
        middle: this.share(count(y => y < max - third && y >= min + third), total),
        bottom: this.share(count(y => y < min + third), total),
      };
    },
  },
  watch: {
    mapsPlayed: {
      immediate: true,
      handler(maps) {
        if (!maps.includes(this.selectedMap)) {
          this.selectedMap = maps[0] ?? null;
        }
      },
    },
  },
};
</script>
