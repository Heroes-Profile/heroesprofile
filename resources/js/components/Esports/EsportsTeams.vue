<template>
  <div>

    <div class="mb-4">
      <input type="text" v-model="filterText" class="border p-2 rounded-md" placeholder="Filter by team name">
    </div>

    <div class="flex flex-wrap gap-5">
      <a :href="`/Esports/${esport}/Division/${team.division}/Team/${team.team_name}?season=${season}`"  v-for="(team, index) in filteredData" :key="index" >
        <round-image :size="'big'" :title="team.team_name" :image="team.icon_url" :hovertextstyleoverride="true">
          <image-hover-box :title="team.team_name" :paragraph-one="'Division: ' + team.division" :paragraph-two="'Win Rate: ' + team.win_rate" :paragraph-three="'Games Played: ' + team.games_played"></image-hover-box>
        </round-image>
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
    filteredData() {
      const lowercaseFilter = this.filterText.toLowerCase();
      return this.data.filter((team) =>
        team.team_name.toLowerCase().includes(lowercaseFilter)
      );
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>