<template>
  <!-- Every season, not just the filtered ones: the latest on the right, 20 at a time (10 on phones), sliding one season per click or dragged -->
  <panel-box :title="$t('Win Rate Per Season')">
    <div v-if="seasons.length" class="relative text-sm">
      <line-chart :data="seasons" :visible-count="windowSize" :end-index="end" :aspect-ratio="3.1" :gridded="true" :dataAttribute="'win_rate'" top-attribute="games_played" :top-label="$t('Games')" :tooltip-label="$t('Win Rate')" :point-radius="3" :point-hover-radius="5" :percent="true" :dim-empty="true" :title="title" @pan="windowEnd = $event"></line-chart>
      <!-- Arrows at both ends of the games row and the season labels -->
      <template v-for="row in ['top-1', 'bottom-0']" :key="row">
        <button v-if="end > windowSize" type="button" :class="['absolute left-0 text-subtext hover:text-white', row]" :aria-label="$t('Older seasons')" @click="shift(-1)"><i class="fas fa-chevron-left"></i></button>
        <button v-if="end < seasons.length" type="button" :class="['absolute right-0 text-subtext hover:text-white', row]" :aria-label="$t('Newer seasons')" @click="shift(1)"><i class="fas fa-chevron-right"></i></button>
      </template>
    </div>
    <p v-else class="text-subtext">{{ $t('No season data for these filters.') }}</p>
  </panel-box>
</template>

<script>
export default {
  name: 'PlayerSeasonWinRatePanel',
  props: {
    // A player stats response: season_history, or season_win_rate_data from before it existed
    data: Object,
    title: String,
  },
  data() {
    return {
      // Index after the right-most season shown; null = the latest
      windowEnd: null,
      phone: window.matchMedia('(max-width: 767px)').matches,
    };
  },
  mounted() {
    this.phoneQuery = window.matchMedia('(max-width: 767px)');
    this.onPhoneChange = (event) => { this.phone = event.matches; };
    this.phoneQuery.addEventListener('change', this.onPhoneChange);
  },
  beforeUnmount() {
    this.phoneQuery?.removeEventListener('change', this.onPhoneChange);
  },
  computed: {
    // Seasons in view: 20 on wider screens, 10 on phones so their labels fit
    windowSize() {
      return this.phone ? 10 : 20;
    },
    seasons() {
      if (!this.data) {
        return [];
      }
      const seasons = this.data.season_history ?? Object.values(this.data.season_win_rate_data ?? {});
      return seasons.map(season => ({ ...season, x_label: this.seasonLabel(season.x_label) }));
    },
    end() {
      return this.windowEnd ?? this.seasons.length;
    },
  },
  watch: {
    data() {
      this.windowEnd = null;
    },
  },
  methods: {
    // The server's "S2 2026", as "2026 Season 2" in the page's language; English pages keep the short form
    seasonLabel(label) {
      const match = String(label ?? '').match(/^S(\S+) (\d{4})$/);
      if (!match || !window.translations?.[':year Season :season']) {
        return label;
      }
      return this.$t(':year Season :season', { year: match[2], season: match[1] });
    },
    shift(step) {
      const length = this.seasons.length;
      this.windowEnd = Math.min(length, Math.max(Math.min(this.windowSize, length), this.end + step));
    },
  },
};
</script>
