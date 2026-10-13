<template>
  <svg :viewBox="viewBox" class="w-full max-h-[75vh] bg-darken rounded select-none" preserveAspectRatio="xMidYMid meet">
    <defs>
      <filter v-for="team in [0, 1, -1]" :key="'f' + team" :id="tintId(team)" color-interpolation-filters="sRGB">
        <feColorMatrix type="matrix" :values="tintMatrix(team)"></feColorMatrix>
      </filter>
      <clipPath :id="portraitClip" clipPathUnits="objectBoundingBox">
        <circle cx="0.5" cy="0.5" r="0.5"></circle>
      </clipPath>
    </defs>

    <image
      v-if="minimap"
      :href="minimap.src"
      x="0" :y="flip(minimap.top)" :width="minimap.width" :height="minimap.height"
      preserveAspectRatio="none"
    ></image>

    <!-- Structures standing at this moment -->
    <image
      v-for="(icon, index) in standing"
      :key="'s' + index"
      :href="icon.src"
      :x="icon.x - icon.size / 2" :y="flip(icon.y) - icon.size / 2" :width="icon.size" :height="icon.size"
      :filter="'url(#' + tintId(icon.team) + ')'"
    ></image>

    <!-- Camps: every camp of the game, flashing in the capturing team's colour -->
    <circle v-for="camp in campSpots" :key="'c' + camp.key" :cx="camp.x" :cy="flip(camp.y)" r="2.5" fill="none" stroke="#d98c40" stroke-width="0.6" stroke-opacity="0.6"></circle>
    <circle
      v-for="(c, index) in recentCaptures"
      :key="'cc' + index"
      :cx="c.x" :cy="flip(c.y)" :r="2.5 + c.age"
      fill="none" :stroke="teamColor(c.team)" stroke-width="1" :stroke-opacity="1 - c.age / 10"
    ></circle>

    <!-- Where minions died in the last few seconds -->
    <circle
      v-for="(m, index) in recentMinions"
      :key="'m' + index"
      :cx="m[1]" :cy="flip(m[2])" r="0.7"
      :fill="teamColor(m[3])" :fill-opacity="1 - (t - m[0]) / minionWindow"
    ></circle>

    <!-- Deaths: lines from the killers to the spot, then an X -->
    <g v-for="(d, index) in recentDeaths" :key="'d' + index" :opacity="1 - d.age / deathWindow">
      <line
        v-for="(k, ki) in d.killers"
        :key="ki"
        :x1="k[0]" :y1="flip(k[1])" :x2="d.x" :y2="flip(d.y)"
        :stroke="teamColor(1 - d.team)" stroke-width="0.5" stroke-dasharray="1 1"
      ></line>
      <path :d="cross(d.x, flip(d.y), 2.5)" :stroke="teamColor(d.team)" stroke-width="1.2"></path>
    </g>

    <!-- Pings -->
    <circle
      v-for="(p, index) in recentPings"
      :key="'p' + index"
      :cx="p.x" :cy="flip(p.y)" :r="1 + p.age * 3"
      fill="none" :stroke="teamColor(p.team)" stroke-width="0.6" :stroke-opacity="1 - p.age / pingWindow"
    ></circle>

    <!-- Selected player's camera -->
    <rect
      v-if="camera"
      :x="camera[0] - cameraSize[0] / 2" :y="flip(camera[1]) - cameraSize[1] / 2" :width="cameraSize[0]" :height="cameraSize[1]"
      fill="none" stroke="#ffffff" stroke-width="0.4" stroke-opacity="0.7"
    ></rect>

    <!-- Heroes -->
    <g v-for="hero in heroes" :key="'hero' + hero.idx" class="cursor-pointer" @click="$emit('select', hero.idx)">
      <circle :cx="hero.x" :cy="flip(hero.y)" :r="portrait / 2 + 0.6" :fill="teamColor(hero.team)"></circle>
      <image
        v-if="hero.short_name"
        :href="'/images/heroes/circular/' + hero.short_name + '.png'"
        :x="hero.x - portrait / 2" :y="flip(hero.y) - portrait / 2" :width="portrait" :height="portrait"
        :clip-path="'url(#' + portraitClip + ')'"
      ></image>
      <circle
        v-if="hero.idx === selected"
        :cx="hero.x" :cy="flip(hero.y)" :r="portrait / 2 + 1.4"
        fill="none" stroke="#f2b134" stroke-width="0.8"
      ></circle>
      <title>{{ hero.label }}</title>
    </g>
  </svg>
</template>

<script>
import { positionAt } from './replayTracks.js';
import { structureIcons, teamColor, tintMatrix } from './mapAssets.js';

const KIND = { TownTownHallL2: 'fort', TownTownHallL3: 'keep', KingsCore: 'core', VanndarStormpike: 'core', DrekThar: 'core' };

// The replay at one moment: structures, camps, minion and hero deaths, pings and heroes
// on the minimap. Game y grows upward, SVG y downward.
export default {
  name: 'DeepAnalyticsReplayMap',
  props: {
    data: Object,
    tracks: Map,
    t: Number,
    selected: { type: Number, default: null },
    minimap: Object,
  },
  emits: ['select'],
  data() {
    return {
      portrait: 8,
      minionWindow: 15,
      deathWindow: 8,
      pingWindow: 4,
      // Roughly what the game camera shows, in map units
      cameraSize: [36, 20],
    };
  },
  computed: {
    viewBox() {
      const m = this.minimap;
      return m ? `0 0 ${m.width} ${m.height}` : '0 0 250 220';
    },
    top() {
      return this.minimap ? this.minimap.top : 220;
    },
    // Each map structure with the time it fell, matched to the replay's structure units by kind and spot
    structures() {
      const units = this.data.units.filter(u => KIND[u.name] && u.d);
      return structureIcons(this.minimap).map(icon => {
        const fell = icon.kind === 'watch' ? null : units.find(u => KIND[u.name] === icon.kind && Math.hypot(u.b[1] - icon.x, u.b[2] - icon.y) <= 5);
        return { ...icon, fell: fell ? fell.d[0] : null };
      });
    },
    standing() {
      return this.structures.filter(s => s.fell === null || s.fell > this.t);
    },
    campSpots() {
      const spots = new Map();
      this.data.camps.forEach(c => {
        if (c.x !== null) spots.set(c.camp + ':' + c.type, { key: c.camp + ':' + c.type, x: c.x, y: c.y });
      });
      return [...spots.values()];
    },
    recentCaptures() {
      return this.data.camps
        .filter(c => c.x !== null && c.t <= this.t && this.t - c.t < 10)
        .map(c => ({ ...c, age: this.t - c.t }));
    },
    recentMinions() {
      return this.data.minion_deaths.filter(m => m[1] >= 0 && m[0] <= this.t && this.t - m[0] < this.minionWindow);
    },
    recentDeaths() {
      return this.data.deaths
        .filter(d => d.x !== null && d.t <= this.t && this.t - d.t < this.deathWindow)
        .map(d => ({
          x: d.x,
          y: d.y,
          age: this.t - d.t,
          team: this.data.players[d.v]?.team ?? 0,
          killers: d.k.map(k => positionAt(this.tracks.get(k), d.t)).filter(Boolean),
        }));
    },
    recentPings() {
      return this.data.pings
        .filter(p => p[0] <= this.t && this.t - p[0] < this.pingWindow)
        .map(p => ({ x: p[2], y: p[3], age: this.t - p[0], team: this.data.players[p[1]]?.team ?? 0 }));
    },
    camera() {
      if (this.selected === null) return null;
      const pts = this.data.camera.find(c => c.p === this.selected)?.pts || [];
      let last = null;
      for (const pt of pts) {
        if (pt[0] > this.t) break;
        last = pt;
      }
      return last ? [last[1], last[2]] : null;
    },
    heroes() {
      return this.data.players.map(p => {
        const at = positionAt(this.tracks.get(p.idx), this.t);
        return at ? { ...p, x: at[0], y: at[1], label: (p.battletag ? p.battletag + ' - ' : '') + p.hero } : null;
      }).filter(Boolean);
    },
    portraitClip() {
      return 'da-portrait-' + this.$.uid;
    },
  },
  methods: {
    teamColor,
    tintMatrix,
    flip(y) {
      return this.top - y;
    },
    tintId(team) {
      return 'da-replay-tint-' + this.$.uid + '-' + team;
    },
    cross(x, y, r) {
      return `M${x - r} ${y - r} L${x + r} ${y + r} M${x + r} ${y - r} L${x - r} ${y + r}`;
    },
  },
};
</script>
