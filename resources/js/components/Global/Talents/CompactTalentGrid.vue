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
        class="flex items-center gap-2 p-2 min-h-24 border-t border-lighten"
        :class="{ 'bg-teal/20': talent.games_played > 0 && talent[tier.highlightKey] === tier.highestValue }">
        <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo"></talent-image-wrapper>
        <div class="min-w-0 flex-1">
          <h3 class="text-sm font-bold leading-tight mb-1.5">{{ talent.talentInfo.title }}</h3>
          <dl class="grid grid-cols-[max-content_minmax(0,1fr)_max-content] items-center gap-x-1.5 gap-y-1 text-xs">
            <dt><span aria-hidden="true">WR</span><span class="sr-only">Win rate</span></dt>
            <dd class="h-2 overflow-hidden rounded-full bg-black/30" aria-hidden="true">
              <span class="block h-full rounded-full bg-green" :style="{ width: percentWidth(talent.win_rate) }"></span>
            </dd>
            <dd class="font-bold text-right whitespace-nowrap tabular-nums">{{ formatPercent(talent.win_rate) }}</dd>

            <dt><span aria-hidden="true">Pop</span><span class="sr-only">Popularity</span></dt>
            <dd class="h-2 overflow-hidden rounded-full bg-black/30" aria-hidden="true">
              <span class="block h-full rounded-full bg-purple" :style="{ width: percentWidth(talent.popularity) }"></span>
            </dd>
            <dd class="font-bold text-right whitespace-nowrap tabular-nums">{{ formatPercent(talent.popularity) }}</dd>

            <dt>
              <i class="fa-solid fa-gamepad text-xs text-white/70" aria-hidden="true"></i>
              <span class="sr-only">Games played</span>
            </dt>
            <dd class="col-span-2">{{ formatGames(talent.games_played) }}</dd>
            <template v-if="statFilter && statFilter !== 'win_rate'">
              <dt class="col-span-2">{{ statFilterLabel(statFilter) }}</dt>
              <dd class="font-bold text-right">{{ formatNumber(talent.total_filter_type) }}</dd>
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
      const number = Number(value) || 0;
      return `${number.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      })}%`;
    },
    percentWidth(value) {
      return `${Math.min(100, Math.max(0, Number(value) || 0))}%`;
    },
    formatGames(value) {
      const games = Number(value) || 0;
      return `${games.toLocaleString('en-US')} ${games === 1 ? 'game' : 'games'}`;
    },
    formatNumber(value) {
      return value == null ? '—' : Number(value).toLocaleString('en-US');
    },
  },
};
</script>
