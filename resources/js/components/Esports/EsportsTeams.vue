<template>
  <!-- One card of team tiles: the logo (or the team's initials), its name and division -->
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8">
    <panel-box title="Teams" :subtitle="$t('Season :season', { season: season }) + ' · ' + (division ? $t('Division :division', { division: division }) : $t('All divisions').toLowerCase())" :divider="false">
      <template #right>
        <search-input v-model="filterText" :placeholder="$t('Filter by team name')" class="w-full sm:w-[18.125rem] sm:ml-auto"></search-input>
      </template>
      <p v-if="!filteredData.length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: filterText }) }}</p>
      <div v-else class="grid sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-4">
        <a v-for="team in filteredData" :key="team.team_name + team.division" :href="`/Esports/${esport}/Team/${encodeURIComponent(teamName(team))}?season=${season}&division=${team.division}`" class="flex flex-col items-center gap-3 bg-surface-1 px-4 py-6 min-w-0 text-center link-tile">
          <img v-if="team.icon_url && !failed[team.team_name]" :src="team.icon_url" :alt="teamName(team)" class="w-20 h-20 object-contain text-[0rem]" @error="failed[team.team_name] = true"/>
          <span v-else class="w-20 h-20 rounded-full bg-surface-3 flex items-center justify-center text-2xl font-bold text-subtext">{{ initials(team) }}</span>
          <span class="max-w-full truncate font-semibold"><span class="link-tile-title">{{ teamName(team) }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
          <span class="rounded-md bg-surface-3 px-2 py-0.5 text-xs text-subtext">{{ $t('Division :division', { division: team.division }) }}</span>
        </a>
      </div>
    </panel-box>
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
    division: [String, Number],
  },
  data(){
    return {
      filterText: '',
      // Teams whose logo failed to load show their initials
      failed: {},
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
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
    initials(team) {
      return this.teamName(team).split(/\s+/).filter(Boolean).slice(0, 2).map(word => word.charAt(0).toUpperCase()).join('');
    },
    teamName(team) {
      return this.esport === 'NGS' ? this.$ngsTeam(team.team_name).name : team.team_name;
    },
  }
}
</script>