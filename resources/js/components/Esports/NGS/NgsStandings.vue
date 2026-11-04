<template>
  <div>
    <div v-for="(division, divisionName) in data" :key="divisionName" class="max-w-[1500px] mx-auto flex justify-start mb-2 mt-4">
      <table class="min-w-0 w-full responsive-table max-md:text-xs">
        <thead>
          <tr class="bg-surface-3">
            <th colspan="7"><a :href="'/Esports/NGS/Division/' + divisionName">Division {{ divisionName }}</a></th>
          </tr>
        </thead>
        <thead >
          <tr>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
            </th>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              Team
            </th>            
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              Points
            </th>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              <span class="max-md:hidden">Dominations</span><span class="md:hidden text-[9px]">Denom- inations</span>
            </th>
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              Games Won
            </th>   
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              Games Lost
            </th>   
            <th class="py-2 px-3 text-center max-md:text-xs md:text-sm leading-4">
              Matches Played
            </th>     
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-for="(team, teamData) in division" :key="teamData.team_name">
            <td class="max-md:max-w-[15px] max-md:p-1">{{ team.rank }}</td>
            <td class="max-md:max-w-[100px]"><a class="group/bt inline-flex items-center max-md:break-words" :href="`/Esports/NGS/Team/${team.team_name}?season=${season}&division=${divisionName}`"><span class="group-hover/bt:underline">{{ team.team_name }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></td>
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
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>