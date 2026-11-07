<template>
  <panel-box :inner="true" title="Heroes Profile MMR" :link-text="linkHref ? 'HP MMR Breakdown' : null" :link-href="linkHref ? breakdownHref() : null">
    <template #title>
      <round-image size="small" icon="fas fa-info" title="info" popupsize="large">
        <div>
          <p class="max-sm:text-xs">Heroes Profile MMR is a custom algorithm developed by Heroes Profile for approximating a player's skill. This MMR or rank does not correlate to your in-game rank or MMR.</p>
        </div>
      </round-image>
    </template>
    <div class="flex flex-col gap-2">
      <div class="flex gap-12 items-center font-medium text-subtext max-md:hidden">
        <span class="w-[12.5rem] shrink-0">Game mode</span>
        <span class="flex-1">Win Rate</span>
        <span v-if="tiers" class="flex-1 max-w-[12.5rem]">HP Rank Tier</span>
        <span class="flex-1 max-w-[6.25rem] text-center">HP MMR</span>
      </div>
      <template v-for="mode in mmrModes" :key="mode.label">
        <div class="h-px bg-gray-dark"></div>
        <!-- Phones stack each mode: name with a Win Rate caption, then the bar, then tier and MMR -->
        <div class="flex flex-wrap md:flex-nowrap gap-x-6 md:gap-x-12 gap-y-2 items-center">
          <a v-if="linkHref" class="max-md:flex-1 md:w-[12.5rem] shrink-0 font-semibold text-header truncate hover:underline" :href="modeHref(mode)">{{ mode.label }}<i class="fas fa-chevron-right text-sm ml-3"></i></a>
          <span v-else class="max-md:flex-1 md:w-[12.5rem] shrink-0 font-semibold text-header truncate">{{ mode.label }}</span>
          <span class="md:hidden text-xs text-subtext">Win Rate</span>
          <stat-bar class="max-md:w-full md:flex-1 min-w-0" :value="mode.data ? mode.data.win_rate.toFixed(2) : '0.00'" suffix="%" :color="$winRateColor(mode.data ? mode.data.win_rate : 0)"></stat-bar>
          <span v-if="tiers" class="md:flex-1 md:max-w-[12.5rem]"><rank-tier-pill v-if="mode.data" :tier="mode.data.rank_tier"></rank-tier-pill></span>
          <span class="md:flex-1 md:max-w-[6.25rem] md:text-center font-semibold max-md:ml-auto"><span class="md:hidden text-xs text-subtext font-normal mr-2">HP MMR</span>{{ mode.data ? mode.data.mmr.toLocaleString('en-US') : 0 }}</span>
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
    // Extra query for the mode links, such as { hero: 'Li-Ming' }
    linkQuery: { type: Object, default: () => ({}) },
    // Rows to show instead of the six game modes, as { label, code, data }
    modes: { type: Array, default: null },
    tiers: { type: Boolean, default: true },
  },
  methods: {
    breakdownHref(query = {}) {
      const params = new URLSearchParams({ ...query, ...this.linkQuery }).toString();
      return params ? this.linkHref + '?' + params : this.linkHref;
    },
    modeHref(mode) {
      return this.breakdownHref({ game_type: mode.code });
    },
  },
  computed: {
    mmrModes(){
      if (this.modes) return this.modes;
      return [
        { label: 'Quick Match', code: 'qm', data: this.data.qm_mmr_data },
        { label: 'Unranked Draft', code: 'ud', data: this.data.ud_mmr_data },
        { label: 'Hero League', code: 'hl', data: this.data.hl_mmr_data },
        { label: 'Team League', code: 'tl', data: this.data.tl_mmr_data },
        { label: 'Storm League', code: 'sl', data: this.data.sl_mmr_data },
        { label: 'ARAM', code: 'ar', data: this.data.ar_mmr_data },
      ];
    },
  },
};
</script>
