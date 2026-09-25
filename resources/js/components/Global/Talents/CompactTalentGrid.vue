<template>
  <section v-if="tiers.some(tier => tier.talents.length)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-2" aria-label="Talent statistics">
    <article v-for="tier in tiers" :key="tier.level" class="min-w-0 bg-lighten rounded border border-lighten">
      <div class="bg-teal text-white px-3 py-2 rounded-t">
        <h2 class="font-bold">Level {{ tier.level }}</h2>
      </div>
      <div v-for="talent in tier.talents" :key="talent.talentInfo.talent_id"
        class="flex items-center gap-2 p-2 min-h-24 border-t border-lighten"
        :class="{ 'bg-teal/20': talent.games_played > 0 && talent.win_rate === tier.highestWinRate }">
        <talent-image-wrapper class="shrink-0" :talent="talent.talentInfo"></talent-image-wrapper>
        <div class="min-w-0 flex-1">
          <h3 class="text-sm font-bold leading-tight mb-1.5">{{ talent.talentInfo.title }}</h3>
          <dl class="grid grid-cols-[max-content_minmax(0,1fr)] items-center gap-x-1.5 gap-y-1 text-xs">
            <dt><span aria-hidden="true">WR</span><span class="sr-only">Win rate</span></dt>
            <dd>
              <stat-bar :value="talent.win_rate" :display-text="formatPercent(talent.win_rate)"
                color="teal" :divider="false"
                class="text-xs font-bold leading-5"></stat-bar>
            </dd>

            <dt><span aria-hidden="true">Pop</span><span class="sr-only">Popularity</span></dt>
            <dd>
              <stat-bar :value="talent.popularity" :display-text="formatPercent(talent.popularity)"
                color="purple" :divider="false"
                class="text-xs font-bold leading-5"></stat-bar>
            </dd>

            <dt>
              <i class="fa-solid fa-gamepad text-xs text-white/70" aria-hidden="true"></i>
              <span class="sr-only">Games played</span>
            </dt>
            <dd>{{ formatGames(talent.games_played) }}</dd>
            <template v-if="statFilter && statFilter !== 'win_rate'">
              <dt>{{ statFilterLabel(statFilter) }}</dt>
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
    formatPercent(value) {
      const number = Number(value) || 0;
      return `${number.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      })}%`;
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
