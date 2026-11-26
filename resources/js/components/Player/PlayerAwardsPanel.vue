<template>
  <!-- A player's end of match awards under the page's filters, most earned first -->
  <panel-box :inner="true" :title="$t('Awards')" :link-text="'All Awards'" :link-href="awardsUrl" :skeleton="loading">
    <template v-if="loading">
      <div class="flex-1 grid grid-cols-2 md:grid-cols-3 auto-rows-fr gap-3">
        <skeleton-box v-for="index in limit" :key="index" class="h-14 rounded-lg"></skeleton-box>
      </div>
      <!-- The "Awards across" line's height, so the card is as tall as when it loads -->
      <skeleton-box class="h-[1rem] w-40"></skeleton-box>
    </template>
    <template v-else-if="earned.length">
      <!-- Rows share the card's height, so the tiles grow when the card is stretched to its neighbour -->
      <div class="flex-1 grid grid-cols-2 md:grid-cols-3 auto-rows-fr gap-3">
        <!-- Compact: the game count sits beside the rate -->
        <div v-for="award in earned" :key="award.award_id" class="flex items-center gap-3 bg-surface-2 rounded px-3 py-2 min-w-0" :title="award.description">
          <img :src="`/images/awards/${award.icon}_blue.png`" :alt="award.title" class="w-9 h-9 shrink-0" />
          <div class="flex flex-col min-w-0">
            <span class="text-sm text-subtext truncate leading-tight">{{ award.title }}</span>
            <span class="flex items-baseline gap-2 whitespace-nowrap">
              <span class="text-lg font-semibold text-header leading-tight">{{ award.rate.toFixed(2) }}%</span>
              <span class="text-xs text-subtext truncate">{{ $t(':count games', { count: award.count.toLocaleString('en-US') }) }}</span>
            </span>
          </div>
        </div>
      </div>
      <p class="text-xs text-subtext">{{ $t('Awards across :games games', { games: total.toLocaleString('en-US') }) }}</p>
    </template>
    <p v-else class="text-subtext">{{ $t('No awards for these filters.') }}</p>
  </panel-box>
</template>

<script>
export default {
  name: 'PlayerAwardsPanel',
  props: {
    battletag: String,
    blizzid: [String, Number],
    region: [String, Number],
    // The page's filters as the awards API takes them: game_type, hero (an id), season, game_map, start_date, end_date
    params: Object,
    // Carried onto the All Awards link, e.g. { hero: 'Alexstrasza' }
    linkQuery: Object,
    // How many awards to show
    limit: { type: Number, default: 9 },
    // A page's loading layout: the grey tiles only, nothing fetched
    placeholder: { type: Boolean, default: false },
  },
  data() {
    return {
      loading: true,
      awards: [],
      total: 0,
    };
  },
  computed: {
    // The Awards page, with linkQuery as its query string (templates can't reach URLSearchParams)
    awardsUrl() {
      const query = this.linkQuery ? '?' + new URLSearchParams(this.linkQuery).toString() : '';
      return `/Player/${this.battletag}/${this.blizzid}/${this.region}/Awards${query}`;
    },
    earned() {
      return this.awards.filter(award => award.count > 0).slice(0, this.limit);
    },
    // A new filters object is passed on every render; only refetch when the values change
    paramsKey() {
      return JSON.stringify(this.params);
    },
  },
  watch: {
    paramsKey() {
      this.getData();
    },
  },
  mounted() {
    if (!this.placeholder) {
      this.getData();
    }
  },
  beforeUnmount() {
    this.cancelTokenSource?.cancel('Request canceled');
  },
  methods: {
    async getData() {
      this.loading = true;
      this.cancelTokenSource?.cancel('Request canceled');
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try {
        const response = await this.$globalAsyncPost('/api/v1/player/awards', {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          ...this.params,
        }, { cancelToken: this.cancelTokenSource.token });
        this.awards = response.data.awards || [];
        this.total = response.data.total_games || 0;
        this.loading = false;
      } catch (error) {
        if (!this.$axios.isCancel(error)) {
          this.awards = [];
          this.loading = false;
        }
      }
    },
  },
};
</script>
