<template>
  <div>
    <div v-for="(division, divisionName) in data" :key="divisionName" class="max-w-[1500px] mx-auto flex justify-start mb-2 mt-4">
      <table class="max-md:text-xs">
        <thead>
          <tr class="bg-blue">
            <th colspan="7"><a :href="'/Esports/NGS/Division/' + divisionName">Division {{ divisionName }}</a></th>
          </tr>
        </thead>
        <thead >
          <tr>
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
            </th>
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
              Team
            </th>            
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
              Points
            </th>
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
              <span class="max-md:hidden">Dominations</span><span class="md:hidden text-[9px]">Denom- inations</span>
            </th>
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
              Games Won
            </th>   
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
              Games Lost
            </th>   
            <th class="py-2 px-3  text-left max-md:text-xs md:text-sm leading-4 text-gray-500 tracking-wider">
              Matches Played
            </th>     
          </tr>
        </thead>
        <tbody>
          <tr v-for="(team, teamData) in division" :key="teamData.team_name">
            <td class="max-md:max-w-[15px] max-md:p-1">{{ team.rank }}</td>
            <td class="max-md:max-w-[100px]"><a class="link max-md:break-words" :href="`/Esports/NGS/Team/${team.team_name}?season=${season}&division=${divisionName}`">{{ team.team_name }}</a></td>
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