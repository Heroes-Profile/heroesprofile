<template>
  <section v-if="tiers.some(tier => tier.talents.length)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-2" aria-label="Talent statistics">
    <article v-for="tier in tiers" :key="tier.level" class="min-w-0 bg-lighten rounded border border-lighten">
      <h2 class="bg-blue text-white px-3 py-2 font-bold rounded-t">Level {{ tier.level }}</h2>
      <div v-for="talent in tier.talents" :key="talent.talentInfo.talent_id"
        class="flex items-center gap-2 p-2 min-h-[96px] border-t border-lighten"
        :class="{ 'bg-teal/20': talent.games_played > 0 && talent.win_rate === tier.highestWinRate }">
        <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo"></talent-image-wrapper>
        <div class="min-w-0 flex-1">
          <h3 class="text-sm font-bold leading-tight mb-2">{{ talent.talentInfo.title }}</h3>
          <dl class="grid grid-cols-2 gap-x-2 gap-y-1 text-xs">
            <div><dt>Win Rate</dt><dd class="font-bold">{{ formatPercent(talent.win_rate) }}</dd></div>
            <div><dt>Popularity</dt><dd class="font-bold">{{ formatPercent(talent.popularity) }}</dd></div>
            <div class="col-span-2"><dt>Games Played</dt><dd class="font-bold">{{ formatNumber(talent.games_played) }}</dd></div>
            <div v-if="statFilter && statFilter !== 'win_rate'" class="col-span-2">
              <dt>Avg {{ statLabel }}</dt><dd class="font-bold">{{ formatNumber(talent.total_filter_type) }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </article>
  </section>
  <p v-else class="p-4 text-center bg-lighten">No talent statistics match these filters.</p>
</template>

<script>
export default {
  props: {
    talentData: { type: Object, required: true },
    statFilter: { type: String, default: 'win_rate' },
  },
  computed: {
    tiers() {
      return [1, 4, 7, 10, 13, 16, 20].map(level => ({
        level,
        talents: [...(this.talentData[level] || [])].sort((a, b) => a.sort - b.sort),
        highestWinRate: Math.max(...(this.talentData[level] || []).map(talent => talent.win_rate)),
      }));
    },
    statLabel() {
      return this.statFilter.replace(/_/g, ' ');
    },
  },
  methods: {
    formatPercent(value) {
      return Number(value || 0).toFixed(2) + '%';
    },
    formatNumber(value) {
      return value == null ? '—' : Number(value).toLocaleString('en-US');
    },
  },
};
</script>
