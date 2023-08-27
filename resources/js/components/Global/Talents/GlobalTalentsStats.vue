<template>
  <div>
    Lets rethink how filtering is done.
    <h1>Global Talent Statistics</h1>
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
  name: 'GlobalTalentsStats',
  components: {
  },
  props: {
    inputhero: Object,
    heroes: Array,
  },
  data(){
    return {
    	infoText: "Talents",
      selectedHero: null,
    }
  },
  created(){    
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getTalentData();
      this.getTalentBuildData();
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
      this.getTalentData();
      this.getTalentBuildData();

      history.pushState(null, null, this.selectedHero.name);
    },
  	async getTalentData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents", {
          hero: this.selectedHero.name,
        });

        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
    async getTalentBuildData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents/build", {
          hero: this.selectedHero.name,
        });

        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>