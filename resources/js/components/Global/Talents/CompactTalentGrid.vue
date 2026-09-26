<template>
  <section v-if="tiers.some(tier => tier.talents.length)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-2" aria-label="Talent statistics">
    <article v-for="tier in tiers" :key="tier.level" class="min-w-0">
      <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase">Level {{ tier.level }}</h2>
      <div class="rounded-b overflow-hidden variable-background variable-text">
        <div v-for="talent in tier.talents" :key="talent.talentInfo.talent_id"
          class="py-2 px-3 border-b border-white last:border-b-0"
          :class="talent.games_played > 0 && talent.win_rate === tier.highestWinRate ? 'talent-card-row-highlight' : 'talent-card-row'">
          <div class="flex items-center gap-2">
            <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo"></talent-image-wrapper>
            <h3 class="text-sm">{{ talent.talentInfo.title }}</h3>
          </div>
          <dl class="grid grid-cols-[max-content_minmax(0,1fr)] items-center gap-x-2 gap-y-1 mt-2">
            <dt class="text-[10px] uppercase">Win Rate</dt>
            <dd>
              <stat-bar :value="talent.win_rate" :display-text="talent.win_rate.toFixed(2)" suffix="%"
                color="blue" class="stat-bar-light rounded-l-lg w-full text-xs"></stat-bar>
            </dd>
            <dt class="text-[10px] uppercase">Popularity</dt>
            <dd>
              <stat-bar :value="talent.popularity" :display-text="talent.popularity.toFixed(2)" suffix="%"
                color="yellow" class="stat-bar-light rounded-l-lg w-full text-xs"></stat-bar>
            </dd>
          </dl>
          <div class="flex justify-between gap-2 mt-1 text-[10px]">
            <span v-if="statFilter && statFilter !== 'win_rate'">{{ statFilterLabel(statFilter) }}: {{ formatNumber(talent.total_filter_type) }}</span>
            <span class="ml-auto">{{ formatGames(talent.games_played) }}</span>
          </div>
        </div>
      </div>
    </article>
  </section>
  <p v-else class="p-4 text-center bg-lighten">No talent statistics match these filters.</p>
</template>

<script>
import StatBar from '../../StatBar.vue';
import { statFilterLabel } from '../../../utils/statFilterLabel';

export default {
  components: {
    StatBar,
  },
  props: {
    talentData: { type: Object, required: true },
    statFilter: { type: String, default: 'win_rate' },
  },
  computed: {
    tiers() {
      return [1, 4, 7, 10, 13, 16, 20].map(level => {
        const talents = [...(this.talentData[level] || [])].sort((a, b) => a.sort - b.sort);
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
