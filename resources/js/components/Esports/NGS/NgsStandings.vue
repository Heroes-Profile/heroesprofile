<template>
  <div>
    <p v-if="teamSearch.trim() && !Object.keys(shownData).length" class="max-w-[93.75rem] mx-auto mt-4 text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: teamSearch }) }}</p>
    <div v-for="(division, divisionName) in shownData" :key="divisionName" class="max-w-[93.75rem] mx-auto flex justify-start mb-2 mt-4">
      <table class="min-w-0 w-full responsive-table max-md:text-xs">
        <thead>
          <tr class="bg-surface-3">
            <th colspan="7"><a :href="'/Esports/NGS/Division/' + divisionName">{{ $t('Division :divisionname', { divisionname: divisionName }) }}</a></th>
          </tr>
        </thead>
        <thead >
          <tr>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
            </th>
            <th class="py-2 px-3 text-left max-md:text-xs md:text-sm leading-4">
              <!-- One search for every division, so it finds a team wherever it plays -->
              <div class="flex items-center gap-3">
                <span>{{ $t('Team') }}</span>
                <search-input v-model="teamSearch" :placeholder="$t('Search team')" :collapsible="true" class="w-40"></search-input>
              </div>
            </th>            
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Points') }}
            </th>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              <span class="max-md:hidden">{{ $t('Dominations') }}</span><span class="md:hidden text-[0.5625rem]">Denom- inations</span>
            </th>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Games Won') }}
            </th>   
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Games Lost') }}
            </th>   
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Matches Played') }}
            </th>     
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-for="(team, teamData) in division" :key="teamData.team_name">
            <td class="max-md:max-w-[0.9375rem] max-md:p-1">{{ team.rank }}</td>
            <td class="max-md:max-w-[6.25rem] text-left">
              <div class="flex items-center gap-2">
                <a class="group/bt inline-flex items-center max-md:break-words" :href="`/Esports/NGS/Team/${encodeURIComponent($ngsTeam(team.team_name).name)}?season=${season}&division=${divisionName}`"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                <round-image v-if="$ngsTeam(team.team_name).withdrawnSeason" class="text-xs" size="small" icon="fa-solid fa-ban" :title="$t('Withdrawn')" popupsize="small" mobileClick="true"><p class="font-normal">{{ $t('Withdrew from the league in Season :withdrawnseason.', { withdrawnseason: $ngsTeam(team.team_name).withdrawnSeason }) }}</p></round-image>
              </div>
            </td>
            <td>{{ team.points }}</td>
            <td>{{ team.dominations }}</td>
            <td>{{ team.games_won }}</td>
            <td>{{ team.games_lost }}</td>
            <td>{{ team.matches_played }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NgsStandings',
  components: {
  },
  props: {
    divisions: Array,
    season: Number,
    data: Object,
  },
  data(){
    return {
      teamSearch: '',
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    // Divisions with the teams matching the search; divisions with none drop out while searching
    shownData() {
      const search = this.teamSearch.trim().toLowerCase();
      if (!search || !this.data) {
        return this.data;
      }
      const shown = {};
      for (const [divisionName, division] of Object.entries(this.data)) {
        const teams = Object.values(division).filter(team => this.$ngsTeam(team.team_name).name.toLowerCase().includes(search));
        if (teams.length) {
          shown[divisionName] = teams;
        }
      }
      return shown;
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>