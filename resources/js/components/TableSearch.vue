<template>
  <!-- A player table is scanned while the search runs, then the matches drop in -->
  <div :class="picker ? 'w-full' : 'max-w-[93.75rem] mx-auto w-full px-4 mt-10 pb-10'">
    <p v-if="phase !== 'searching'" class="text-center text-subtext text-sm mb-4">
      <template v-if="phase === 'choose'">{{ $t(':results players named :query. Pick one.', { results: results.length, query: query }) }}</template>
      <template v-else-if="phase === 'picked'">{{ picker ? $t('Player chosen') : $t('Opening profile...') }}</template>
    </p>

    <!-- In a card like the site's other tables. While searching the message sits on a tab of the card, as on the
         profile's skeleton, and the space the results line will take is kept above it so nothing moves -->
    <panel-box :class="['relative', { 'md:mt-9 md:!rounded-tl-none': phase === 'searching' }]">
    <loading-component v-if="phase === 'searching'" :compact="true" :tab="true" :textoverride="true" :overrideimage="loaderImage">{{ $t('Searching for :query...', { query: query }) }}</loading-component>
    <div class="relative overflow-clip">
      <table class="min-w-0 w-full rounded-table relative">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Player') }}</th>
            <th v-if="showRegion" class="py-2 px-3 text-center text-sm leading-4">{{ $t('Region') }}</th>
            <th v-if="showTogether" class="py-2 px-3 text-center text-sm leading-4">{{ $t('Games Together') }}</th>
            <template v-if="!namesOnly">
            <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Games Played') }}</th>
            <!-- Phones have no room for the latest game; the popup picker drops the latest hero too -->
            <th :class="['py-2 px-3 text-left text-sm leading-4', { 'max-md:hidden': picker }]">{{ $t('Latest Hero') }}</th>
            <th class="py-2 px-3 text-center text-sm leading-4 max-md:hidden">{{ $t('Latest Game') }}</th>
            </template>
          </tr>
        </thead>

        <tbody v-if="phase === 'searching'" class="text-center">
          <tr v-for="row in SKELETON_ROWS" :key="row">
            <td><skeleton-box class="w-32 h-4 mx-auto"></skeleton-box></td>
            <td v-if="showRegion"><skeleton-box class="w-12 h-4 mx-auto"></skeleton-box></td>
            <td v-if="showTogether"><skeleton-box class="w-12 h-4 mx-auto"></skeleton-box></td>
            <template v-if="!namesOnly">
            <td><skeleton-box class="w-16 h-4 mx-auto"></skeleton-box></td>
            <td :class="['py-2 px-3', { 'max-md:hidden': picker }]"><div class="flex items-center gap-3"><skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box><skeleton-box class="hidden md:block w-20 h-4"></skeleton-box></div></td>
            <td class="max-md:hidden"><skeleton-box class="w-28 h-4 mx-auto"></skeleton-box></td>
            </template>
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
              <a class="group/bt inline-flex items-center" :href="profileUrl(item)" @click.prevent.stop="pick(index)"><span class="group-hover/bt:underline">{{ item.battletagShort }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
            </td>
            <td v-if="showRegion">{{ item.regionName }}</td>
            <td v-if="showTogether" class="font-semibold text-lteal">{{ Number(item.gamesTogether || 0).toLocaleString('en-US') }}</td>
            <template v-if="!namesOnly">
            <td>{{ Number(item.totalGamesPlayed).toLocaleString('en-US') }}</td>
            <!-- As in the Global Hero Stats table: portrait and name, left-aligned -->
            <td :class="['py-2 px-3', { 'max-md:hidden': picker }]"><div v-if="item.latestHero" class="flex items-center justify-start"><hero-image-wrapper class="mr-3" :hero="item.latestHero"></hero-image-wrapper><span class="hidden md:block font-medium">{{ $heroName(item.latestHero) }}</span></div></td>
            <td class="max-md:hidden">
              <a v-if="item.latestReplayID" class="group/bt inline-flex items-center" :href="matchUrl(item)" target="_blank" rel="noopener" @click.stop><span class="group-hover/bt:underline">{{ $formatDate(item.latest_game, { dateStyle: 'short', timeStyle: 'short' }) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
              <template v-else>{{ $formatDate(item.latest_game, { dateStyle: 'short', timeStyle: 'short' }) }}</template>
            </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
    </panel-box>

    <!-- Covers the page and stays while the profile loads -->
    <div v-if="!picker" :class="['fixed inset-0 z-[100] bg-page pointer-events-none transition-opacity duration-500 flex items-center', fadeOut ? 'opacity-100' : 'opacity-0']">
      <loading-component v-if="fadeOut" :overrideimage="loaderImage"></loading-component>
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
    // Off where every result is in one region (NGS is all NA)
    showRegion: {
      type: Boolean,
      default: true,
    },
    // The loaders' spinning logo, e.g. the NGS one on NGS pages; the site's own when left out
    // Choosing a player in a popup rather than opening their profile
    picker: Boolean,
    // A Games Together column (friend or foe search), from each result's gamesTogether
    showTogether: Boolean,
    // Just the player column, where the search returns no game counts or latest game (Other esports)
    namesOnly: Boolean,
    loaderImage: {
      type: String,
      default: null,
    },
    // Where Latest Game links to, e.g. the NGS match page instead of the main one
    matchUrl: {
      type: Function,
      default: item => `/Match/Single/${item.latestReplayID}`,
    },
    // Where a result's name links to, e.g. the NGS player page instead of the main profile
    profileUrl: {
      type: Function,
      default: item => `/Player/${item.battletagShort}/${item.blizz_id}/${item.region}`,
    },
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
      if (!this.picker) {
        this.timer = setTimeout(() => (this.fadeOut = true), 400);
      }
    },
  },
};
</script>
