<template>
  <!-- A card per team, styled like the leagues on /Esports: its logo, then its name, division and record, and a link -->
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
    <search-input v-model="filterText" :placeholder="$t('Filter by team name')" class="w-full sm:w-[18.125rem]"></search-input>
    <p v-if="!filteredData.length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: filterText }) }}</p>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-6 md:gap-8">
      <a v-for="team in filteredData" :key="team.team_name + team.division" :href="`/Esports/${esport}/Team/${team.team_name}?season=${season}&division=${team.division}`" class="group/bt bg-surface rounded p-6 flex flex-col gap-5 min-w-0 hover:bg-surface-1 transition-colors">
        <div class="h-[12.5rem] rounded-control bg-surface-1 group-hover/bt:bg-surface-2 transition-colors flex items-center justify-center">
          <img :src="team.icon_url || fallbackImage" :alt="teamName(team)" class="max-w-[10rem] max-h-[10rem] object-contain text-[0rem]" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = fallbackImage; } }"/>
        </div>
        <div class="flex items-end justify-between gap-4">
          <div class="min-w-0">
            <h2 class="text-xl font-bold truncate">{{ teamName(team) }}</h2>
            <p class="text-sm text-subtext mt-1">{{ [$t('Division :division', { division: team.division }), $t('Win Rate') + ' ' + team.win_rate + '%', $t(':played games', { played: team.games_played })].join(' · ') }}</p>
          </div>
          <span class="inline-flex items-center whitespace-nowrap shrink-0"><span class="group-hover/bt:underline">{{ $t('View stats') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
        </div>
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsTeams',
  components: {
  },
  props: {
    esport: String,
    season: Number,
    data: Array,
  },
  data(){
    return {
      filterText: '',
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    // The league's own logo for a team without one
    fallbackImage() {
      return this.$esportLeague(this.esport).loaderImage;
    },
    filteredData() {
      const lowercaseFilter = this.filterText.toLowerCase();
      return this.data.filter((team) =>
        this.teamName(team).toLowerCase().includes(lowercaseFilter)
      );
    },
  },
  watch: {
  },
  methods: {
    teamName(team) {
      return this.esport === 'NGS' ? this.$ngsTeam(team.team_name).name : team.team_name;
    },
  }
}
</script>