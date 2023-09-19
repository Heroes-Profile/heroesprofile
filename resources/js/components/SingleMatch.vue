<template>
  <div>
    <div v-if="data">
      <div>
        <span>Match Scores</span>
        <span>Talents</span>
        <span>Experience</span>
        <span>MMR</span>
        <span>Advanced Stats</span>
      </div>

      <div>
        <h1>{{ data.game_date }}</h1>
      </div>

      <div class="box thing woot woot">
        <span>{{ data.game_map }}</span>
        <span>{{ data.game_type }}</span>
        <span>{{ data.game_length }}</span>
      </div>


        <div class="bg-lighten p-10 text-center">
          <div class="flex flex-wrap justify-center">
            <group-box :text="getTeamText(1, data.players[0])" :data="data.players[0]"></group-box>
            <group-box :text="getTeamText(2, data.players[1])" :data="data.players[1]"></group-box>
          </div>
        </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'SingleMatch',
  components: {
  },
  props: {
    replayid: Number
  },
  data(){
    return {
      data: null,
    }
  },
  created(){
    this.getData();
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/match/single", {
          replayID: this.replayid
        });
        this.data = response.data; 
      }catch(error){
        console.log(error);
      }
    },
    getTeamText(team, data){
      let winner = data[0].winner ? "Winner" : "Loser";
      return "Team " + team + " - " + winner;
    }
  }
}
</script>