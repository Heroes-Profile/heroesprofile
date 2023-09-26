<template>
  <div>

    <div class="mb-4">
      <input type="text" v-model="filterText" class="border p-2 rounded-md" placeholder="Filter by team name">
    </div>

    <div class="flex flex-wrap gap-5">
      <round-image :size="'big'" v-for="(team, index) in filteredData" :key="index" :title="team.team_name" :image="team.icon_url" :hovertextstyleoverride="true">
            <div class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
              <h2>{{ team.team_name }}</h2>
              <p>Division: {{ team.division }}</p>
              <p>Win Rate: {{ team.win_rate }}</p>
              <p>Games Played: {{ team.games_played }}</p>
            </div>
      </round-image>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Teams',
  components: {
  },
  props: {
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