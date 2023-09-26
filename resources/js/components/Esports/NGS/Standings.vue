<template>
  <div>
    <div v-for="(division, divisionName) in data" :key="divisionName">
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th colspan="7"><a :href="'/Esports/NGS/Division/' + divisionName">Division {{ divisionName }}</a></th>
          </tr>
          <tr>
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            </th>
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Team
            </th>            
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Points
            </th>
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Dominations
            </th>
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Games Won
            </th>   
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Games Lost
            </th>   
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Matches Played
            </th>     
          </tr>
        </thead>
        <tbody>
          <tr v-for="(team, teamData) in division" :key="teamData.team_name">
            <td>{{ team.rank }}</td>
            <td><a :href="'/Esports/NGS/Team/' + team.team_name">{{ team.team_name }}</a></td>
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
  name: 'Standings',
  components: {
  },
  props: {
    divisions: Array,
    seasons: Array,
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