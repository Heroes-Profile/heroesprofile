<template>
  <!-- Every season, not just the filtered ones: the latest on the right, 10 at a time, sliding one season per click -->
  <panel-box title="Win Rate Per Season">
    <template v-if="seasons.length">
      <line-chart :data="seasons" :visible-count="windowSize" :end-index="end" :aspect-ratio="3.1" :gridded="true" :dataAttribute="'win_rate'" :point-radius="3" :point-hover-radius="5" :percent="true" :dim-empty="true" :title="title"></line-chart>
      <div class="flex justify-between text-sm">
        <button v-if="end > windowSize" type="button" class="text-subtext hover:text-white" aria-label="Older seasons" @click="shift(-1)"><i class="fas fa-chevron-left"></i></button>
        <span v-else></span>
        <button v-if="end < seasons.length" type="button" class="text-subtext hover:text-white" aria-label="Newer seasons" @click="shift(1)"><i class="fas fa-chevron-right"></i></button>
      </div>
    </template>
    <p v-else class="text-subtext">No season data for these filters.</p>
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
      windowSize: 10,
    };
  },
  computed: {
    seasons() {
      if (!this.data) {
        return [];
      }
      return this.data.season_history ?? Object.values(this.data.season_win_rate_data ?? {});
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
    shift(step) {
      const length = this.seasons.length;
      this.windowEnd = Math.min(length, Math.max(Math.min(this.windowSize, length), this.end + step));
    },
  },
};
</script>
