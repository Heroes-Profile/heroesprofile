<template>
  <!-- Minimized: a slim bar, remembered until the visitor reopens it -->
  <div v-if="minimized" class="xalatath-scoreboard text-white px-4 py-2">
    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 max-w-[1500px] mx-auto text-sm">
      <span class="font-logo text-base tracking-wide xalatath-title">Xal'atath</span>
      <span class="opacity-80">Void Corruption: stage {{ live.stage }} of 5</span>
      <div class="h-2 w-28 bg-darken rounded overflow-hidden max-md:hidden">
        <div class="h-full xalatath-progress" :style="{ width: progress + '%' }"></div>
      </div>
      <button type="button" class="underline opacity-75 hover:opacity-100" @click="setMinimized(false)">
        Show <i class="fas fa-chevron-down ml-1"></i>
      </button>
    </div>
  </div>

  <div v-else class="xalatath-scoreboard relative text-white text-center px-4 py-3">
    <button type="button" class="absolute top-2 right-3 text-xs opacity-60 hover:opacity-100" title="Minimize" @click="setMinimized(true)">
      Hide <i class="fas fa-chevron-up ml-1"></i>
    </button>
    <div class="font-logo text-3xl tracking-wide xalatath-title mb-2">Xal'atath</div>

    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 max-w-[1500px] mx-auto">
      <div v-for="tile in tiles" :key="tile.label" class="flex flex-col min-w-[110px]">
        <span class="text-xs uppercase flex items-center justify-center gap-1">
          <span class="opacity-75">{{ tile.label }}</span>
          <round-image size="small" icon="fas fa-info" title="info" popupsize="xlarge" class="scale-75 normal-case hover:z-50"><slot><p class="text-base p-2">{{ tile.info }}</p></slot></round-image>
        </span>
        <span class="text-lg font-bold tabular-nums">{{ format(displayed[tile.label]) }}</span>
      </div>

      <button type="button" class="text-xs underline opacity-75 hover:opacity-100" @click="expanded = !expanded">
        {{ expanded ? 'Less' : 'More' }}
      </button>
    </div>

    <div v-if="expanded" class="flex flex-wrap justify-center gap-x-6 gap-y-2 mt-3 text-sm">
      <div v-for="tile in extraTiles" :key="tile.label" class="flex flex-col min-w-[110px]">
        <span class="text-xs uppercase flex items-center justify-center gap-1">
          <span class="opacity-75">{{ tile.label }}</span>
          <round-image size="small" icon="fas fa-info" title="info" popupsize="xlarge" class="scale-75 normal-case hover:z-50"><slot><p class="text-base p-2">{{ tile.info }}</p></slot></round-image>
        </span>
        <span class="font-bold tabular-nums">{{ format(tile.value) }}</span>
      </div>
    </div>

    <div class="max-w-[700px] mx-auto mt-3 text-xs">
      <div class="flex justify-between mb-1">
        <span class="flex items-center gap-1">
          Void Corruption: stage {{ live.stage }} of 5
          <round-image size="small" icon="fas fa-info" title="info" popupsize="xlarge" class="scale-75 hover:z-50"><slot><p class="text-base p-2">Every Xal'atath game played and every time she is banned in draft spreads the Void. The site grows more corrupted at each stage. Counts replays uploaded to Heroes Profile.</p></slot></round-image>
        </span>
        <span v-if="live.nextThreshold">{{ format(live.corruption) }} / {{ format(live.nextThreshold) }} games + bans</span>
        <span v-else>The Nexus has fallen</span>
      </div>
      <div class="h-2 bg-darken rounded overflow-hidden">
        <div class="h-full xalatath-progress" :style="{ width: progress + '%' }"></div>
      </div>
      <button type="button" class="mt-2 underline opacity-75 hover:opacity-100" @click="toggleOptOut">
        {{ optOut ? 'Let the Void back in' : 'Resist the Void (turn off site corruption)' }}
      </button>
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

const MINIMIZED_KEY = 'xalatathScoreboardMinimized';

// '1' = minimized, '0' = opened by the visitor. With no choice yet, mobile starts minimized.
function readMinimized() {
  const mobileDefault = window.innerWidth < 768;
  try {
    const stored = localStorage.getItem(MINIMIZED_KEY);
    return stored === null ? mobileDefault : stored === '1';
  } catch (e) {
    return mobileDefault;
  }
}

export default {
  name: 'XalatathScoreboard',
  props: {
    event: {
      type: Object,
      required: true,
    },
    optOut: Boolean,
  },
  data() {
    return {
      expanded: false,
      // Read before first render so a minimized bar never flashes open.
      minimized: readMinimized(),
      displayed: {},
      live: this.event,
      pollTimer: null,
    };
  },
  computed: {
    totals() {
      return this.live.totals || {};
    },
    gamesPlayed() {
      return this.stat('games_played');
    },
    tiles() {
      return [
        { label: 'Minds Consumed', value: this.stat('takedowns'), info: 'Takedowns (kills + assists) by Xal\'atath.' },
        { label: 'Void Damage Unleashed', value: this.stat('spell_damage'), info: 'Total spell damage dealt by Xal\'atath.' },
        { label: 'Realms Shattered', value: Math.floor(this.stat('siege_damage') / 12900), info: 'Total siege damage by Xal\'atath, divided by 12,900.' },
        { label: 'Mass Consumptions', value: this.stat('multikill'), info: 'Multikills by Xal\'atath.' },
        { label: 'Banished to the Void', value: this.stat('deaths'), info: 'Xal\'atath deaths.' },
        { label: 'Sealed Away', value: this.stat('bans'), info: 'Times Xal\'atath was banned in draft.' },
        { label: 'Cold Feet', value: this.stat('escapes'), info: 'Escapes by Xal\'atath (surviving at low health).' },
      ];
    },
    extraTiles() {
      return [
        { label: 'Games Played', value: this.gamesPlayed, info: 'Games played as Xal\'atath.' },
        { label: 'Victories', value: this.stat('wins'), info: 'Games won as Xal\'atath.' },
        { label: 'Minds Enthralled (hrs)', value: this.hours(this.stat('time_cc_enemy_heroes')), info: 'Time spent crowd controlling enemy heroes, in hours.' },
        { label: 'Void Unleashed in Battle', value: this.stat('teamfight_hero_damage'), info: 'Team fight hero damage by Xal\'atath.' },
        { label: 'Hours Ablaze', value: this.hours(this.stat('on_fire_time')), info: 'Time spent on fire, in hours.' },
        { label: 'Longest Rampage', value: this.stat('highest_kill_streak'), info: 'Highest kill streak in a single game.' },
        { label: 'Outnumbered & Undone', value: this.stat('outnumbered_deaths'), info: 'Deaths while outnumbered.' },
        { label: 'Hours Sealed in the Dagger', value: this.hours(this.stat('time_spent_dead')), info: 'Time spent dead, in hours.' },
      ];
    },
    progress() {
      if (!this.live.nextThreshold) {
        return 100;
      }
      const span = this.live.nextThreshold - this.live.previousThreshold;
      return Math.min(100, Math.max(0, ((this.live.corruption - this.live.previousThreshold) / span) * 100));
    },
  },
  mounted() {
    this.countUp();
    // Matches the 30s server cache; skipped while the tab is hidden.
    this.pollTimer = setInterval(this.poll, 30000);
  },
  beforeUnmount() {
    clearInterval(this.pollTimer);
  },
  methods: {
    stat(column) {
      return Number(this.totals[column] || 0);
    },
    hours(seconds) {
      return Math.floor(seconds / 3600);
    },
    format(value) {
      return Number(value || 0).toLocaleString('en-US');
    },
    async poll() {
      if (document.hidden) {
        return;
      }
      try {
        const response = await this.$axios.get('/Event/Xalatath/Totals');
        if (response.status === 200 && response.data && response.data.totals) {
          const previousStage = this.live.stage;
          this.live = response.data;
          if (!this.optOut && response.data.stage > previousStage) {
            window.dispatchEvent(new CustomEvent('void-stage-up', { detail: { from: previousStage, to: response.data.stage } }));
          }
          this.countUp();
        } else if (response.status === 204) {
          clearInterval(this.pollTimer);
        }
      } catch (error) {
        // Throttled or offline; try again next tick.
      }
    },
    countUp() {
      const duration = 1500;
      const start = performance.now();
      const from = { ...this.displayed };
      const step = (now) => {
        const t = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - t, 3);
        this.tiles.forEach((tile) => {
          const begin = from[tile.label] || 0;
          this.displayed[tile.label] = Math.floor(begin + (tile.value - begin) * eased);
        });
        if (t < 1) {
          requestAnimationFrame(step);
        }
      };
      requestAnimationFrame(step);
    },
    setMinimized(value) {
      this.minimized = value;
      try {
        localStorage.setItem(MINIMIZED_KEY, value ? '1' : '0');
      } catch (e) {
        // storage blocked; just applies to this page view
      }
      if (!value) {
        this.displayed = {};
        this.countUp();
      }
    },
    toggleOptOut() {
      if (this.optOut) {
        Cookies.remove('void_corruption_optout', { path: '/' });
      } else {
        Cookies.set('void_corruption_optout', '1', { expires: 365, path: '/' });
      }
      window.location.reload();
    },
  },
};
</script>
