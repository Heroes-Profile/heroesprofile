<template>
  <span v-if="region" class="bg-gray-dark rounded px-2 py-1 text-xs font-semibold">{{ region }}</span>
</template>

<script>
export default {
  name: 'RegionTag',
  props: {
    // Region name, e.g. "NA"
    region: String,
  },
};
</script>
