<template>
  <!-- Win rate and the stats beside a player, hero, map or role picture; grey blocks in the same shape while loading -->
  <div v-if="loading" class="flex flex-col gap-6">
    <div class="flex flex-col gap-1.5">
      <skeleton-box class="w-20 h-[1.35rem]"></skeleton-box>
      <div class="flex flex-col gap-2">
        <skeleton-box class="w-32 h-9"></skeleton-box>
        <skeleton-box class="h-[1.875rem]"></skeleton-box>
      </div>
    </div>
    <!-- Spaced like the stats below: a 2.7rem block over the same padding and divider, none under the last row -->
    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
      <div v-for="index in count" :key="index" class="pb-2 border-b border-field last:border-b-0 last:pb-0 [&:nth-last-child(2):nth-child(odd)]:border-b-0 [&:nth-last-child(2):nth-child(odd)]:pb-0">
        <skeleton-box class="h-[2.7rem]"></skeleton-box>
      </div>
    </div>
  </div>

  <div v-else class="flex flex-col gap-6">
    <!-- The rate large above its bar -->
    <div class="flex flex-col gap-1.5">
      <span class="text-lg leading-[1.2] text-header">{{ $t('Win Rate') }}</span>
      <div class="flex flex-col gap-2">
        <span class="text-3xl leading-[1.2] font-semibold text-[#a7e9e9]">{{ winRate.toFixed(2) }}%</span>
        <stat-bar size="big" :value="winRate.toFixed(2)" display-text="" :color="$winRateColor(winRate)"></stat-bar>
      </div>
    </div>

    <!-- 44px rows with a divider under each but the last row, which holds one stat when the count is odd -->
    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
      <stat-box v-for="stat in stats" :key="stat.label" class="!m-0 !px-0 !pt-0 !min-w-0 !rounded-none [&_.text-sm]:!text-[1rem] [&_span]:!leading-[1.2] pb-2 border-b border-field last:border-b-0 last:pb-0 [&:nth-last-child(2):nth-child(odd)]:border-b-0 [&:nth-last-child(2):nth-child(odd)]:pb-0" :title="stat.label" :value="stat.value"></stat-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OverviewStats',
  props: {
    winRate: { type: Number, default: 0 },
    // { label, value }, two to a row
    stats: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    // How many stat blocks the loading version shows
    count: { type: Number, default: 8 },
  },
};
</script>
