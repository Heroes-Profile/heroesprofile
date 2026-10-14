<template>
  <div class="bg-darken rounded p-3 mb-2 text-sm">
    <!-- Win chance, split between the teams -->
    <div class="flex justify-between text-xs mb-1">
      <span :style="{ color: teamColor(0) }">Blue {{ pct(chance) }}</span>
      <span :style="{ color: teamColor(1) }">{{ pct(1 - chance) }} Red</span>
    </div>
    <div class="flex h-2 rounded overflow-hidden bg-gray-dark">
      <div class="transition-all" :style="{ width: 100 * chance + '%', background: teamColor(0) }"></div>
      <div class="transition-all" :style="{ width: 100 * (1 - chance) + '%', background: teamColor(1) }"></div>
    </div>

    <!-- Comic style: the logo is the announcer, its latest line in a speech bubble pointing at it -->
    <div class="flex items-center gap-4 mt-3">
      <img
        src="/images/logo/heroesprofilelogo.png"
        alt="Heroes Profile"
        title="Heroes Profile"
        class="w-14 h-auto shrink-0 announcer-logo"
        :class="{ talking }"
        @animationend="talking = false"
      >
      <!-- Always two lines tall so the page never shifts; longer lines are cut, full text on hover -->
      <div class="speech-bubble flex-1" :class="{ pop: talking }" :title="said.length ? fullText(said[0]) : ''">
        <div class="line-clamp-2">
          <template v-if="said.length">
            <span class="font-mono bubble-time mr-2">{{ clock(said[0].t) }}</span>
            <span class="font-bold">{{ said[0].text }}</span>
            <span v-if="said[0].why" class="bubble-why ml-1">Why: {{ said[0].why }}.</span>
          </template>
          <span v-else>Waiting for the gates to open.</span>
        </div>
      </div>
    </div>

    <!-- The two lines before it: one line each, and the space is kept even before there are two -->
    <div class="mt-2 ml-[4.5rem] h-8 text-xs opacity-60">
      <div v-for="(line, index) in said.slice(1)" :key="line.t + ':' + index" class="truncate" :title="fullText(line)">
        <span class="font-mono text-gray-light mr-2">{{ clock(line.t) }}</span>
        <span>{{ line.text }}</span>
      </div>
    </div>

    <div class="mt-2 text-xs text-gray-light">
      {{ source === 'heuristic'
        ? 'Estimated from levels, XP, structures and deaths; not yet trained on past games.'
        : 'Win chance from a model trained on ' + model.games.toLocaleString('en-US') + ' games; the favourite wins about 7 times in 10.' }}
    </div>
  </div>
</template>

<script>
import analytics from '../../utils/deepAnalyticsFormat.js';
import { teamColor } from '../../utils/mapAssets.js';

// Win chance for the replay viewer's announcer. A logistic model over the game state at one moment,
// always from Blue's side (positive favours Blue). The weights below are a hand-tuned starting point;
// weights trained on the baseline sample replace them later with the same features, so nothing else
// changes when they do.

const MODEL = {
  // Trained in BigQuery ML (deep_analytics.win_model) on 86,476 minute snapshots of 5,023 baseline games;
  // on held-out games the favourite wins 71% overall, 78% from minute 10 to 20
  source: 'trained',
  trainedOn: '2026-10-14',
  games: 5023,
  intercept: -0.0528,
  weights: {
    level_diff: 0.2856,
    level_diff_late: 0.169, // level_diff x minutes / 10
    xp_diff_k: 0.047, // XP lead in thousands
    forts_diff: 0.3226, // enemy forts destroyed minus own forts lost
    keeps_diff: 0.3911,
    dead_diff: 0.1035, // Red heroes dead minus Blue heroes dead, right now (see respawnEstimate)
    dead_diff_late: 0.0546, // dead_diff x minutes / 10
    takedown_diff: 0.0297,
  },
};

// The model was trained without respawn times (BigQuery doesn't have them), counting a hero as dead for
// this long after dying; the same estimate is used here so the feature means what it did in training.
// Measured from the docs: 15 s + 1.2 s a level to 10, then 2.5 s a level, at most 75 s.
function respawnEstimate(level) {
  return Math.min(75, level <= 10 ? 15 + 1.2 * (level - 1) : 25.8 + 2.5 * (level - 10));
}

const FALLEN = { TownTownHallL2: 'fort', TownTownHallL3: 'keep' };

/** Everything the model needs, sorted once per game. */
function buildContext(data) {
  const levels = [0, 1].map(team => data.team_levels.filter(l => l.team === team).sort((a, b) => a.t - b.t));
  const total = b => b.minion + b.creep + b.structure + b.hero + b.trickle;
  const xp = [0, 1].map(team => data.xp_breakdown.filter(b => b.team === team).map(b => [b.t, total(b)]).sort((a, b) => a[0] - b[0]));
  // Structures by the team that lost them
  const fallen = data.units.filter(u => FALLEN[u.name] && u.d).map(u => ({ t: u.d[0], team: u.team, kind: FALLEN[u.name] }));
  const deaths = [...data.deaths].sort((a, b) => a.t - b.t);
  const teamOf = Object.fromEntries(data.players.map(p => [p.idx, p.team]));
  // Each death with how long the model counts it, from the victim team's level at the time
  const levelAt = (team, t) => {
    let level = 1;
    for (const l of levels[team]) {
      if (l.t > t) break;
      level = l.lvl;
    }
    return level;
  };
  const downs = deaths.map(d => ({ t: d.t, team: teamOf[d.v], until: d.t + respawnEstimate(levelAt(teamOf[d.v], d.t)) }));
  return { levels, xp, fallen, deaths, teamOf, downs, gates: data.gates_open ?? 0 };
}

function latest(series, t, pick) {
  let value = null;
  for (const item of series) {
    if (pick(item)[0] > t) break;
    value = pick(item)[1];
  }
  return value;
}

/** The feature values at game time t, from Blue's side. */
function features(ctx, t) {
  const level = team => latest(ctx.levels[team], t, l => [l.t, l.lvl]) ?? 1;
  const xp = team => latest(ctx.xp[team], t, b => b) ?? 0;
  const lost = (team, kind) => ctx.fallen.filter(f => f.team === team && f.kind === kind && f.t <= t).length;
  // Heroes in no life at t, counted only once the game has started
  const dead = team => ctx.downs.filter(d => d.team === team && d.t <= t && t < d.until).length;
  const takedowns = team => ctx.deaths.filter(d => d.t <= t && ctx.teamOf[d.v] !== team).length;
  const minutes = Math.min(t, 1800) / 600;

  const levelDiff = level(0) - level(1);
  const deadDiff = dead(1) - dead(0);
  return {
    level_diff: levelDiff,
    level_diff_late: levelDiff * minutes,
    xp_diff_k: (xp(0) - xp(1)) / 1000,
    forts_diff: lost(1, 'fort') - lost(0, 'fort'),
    keeps_diff: lost(1, 'keep') - lost(0, 'keep'),
    dead_diff: deadDiff,
    dead_diff_late: deadDiff * minutes,
    takedown_diff: takedowns(0) - takedowns(1),
    // For the commentary, not the model
    _levels: [level(0), level(1)],
    _dead: [dead(0), dead(1)],
  };
}

/** Blue's chance to win, 0..1. */
function blueChance(f, model = MODEL) {
  let z = model.intercept;
  for (const [name, weight] of Object.entries(model.weights)) z += weight * (f[name] ?? 0);
  return 1 / (1 + Math.exp(-z));
}

/** Each feature's push toward Blue (positive) or Red (negative). */
function contributions(f, model = MODEL) {
  return Object.entries(model.weights).map(([name, weight]) => ({ name, value: weight * (f[name] ?? 0) }));
}

// The "Heroes Profile" announcer: Blue's win chance for every second of the game, and lines of
// commentary at the moments worth calling out, built once per game.

const SWING_WINDOW = 20; // seconds to look back for a swing
const SWING = 0.12; // a change in win chance this big within the window is a swing
const QUIET = 150; // after this long without a line, sum up the state of the game
const CAUSE_ORDER = ['wipes', 'structures', 'levels', 'camps', 'objectives', 'kills'];
// Called out as they happen, in the feed's own words
const CALLED = ['kills', 'multikills', 'wipes', 'structures'];

function buildAnnouncer(data, events, model = MODEL) {
  const ctx = buildContext(data);
  const side = t => (t === 0 ? 'Blue' : 'Red');
  const chance = [];
  const state = [];
  for (let t = 0; t <= data.length_s; t++) {
    const f = features(ctx, t);
    state.push(f);
    chance.push(t < ctx.gates ? 0.5 : blueChance(f, model));
  }

  const lines = [];
  const say = (t, text, why = null) => lines.push({ t, text, why });
  const pct = p => Math.round(p * 100) + '%';
  const favourite = p => (p >= 0.5 ? 0 : 1);
  const odds = p => (p >= 0.5 ? p : 1 - p);
  const reasons = (t, team) => why(state[t], team, model);

  say(ctx.gates, 'The gates are open on ' + (data.game_map || 'this map') + '. Nothing separates the teams yet.');

  let lastLine = ctx.gates;
  let lastSwing = -Infinity;
  let leader = null;
  const firstTo = { 10: null, 20: null };

  const calls = new Map();
  events.filter(e => CALLED.includes(e.type)).forEach(e => {
    if (!calls.has(e.t)) calls.set(e.t, []);
    calls.get(e.t).push(e.label);
  });

  for (let t = ctx.gates + 1; t <= data.length_s; t++) {
    const p = chance[t];

    // Kills, first blood, multi-takedowns, wipes, forts and keeps
    for (const label of calls.get(t) || []) {
      say(t, label + '.');
      lastLine = t;
    }

    // First to the big talent tiers
    for (const level of [10, 20]) {
      if (firstTo[level] === null) {
        const team = [0, 1].find(k => state[t]._levels[k] >= level);
        if (team !== undefined) {
          firstTo[level] = team;
          say(t, side(team) + ' hit level ' + level + ' first' + (level === 10 ? '; heroics are online.' : '; the final talents are in.'));
          lastLine = t;
        }
      }
    }

    // Swings: a big change in a short time, explained by the biggest thing that just happened
    const before = chance[Math.max(ctx.gates, t - SWING_WINDOW)];
    if (Math.abs(p - before) >= SWING && t - lastSwing > SWING_WINDOW) {
      const gainer = p > before ? 0 : 1;
      const cause = causeOf(events, t - SWING_WINDOW, t, gainer);
      const from = gainer === 0 ? before : 1 - before;
      const to = gainer === 0 ? p : 1 - p;
      // Reasons only when the swing put them in front; otherwise they'd describe the other team's lead
      say(t, 'Big swing to ' + side(gainer) + (cause ? ' after ' + cause : '') + ': ' + pct(from) + ' to ' + pct(to) + '.', favourite(p) === gainer ? reasons(t, gainer) : null);
      lastSwing = t;
      lastLine = t;
    }

    // A new favourite, once it has held for 10 seconds
    const now = Math.abs(p - 0.5) < 0.03 ? null : favourite(p);
    if (now !== null && now !== leader && t >= ctx.gates + 10 && favourite(chance[t - 10]) === now && Math.abs(chance[t - 10] - 0.5) >= 0.03) {
      if (leader !== null) {
        say(t, side(now) + ' are now favoured at ' + pct(odds(p)) + '.', reasons(t, now));
        lastLine = t;
      }
      leader = now;
    }

    // Quiet stretch: sum up
    if (t - lastLine >= QUIET) {
      const team = favourite(p);
      const r = reasons(t, team);
      say(t, odds(p) < 0.55 ? 'Still close: ' + side(0) + ' ' + pct(p) + ', ' + side(1) + ' ' + pct(1 - p) + '.' : side(team) + ' favoured at ' + pct(odds(p)) + '.', r);
      lastLine = t;
    }
  }

  return { chance, lines, source: model.source };
}

// The most telling event in (from, to] that helped `team`, for a swing line
function causeOf(events, from, to, team) {
  const recent = events.filter(e => e.t > from && e.t <= to && e.team === team);
  for (const type of CAUSE_ORDER) {
    const hit = recent.filter(e => e.type === type);
    if (hit.length === 0) continue;
    if (type === 'kills') return hit.length === 1 ? 'a kill' : hit.length + ' kills';
    return hit[hit.length - 1].label;
  }
  return null;
}

// Why `team` is ahead, from the biggest contributions in its favour, in plain words
function why(f, team, model = MODEL) {
  const sign = team === 0 ? 1 : -1;
  const grouped = {};
  for (const c of contributions(f, model)) {
    const key = c.name.replace(/_late$/, '');
    grouped[key] = (grouped[key] || 0) + c.value * sign;
  }
  const phrase = {
    level_diff: () => plural(Math.abs(f.level_diff), 'level') + ' ahead',
    xp_diff_k: () => (Math.abs(f.xp_diff_k)).toFixed(1) + 'k XP ahead',
    forts_diff: () => plural(Math.abs(f.forts_diff), 'more fort') + ' taken',
    keeps_diff: () => plural(Math.abs(f.keeps_diff), 'more keep') + ' taken',
    dead_diff: () => plural(Math.abs(f.dead_diff), 'more enemy hero', 'more enemy heroes') + ' dead right now',
    takedown_diff: () => plural(Math.abs(f.takedown_diff), 'more takedown'),
  };
  const top = Object.entries(grouped)
    .filter(([key, value]) => value > 0.05 && phrase[key])
    .sort((a, b) => b[1] - a[1])
    .slice(0, 3)
    .map(([key]) => phrase[key]());
  return top.length ? top.join(', ') : null;
}

function plural(n, word, many = null) {
  return n + ' ' + (n === 1 ? word : many ?? word + 's');
}

// "Heroes Profile", the replay announcer: each team's chance to win as the game goes, and a line
// of commentary when something changes it. `t` is whole game seconds.
export default {
  name: 'DeepAnalyticsReplayAnnouncer',
  mixins: [analytics],
  props: {
    data: Object,
    events: Array,
    t: Number,
  },
  data() {
    return {
      talking: false,
    };
  },
  watch: {
    // A new latest line: say it. Restart the animation if it's already running.
    latestKey(now, before) {
      if (now === before || now === null) return;
      this.talking = false;
      this.$nextTick(() => {
        requestAnimationFrame(() => {
          this.talking = true;
        });
      });
    },
  },
  computed: {
    latestKey() {
      const line = this.said[0];
      return line ? line.t + ':' + line.text : null;
    },
    // Built once per game
    announcer() {
      return buildAnnouncer(this.data, this.events);
    },
    source() {
      return this.announcer.source;
    },
    model() {
      return MODEL;
    },
    chance() {
      const series = this.announcer.chance;
      return series[Math.max(0, Math.min(series.length - 1, this.t))] ?? 0.5;
    },
    said() {
      return this.announcer.lines.filter(line => line.t <= this.t).slice(-3).reverse();
    },
  },
  methods: {
    teamColor,
    fullText(line) {
      return this.clock(line.t) + ' ' + line.text + (line.why ? ' Why: ' + line.why + '.' : '');
    },
    pct(p) {
      return Math.round(p * 100) + '%';
    },
  },
};
</script>

<style scoped>
.announcer-logo.talking {
  animation: announcer-talk 1.1s ease-in-out;
}

/* A comic speech bubble with its tail pointing left at the logo */
.speech-bubble {
  position: relative;
  background: #f4f4f8;
  color: #111;
  border: 2px solid #111;
  border-radius: 1rem;
  padding: 0.6rem 0.9rem;
  line-height: 1.4;
  /* Exactly two lines plus padding and border, whatever the text */
  height: calc(2.8em + 1.2rem + 4px);
  display: flex;
  align-items: center;
}

.speech-bubble::before,
.speech-bubble::after {
  content: '';
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  border-style: solid;
}

/* The tail's outline, then its fill on top */
.speech-bubble::before {
  left: -14px;
  border-width: 9px 14px 9px 0;
  border-color: transparent #111 transparent transparent;
}

.speech-bubble::after {
  left: -10px;
  border-width: 7px 11px 7px 0;
  border-color: transparent #f4f4f8 transparent transparent;
}

.bubble-time,
.bubble-why {
  color: #555;
}

.speech-bubble.pop {
  animation: bubble-pop 0.3s ease-out;
}

@keyframes bubble-pop {
  0% { transform: scale(0.92); opacity: 0.6; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes announcer-talk {
  0% { transform: translateY(0) rotate(0); }
  20% { transform: translateY(-3px) rotate(-5deg); }
  45% { transform: translateY(0) rotate(4deg); }
  70% { transform: translateY(-2px) rotate(-2deg); }
  100% { transform: translateY(0) rotate(0); }
}

@media (prefers-reduced-motion: reduce) {
  .announcer-logo.talking,
  .speech-bubble.pop {
    animation: none;
  }
}
</style>
