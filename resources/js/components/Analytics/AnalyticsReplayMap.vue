<template>
  <!-- The dark card spans the column like the cards around it; the map inside is 75% wide.
       Wheel zooms around the cursor, dragging pans, and the buttons do the same for touch. -->
  <div class="relative w-full bg-darken rounded">
  <div class="absolute top-2 right-2 z-10 flex gap-1">
    <custom-button :text="'+'" :alt="'Zoom in'" size="small" :ignoreclick="true" @click="zoomBy(1.5)"></custom-button>
    <custom-button :text="'−'" :alt="'Zoom out'" size="small" :ignoreclick="true" @click="zoomBy(1 / 1.5)"></custom-button>
    <custom-button v-if="zoom > 1" :text="'Reset'" :alt="'Reset zoom'" size="small" :ignoreclick="true" @click="resetZoom"></custom-button>
  </div>
  <svg
    ref="svg"
    :viewBox="viewBox"
    class="block w-3/4 mx-auto max-h-[56vh] select-none touch-none"
    :class="{ 'cursor-grab': zoom > 1 && !dragging, 'cursor-grabbing': dragging }"
    preserveAspectRatio="xMidYMid meet"
    @wheel.prevent="onWheel"
    @pointerdown="onPointerDown"
    @pointermove="onPointerMove"
    @pointerup="onPointerUp"
    @pointercancel="onPointerUp"
  >
    <defs>
      <filter v-for="team in [0, 1, -1]" :key="'f' + team" :id="tintId(team)" color-interpolation-filters="sRGB">
        <feColorMatrix type="matrix" :values="tintMatrix(team)"></feColorMatrix>
      </filter>
      <filter :id="tintId('symbiote')" color-interpolation-filters="sRGB">
        <feColorMatrix type="matrix" :values="symbioteTint"></feColorMatrix>
      </filter>
      <clipPath :id="portraitClip" clipPathUnits="objectBoundingBox">
        <circle cx="0.5" cy="0.5" r="0.5"></circle>
      </clipPath>
    </defs>

    <image
      v-if="minimap"
      :href="minimap.src"
      :x="minimap.left || 0" :y="flip(minimap.top)" :width="minimap.width" :height="minimap.height"
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

    <!-- Map shortcuts while they're open: Haunted Mines' ladders, the waygates -->
    <image
      v-for="(marker, index) in shortcuts"
      :key="'sc' + index"
      :href="marker.src"
      :x="marker.x - marker.size / 2" :y="flip(marker.y) - marker.size / 2" :width="marker.size" :height="marker.size"
      :filter="'url(#' + tintId(-1) + ')'"
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
    <g v-for="hero in heroes" :key="'hero' + hero.idx + (hero.unit || '')" class="cursor-pointer" :opacity="hero.symbiote ? 0.45 : 1" @click="select(hero.idx)">
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
      <!-- Which of several bodies this is (the Lost Vikings, Chen's elements...): its initial under the portrait -->
      <text
        v-if="hero.unit"
        :x="hero.x" :y="flip(hero.y) + portrait / 2 + 2.6"
        text-anchor="middle" font-size="3" font-weight="bold" fill="#fff" stroke="#000" stroke-width="0.4" paint-order="stroke"
      >{{ hero.unit.charAt(0) }}</text>
      <title>{{ hero.label }}</title>
    </g>

    <!-- Abathur's Symbiote: his hat on top of the ally it rides, green as the game draws it on the minimap -->
    <image
      v-for="(sym, index) in symbiotes"
      :key="'sym' + index"
      href="/images/analytics/minimaps/icons/abathur_hat.png"
      :x="sym.x - portrait * 0.6" :y="flip(sym.y) - portrait * 1.25" :width="portrait * 1.2" :height="portrait * 1.2"
      :filter="'url(#' + tintId('symbiote') + ')'"
      class="pointer-events-none"
    ></image>
  </svg>
  </div>
</template>

<script>
import { positionAt, positionsAt, symbiotesAt, MAIN_BODIES } from '../../utils/replayTracks.js';

// Names for the extra bodies some heroes control; the hero's own body gets none. Any other hero
// unit an Abathur player controls is his Ultimate Evolution clone.
const BODY_NAMES = {
  HeroOlaf: 'Olaf', HeroBaleog: 'Baleog', HeroErik: 'Erik',
  HeroChenStorm: 'Storm', HeroChenEarth: 'Earth', HeroChenFire: 'Fire',
  HeroDVaMech: 'Mech', HeroDVaPilot: 'Pilot',
  SamuroMirrorImage: 'Mirror Image', AbathurEvolvedMonstrosity: 'Monstrosity',
};

function bodyName(unit) {
  if (!unit || MAIN_BODIES.includes(unit)) return null;
  return BODY_NAMES[unit] || 'Clone';
}
import { structureIcons, teamColor, tintMatrix, tintMatrixFor, SYMBIOTE_COLOR, shortcutOpenSpans, shortcutMarkers } from '../../utils/mapAssets.js';

const KIND = { TownTownHallL2: 'fort', TownTownHallL3: 'keep', KingsCore: 'core', VanndarStormpike: 'core', DrekThar: 'core' };

// The replay at one moment: structures, camps, minion and hero deaths, pings and heroes
// on the minimap. Game y grows upward, SVG y downward.
export default {
  name: 'AnalyticsReplayMap',
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
      // 1 = the whole map; centre is in SVG units, null = the map's own centre
      zoom: 1,
      centre: null,
      dragging: false,
      drag: null,
      maxZoom: 6,
    };
  },
  computed: {
    // Only the drawn part of the minimap; the image's transparent border would otherwise take up space
    // The whole drawn map in SVG units: x, y, width, height
    fullView() {
      const m = this.minimap;
      if (!m) return [0, 0, 250, 220];
      const [left, bottom, right, top] = m.view;
      return [left, this.flip(top), right - left, top - bottom];
    },
    // The part of the map in view at the current zoom, kept inside the map
    view() {
      const [x, y, w, h] = this.fullView;
      const vw = w / this.zoom;
      const vh = h / this.zoom;
      const [cx, cy] = this.centre ?? [x + w / 2, y + h / 2];
      const left = Math.min(Math.max(cx - vw / 2, x), x + w - vw);
      const topEdge = Math.min(Math.max(cy - vh / 2, y), y + h - vh);
      return [left, topEdge, vw, vh];
    },
    viewBox() {
      return this.view.join(' ');
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
      const riding = new Set(this.symbiotes.map(sym => sym.abathur));
      return this.data.players.flatMap(p => positionsAt(this.tracks.get(p.idx), this.t).map(at => {
        const unit = bodyName(at.unit);
        // Abathur himself is faded while his Symbiote is out on an ally
        const symbiote = riding.has(p.idx) && !unit;
        return { ...p, x: at.x, y: at.y, unit, symbiote, label: (p.battletag ? p.battletag + ' - ' : '') + p.hero + (unit ? ' (' + unit + ')' : '') };
      }));
    },
    symbiotes() {
      return symbiotesAt(this.data.symbiotes, this.tracks, this.t);
    },
    shortcutSpans() {
      return this.minimap ? shortcutOpenSpans(this.minimap.slug, this.data.units, this.data.length_s) : [];
    },
    shortcuts() {
      return this.minimap ? shortcutMarkers(this.minimap.slug, this.shortcutSpans, this.t) : [];
    },
    symbioteTint() {
      return tintMatrixFor(SYMBIOTE_COLOR);
    },
    portraitClip() {
      return 'da-portrait-' + this.$.uid;
    },
  },
  methods: {
    teamColor,
    // A point on screen in SVG units
    toMap(clientX, clientY) {
      const point = this.$refs.svg.createSVGPoint();
      point.x = clientX;
      point.y = clientY;
      const mapped = point.matrixTransform(this.$refs.svg.getScreenCTM().inverse());
      return [mapped.x, mapped.y];
    },
    // Zoom by a factor, keeping `at` (SVG units, default the middle of the view) where it is on screen
    zoomBy(factor, at = null) {
      const next = Math.min(this.maxZoom, Math.max(1, this.zoom * factor));
      if (next === this.zoom) return;
      const [x, y, w, h] = this.view;
      const [px, py] = at ?? [x + w / 2, y + h / 2];
      const [cx, cy] = [x + w / 2, y + h / 2];
      const keep = this.zoom / next;
      this.centre = [px + (cx - px) * keep, py + (cy - py) * keep];
      this.zoom = next;
      if (next === 1) this.centre = null;
    },
    resetZoom() {
      this.zoom = 1;
      this.centre = null;
    },
    onWheel(event) {
      this.zoomBy(event.deltaY < 0 ? 1.2 : 1 / 1.2, this.toMap(event.clientX, event.clientY));
    },
    onPointerDown(event) {
      if (this.zoom === 1) return;
      const [x, y, w, h] = this.view;
      this.drag = { id: event.pointerId, startX: event.clientX, startY: event.clientY, centre: [x + w / 2, y + h / 2], moved: false };
    },
    onPointerMove(event) {
      if (!this.drag || event.pointerId !== this.drag.id) return;
      const dx = event.clientX - this.drag.startX;
      const dy = event.clientY - this.drag.startY;
      // A small threshold so clicking a hero still selects it
      if (!this.drag.moved && Math.hypot(dx, dy) < 4) return;
      if (!this.drag.moved) {
        this.drag.moved = true;
        this.dragging = true;
        this.$refs.svg.setPointerCapture(event.pointerId);
      }
      const rect = this.$refs.svg.getBoundingClientRect();
      const [, , w, h] = this.view;
      const unitsPerPixel = Math.max(w / rect.width, h / rect.height);
      this.centre = [this.drag.centre[0] - dx * unitsPerPixel, this.drag.centre[1] - dy * unitsPerPixel];
    },
    onPointerUp(event) {
      if (this.drag && event.pointerId === this.drag.id && this.drag.moved) {
        this.$refs.svg.releasePointerCapture(event.pointerId);
        // Swallow the click that ends a drag
        this.justDragged = true;
        setTimeout(() => { this.justDragged = false; }, 0);
      }
      this.drag = null;
      this.dragging = false;
    },
    select(idx) {
      if (!this.justDragged) this.$emit('select', idx);
    },
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
