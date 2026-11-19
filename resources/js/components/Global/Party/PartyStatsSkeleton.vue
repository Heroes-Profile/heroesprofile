<template>
  <!-- party-stats-display's own layout with grey blocks while it loads; the default slot (e.g. the loading message) goes on its first panel -->
  <party-stats-display :mode="mode" :skeleton="true">
    <slot></slot>
  </party-stats-display>
</template>

<script>
export default {
  name: 'PartyStatsSkeleton',
  props: {
    mode: {
      type: String,
      default: 'global',
    },
  },
};
</script>
