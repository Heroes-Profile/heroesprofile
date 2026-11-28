<template>
  <span v-if="region && full" class="text-subtext">{{ fullName }}</span>
  <span v-else-if="region" :class="['bg-surface rounded font-semibold', large ? 'px-3 py-1 text-sm' : 'px-2 py-1 text-xs']">{{ region }}</span>
</template>

<script>
const FULL_NAMES = { NA: 'North America', EU: 'Europe', KR: 'Korea', CN: 'China' };

export default {
  name: 'RegionTag',
  props: {
    // Region name, e.g. "NA"
    region: String,
    // Spelled out as plain text, e.g. "North America"
    full: Boolean,
    // Beside a page's big BattleTag heading
    large: Boolean,
  },
  computed: {
    fullName() {
      return FULL_NAMES[this.region] ? this.$t(FULL_NAMES[this.region]) : this.region;
    },
  },
};
</script>
