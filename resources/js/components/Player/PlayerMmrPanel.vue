<template>
  <panel-box :inner="true" title="Heroes Profile MMR" :link-text="linkHref ? 'View HP MMR Breakdown' : null" :link-href="linkHref">
    <template #title>
      <round-image size="small" icon="fas fa-info" title="info" popupsize="large">
        <div>
          <p class="max-sm:text-xs">Heroes Profile MMR is a custom algorithm developed by Heroes Profile for approximating a player's skill. This MMR or rank does not correlate to your in-game rank or MMR.</p>
        </div>
      </round-image>
    </template>
    <div class="flex flex-col gap-2">
      <div class="flex gap-12 items-center font-medium text-subtext max-md:hidden">
        <span class="w-[200px] shrink-0">Game mode</span>
        <span class="flex-1">Win Rate</span>
        <span class="flex-1 max-w-[200px]">HP Rank Tier</span>
        <span class="flex-1 max-w-[100px] text-center">HP MMR</span>
      </div>
      <template v-for="mode in mmrModes" :key="mode.label">
        <div class="h-px bg-gray-dark"></div>
        <!-- Phones stack each mode: name, then bar, then tier and MMR -->
        <div class="flex flex-wrap md:flex-nowrap gap-x-6 md:gap-x-12 gap-y-2 items-center">
          <span class="w-full md:w-[200px] shrink-0 font-semibold text-header truncate">{{ mode.label }}</span>
          <stat-bar class="max-md:w-full md:flex-1 min-w-0" :value="mode.data ? mode.data.win_rate.toFixed(2) : '0.00'" suffix="%" :color="$winRateColor(mode.data ? mode.data.win_rate : 0)"></stat-bar>
          <span class="md:flex-1 md:max-w-[200px]"><rank-tier-pill v-if="mode.data" :tier="mode.data.rank_tier"></rank-tier-pill></span>
          <span class="md:flex-1 md:max-w-[100px] md:text-center font-semibold max-md:ml-auto">{{ mode.data ? mode.data.mmr.toLocaleString('en-US') : 0 }}</span>
        </div>
      </template>
    </div>
  </panel-box>
</template>

<script>
export default {
  name: 'PlayerMmrPanel',
  props: {
    // Holds qm_mmr_data, ud_mmr_data and so on, as the profile and single hero data both do
    data: Object,
    linkHref: String,
  },
  computed: {
    mmrModes(){
      return [
        { label: 'Quick Match', data: this.data.qm_mmr_data },
        { label: 'Unranked Draft', data: this.data.ud_mmr_data },
        { label: 'Hero League', data: this.data.hl_mmr_data },
        { label: 'Team League', data: this.data.tl_mmr_data },
        { label: 'Storm League', data: this.data.sl_mmr_data },
        { label: 'ARAM', data: this.data.ar_mmr_data },
      ];
    },
  },
};
</script>
