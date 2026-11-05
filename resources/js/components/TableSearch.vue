<template>
  <!-- A player table is scanned while the search runs, then the matches drop in -->
  <div class="max-w-[1500px] mx-auto w-full px-4 mt-10 pb-10">
    <loading-component v-if="phase === 'searching'" class="mb-4" :compact="true" :textoverride="true">Searching for {{ query }}...</loading-component>
    <p v-else class="text-center text-subtext text-sm mb-4">
      <template v-if="phase === 'choose'">{{ results.length }} players named {{ query }}. Pick one.</template>
      <template v-else-if="phase === 'picked'">Opening profile...</template>
    </p>

    <div class="relative overflow-hidden">
      <table class="min-w-0 w-full responsive-table relative">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">BattleTag</th>
            <th class="py-2 px-3 text-center text-sm leading-4">Region</th>
            <th class="py-2 px-3 text-center text-sm leading-4">Games Played</th>
            <th class="py-2 px-3 text-center text-sm leading-4">Latest Hero</th>
            <th class="py-2 px-3 text-center text-sm leading-4">Latest Game</th>
          </tr>
        </thead>

        <tbody v-if="phase === 'searching'" class="text-center">
          <tr v-for="row in SKELETON_ROWS" :key="row">
            <td><skeleton-box class="w-32 h-4 mx-auto"></skeleton-box></td>
            <td><skeleton-box class="w-12 h-4 mx-auto"></skeleton-box></td>
            <td><skeleton-box class="w-16 h-4 mx-auto"></skeleton-box></td>
            <td><skeleton-box class="w-10 h-10 mx-auto !rounded-full"></skeleton-box></td>
            <td><skeleton-box class="w-28 h-4 mx-auto"></skeleton-box></td>
          </tr>
        </tbody>

        <tbody v-else class="text-center">
          <tr
            v-for="(item, index) in results"
            :key="index"
            :class="['cursor-pointer transition-opacity duration-300', picked !== null && picked !== index ? 'opacity-0 pointer-events-none' : '']"
            @click="pick(index)"
          >
            <td>
              <a class="group/bt inline-flex items-center" :href="`/Player/${item.battletagShort}/${item.blizz_id}/${item.region}`" @click.prevent.stop="pick(index)"><span class="group-hover/bt:underline">{{ item.battletagShort }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
            </td>
            <td>{{ item.regionName }}</td>
            <td>{{ Number(item.totalGamesPlayed).toLocaleString('en-US') }}</td>
            <td><hero-image-wrapper v-if="item.latestHero" class="flex justify-center" :hero="item.latestHero"></hero-image-wrapper></td>
            <td>
              <a v-if="item.latestReplayID" class="group/bt inline-flex items-center" :href="`/Match/Single/${item.latestReplayID}`" target="_blank" rel="noopener" @click.stop><span class="group-hover/bt:underline">{{ item.latest_game }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
              <template v-else>{{ item.latest_game }}</template>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Sweeps the rows while searching -->
      <div v-if="phase === 'searching'" class="table-scan absolute inset-x-0 h-12 pointer-events-none bg-gradient-to-b from-transparent via-teal/40 to-transparent"></div>
    </div>

    <!-- Covers the page and stays while the profile loads -->
    <div :class="['fixed inset-0 z-[100] bg-page pointer-events-none transition-opacity duration-500 flex items-center', fadeOut ? 'opacity-100' : 'opacity-0']">
      <loading-component v-if="fadeOut"></loading-component>
    </div>
  </div>
</template>

<script>
// The scan runs at least this long, however quick the search
const MIN_SECONDS = 1.5;
// One match stays on screen this long before its profile opens
const SINGLE_SECONDS = 0.9;

export default {
  name: 'TableSearch',
  props: {
    query: String,
    // null while the search runs
    results: Array,
  },
  emits: ['select', 'none'],
  data() {
    return {
      SKELETON_ROWS: 6,
      phase: 'searching',
      picked: null,
      fadeOut: false,
    };
  },
  watch: {
    results(results) {
      if (results) {
        const wait = Math.max(0, MIN_SECONDS * 1000 - (Date.now() - this.started));
        this.timer = setTimeout(() => this.actOnResults(), wait);
      }
    },
  },
  mounted() {
    this.started = Date.now();
    // Coming back from the profile with the back button shows a fresh search
    this.onPageShow = event => event.persisted && window.location.reload();
    window.addEventListener('pageshow', this.onPageShow);
  },
  beforeUnmount() {
    clearTimeout(this.timer);
    window.removeEventListener('pageshow', this.onPageShow);
  },
  methods: {
    actOnResults() {
      if (this.results.length === 0) {
        this.$emit('none');
      } else if (this.results.length === 1) {
        this.phase = 'choose';
        this.timer = setTimeout(() => this.pick(0), SINGLE_SECONDS * 1000);
      } else {
        this.phase = 'choose';
      }
    },

    pick(index) {
      if (this.picked !== null) {
        return;
      }
      this.picked = index;
      this.phase = 'picked';
      this.$emit('select', this.results[index]);
      this.timer = setTimeout(() => (this.fadeOut = true), 400);
    },
  },
};
</script>
