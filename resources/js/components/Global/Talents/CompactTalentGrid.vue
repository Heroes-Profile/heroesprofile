<template>
  <!-- A row per talent level: the level, then a tile per talent. The best win rate on each level is marked BEST -->
  <section v-if="skeleton || tiers.some(tier => tier.talents.length)" class="flex flex-col gap-2" :aria-label="$t('Talent statistics')">
    <div v-for="tier in (skeleton ? skeletonTiers : tiers)" :key="tier.level" class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-2">
      <div class="rounded-control bg-surface-1 flex flex-col items-center justify-center py-3">
        <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Level') }}</span>
        <span class="text-2xl font-bold leading-none mt-1">{{ tier.level }}</span>
      </div>
      <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-2 min-w-0">
        <template v-if="skeleton">
          <div v-for="talent in tier.count" :key="talent" class="rounded-control bg-surface-1 border-2 border-surface-1 p-3 flex flex-col gap-2.5 min-w-0">
            <div class="flex items-center gap-2"><skeleton-box class="w-[2.625rem] h-[2.625rem] !rounded-full shrink-0"></skeleton-box><span class="text-sm font-semibold skeleton-text">Pressurized Glands</span></div>
            <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] gap-3 items-end">
              <span class="flex flex-col"><span class="text-[0.625rem] skeleton-text">Win rate</span><span class="font-semibold skeleton-text">00.00%</span></span>
              <stat-bar value="0" display-text="" :hide-value="true"></stat-bar>
              <span class="flex flex-col items-end"><span class="text-[0.625rem] skeleton-text">Games</span><span class="font-semibold skeleton-text">0,000</span></span>
            </div>
          </div>
        </template>
        <div v-else v-for="talent in tier.talents" :key="talent.talentInfo.talent_id"
          :class="['rounded-control bg-surface-1 border-2 p-3 flex flex-col gap-2.5 min-w-0', isBest(talent, tier) ? 'border-teal' : 'border-surface-1']">
          <div class="flex items-center gap-2 min-w-0">
            <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo" :popup-hoverable="false"></talent-image-wrapper>
            <h3 class="text-sm font-semibold truncate min-w-0">{{ $talentName(talent.talentInfo) }}</h3>
            <span v-if="isBest(talent, tier)" class="ml-auto shrink-0 px-1.5 py-0.5 rounded-[0.25rem] bg-teal/20 text-lteal text-[0.625rem] font-bold uppercase">{{ $t('Best') }}</span>
          </div>
          <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] gap-3 items-end">
            <span class="flex flex-col">
              <span class="text-[0.625rem] text-subtext">{{ $t('Win rate') }}</span>
              <span :class="['font-semibold tabular-nums', talent.win_rate > 50 ? 'text-win' : '']">{{ talent.win_rate.toFixed(2) }}%</span>
            </span>
            <span class="flex flex-col gap-1 min-w-0">
              <span class="flex justify-between gap-2 text-[0.625rem] text-subtext"><span>{{ $t('Popularity') }}</span><span class="tabular-nums">{{ talent.popularity.toFixed(2) }}%</span></span>
              <stat-bar :value="talent.popularity" display-text="" color="teal"></stat-bar>
            </span>
            <span class="flex flex-col items-end">
              <span class="text-[0.625rem] text-subtext">{{ $t('Games') }}</span>
              <span class="font-semibold tabular-nums">{{ formatNumber(talent.games_played) }}</span>
            </span>
          </div>
          <span v-if="statFilter && statFilter !== 'win_rate'" class="text-[0.625rem] text-subtext">{{ statFilterLabel(statFilter) }}: {{ formatNumber(talent.total_filter_type) }}</span>
        </div>
      </div>
    </div>
  </section>
  <p v-else class="p-4 text-center text-subtext">{{ $t('No talent statistics match these filters.') }}</p>
</template>

<script>
import { statFilterLabel } from '../../../utils/statFilterLabel';

export default {
  name: 'CompactTalentGrid',
  props: {
    talentData: { type: Object, default: () => ({}) },
    statFilter: { type: String, default: 'win_rate' },
    // A loading layout: the rows and tiles in grey
    skeleton: { type: Boolean, default: false },
  },
  data() {
    return {
      skeletonTiers: [1, 4, 7, 10, 13, 16, 20].map(level => ({ level, count: level === 10 ? 2 : 3 })),
    };
  },
  computed: {
    tiers() {
      return [1, 4, 7, 10, 13, 16, 20].map(level => {
        const talents = [...(this.talentData?.[level] || [])].sort((a, b) => a.sort - b.sort);
        return {
          level,
          talents,
          highestWinRate: Math.max(...talents.map(talent => talent.win_rate)),
        };
      });
    },
  },
  methods: {
    statFilterLabel,
    isBest(talent, tier) {
      return talent.games_played > 0 && talent.win_rate === tier.highestWinRate && tier.talents.length > 1;
    },
    formatNumber(value) {
      return value == null ? '—' : Number(value).toLocaleString('en-US');
    },
  },
};
</script>
