<template>
  <div class="relative">
    <svg :viewBox="viewBox" class="w-full max-h-[70vh] bg-darken rounded" preserveAspectRatio="xMidYMid meet">
      <image
        v-if="minimap"
        :href="minimap.src"
        x="0" :y="flip(minimap.top)" :width="minimap.width" :height="minimap.height"
        preserveAspectRatio="none"
      ></image>

      <!-- Heatmap cells under everything else -->
      <rect
        v-for="(cell, index) in heat"
        :key="'h' + index"
        :x="cell.x" :y="flip(cell.y + cellSize)" :width="cellSize" :height="cellSize"
        :fill="heatColor" :fill-opacity="cell.opacity"
      ></rect>

      <!-- Structures: forts, keeps and cores from the map file, or from replay data without one -->
      <rect
        v-for="(b, index) in buildings"
        :key="'b' + index"
        :x="b.x - 1" :y="flip(b.y) - 1" width="2" height="2"
        :fill="teamColor(b.team)" fill-opacity="0.6"
      ></rect>
      <rect
        v-for="(t, index) in towns"
        :key="'t' + index"
        :x="t.x - 2.5" :y="flip(t.y) - 2.5" width="5" height="5"
        fill="none" :stroke="teamColor(t.team)" stroke-width="1"
      ></rect>
      <defs>
        <!-- The game's icons are white; multiply them by the team colour -->
        <filter v-for="team in [0, 1, -1]" :key="'f' + team" :id="tintId(team)" color-interpolation-filters="sRGB">
          <feColorMatrix type="matrix" :values="tintMatrix(team)"></feColorMatrix>
        </filter>
      </defs>
      <image
        v-for="(icon, index) in icons"
        :key="'i' + index"
        :href="icon.src"
        :x="icon.x - icon.size / 2" :y="flip(icon.y) - icon.size / 2" :width="icon.size" :height="icon.size"
        :filter="'url(#' + tintId(icon.team) + ')'"
      ></image>
      <circle
        v-for="(c, index) in camps"
        :key="'c' + index"
        :cx="c.x" :cy="flip(c.y)" r="2.5"
        fill="none" stroke="#d98c40" :stroke-width="highlight === 'camps' ? 1.4 : 0.8" :stroke-opacity="dimmed('camps') ? 0.2 : 1"
      ></circle>

      <!-- Points, e.g. deaths -->
      <circle
        v-for="(p, index) in points"
        :key="'p' + index"
        :cx="p.x" :cy="flip(p.y)" :r="highlight && highlight === p.group ? 2.2 : 1.4"
        :fill="p.color" :fill-opacity="dimmed(p.group) ? 0.08 : highlight ? 1 : 0.75"
      >
        <title>{{ p.title }}</title>
      </circle>
    </svg>
    <!-- Legend: top right over the map; below it on small screens. Hover (or tap) an entry to pick out its points. -->
    <div v-if="legendItems.length > 0" class="flex flex-wrap justify-center gap-4 mt-2 text-sm md:absolute md:top-2 md:right-2 md:mt-0 md:flex-col md:gap-1 md:bg-darken md:rounded md:p-2">
      <span
        v-for="item in legendItems"
        :key="item.key"
        class="cursor-pointer select-none"
        :class="{ 'opacity-40': dimmed(item.key) }"
        @mouseenter="hovered = item.key"
        @mouseleave="hovered = null"
        @click="pinned = pinned === item.key ? null : item.key"
      >
        <span
          class="inline-block w-3 h-3 align-middle mr-1 rounded-full"
          :style="item.outline ? { border: '1px solid ' + item.color } : { background: item.color }"
        ></span>{{ item.label }}
      </span>
    </div>
  </div>
</template>

<script>
import { minimapFor, structureIcons, teamColor, tintMatrix } from '../../utils/mapAssets.js';

// One map: the in-game minimap and its structures when we have them, camp positions from
// replay data, and points or a heatmap on top. Game y grows upward, SVG y downward.
export default {
  name: 'AnalyticsMapView',
  props: {
    mapName: { type: String, default: null },
    layout: { type: Array, default: () => [] },
    // Points can carry a `group` matching a legend entry's `key`
    points: { type: Array, default: () => [] },
    legend: { type: Array, default: () => [] },
    heatmap: { type: Array, default: () => [] },
    cellSize: { type: Number, default: 4 },
    heatColor: { type: String, default: '#00d5d5' },
  },
  data() {
    return {
      hovered: null,
      pinned: null,
    };
  },
  computed: {
    highlight() {
      return this.hovered || this.pinned;
    },
    legendItems() {
      const items = this.camps.length > 0 ? [{ key: 'camps', label: 'Merc camps', color: '#d98c40', outline: true }] : [];
      return [...items, ...this.legend];
    },
    staticStructures() {
      return this.minimap && this.minimap.structures.length > 0;
    },
    buildings() {
      return this.staticStructures ? [] : this.layout.filter(l => l.kind === 'building');
    },
    towns() {
      return this.staticStructures ? [] : this.layout.filter(l => l.kind === 'town');
    },
    icons() {
      return this.staticStructures ? structureIcons(this.minimap) : [];
    },
    camps() {
      return this.layout.filter(l => l.kind === 'camp');
    },
    minimap() {
      return minimapFor(this.mapName);
    },
    extent() {
      // Only the drawn part of the minimap; its transparent border would otherwise take up space
      if (this.minimap) {
        const [left, bottom, right, top] = this.minimap.view;
        return { minX: left, maxX: right, minY: bottom, maxY: top };
      }
      const xs = [], ys = [];
      [...this.layout, ...this.points, ...this.heatmap].forEach(p => {
        xs.push(p.x);
        ys.push(p.y);
      });
      if (xs.length === 0) {
        return { minX: 0, maxX: 250, minY: 0, maxY: 220 };
      }
      return { minX: Math.min(...xs) - 6, maxX: Math.max(...xs) + 6 + this.cellSize, minY: Math.min(...ys) - 6, maxY: Math.max(...ys) + 6 + this.cellSize };
    },
    viewBox() {
      const e = this.extent;
      return `${e.minX} ${this.flip(e.maxY)} ${e.maxX - e.minX} ${e.maxY - e.minY}`;
    },
    heat() {
      const max = Math.max(1, ...this.heatmap.map(c => c.n));
      return this.heatmap.map(c => ({ x: c.x, y: c.y, opacity: 0.15 + 0.85 * Math.sqrt(c.n / max) }));
    },
  },
  methods: {
    flip(y) {
      return this.extent.maxY + this.extent.minY - y;
    },
    teamColor,
    tintMatrix,
    dimmed(key) {
      return this.highlight !== null && this.highlight !== key;
    },
    tintId(team) {
      return 'da-tint-' + this.$.uid + '-' + team;
    },
  },
};
</script>
