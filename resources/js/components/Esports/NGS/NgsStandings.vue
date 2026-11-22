<template>
  <!-- A card per division, its rounded table inside, as on the global pages -->
  <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
    <p v-if="teamSearch.trim() && !Object.keys(shownData).length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: teamSearch }) }}</p>
    <panel-box v-for="(division, divisionName) in shownData" :key="divisionName" :title="$t('Division :divisionname', { divisionname: divisionName })" link-text="View stats" :link-href="'/Esports/NGS/Division/' + divisionName">
      <!-- Shrinks to fit a phone's card instead of running past it -->
      <div ref="tablecontainers" class="overflow-clip">
      <table ref="tables" class="min-w-0 w-full rounded-table max-md:text-xs">
        <thead>
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
              {{ $t('Dominations') }}
            </th>
            <!-- Left out on phones, so the rest fit -->
            <th class="max-md:hidden py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Win') }}
            </th>   
            <!-- Left out on phones, so the rest fit -->
            <th class="max-md:hidden py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Loss') }}
            </th>   
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              {{ $t('Games') }}
            </th>     
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-for="(team, teamData) in division" :key="teamData.team_name">
            <td class="max-md:max-w-[0.9375rem] max-md:p-1">{{ team.rank }}</td>
            <td class="max-md:min-w-[9rem] text-left">
              <!-- The withdrawn mark wraps under the name when the column is too narrow for both (phones) -->
              <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <a class="group/bt inline-flex items-center min-w-0 max-md:break-words" :href="`/Esports/NGS/Team/${encodeURIComponent($ngsTeam(team.team_name).name)}?season=${season}&division=${divisionName}`"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                <round-image v-if="$ngsTeam(team.team_name).withdrawnSeason" class="text-xs shrink-0" size="small" icon="fa-solid fa-ban" :title="$t('Withdrawn')" popupsize="small" mobileClick="true"><p class="font-normal">{{ $t('Withdrew from the league in Season :withdrawnseason.', { withdrawnseason: $ngsTeam(team.team_name).withdrawnSeason }) }}</p></round-image>
              </div>
            </td>
            <td>{{ team.points }}</td>
            <td>{{ team.dominations }}</td>
            <td class="max-md:hidden">{{ team.games_won }}</td>
            <td class="max-md:hidden">{{ team.games_lost }}</td>
            <td>{{ team.matches_played }}</td>
          </tr>
        </tbody>
      </table>
      </div>
    </panel-box>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
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
    this.fitTables();
  },
  // The search adds and removes divisions
  updated() {
    this.fitTables();
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
    fitTables() {
      (this.$refs.tables || []).forEach((table, index) => scaleTableToFit(table, this.$refs.tablecontainers[index]));
    },
  }
}
</script>