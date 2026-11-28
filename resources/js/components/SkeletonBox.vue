<template>
  <!-- A text line unless given its own radius, as the Figma loading frame: lines 4px, images and rows 8px, cards as their card -->
  <div :class="['skeleton-box bg-surface-3 animate-pulse', { 'rounded-[0.25rem]': !ownRadius() }]"></div>
</template>

<script>
// A grey block standing in for content while it loads; size it with classes
export default {
  name: 'SkeletonBox',
  methods: {
    ownRadius() {
      return /rounded/.test(JSON.stringify(this.$attrs.class ?? ''));
    },
  },
};
</script>
