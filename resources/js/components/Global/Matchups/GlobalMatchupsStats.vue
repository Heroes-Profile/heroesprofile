<template>
  <div>
    Lets rethink how filtering is done.
    <h1>Hero Matchups Statistics</h1>
    <infobox :input="infoText"></infobox>


    <div v-if="!selectedHero">
      <div v-for="hero in heroes" :key="hero.id">
        <hero-box :hero="hero" @click="clickedHero(hero)"></hero-box>
      </div>
    </div>

    <div v-else>

    </div>
    {{ "<filters></filters>" }}


  </div>
</template>

<script>
export default {
  name: 'GlobalMatchupsStats',
  components: {
  },
  props: {
    inputhero: Object,
    heroes: Array,
  },
  data(){
    return {
      infoText: "Hero Matchups provide information on which heroes are good with and against for a particular hero",
      selectedHero: null,
    }
  },
  created(){
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getData();
    }
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    clickedHero(hero) {
      this.selectedHero = hero;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      this.getData();
    },
    async getData(){
      console.log("Hero = " + this.selectedHero.name);
      console.log("timeframe = " + "2.55.3.90670");
      console.log("game_type = " + 5);
      try{
        const response = await this.$axios.post("/api/v1/global/matchups", {
          userinput: this.selectedHero.name,
          timeframe: "2.55.3.90670",
          game_type: 5,
        });
        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>