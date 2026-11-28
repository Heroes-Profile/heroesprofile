<template>
  <!-- A card per division, two to a row, its rounded table inside -->
  <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-4">
    <p v-if="teamSearch.trim() && !Object.keys(shownData).length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: teamSearch }) }}</p>
    <div class="grid xl:grid-cols-2 gap-6">
      <panel-box v-for="(division, divisionName) in shownData" :key="divisionName" :title="$t('Division :divisionname', { divisionname: divisionName })" :subtitle="divisionSummary(division)" link-text="View stats" :link-href="'/Esports/NGS/Division/' + divisionName" :divider="false">
        <!-- Shrinks to fit a phone's card instead of running past it -->
        <div ref="tablecontainers" class="overflow-clip">
        <table ref="tables" class="min-w-0 w-full rounded-table max-md:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3 text-center text-sm leading-4 w-12">#</th>
              <th class="py-2 px-3 text-left text-sm leading-4">
                <!-- One search for every division, so it finds a team wherever it plays -->
                <div class="flex items-center gap-3">
                  <span>{{ $t('Team') }}</span>
                  <search-input v-model="teamSearch" :placeholder="$t('Search team')" :collapsible="true" class="w-40"></search-input>
                </div>
              </th>
              <th class="py-2 px-3 text-center text-sm leading-4" :title="$t('Points')">{{ $t('Pts') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4" :title="$t('Dominations')">{{ $t('Dom.') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4" :title="$t('Win')">{{ $t('W') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4" :title="$t('Loss')">{{ $t('L') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4" :title="$t('Matches played')">{{ $t('MP') }}</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <tr v-for="team in division" :key="team.team_name">
              <td class="max-md:p-1">
                <span class="text-xs text-subtext">{{ team.rank }}</span>
              </td>
              <td class="text-left">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                  <a class="link-row" :class="['inline-flex items-center min-w-0 max-md:break-words', { 'text-subtext': withdrawn(team) }]" :href="`/Esports/NGS/Team/${encodeURIComponent($ngsTeam(team.team_name).name)}?season=${season}&division=${divisionName}`"><span class="font-semibold">{{ $ngsTeam(team.team_name).name }}</span></a>
                  <icon-with-hover v-if="withdrawn(team)" popupsize="fit">
                    <template #trigger><span class="rounded-md bg-red/25 text-lred px-1.5 text-[0.625rem] font-bold leading-4 cursor-default">{{ $t('WD') }}</span></template>
                    <p>{{ $t('Withdrawn') }}</p>
                  </icon-with-hover>
                </div>
              </td>
              <td class="font-bold">{{ team.points }}</td>
              <td>{{ team.dominations }}</td>
              <td>{{ team.games_won }}</td>
              <td>{{ team.games_lost }}</td>
              <td>{{ team.matches_played }}</td>
            </tr>
          </tbody>
        </table>
        </div>
      </panel-box>
    </div>
    <p class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-subtext">
      <span><span class="font-bold text-white mr-1.5">{{ $t('WD') }}</span>{{ $t('Withdrawn from the season') }}</span>
      <span><span class="font-bold text-white mr-1.5">{{ $t('Pts') }}</span>{{ $t('Points · Dom. dominations (2–0 wins) · W wins · L losses · MP matches played') }}</span>
    </p>
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
    withdrawn(team) {
      return !!this.$ngsTeam(team.team_name).withdrawnSeason;
    },
    // "11 teams · 10 matches each"
    divisionSummary(division) {
      const teams = Object.values(division);
      const matches = Math.max(0, ...teams.map(team => Number(team.matches_played) || 0));
      return this.$t(':teams teams · :matches matches each', { teams: teams.length, matches: matches });
    },
    fitTables() {
      (this.$refs.tables || []).forEach((table, index) => scaleTableToFit(table, this.$refs.tablecontainers[index]));
    },
  }
}
</script>