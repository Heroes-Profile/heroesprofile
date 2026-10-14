<template>
  <!-- One row like the game's bar; scaled down to fit when the column is narrower than the bar.
       Portraits are the rectangle art's own shape (310x451), 55x80, so none of it is cropped. -->
  <div ref="frame" class="w-full flex justify-center bg-darken rounded p-2 mb-2 overflow-hidden">
  <div ref="bar" class="flex items-stretch justify-center gap-2 w-max" :style="{ zoom: scale }">
    <!-- Blue heroes -->
    <div class="flex gap-1">
      <div v-for="hero in heroesOn(0)" :key="hero.idx" class="flex flex-col items-center gap-1">
        <div
          class="relative cursor-pointer rounded overflow-hidden w-[55px] [&_div]:!w-[55px] [&_img]:!w-[55px] [&_img]:!min-w-0 [&_img]:object-contain"
          :class="{ 'ring-2 ring-lyellow': hero.idx === selected }"
          @click="$emit('select', hero.idx)"
        >
          <hero-image-wrapper :hero="hero.portrait" :rectangle="true" :size="'big'"></hero-image-wrapper>
          <div v-if="hero.respawn !== null" class="absolute inset-0 flex items-center justify-center pointer-events-none" style="background: rgba(0, 0, 0, 0.65)">
            <span class="text-3xl font-bold text-lred">{{ hero.respawn }}</span>
          </div>
          <div class="absolute bottom-0 inset-x-0 h-1 pointer-events-none" :style="{ background: teamColor(0) }"></div>
        </div>
        <talent-image-wrapper v-if="hero.heroic" :talent="hero.heroic" :size="'small'"></talent-image-wrapper>
        <div v-else class="w-10 h-10"></div>
      </div>
    </div>

    <!-- Clock, team levels and progress to the next level -->
    <div class="flex flex-col items-center justify-center min-w-[9em]">
      <div class="font-mono text-lg">{{ clock(t) }}</div>
      <div class="flex items-center gap-3">
        <span class="text-4xl font-bold" :style="{ color: teamColor(0) }">{{ levels[0].level }}</span>
        <span class="text-gray-light">vs</span>
        <span class="text-4xl font-bold" :style="{ color: teamColor(1) }">{{ levels[1].level }}</span>
      </div>
      <div class="flex gap-2 w-full mt-1">
        <stat-bar class="flex-1" :value="levels[0].progress" :display-text="''" color="blue"></stat-bar>
        <stat-bar class="flex-1" :value="levels[1].progress" :display-text="''" color="red"></stat-bar>
      </div>
    </div>

    <!-- Red heroes -->
    <div class="flex gap-1">
      <div v-for="hero in heroesOn(1)" :key="hero.idx" class="flex flex-col items-center gap-1">
        <div
          class="relative cursor-pointer rounded overflow-hidden w-[55px] [&_div]:!w-[55px] [&_img]:!w-[55px] [&_img]:!min-w-0 [&_img]:object-contain"
          :class="{ 'ring-2 ring-lyellow': hero.idx === selected }"
          @click="$emit('select', hero.idx)"
        >
          <hero-image-wrapper :hero="hero.portrait" :rectangle="true" :size="'big'"></hero-image-wrapper>
          <div v-if="hero.respawn !== null" class="absolute inset-0 flex items-center justify-center pointer-events-none" style="background: rgba(0, 0, 0, 0.65)">
            <span class="text-3xl font-bold text-lred">{{ hero.respawn }}</span>
          </div>
          <div class="absolute bottom-0 inset-x-0 h-1 pointer-events-none" :style="{ background: teamColor(1) }"></div>
        </div>
        <talent-image-wrapper v-if="hero.heroic" :talent="hero.heroic" :size="'small'"></talent-image-wrapper>
        <div v-else class="w-10 h-10"></div>
      </div>
    </div>
  </div>
  </div>
</template>

<script>
import analytics from '../../utils/deepAnalyticsFormat.js';
import { teamColor } from '../../utils/mapAssets.js';

const HEROIC_TIER = 3; // the level 10 pick

// The in-game style top bar: each team's heroes with respawn timers and heroic, and the team
// levels with progress to the next one. `t` is whole game seconds, so it updates once a second.
export default {
  name: 'DeepAnalyticsReplayScoreboard',
  mixins: [analytics],
  props: {
    data: Object,
    t: Number,
    selected: { type: Number, default: null },
  },
  emits: ['select'],
  data() {
    return {
      scale: 1,
    };
  },
  mounted() {
    this.observer = new ResizeObserver(this.fit);
    this.observer.observe(this.$refs.frame);
    this.fit();
  },
  beforeUnmount() {
    this.observer?.disconnect();
  },
  computed: {
    // Built once, so the portraits don't re-render every second
    portraits() {
      return Object.fromEntries(this.data.players.map(p => [p.idx, { name: p.hero, short_name: p.short_name }]));
    },
    livesByPlayer() {
      const lives = {};
      this.data.lives.forEach(([p, born, , , died]) => {
        (lives[p] = lives[p] || []).push({ born, died: died ?? this.data.length_s });
      });
      Object.values(lives).forEach(list => list.sort((a, b) => a.born - b.born));
      return lives;
    },
    levels() {
      return [0, 1].map(team => {
        const ups = this.data.team_levels.filter(l => l.team === team).sort((a, b) => a.t - b.t);
        // Nothing fills before the gates open (or, without that time, before the level 1 record at spawn)
        const start = Math.max(this.data.gates_open ?? 0, ups[0]?.t ?? 0);
        if (ups.length === 0 || this.t < start) {
          return { level: ups.length && this.t >= ups[0].t ? ups[0].lvl : 1, progress: 0 };
        }
        let current = ups[0];
        let next = null;
        for (const up of ups) {
          if (up.t <= this.t) current = up;
          else {
            next = up;
            break;
          }
        }
        // Time-based, from the level-up times; the game doesn't record XP between them
        const from = Math.max(current.t, start);
        const progress = next ? Math.floor(100 * (this.t - from) / Math.max(1, next.t - from)) : 100;
        return { level: current.lvl, progress };
      });
    },
  },
  methods: {
    teamColor,
    fit() {
      const frame = this.$refs.frame;
      const bar = this.$refs.bar;
      if (!frame || !bar) return;
      // The bar's natural width is its width at the current zoom divided by that zoom
      const natural = bar.getBoundingClientRect().width / this.scale;
      const room = frame.clientWidth - 16;
      this.scale = natural > room ? Math.max(0.5, room / natural) : 1;
    },
    heroesOn(team) {
      return this.data.players.filter(p => p.team === team).map(p => ({
        idx: p.idx,
        portrait: this.portraits[p.idx],
        respawn: this.respawnIn(p.idx),
        heroic: this.data.talents.find(pick => pick.p === p.idx && pick.tier === HEROIC_TIER && pick.t <= this.t)?.talent ?? null,
      }));
    },
    // Seconds until a dead hero is back, '' when they don't come back before the end, null when alive
    respawnIn(idx) {
      const lives = this.livesByPlayer[idx] || [];
      if (lives.some(l => this.t >= l.born && this.t <= l.died)) return null;
      if (lives.length === 0 || this.t < lives[0].born) return null;
      const next = lives.find(l => l.born > this.t);
      return next ? next.born - this.t : '';
    },
  },
};
</script>
