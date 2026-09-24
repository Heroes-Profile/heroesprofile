<template>
  <section v-if="tiers.some(tier => tier.talents.length)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-2" aria-label="Talent statistics">
    <article v-for="tier in tiers" :key="tier.level" class="min-w-0 bg-lighten rounded border border-lighten">
      <div class="flex flex-wrap items-center justify-between gap-1 bg-teal text-white px-3 py-2 rounded-t">
        <h2 class="font-bold">Level {{ tier.level }}</h2>
        <div class="flex gap-1" role="group" :aria-label="'Highlight level ' + tier.level + ' talent by'">
          <button v-for="column in highlightColumns" :key="column.key" type="button"
            class="text-xs px-1.5 py-0.5 rounded hover:bg-lblue"
            :class="tier.highlightKey === column.key ? 'bg-blue' : 'bg-teal border border-white/40'"
            :title="'Highlight highest ' + column.title" :aria-pressed="tier.highlightKey === column.key"
            @click="highlightKeys = { ...highlightKeys, [tier.level]: column.key }">
            {{ column.label }}
          </button>
        </div>
      </div>
      <div v-for="talent in tier.talents" :key="talent.talentInfo.talent_id"
        class="flex items-center gap-2 p-2 min-h-[96px] border-t border-lighten"
        :class="{ 'bg-teal/20': talent.games_played > 0 && talent[tier.highlightKey] === tier.highestValue }">
        <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo"></talent-image-wrapper>
        <div class="min-w-0 flex-1">
          <h3 class="text-sm font-bold leading-tight mb-2">{{ talent.talentInfo.title }}</h3>
          <dl class="grid grid-cols-[auto_1fr] gap-x-3 text-xs">
            <dt>Win Rate</dt><dd class="font-bold text-right">{{ formatPercent(talent.win_rate) }}</dd>
            <dt>Popularity</dt><dd class="font-bold text-right">{{ formatPercent(talent.popularity) }}</dd>
            <dt>Games Played</dt><dd class="font-bold text-right">{{ formatNumber(talent.games_played) }}</dd>
            <template v-if="statFilter && statFilter !== 'win_rate'">
              <dt>{{ statFilterLabel(statFilter) }}</dt><dd class="font-bold text-right">{{ formatNumber(talent.total_filter_type) }}</dd>
            </template>
          </dl>
        </div>
      </div>
    </article>
  </section>
  <p v-else class="p-4 text-center bg-lighten">No talent statistics match these filters.</p>
</template>

<script>
import { statFilterLabel } from '../../../utils/statFilterLabel';

export default {
  props: {
    talentData: { type: Object, required: true },
    statFilter: { type: String, default: 'win_rate' },
  },
  data() {
    return {
      highlightKeys: {},
    };
  },
  computed: {
    highlightColumns() {
      const columns = [
        { key: 'win_rate', label: 'WR', title: 'win rate' },
        { key: 'popularity', label: 'Pop', title: 'popularity' },
        { key: 'games_played', label: 'GP', title: 'games played' },
      ];
      if (this.statFilter && this.statFilter !== 'win_rate') {
        columns.push({ key: 'total_filter_type', label: 'Avg', title: statFilterLabel(this.statFilter) });
      }
      return columns;
    },
    tiers() {
      return [1, 4, 7, 10, 13, 16, 20].map(level => {
        const talents = [...(this.talentData[level] || [])].sort((a, b) => a.sort - b.sort);
        const highlightKey = this.highlightKeys[level] || 'win_rate';
        return {
          level,
          talents,
          highlightKey,
          highestValue: Math.max(...talents.map(talent => talent[highlightKey])),
        };
      });
    },
  },
  watch: {
    statFilter() {
      this.highlightKeys = {};
    },
  },
  methods: {
    statFilterLabel,
    formatPercent(value) {
      return Number(value || 0).toFixed(2) + '%';
    },
    formatNumber(value) {
      return value == null ? '—' : Number(value).toLocaleString('en-US');
    },
  },
};
</script>
