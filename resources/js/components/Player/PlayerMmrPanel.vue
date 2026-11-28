<template>
  <panel-box :inner="true" :title="kind ? mmrLabel : $t('Heroes Profile MMR')" :link-text="linkHref || skeleton ? 'HP MMR Breakdown' : null" :link-href="linkHref ? breakdownHref() : null" :skeleton="skeleton">
    <template #title>
      <round-image size="small" icon="fas fa-info" title="info" popupsize="large">
        <div>
          <p class="max-sm:text-xs">{{ $t("Heroes Profile MMR is a custom algorithm developed by Heroes Profile for approximating a player's skill. This MMR or rank does not correlate to your in-game rank or MMR.") }}</p>
        </div>
      </round-image>
    </template>
    <div class="flex flex-col gap-2">
      <div class="flex gap-12 items-center font-medium text-subtext max-md:hidden">
        <span class="w-[12.5rem] shrink-0"><span :class="{ 'skeleton-text': skeleton }">{{ $t('Game mode') }}</span></span>
        <span class="flex-1"><span :class="{ 'skeleton-text': skeleton }">{{ $t('Win Rate') }}</span></span>
        <span v-if="tiers" class="flex-1 max-w-[12.5rem]"><span :class="{ 'skeleton-text': skeleton }">{{ $t('HP Rank Tier') }}</span></span>
        <span class="flex-1 max-w-[6.25rem] text-center"><span :class="{ 'skeleton-text': skeleton }">{{ mmrLabel }}</span></span>
      </div>
      <template v-for="mode in mmrModes" :key="mode.label">
        <div class="h-px bg-gray-dark"></div>
        <!-- Phones stack each mode: name with a Win Rate caption, then the bar, then tier and MMR -->
        <div class="flex flex-wrap md:flex-nowrap gap-x-6 md:gap-x-12 gap-y-2 items-center">
          <a v-if="linkHref" class="max-md:flex-1 md:w-[12.5rem] shrink-0 font-semibold text-header truncate link-more" :href="modeHref(mode)">{{ $t(mode.label) }}</a>
          <span v-else class="max-md:flex-1 md:w-[12.5rem] shrink-0 font-semibold text-header truncate"><span :class="{ 'skeleton-text': skeleton }">{{ $t(mode.label) }}</span></span>
          <span class="md:hidden text-xs text-subtext">{{ $t('Win Rate') }}</span>
          <!-- No games in this mode: the empty bar, without a 00.00% or an MMR of 0 -->
          <stat-bar class="max-md:w-full md:flex-1 min-w-0" :value="mode.data ? mode.data.win_rate.toFixed(2) : '0.00'" suffix="%" :color="$winRateColor(mode.data ? mode.data.win_rate : 0)" :hide-value="!mode.data"></stat-bar>
          <!-- An invisible pill holds the row's height when there's no tier, so every row lines up -->
          <span v-if="tiers" class="md:flex-1 md:max-w-[12.5rem]"><rank-tier-pill align="left" :class="{ invisible: !mode.data }" :tier="mode.data ? mode.data.rank_tier : 'Unranked'" :aria-hidden="!mode.data || null"></rank-tier-pill></span>
          <span class="md:flex-1 md:max-w-[6.25rem] md:text-center font-semibold max-md:ml-auto"><template v-if="mode.data"><span class="md:hidden text-xs text-subtext font-normal mr-2">{{ mmrLabel }}</span>{{ mode.data.mmr.toLocaleString('en-US') }}</template></span>
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
    // 'Hero', 'Role' or 'Map' on those pages, so the title and column say which MMR it is
    kind: String,
    linkHref: String,
    // Extra query for the mode links, such as { hero: 'Li-Ming' }
    linkQuery: { type: Object, default: () => ({}) },
    // Rows to show instead of the six game modes, as { label, code, data }
    modes: { type: Array, default: null },
    tiers: { type: Boolean, default: true },
    // A loading skeleton: labels, headers and link as grey blocks their size, bars empty
    skeleton: Boolean,
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
    mmrLabel() {
      return { Hero: this.$t('HP Hero MMR'), Role: this.$t('HP Role MMR'), Map: this.$t('HP Map MMR') }[this.kind] ?? this.$t('HP MMR');
    },
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
