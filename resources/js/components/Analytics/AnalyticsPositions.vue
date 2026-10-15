<template>
  <div>
    <infobox class="mb-6" :input="'Two heatmaps of your games on the selected map. A replay only records your hero\'s real position every several seconds, plus the spots where you died or picked up a regen globe. Most other recorded points are estimates: the spot you clicked to move or aim an ability. The first map uses only real positions, so it shows where your hero actually was. The second uses only the click targets. Team 1 and Team 2 start in different bases, so each side has its own map; the map with the most data is selected first.'"></infobox>

    <h2 class="text-xl font-bold mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Where you spend your time
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">Each square is 4 by 4 map units; brighter means more real positions recorded there across your games. Real positions are sparse, so quick stretches like walking between lanes show up less.</p></slot>
        </round-image>
      </span>
    </h2>
    <div class="flex flex-wrap justify-center items-center gap-4 mb-2">
      <single-select-filter
        :key="mapsPlayed.join()"
        :values="mapOptions"
        :text="'Map'"
        :defaultValue="mapName(selectedMap)"
        :disabledeselectfilters="true"
        @input-changed="selectedMap = mapId($event.value)"
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
      <span class="inline-flex items-center gap-1">
        Share of your real positions in each third of the map. Lane split is approximate.
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">The map is cut into three equal horizontal bands between its highest and lowest structures and camps. The bands don't follow the real lane paths, so treat this as a rough guide on three-lane maps.</p></slot>
        </round-image>
      </span>
    </p>

    <h2 class="text-xl font-bold mt-8 mb-2 text-teal">
      <span class="inline-flex items-center gap-1">
        Where you click
        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <slot><p class="text-sm p-2 text-left">Built only from estimated points: the target spots of your move and ability orders. It shows where you send your hero and aim, not where your hero stood.</p></slot>
        </round-image>
      </span>
    </h2>
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
    gameMaps: Array,
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
    // The site's full map filter list, alphabetical like everywhere else
    mapOptions() {
      return this.gameMaps || [];
    },
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
