<template>
  <!-- A table's panel with grey rows while it loads, like the leaderboard -->
  <panel-box>
    <skeleton-box v-if="heading" class="h-8 w-80 max-w-full"></skeleton-box>
    <div class="flex flex-col gap-1">
      <skeleton-box class="h-8"></skeleton-box>
      <skeleton-box v-for="index in rows" :key="index" class="h-10"></skeleton-box>
    </div>
  </panel-box>
</template>

<script>
export default {
  name: 'TableSkeleton',
  props: {
    rows: {
      type: Number,
      default: 15,
    },
    heading: Boolean,
  },
};
</script>
