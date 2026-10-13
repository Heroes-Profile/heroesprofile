<template>
  <div>
    <page-heading
      :heading="'Replay Viewer'"
      :infoText1="data ? (data.game_map || 'Unknown map') + ' - ' + clock(data.length_s) : 'Loading game'"
    ></page-heading>

    <div class="max-w-[1500px] mx-auto px-4 mt-4">
      <loading-component v-if="loading"></loading-component>
      <p v-else-if="error" class="text-center text-red">{{ error }}</p>
      <div v-else class="flex flex-wrap gap-4">
        <!-- Map and controls -->
        <div class="flex-1 min-w-0 basis-[60%]">
          <deep-analytics-replay-map
            :data="data"
            :tracks="tracks"
            :t="t"
            :selected="selected"
            :minimap="minimap"
            @select="selected = $event"
          ></deep-analytics-replay-map>
          <deep-analytics-replay-timeline
            :t="t"
            :length="data.length_s"
            :playing="playing"
            :speed="speed"
            :markers="markers"
            @toggle="toggle"
            @seek="seek"
            @speed="speed = $event"
          ></deep-analytics-replay-timeline>
          <div class="flex flex-wrap items-center gap-3 mt-3 text-sm">
            <span>Movement</span>
            <multi-tab-button :options="modeOptions" v-model="mode"></multi-tab-button>
            <span class="text-gray-light">
              Positions between the game's own updates are estimated from move orders. Space plays or pauses, arrows skip 5 seconds.
            </span>
          </div>
        </div>

        <!-- Panels -->
        <div class="w-[380px] max-md:w-full flex flex-col gap-4 text-sm">
          <!-- Teams: level, takedowns, XP over the game -->
          <div class="bg-darken rounded p-3">
            <div v-for="team in [0, 1]" :key="'team' + team" class="flex justify-between items-center mb-1">
              <span class="font-bold" :style="{ color: teamColor(team) }">
                {{ team === 0 ? 'Blue' : 'Red' }}{{ yourTeam === team ? ' (your team)' : '' }}
              </span>
              <span>Level {{ levelAt(team) }} &middot; {{ killsAt(team) }} takedowns</span>
            </div>
            <svg viewBox="0 0 100 30" class="w-full h-20 mt-2" preserveAspectRatio="none">
              <polyline v-for="team in [0, 1]" :key="'xp' + team" :points="xpLine(team)" fill="none" :stroke="teamColor(team)" stroke-width="0.8" vector-effect="non-scaling-stroke"></polyline>
              <line :x1="100 * t / data.length_s" :x2="100 * t / data.length_s" y1="0" y2="30" stroke="#ffffff" stroke-width="1" vector-effect="non-scaling-stroke" stroke-opacity="0.6"></line>
            </svg>
            <div class="text-center text-gray-light">Team XP; {{ xpLead }}</div>
          </div>

          <!-- Players -->
          <div class="bg-darken rounded p-3">
            <div v-for="team in [0, 1]" :key="'players' + team" class="mb-2">
              <div
                v-for="p in playersOn(team)"
                :key="p.idx"
                class="flex items-center gap-2 py-0.5 px-1 rounded cursor-pointer"
                :class="{ 'bg-lighten': p.idx === selected }"
                @click="selected = p.idx"
              >
                <img v-if="p.short_name" :src="'/images/heroes/circular/' + p.short_name + '.png'" class="w-6 h-6 rounded-full border-2" :style="{ borderColor: teamColor(team), opacity: aliveAt(p.idx) ? 1 : 0.35 }" :alt="p.hero">
                <span class="flex-1 truncate">{{ p.battletag || p.hero }}<span v-if="p.you" class="text-yellow"> (you)</span></span>
                <span class="font-mono">{{ takedownsAt(p.idx) }} / {{ deathsAt(p.idx) }}</span>
              </div>
            </div>
            <div class="text-gray-light text-right">takedowns / deaths</div>
          </div>

          <!-- Selected player -->
          <div v-if="selectedPlayer" class="bg-darken rounded p-3">
            <div class="font-bold mb-2">{{ selectedPlayer.hero }}{{ selectedPlayer.battletag ? ' - ' + selectedPlayer.battletag : '' }}</div>
            <div class="flex flex-wrap gap-1 mb-2">
              <img
                v-for="talent in talentsAt(selected)"
                :key="talent.tier"
                :src="talent.icon ? '/images/talents/' + talent.icon : '/images/talents/no-image.png'"
                :title="'Level tier ' + (talent.tier + 1) + ': ' + talent.title + ' (' + clock(talent.t) + ')'"
                :alt="talent.title"
                class="w-8 h-8 rounded"
              >
              <span v-if="talentsAt(selected).length === 0" class="text-gray-light">No talents picked yet</span>
            </div>
            <div>Globes picked up: {{ globesAt(selected) }}</div>
            <div v-if="disconnectedAt(selected)" class="text-red">Disconnected</div>
            <div class="text-gray-light mt-1">The white box on the map is roughly where their camera is.</div>
          </div>

          <!-- Event feed -->
          <div class="bg-darken rounded p-3">
            <div class="font-bold mb-1">Events</div>
            <div
              v-for="(e, index) in feed"
              :key="index"
              class="flex gap-2 cursor-pointer hover:bg-lighten rounded px-1"
              @click="seek(Math.max(0, e.t - 5))"
            >
              <span class="font-mono text-gray-light">{{ clock(e.t) }}</span>
              <span :style="{ color: e.color }">{{ e.label }}</span>
            </div>
            <div v-if="feed.length === 0" class="text-gray-light">Nothing yet</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { markRaw } from 'vue';
import analytics from './deepAnalyticsFormat.js';
import { buildTracks, positionAt } from './replayTracks.js';
import { loadWalkable, minimapFor, teamColor } from './mapAssets.js';

const FALLEN = { TownTownHallL2: 'fort', TownTownHallL3: 'keep', KingsCore: 'core', VanndarStormpike: 'core', DrekThar: 'core' };
const TIERS = [4, 7, 10, 13, 16, 20];

// One game played back on the minimap from its deep doc, with team and player panels.
export default {
  name: 'DeepAnalyticsReplay',
  mixins: [analytics],
  props: {
    replayId: Number,
  },
  data() {
    return {
      data: null,
      loading: true,
      error: '',
      tracks: markRaw(new Map()),
      walkable: null,
      mode: 'follow',
      modeOptions: [
        { key: 'follow', label: 'Follow move orders' },
        { key: 'smooth', label: 'Smooth' },
      ],
      t: 0,
      playing: false,
      speed: 4,
      selected: null,
      frame: null,
      lastFrame: null,
    };
  },
  async mounted() {
    window.addEventListener('keydown', this.onKey);
    try {
      const response = await this.$axios.get('/DeepAnalytics/Replay/' + this.replayId + '/Data');
      this.data = response.data;
      this.selected = this.data.players.find(p => p.you)?.idx ?? null;
      this.walkable = await loadWalkable(this.minimap);
      this.rebuild();
    } catch (e) {
      this.error = 'Could not load this game. Please try again in a minute.';
    } finally {
      this.loading = false;
    }
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.onKey);
    cancelAnimationFrame(this.frame);
  },
  watch: {
    mode() {
      this.rebuild();
    },
  },
  computed: {
    minimap() {
      return this.data ? minimapFor(this.data.game_map) : null;
    },
    yourTeam() {
      return this.data.players.find(p => p.you)?.team ?? null;
    },
    selectedPlayer() {
      return this.data.players.find(p => p.idx === this.selected) || null;
    },
    hero() {
      return idx => this.data.players[idx]?.hero ?? '?';
    },
    // Everything worth a marker or a feed line, in time order
    events() {
      const events = [];
      this.data.deaths.forEach(d => {
        const victim = this.data.players[d.v];
        const killers = d.k.map(k => this.hero(k)).join(', ');
        events.push({
          t: d.t,
          color: teamColor(1 - (victim?.team ?? 0)),
          label: (killers || 'Something') + ' killed ' + (victim?.hero ?? '?'),
        });
      });
      this.data.units.filter(u => FALLEN[u.name] && u.d).forEach(u => {
        events.push({ t: u.d[0], color: '#bbbbbb', label: (u.team === 0 ? 'Blue' : 'Red') + ' ' + FALLEN[u.name] + ' destroyed' });
      });
      this.data.camps.forEach(c => {
        events.push({ t: c.t, color: '#d98c40', label: (c.team === 0 ? 'Blue' : 'Red') + ' took a ' + c.type.toLowerCase() });
      });
      this.data.team_levels.filter(l => TIERS.includes(l.lvl)).forEach(l => {
        events.push({ t: l.t, color: '#f2b134', label: (l.team === 0 ? 'Blue' : 'Red') + ' reached level ' + l.lvl });
      });
      return events.sort((a, b) => a.t - b.t);
    },
    markers() {
      return this.events;
    },
    feed() {
      return this.events.filter(e => e.t <= this.t).slice(-12).reverse();
    },
    xpSeries() {
      const total = b => b.minion + b.creep + b.structure + b.hero + b.trickle;
      return [0, 1].map(team => this.data.xp_breakdown.filter(b => b.team === team).map(b => [b.t, total(b)]));
    },
    xpMax() {
      return Math.max(1, ...this.xpSeries.flat().map(p => p[1]));
    },
    xpLead() {
      const at = this.xpSeries.map(series => {
        let value = 0;
        series.forEach(([t, xp]) => {
          if (t <= this.t) value = xp;
        });
        return value;
      });
      const lead = at[0] - at[1];
      if (lead === 0) return 'even';
      return (lead > 0 ? 'Blue' : 'Red') + ' ahead by ' + Math.abs(lead).toLocaleString('en-US');
    },
  },
  methods: {
    teamColor,
    rebuild() {
      if (!this.data) return;
      this.tracks = markRaw(buildTracks(this.data, { mode: this.mode, walkable: this.walkable }));
    },
    toggle() {
      this.playing = !this.playing;
      if (this.playing) {
        if (this.t >= this.data.length_s) this.t = 0;
        this.lastFrame = null;
        this.frame = requestAnimationFrame(this.tick);
      } else {
        cancelAnimationFrame(this.frame);
      }
    },
    tick(now) {
      if (!this.playing) return;
      if (this.lastFrame !== null) {
        this.t = Math.min(this.data.length_s, this.t + (now - this.lastFrame) / 1000 * this.speed);
      }
      this.lastFrame = now;
      if (this.t >= this.data.length_s) {
        this.playing = false;
        return;
      }
      this.frame = requestAnimationFrame(this.tick);
    },
    seek(value) {
      this.t = Math.max(0, Math.min(this.data.length_s, value));
    },
    onKey(e) {
      if (!this.data || ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
      if (e.code === 'Space') {
        e.preventDefault();
        this.toggle();
      } else if (e.code === 'ArrowLeft') {
        this.seek(this.t - 5);
      } else if (e.code === 'ArrowRight') {
        this.seek(this.t + 5);
      }
    },
    playersOn(team) {
      return this.data.players.filter(p => p.team === team);
    },
    aliveAt(idx) {
      return positionAt(this.tracks.get(idx), this.t) !== null;
    },
    levelAt(team) {
      let level = 1;
      this.data.team_levels.forEach(l => {
        if (l.team === team && l.t <= this.t && l.lvl > level) level = l.lvl;
      });
      return level;
    },
    killsAt(team) {
      return this.data.deaths.filter(d => d.t <= this.t && this.data.players[d.v]?.team !== team).length;
    },
    takedownsAt(idx) {
      return this.data.deaths.filter(d => d.t <= this.t && d.k.includes(idx)).length;
    },
    deathsAt(idx) {
      return this.data.deaths.filter(d => d.t <= this.t && d.v === idx).length;
    },
    talentsAt(idx) {
      return this.data.talents.filter(talent => talent.p === idx && talent.t <= this.t);
    },
    globesAt(idx) {
      return this.data.globe_points.filter(g => g[1] === idx && g[0] <= this.t).length;
    },
    disconnectedAt(idx) {
      return this.data.disconnects.some(d => d.p === idx && d.left <= this.t && (d.rejoined === null || d.rejoined > this.t));
    },
    xpLine(team) {
      const length = this.data.length_s;
      return this.xpSeries[team].map(([t, xp]) => `${(100 * t / length).toFixed(2)},${(30 - 28 * xp / this.xpMax).toFixed(2)}`).join(' ');
    },
  },
};
</script>
