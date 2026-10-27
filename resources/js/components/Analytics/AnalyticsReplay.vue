<template>
  <div>
    <page-heading
      :heading="'Replay Viewer (Pre-Alpha)'"
      :infoText1="data ? (data.game_map || 'Unknown map') + ' - ' + clock(data.length_s) : 'Loading game'"
      :infoText2="'Pre-alpha: this is in very early development. Things will change, and some of what you see may be wrong or missing.'"
    ></page-heading>

    <!-- Page level, like the Talent Builder, so it fills the screen width -->
    <loading-component v-if="loading" :textoverride="preparing">Preparing replay, usually under a minute...</loading-component>

    <div v-else class="max-w-[1500px] mx-auto px-4 mt-4">
      <p v-if="error" class="text-center text-red">{{ error }}</p>
      <template v-else>
      <infobox class="mb-4" :input="'Watch the game play out on the minimap, with the in-game top bar, each team\'s win chance and a feed of events. Replays only record exact hero positions now and then, so hero positions between those moments are estimates.'"></infobox>
      <div class="flex flex-wrap gap-4">
        <!-- Map and controls -->
        <div class="flex-1 min-w-0 basis-[60%]">
          <!-- The announcer: win chance and commentary -->
          <analytics-replay-announcer :data="data" :events="events" :t="second"></analytics-replay-announcer>
          <!-- In-game style top bar, centred over the map -->
          <analytics-replay-scoreboard :data="data" :t="second" :selected="selected" @select="selected = $event"></analytics-replay-scoreboard>
          <analytics-replay-map
            :data="data"
            :tracks="tracks"
            :t="t"
            :selected="selected"
            :minimap="minimap"
            @select="selected = $event"
          ></analytics-replay-map>
          <analytics-replay-timeline
            :t="t"
            :length="data.length_s"
            :playing="playing"
            :speed="speed"
            :markers="markers"
            @toggle="toggle"
            @seek="seek"
            @speed="speed = $event"
          ></analytics-replay-timeline>
          <div class="flex flex-wrap items-center gap-3 mt-3 text-sm">
            <custom-button
              :text="'XP Graph'"
              :alt="'Jump to the team XP graph'"
              size="small"
              :ignoreclick="true"
              @click="$refs.xp.scrollIntoView({ behavior: 'smooth', block: 'start' })"
            ></custom-button>
            <span class="inline-flex items-center gap-1">
              Movement
              <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                <slot><p class="text-sm p-2 text-left">Replays only record exact positions now and then (position updates, spawns, globe pickups, deaths). Follow move orders walks heroes toward where they were ordered to move, faster while mounted and around walls; Smooth glides them between exact positions, also around walls. Follow is the more accurate of the two.</p></slot>
              </round-image>
            </span>
            <multi-tab-button :options="modeOptions" v-model="mode"></multi-tab-button>
            <span class="text-gray-light">
              {{ mode === 'follow' ? "Between the game's own position updates, heroes walk along their move orders." : "Between the game's own position updates, heroes glide straight to the next one." }} Space plays or pauses, arrows skip 5 seconds.
            </span>
          </div>

          <!-- Teams: level, takedowns, XP over the game -->
          <div ref="xp" class="bg-darken rounded p-3 mt-4 text-sm scroll-mt-4">
            <div v-for="team in [0, 1]" :key="'team' + team" class="flex justify-between items-center mb-1">
              <span class="font-bold" :style="{ color: teamColor(team) }">
                {{ team === 0 ? 'Blue' : 'Red' }}{{ yourTeam === team ? ' (your team)' : '' }}
              </span>
              <span>Level {{ levelAt(team) }} &middot; {{ killsAt(team) }} takedowns</span>
            </div>
            <div class="text-center text-gray-light mb-1">Team XP: {{ xpLead }}</div>
            <!-- The match page's experience chart, in this page's team colours and names -->
            <div class="w-full">
              <dual-line-chart :data="experience" :winner="winningTeam" :team-colors="[teamColor(0), teamColor(1)]" :team-names="['Blue', 'Red']"></dual-line-chart>
            </div>
          </div>
        </div>

        <!-- Panels -->
        <div class="w-[380px] max-md:w-full flex flex-col gap-4 text-sm">
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
                <div :style="{ opacity: aliveAt(p.idx) ? 1 : 0.35 }">
                  <hero-image-wrapper v-if="p.short_name" :hero="{ name: p.hero, short_name: p.short_name }" :size="'small'"></hero-image-wrapper>
                </div>
                <span class="flex-1 truncate">{{ p.battletag || p.hero }}<span v-if="p.you" class="text-yellow"> (you)</span></span>
                <span class="font-mono">{{ takedownsAt(p.idx) }} / {{ deathsAt(p.idx) }}</span>
              </div>
            </div>
            <div class="text-gray-light text-right">takedowns / deaths</div>
          </div>

          <!-- Selected player -->
          <div v-if="selectedPlayer" class="bg-darken rounded p-3">
            <div class="font-bold mb-2">{{ selectedPlayer.hero }}{{ selectedPlayer.battletag ? ' - ' + selectedPlayer.battletag : '' }}</div>
            <div class="flex flex-wrap gap-2 mb-2">
              <div v-for="pick in talentsAt(selected)" :key="pick.tier" class="flex flex-col items-center">
                <talent-image-wrapper :talent="pick.talent" :size="'small'"></talent-image-wrapper>
                <span class="text-xs text-gray-light">{{ clock(pick.t) }}</span>
              </div>
              <span v-if="talentsAt(selected).length === 0" class="text-gray-light">No talents picked yet</span>
            </div>
            <div>Globes picked up: {{ globesAt(selected) }}</div>
            <div v-if="disconnectedAt(selected)" class="text-red">Disconnected</div>
            <div class="text-gray-light mt-1">The white box on the map is roughly where their camera is.</div>
          </div>

          <!-- Event feed: newest first, up to the current game time; click one to jump just before it -->
          <div class="bg-darken rounded p-3">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
              <span class="font-bold">Events</span>
              <multi-select-filter
                :values="eventTypes"
                :text="'Show'"
                :defaultValue="defaultTypes"
                @input-changed="shownTypes = $event.value"
              ></multi-select-filter>
            </div>
            <div class="max-h-80 overflow-y-auto">
              <div
                v-for="(e, index) in feed"
                :key="index"
                class="flex gap-3 cursor-pointer hover:bg-lighten rounded px-1"
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

      <!-- How each player played, against the same hero on the same map -->
      <template v-if="!sample">
        <analytics-replay-performance
          :replay-id="replayId"
          :players="data.players"
          :selected="selected"
          :allowed="performanceAllowed"
          :patreon-linked="patreonLinked"
          @select="selected = $event"
        ></analytics-replay-performance>
        <analytics-paper v-if="performanceAllowed" :tab="'performance'"></analytics-paper>
      </template>
      </template>
    </div>
  </div>
</template>

<script>
import { markRaw } from 'vue';
import analytics from '../../utils/analyticsFormat.js';
import { buildTracks, positionAt } from '../../utils/replayTracks.js';
import { loadWalkable, minimapFor, teamColor, MAP_SHORTCUT_EXITS } from '../../utils/mapAssets.js';
import { loadWalkGrid } from '../../utils/replayPathing.js';

// The replay viewer's event feed and timeline markers, built once per game from its extract doc.
// Each event is { t, type, color, label, team }; `type` is one of EVENT_TYPES' codes and `team` is the
// team it helps (null when neither, e.g. a disconnect).

const EVENT_TYPES = [
  { code: 'kills', name: 'Kills', on: true },
  { code: 'multikills', name: 'First blood & multi-kills', on: true },
  { code: 'wipes', name: 'Team wipes', on: true },
  { code: 'structures', name: 'Forts, keeps & cores', on: true },
  { code: 'defenses', name: 'Towers, walls, gates & wells', on: false },
  { code: 'camps', name: 'Merc camps', on: true },
  { code: 'objectives', name: 'Map objectives', on: true },
  { code: 'levels', name: 'Talent levels', on: true },
  { code: 'talents', name: 'Talent picks', on: true },
  { code: 'xp', name: 'XP lead swaps', on: true },
  { code: 'globes', name: 'Regen globes', on: false },
  { code: 'disconnects', name: 'Disconnects', on: true },
];

const TIERS = [4, 7, 10, 13, 16, 20];
const FIGHT_GAP = 10; // seconds between deaths that still count as the same fight
const STRUCTURE = [
  [/^TownTownHallL2/, 'fort', 'structures'],
  [/^TownTownHallL3/, 'keep', 'structures'],
  [/^(KingsCore|VanndarStormpike|DrekThar)/, 'core', 'structures'],
  [/^TownCannonTower/, 'tower', 'defenses'],
  [/^TownWall/, 'wall', 'defenses'],
  [/^TownGate/, 'gate', 'defenses'],
  [/^TownMoonwell/, 'healing well', 'defenses'],
];
const STREAK = { 3: 'triple', 4: 'quadruple', 5: 'quintuple' };

function buildEvents(data, clock) {
  const hero = idx => data.players[idx]?.hero ?? '?';
  const team = idx => data.players[idx]?.team ?? 0;
  const side = t => (t === 0 ? 'Blue' : 'Red');
  const names = list => list.map(hero).join(', ');
  const events = [];
  const add = (t, type, color, label, helps = null) => events.push({ t, type, color, label, team: helps });
  const deaths = [...data.deaths].sort((a, b) => a.t - b.t);

  // Kills, first blood
  deaths.forEach((d, i) => {
    const color = teamColor(1 - team(d.v));
    add(d.t, 'kills', color, (names(d.k) || 'Something') + ' killed ' + hero(d.v), 1 - team(d.v));
    if (i === 0) add(d.t, 'multikills', color, 'First blood: ' + hero(d.v) + ' died', 1 - team(d.v));
  });

  // Multi-takedowns: three or more of a hero's takedowns with no more than FIGHT_GAP between them (doubles
  // are in nearly every fight), at the end of the run
  data.players.forEach(p => {
    const times = deaths.filter(d => d.k.includes(p.idx)).map(d => d.t);
    chains(times).forEach(chain => {
      if (chain.length >= 3) {
        add(chain[chain.length - 1], 'multikills', teamColor(p.team), p.hero + ': ' + (STREAK[chain.length] ?? chain.length) + ' takedown', p.team);
      }
    });
  });

  // Team wipes: 3+ heroes of one team down in one fight, all 5 is an ace
  [0, 1].forEach(t => {
    const times = deaths.filter(d => team(d.v) === t).map(d => d.t);
    chains(times).forEach(chain => {
      if (chain.length >= 5) add(chain[chain.length - 1], 'wipes', teamColor(1 - t), 'Ace: ' + side(t) + ' wiped', 1 - t);
      else if (chain.length >= 3) add(chain[chain.length - 1], 'wipes', teamColor(1 - t), side(t) + ' lost ' + chain.length + ' heroes in a fight', 1 - t);
    });
  });

  // Structures, with who got the kill when there was one
  data.structures.buildings.forEach(b => {
    const match = STRUCTURE.find(([pattern]) => pattern.test(b.name));
    if (!match) return;
    const by = b.killer >= 0 ? ' by ' + hero(b.killer) : '';
    add(b.t, match[2], match[2] === 'structures' ? '#bbbbbb' : '#888888', side(b.team) + ' ' + match[1] + ' destroyed' + by, 1 - b.team);
  });

  data.camps.forEach(c => {
    add(c.t, 'camps', '#d98c40', side(c.team) + ' took a ' + c.type.toLowerCase(), c.team);
  });

  // Map objectives: the game reports several stats for one moment, so they're joined into one line
  const moments = new Map();
  // Objective types start with the map's name ('InfernalShrines...'), which the feed doesn't need
  const mapPrefix = (data.game_map || '').replace(/[^A-Za-z]/g, '');
  data.objectives.forEach(o => {
    const key = o.t + ':' + o.team;
    if (!moments.has(key)) moments.set(key, { t: o.t, team: o.team, parts: [] });
    const value = o.v >= 0 ? ' ' + o.v.toLocaleString('en-US') : '';
    const who = o.p >= 0 ? ' (' + hero(o.p) + ')' : '';
    moments.get(key).parts.push(humanize(o.type.startsWith(mapPrefix) ? o.type.slice(mapPrefix.length) : o.type) + value + who);
  });
  moments.forEach(m => add(m.t, 'objectives', teamColor(m.team), side(m.team) + ': ' + m.parts.join('; '), m.team));

  data.team_levels.filter(l => TIERS.includes(l.lvl)).forEach(l => {
    add(l.t, 'levels', '#f2b134', side(l.team) + ' reached level ' + l.lvl, l.team);
  });

  data.talents.forEach(pick => {
    const level = pick.talent?.level ?? [1, 4, 7, 10, 13, 16, 20][pick.tier];
    add(pick.t, 'talents', teamColor(team(pick.p)), hero(pick.p) + ' took ' + (pick.talent?.title ?? 'a talent') + ' (level ' + level + ')', team(pick.p));
  });

  // XP lead swaps, from the periodic team XP totals
  const total = b => b.minion + b.creep + b.structure + b.hero + b.trickle;
  const byTime = new Map();
  data.xp_breakdown.forEach(b => {
    if (!byTime.has(b.t)) byTime.set(b.t, [null, null]);
    byTime.get(b.t)[b.team] = total(b);
  });
  let leader = null;
  [...byTime.entries()].sort((a, b) => a[0] - b[0]).forEach(([t, [blue, red]]) => {
    if (blue === null || red === null || blue === red) return;
    const now = blue > red ? 0 : 1;
    if (leader !== null && now !== leader) {
      add(t, 'xp', teamColor(now), side(now) + ' took the XP lead (+' + Math.abs(blue - red).toLocaleString('en-US') + ')', now);
    }
    leader = now;
  });

  data.globe_points.forEach(([t, p]) => {
    add(t, 'globes', teamColor(team(p)), hero(p) + ' picked up a regen globe', team(p));
  });

  // Leaving in the last few seconds is just the game ending
  data.disconnects.forEach(d => {
    if (d.left >= data.length_s - 10) return;
    add(d.left, 'disconnects', '#b33616', hero(d.p) + ' disconnected');
    if (d.rejoined !== null) add(d.rejoined, 'disconnects', '#008b8b', hero(d.p) + ' reconnected (' + clock(d.rejoined - d.left) + ' later)');
  });

  return events.sort((a, b) => a.t - b.t);
}

// Runs of times with no gap bigger than FIGHT_GAP
function chains(times) {
  const out = [];
  times.forEach(t => {
    const last = out[out.length - 1];
    if (last && t - last[last.length - 1] <= FIGHT_GAP) last.push(t);
    else out.push([t]);
  });
  return out;
}

// 'InfernalShrinesPunisherKilledWithSiegeDamageDone' -> 'Infernal shrines punisher killed with siege damage done'
function humanize(type) {
  const words = type.replace(/([a-z])([A-Z])/g, '$1 $2').replace(/([A-Z]+)([A-Z][a-z])/g, '$1 $2').toLowerCase();
  return words.charAt(0).toUpperCase() + words.slice(1);
}


// One game played back on the minimap from its extract doc, with team and player panels.
export default {
  name: 'AnalyticsReplay',
  mixins: [analytics],
  props: {
    replayId: Number,
    urlparameters: { type: Object, default: () => ({}) },
    // The public sample's game: already prepared, read from the sample files
    sample: Boolean,
    // Match performance: Patreon supporters only; everyone else sees it greyed out
    performanceAllowed: Boolean,
    patreonLinked: Boolean,
  },
  data() {
    return {
      data: null,
      loading: true,
      error: '',
      tracks: markRaw(new Map()),
      walkable: null,
      walkGrid: null,
      mode: 'follow',
      modeOptions: [
        { key: 'follow', label: 'Follow move orders' },
        { key: 'smooth', label: 'Smooth' },
      ],
      t: 0,
      playing: false,
      speed: 4,
      selected: null,
      preparing: false,
      stopWaiting: false,
      eventTypes: EVENT_TYPES.map(({ code, name }) => ({ code, name })),
      // The filter copies defaultValue whenever it changes and emits on every copy, so it gets a fixed
      // starting list; binding shownTypes there makes the two update each other forever
      defaultTypes: EVENT_TYPES.filter(type => type.on).map(type => type.code),
      shownTypes: EVENT_TYPES.filter(type => type.on).map(type => type.code),
      frame: null,
      lastFrame: null,
    };
  },
  async mounted() {
    window.addEventListener('keydown', this.onKey);
    try {
      if (!this.sample) {
        await this.ensureReady();
      }
      const response = await this.$axios.get(this.sample ? '/Analytics/Sample/Match/Single/Data' : '/Analytics/Match/Single/' + this.replayId + '/Data');
      this.data = response.data;
      this.selected = this.data.players.find(p => p.you)?.idx ?? null;
      [this.walkable, this.walkGrid] = await Promise.all([loadWalkable(this.minimap), loadWalkGrid(this.minimap)]);
      this.rebuild();
      // ?t= opens the game a few seconds before that moment (coaching examples link here)
      const start = Number((this.urlparameters || {}).t);
      if (start > 0) {
        this.t = Math.max(0, Math.min(start - 5, this.data.length_s));
      }
    } catch (e) {
      this.error = e.message === 'could not start'
        ? 'Could not start preparing this replay. Please try again in a minute.'
        : this.preparing
          ? 'This replay is taking longer than usual to prepare. Please try again in a few minutes.'
          : 'Could not load this game. Please try again in a minute.';
    } finally {
      this.loading = false;
    }
  },
  beforeUnmount() {
    this.stopWaiting = true;
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
    // Everything worth a marker or a feed line, in time order
    events() {
      return buildEvents(this.data, this.clock);
    },
    shownEvents() {
      return this.events.filter(e => this.shownTypes.includes(e.type));
    },
    markers() {
      return this.shownEvents;
    },
    // Whole game seconds, so the feed is rebuilt when the clock passes a second rather than every frame
    second() {
      return Math.floor(this.t);
    },
    feed() {
      return this.shownEvents.filter(e => e.t <= this.second).slice(-60).reverse();
    },
    xpSeries() {
      const total = b => b.minion + b.creep + b.structure + b.hero + b.trickle;
      return [0, 1].map(team => this.data.xp_breakdown.filter(b => b.team === team).map(b => [b.t, total(b)]));
    },
    winningTeam() {
      return this.data.players.find(p => p.winner)?.team ?? 0;
    },
    // Changes once a game minute, so the chart below only redraws then
    currentMinute() {
      return Math.floor(this.t / 60);
    },
    // Per-minute XP lead and level for each team, in the shape the match page's chart takes, up to
    // the current game time; later minutes stay on the axis but empty so the lines grow as it plays
    experience() {
      const byMinute = [{}, {}];
      const total = b => b.minion + b.creep + b.structure + b.hero + b.trickle;
      this.data.xp_breakdown.forEach(b => {
        byMinute[b.team][Math.floor(b.t / 60)] = { xp: total(b), level: b.lvl };
      });
      const minutes = [...new Set(Object.keys(byMinute[0]).concat(Object.keys(byMinute[1])).map(Number))].sort((a, b) => a - b);
      const xp = (team, m) => byMinute[team][m]?.xp ?? 0;
      const upTo = (m, value) => (m <= this.currentMinute ? value : null);
      return {
        x_axis_time: minutes,
        team_one_differences: minutes.map(m => upTo(m, Math.max(0, xp(0, m) - xp(1, m)))),
        team_two_differences: minutes.map(m => upTo(m, Math.max(0, xp(1, m) - xp(0, m)))),
        team_one_level: minutes.map(m => upTo(m, byMinute[0][m]?.level ?? null)),
        team_two_level: minutes.map(m => upTo(m, byMinute[1][m]?.level ?? null)),
      };
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
    // A game nobody has processed yet: start it, then wait until it's parsed and loaded
    async ensureReady() {
      const url = '/Analytics/Match/Single/' + this.replayId;
      if ((await this.$axios.get(url + '/Status')).data.ready) return;
      this.preparing = true;
      try {
        await this.$axios.post(url + '/Process');
      } catch (e) {
        throw new Error('could not start');
      }
      const giveUp = Date.now() + 5 * 60 * 1000;
      while (!this.stopWaiting) {
        await new Promise(resolve => setTimeout(resolve, 3000));
        try {
          if ((await this.$axios.get(url + '/Status')).data.ready) {
            this.preparing = false;
            return;
          }
        } catch (e) {
          // A failed check (a blip, a server restart) is retried on the next one
        }
        if (Date.now() > giveUp) throw new Error('timed out');
      }
    },
    rebuild() {
      if (!this.data) return;
      const shortcutExits = (this.minimap && MAP_SHORTCUT_EXITS[this.minimap.slug]) || [];
      this.tracks = markRaw(buildTracks(this.data, { mode: this.mode, walkable: this.walkable, walkGrid: this.walkGrid, shortcutExits }));
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
  },
};
</script>
