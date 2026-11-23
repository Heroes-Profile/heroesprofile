<template>
  <!-- The match page in grey while it loads: heading, the two team cards, then Match Scores, Talents and the rest -->
  <div>
    <div class="max-w-[120rem] mx-auto px-4 md:px-8 pt-10 pb-6 flex flex-wrap items-end justify-between gap-6">
      <div class="flex flex-col gap-3">
        <skeleton-box class="h-12 md:h-16 w-72 md:w-[28rem]"></skeleton-box>
        <skeleton-box class="h-5 md:h-7 w-64 md:w-[36rem]"></skeleton-box>
      </div>
      <div class="flex gap-3">
        <skeleton-box class="h-[3.125rem] w-40"></skeleton-box>
        <skeleton-box class="h-[3.125rem] w-44"></skeleton-box>
      </div>
    </div>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 pb-10 flex flex-col gap-6">
      <div class="grid xl:grid-cols-2 gap-6">
        <div v-for="team in 2" :key="team" :class="['relative rounded bg-surface border-t-4 border-gray-dark p-5 md:p-6 flex flex-col gap-5', { 'md:!rounded-tl-none': team === 1 }]">
          <loading-component v-if="team === 1" :compact="true" :tab="true" :overrideimage="overrideimage"></loading-component>
          <div class="flex items-center justify-between gap-3">
            <skeleton-box class="h-7 w-40"></skeleton-box>
            <skeleton-box class="h-6 w-32"></skeleton-box>
          </div>
          <div class="grid grid-cols-5 gap-2">
            <div v-for="player in 5" :key="player" class="flex flex-col items-center gap-1.5">
              <skeleton-box class="w-16 h-16 md:w-20 md:h-20 !rounded-full"></skeleton-box>
              <skeleton-box class="h-4 w-16 max-w-full"></skeleton-box>
              <skeleton-box class="h-3 w-12 max-w-full"></skeleton-box>
            </div>
          </div>
          <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
            <skeleton-box v-for="stat in 6" :key="stat" class="h-[3.75rem]"></skeleton-box>
          </div>
        </div>
      </div>

      <panel-box title="Match Scores" subtitle="Best value in each column is highlighted. HP Rating is the Heroes Profile performance score (0–100)." :skeleton="true" :divider="false">
        <table-skeleton :bare="true" :rows="10" :columns="['hero', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
      </panel-box>

      <panel-box title="Talents" subtitle="Talents picked at levels 1, 4, 7, 10, 13, 16 and 20. Empty slots were not reached." :skeleton="true" :divider="false">
        <div class="grid xl:grid-cols-2 gap-6">
          <div v-for="team in 2" :key="team" class="flex flex-col gap-2">
            <skeleton-box class="h-3 w-32"></skeleton-box>
            <div v-for="player in 5" :key="player" class="flex items-center gap-3 rounded bg-surface-1 px-4 py-2.5">
              <skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
              <div class="flex flex-col gap-1.5 flex-1"><skeleton-box class="h-4 w-28"></skeleton-box><skeleton-box class="h-3 w-20"></skeleton-box></div>
              <skeleton-box v-for="talent in 7" :key="talent" class="w-8 h-8 shrink-0 max-sm:hidden"></skeleton-box>
            </div>
          </div>
        </div>
      </panel-box>

      <panel-box title="Experience" subtitle="Team experience over the course of the match." :skeleton="true" :divider="false">
        <skeleton-box class="h-[20rem] w-full"></skeleton-box>
      </panel-box>
    </div>
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
