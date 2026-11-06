<template>
  <!-- Covers the page while a lookup finds the game to open, like the player search's scanning rows -->
  <div class="fixed inset-0 z-50 overflow-y-auto bg-page/95 px-4 pt-24 pb-6">
    <div class="max-w-[1500px] mx-auto">
      <loading-component class="mb-4" :compact="true" :textoverride="true" :overrideimage="overrideimage">{{ text }}</loading-component>
      <div class="relative overflow-hidden">
        <game-summary-skeleton v-for="row in 3" :key="row"></game-summary-skeleton>
        <div class="table-scan absolute inset-x-0 h-12 pointer-events-none bg-gradient-to-b from-transparent via-teal/40 to-transparent"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MatchLookup',
  props: {
    text: String,
    overrideimage: String,
  },
};
</script>
