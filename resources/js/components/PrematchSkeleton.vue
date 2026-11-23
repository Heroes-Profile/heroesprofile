<template>
  <!-- The prematch page in grey while it loads: heading, the MMR balance, then both teams -->
  <div class="w-full max-w-[120rem] mx-auto px-4 md:px-8 pt-10 pb-10 flex flex-col gap-8">
    <div class="flex flex-wrap items-end justify-between gap-6">
      <div class="flex flex-col gap-3">
        <skeleton-box class="h-12 md:h-16 w-56 md:w-80"></skeleton-box>
        <skeleton-box class="h-5 md:h-7 w-64 md:w-[30rem]"></skeleton-box>
      </div>
      <div class="flex gap-3">
        <skeleton-box class="h-[3.125rem] w-72"></skeleton-box>
        <skeleton-box class="h-[3.125rem] w-40"></skeleton-box>
      </div>
    </div>

    <div class="relative rounded bg-surface md:!rounded-tl-none px-5 md:px-8 py-5 flex flex-col gap-3">
      <loading-component :compact="true" :tab="true"></loading-component>
      <div class="flex items-center justify-between gap-4">
        <skeleton-box class="h-12 w-40"></skeleton-box>
        <skeleton-box class="h-8 w-52 max-md:hidden"></skeleton-box>
        <skeleton-box class="h-12 w-40"></skeleton-box>
      </div>
      <skeleton-box class="h-2.5 w-full !rounded-full"></skeleton-box>
    </div>

    <div class="grid xl:grid-cols-2 gap-6 items-start">
      <div v-for="team in 2" :key="team" class="rounded bg-surface overflow-hidden">
        <div :class="['flex items-center gap-3 px-6 py-4', team === 1 ? 'bg-teal/20' : 'bg-loss/20']">
          <skeleton-box class="h-7 w-28"></skeleton-box>
        </div>
        <div class="grid sm:grid-cols-3 gap-3 px-6 pt-5 pb-3">
          <skeleton-box v-for="stat in 3" :key="stat" class="h-[4.5rem]"></skeleton-box>
        </div>
        <div class="px-6 pt-3 pb-2 max-md:hidden"><skeleton-box class="h-3 w-full"></skeleton-box></div>
        <div v-for="player in 5" :key="player" class="flex items-center gap-3 md:gap-4 px-4 md:px-6 py-3.5 border-t border-surface-3">
          <span class="flex shrink-0">
            <skeleton-box v-for="hero in 3" :key="hero" :class="['w-10 h-10 !rounded-full ring-2 ring-surface', { '-ml-2.5 max-sm:hidden': hero > 1 }]"></skeleton-box>
          </span>
          <div class="flex-1 flex flex-col gap-1.5 min-w-0"><skeleton-box class="h-5 w-32 max-w-full"></skeleton-box><skeleton-box class="h-3.5 w-40 max-w-full"></skeleton-box></div>
          <skeleton-box class="h-3.5 w-[12.5rem] shrink-0 max-md:hidden"></skeleton-box>
          <skeleton-box class="h-7 w-28 md:w-[13.75rem] shrink-0"></skeleton-box>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PrematchSkeleton',
};
</script>
