<template>
  <!-- The single match page's layout in grey while a match loads -->
  <div class="max-w-[93.75rem] mx-auto px-4 mt-6 flex flex-col gap-6">
    <loading-component :compact="true" :textoverride="true" :overrideimage="overrideimage">{{ $t('Loading match...') }}</loading-component>
    <skeleton-box class="h-14 w-full max-w-[62.5rem] mx-auto"></skeleton-box>
    <div class="grid md:grid-cols-2 gap-6">
      <table-skeleton :rows="5"></table-skeleton>
      <table-skeleton :rows="5"></table-skeleton>
    </div>
    <table-skeleton :rows="10" :heading="true"></table-skeleton>
  </div>
</template>

<script>
export default {
  name: 'MatchSkeleton',
  props: {
    // An esports league's loader image
    overrideimage: String,
  },
};
</script>
