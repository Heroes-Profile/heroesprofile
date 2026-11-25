<template>
  <!-- Talent stats per level. Vertical: a row per level, the level on the left and a tile per talent. Horizontal (desktop):
       a column per level, as in the Talent Builder. The best win rate on each level is marked BEST -->
  <section v-if="skeleton || tiers.some(tier => tier.talents.length)" class="flex flex-col gap-2" :aria-label="$t('Talent statistics')">
    <!-- Layout switch, saved like the Talent Builder's: a cookie, and the account setting when logged in -->
    <div v-if="!skeleton && !isMobile" class="flex justify-end items-center gap-2 text-sm">
      {{ $t('Layout') }}<tab-button :small="true" :dark="true" :tabs="[{ key: 'vertical', text: 'Vertical' }, { key: 'horizontal', text: 'Horizontal' }]" :overridedefaultside="effectiveStyle" :ignoreclick="true" @tab-click="layoutStyleSelected"></tab-button>
    </div>

    <!-- Padded, so the scroll box doesn't clip a selected level's ring -->
    <div :class="horizontal ? 'overflow-x-auto p-1 -m-1' : ''">
      <div :class="horizontal ? 'grid grid-cols-[repeat(7,minmax(10rem,1fr))] gap-2 items-start' : 'flex flex-col gap-2'">
        <!-- A click on a level highlights it and fades the others, as on the Party page; a second click, or a click
             anywhere outside that level, undoes it -->
        <div v-for="tier in shownTiers" :key="tier.level" :data-tier-level="tier.level" :class="horizontal ? 'flex flex-col gap-2 min-w-0' : 'grid grid-cols-[4.5rem_minmax(0,1fr)] gap-2'">
          <div v-if="skeleton" class="rounded-control bg-surface-1 flex flex-col items-center justify-center py-3">
            <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Level') }}</span>
            <span class="text-2xl font-bold leading-none mt-1">{{ tier.level }}</span>
          </div>
          <button v-else-if="horizontal" type="button" :class="['relative rounded-control py-1.5 text-[0.625rem] font-bold uppercase tracking-wider text-center transition-[colors,opacity]', focusedLevel === tier.level ? 'bg-teal/20 ring-2 ring-teal text-white' : ['bg-surface-1 text-subtext hover:bg-surface-3', { 'opacity-40': focusedLevel }]]" :aria-pressed="focusedLevel === tier.level" :title="levelTitle(tier)" @click="toggleLevel(tier)">
            {{ $t('Level :level', { level: tier.level }) }}
          </button>
          <button v-else type="button" :class="['relative rounded-control flex flex-col items-center justify-center py-3 transition-[colors,opacity]', focusedLevel === tier.level ? 'bg-teal/20 ring-2 ring-teal' : ['bg-surface-1 hover:bg-surface-3', { 'opacity-40': focusedLevel }]]" :aria-pressed="focusedLevel === tier.level" :title="levelTitle(tier)" @click="toggleLevel(tier)">
            <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Level') }}</span>
            <span class="text-2xl font-bold leading-none mt-1">{{ tier.level }}</span>
          </button>

          <div :class="horizontal ? 'flex flex-col gap-2 min-w-0' : 'grid sm:grid-cols-2 xl:grid-cols-4 gap-2 min-w-0'">
            <template v-if="skeleton">
              <div v-for="talent in tier.count" :key="talent" class="rounded-control bg-surface-1 border-2 border-surface-1 p-3 flex flex-col gap-2.5 min-w-0">
                <div class="flex items-center gap-2"><skeleton-box class="w-[2.625rem] h-[2.625rem] !rounded-full shrink-0"></skeleton-box><span class="text-sm font-semibold skeleton-text">Pressurized Glands</span></div>
                <div class="flex flex-col gap-2">
                  <span class="flex items-baseline justify-between"><span class="text-[0.625rem] skeleton-text">Win rate</span><span class="text-xl font-bold leading-none skeleton-text">00.00%</span></span>
                  <span class="flex flex-col gap-1"><span class="flex justify-between text-[0.625rem]"><span class="skeleton-text">Popularity</span><span class="skeleton-text">00.00%</span></span><stat-bar value="0" display-text="" :hide-value="true"></stat-bar></span>
                  <span class="flex justify-between text-[0.625rem]"><span class="skeleton-text">Games</span><span class="skeleton-text">0,000</span></span>
                </div>
              </div>
            </template>
            <div v-else v-for="talent in tier.talents" :key="talent.talentInfo.talent_id"
              :class="['relative rounded-control border-2 p-3 flex flex-col gap-2.5 min-w-0 transition-opacity', isBest(talent, tier) ? 'border-teal bg-surface-1' : 'bg-surface-1 border-surface-1', { 'opacity-25': focusedLevel && focusedLevel !== tier.level }]">
              <div class="flex items-center gap-2 min-w-0">
                <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo" :popup-hoverable="true"></talent-image-wrapper>
                <h3 class="text-sm font-semibold truncate min-w-0">{{ $talentName(talent.talentInfo) }}</h3>
                <!-- Sits on the tile's top edge, clear of the name -->
                <span v-if="isBest(talent, tier)" class="absolute -top-2 right-3 px-1.5 py-0.5 rounded-[0.25rem] bg-teal text-white text-[0.625rem] font-bold uppercase leading-none">{{ $t('Best') }}</span>
              </div>
              <!-- Win rate is the big number; the bar is popularity, grey and labelled so it isn't read as win rate -->
              <div class="flex flex-col gap-2">
                <span class="flex items-baseline justify-between gap-2">
                  <span class="text-[0.625rem] text-subtext">{{ $t('Win rate') }}</span>
                  <span :class="['text-xl font-bold tabular-nums leading-none', { 'text-lteal': isBest(talent, tier) }]">{{ talent.win_rate.toFixed(2) }}%</span>
                </span>
                <span class="flex flex-col gap-1 min-w-0">
                  <span class="flex justify-between gap-2 text-[0.625rem] text-subtext"><span>{{ $t('Popularity') }}</span><span class="tabular-nums">{{ talent.popularity.toFixed(2) }}%</span></span>
                  <stat-bar :value="talent.popularity" display-text="" color="gray-medium"></stat-bar>
                </span>
                <span class="flex justify-between gap-2 text-[0.625rem] text-subtext"><span>{{ $t('Games') }}</span><span class="tabular-nums">{{ formatNumber(talent.games_played) }}</span></span>
              </div>
              <span v-if="statFilter && statFilter !== 'win_rate'" class="text-[0.625rem] text-subtext">{{ statFilterLabel(statFilter) }}: {{ formatNumber(talent.total_filter_type) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <p v-else class="p-4 text-center text-subtext">{{ $t('No talent statistics match these filters.') }}</p>
</template>

<script>
import Cookies from 'js-cookie';
import { statFilterLabel } from '../../../utils/statFilterLabel';

export default {
  name: 'CompactTalentGrid',
  props: {
    talentData: { type: Object, default: () => ({}) },
    statFilter: { type: String, default: 'win_rate' },
    // A loading layout: the rows and tiles in grey
    skeleton: { type: Boolean, default: false },
    // The saved layout from the server (account setting or cookie); pages that don't pass it use the cookie
    layoutStyle: { type: String, default: null },
  },
  data() {
    return {
      skeletonTiers: [1, 4, 7, 10, 13, 16, 20].map(level => ({ level, count: level === 10 ? 2 : 3 })),
      focusedLevel: null,
      localStyle: this.layoutStyle || Cookies.get('talentstatsstyle') || 'vertical',
      isMobile: window.innerWidth < 1024,
    };
  },
  mounted() {
    this.onResize = () => { this.isMobile = window.innerWidth < 1024; };
    window.addEventListener('resize', this.onResize);
    this.onDocumentClick = event => {
      if (this.focusedLevel && !event.target.closest(`[data-tier-level="${this.focusedLevel}"]`)) {
        this.focusedLevel = null;
      }
    };
    document.addEventListener('click', this.onDocumentClick);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.onResize);
    document.removeEventListener('click', this.onDocumentClick);
  },
  computed: {
    // Phones always get the vertical rows
    effectiveStyle() {
      return this.isMobile ? 'vertical' : this.localStyle;
    },
    horizontal() {
      return this.effectiveStyle === 'horizontal';
    },
    shownTiers() {
      if (this.skeleton) {
        return this.skeletonTiers;
      }
      return this.tiers;
    },
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
    toggleLevel(tier) {
      this.focusedLevel = this.focusedLevel === tier.level ? null : tier.level;
    },
    levelTitle(tier) {
      return this.focusedLevel === tier.level ? this.$t('Show all levels') : this.$t('Show only this level');
    },
    layoutStyleSelected(key) {
      this.localStyle = key === 'horizontal' ? 'horizontal' : 'vertical';
      Cookies.set('talentstatsstyle', this.localStyle, { expires: 365, path: '/' });
      // A guest's save is refused by the server, harmlessly
      this.$axios.post('/api/v1/profile/save/settings', { talentstatsstyle: this.localStyle }).catch(() => {});
    },
    isBest(talent, tier) {
      return talent.games_played > 0 && talent.win_rate === tier.highestWinRate && tier.talents.length > 1;
    },
    formatNumber(value) {
      return value == null ? '—' : Number(value).toLocaleString('en-US');
    },
  },
};
</script>
