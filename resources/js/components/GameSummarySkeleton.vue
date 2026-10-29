<template>
  <!-- A game-summary-box row with empty slots, same column widths, while matches load -->
  <div class="mb-1.5 flex flex-wrap items-center justify-between gap-y-4 rounded-lg overflow-hidden bg-surface-1 2xl:flex-nowrap">
    <div class="relative flex-1 lg:max-w-[410px] min-w-[300px] self-stretch flex items-center pl-6 py-4">
      <div class="absolute inset-y-0 left-0 w-1.5 bg-surface-3"></div>
      <div class="flex items-center gap-4">
        <skeleton-box class="w-[82px] h-[82px] shrink-0 !rounded-full"></skeleton-box>
        <div class="flex flex-col gap-2">
          <skeleton-box class="w-36 h-4"></skeleton-box>
          <skeleton-box class="w-20 h-4"></skeleton-box>
          <skeleton-box class="w-24 h-3"></skeleton-box>
          <skeleton-box class="w-40 h-3"></skeleton-box>
        </div>
      </div>
    </div>

    <div class="flex gap-5 items-center px-4 w-full 2xl:w-[430px] shrink-0">
      <div v-for="stat in 3" :key="stat" class="flex flex-col gap-2 flex-1 min-w-0 2xl:flex-none 2xl:w-[118px]">
        <skeleton-box class="w-20 h-4"></skeleton-box>
        <skeleton-box class="w-16 h-6"></skeleton-box>
        <skeleton-box class="w-12 h-3"></skeleton-box>
      </div>
    </div>

    <div class="w-full 2xl:w-[140px] shrink-0 flex flex-col gap-2 max-2xl:items-start max-2xl:px-4 2xl:items-center">
      <skeleton-box class="w-20 h-6"></skeleton-box>
      <skeleton-box class="w-14 h-3"></skeleton-box>
    </div>

    <div class="flex flex-wrap gap-1.5 items-center px-4 w-full 2xl:w-[362px] shrink-0">
      <skeleton-box v-for="talent in 7" :key="talent" class="w-[42px] h-[42px] shrink-0 !rounded-lg"></skeleton-box>
    </div>

    <div class="flex gap-2 pl-4 2xl:pl-1.5 pr-12 py-4 w-full 2xl:w-[362px] shrink-0">
      <div v-for="team in 2" :key="team" class="flex flex-col gap-1.5 w-[150px]">
        <div v-for="player in 5" :key="player" class="flex gap-1 h-4 items-center">
          <!-- Party icon and line slot, then hero icon and name -->
          <span class="w-[19px] shrink-0"></span>
          <skeleton-box class="w-4 h-4 shrink-0 !rounded-full"></skeleton-box>
          <skeleton-box class="flex-1 h-3"></skeleton-box>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GameSummarySkeleton',
};
</script>
