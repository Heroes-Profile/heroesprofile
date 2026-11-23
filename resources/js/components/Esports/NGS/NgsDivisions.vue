<template>
  <!-- A tile per division, its bar showing its games against the busiest division -->
  <div class="max-w-[120rem] mx-auto px-4 md:px-8">
    <panel-box title="Divisions" :subtitle="$t('Season :season · :games games played across :count divisions.', { season: season, games: totalGames.toLocaleString('en-US'), count: data.length })" :divider="false">
      <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <a v-for="division in data" :key="division.division_0" :href="getUrl(division.division_0)" class="group/bt flex flex-col gap-3 rounded bg-surface-1 hover:bg-surface-2 px-5 py-4 transition-colors">
          <div class="flex items-start justify-between gap-3">
            <div class="flex flex-col">
              <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Division') }}</span>
              <span class="text-xl font-bold group-hover/bt:underline">{{ division.division_0 }}</span>
            </div>
            <basil-icon name="arrow-small" class="text-xs mt-2"></basil-icon>
          </div>
          <div class="flex flex-col">
            <span class="text-xs text-subtext">{{ $t('Games played') }}</span>
            <span class="text-lg font-bold">{{ Number(division.count).toLocaleString('en-US') }}</span>
          </div>
          <stat-bar :value="maxGames ? (division.count / maxGames) * 100 : 0" display-text="" color="teal"></stat-bar>
        </a>
      </div>
    </panel-box>
  </div>
</template>

<script>
export default {
  name: 'NgsDivisions',
  components: {
  },
  props: {
    data: Array,
    season: {
      type: [String, Number ]
    }
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    totalGames() {
      return this.data.reduce((sum, division) => sum + Number(division.count || 0), 0);
    },
    maxGames() {
      return Math.max(0, ...this.data.map(division => Number(division.count) || 0));
    },
  },
  watch: {
  },
  methods: {
    getUrl(division){
      let newURL = `/Esports/NGS/Division/${division}`;
      if (this.season) {
        newURL += `?season=${this.season}`;
      }

      return newURL;
    },
  }
}
</script>