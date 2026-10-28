<template>
  <span v-if="region && full" class="text-subtext">{{ fullName }}</span>
  <span v-else-if="region" class="bg-gray-dark rounded px-2 py-1 text-xs font-semibold">{{ region }}</span>
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
  },
  computed: {
    fullName() {
      return FULL_NAMES[this.region] || this.region;
    },
  },
};
</script>
