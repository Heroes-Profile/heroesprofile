GlobalHeroStats<template>
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
    inputHero: String,
    heroes: Array,
  },
  data(){
    return {
    	infoText: "Talents",
      selectedHero: null,
    }
  },
  created(){
    if(this.inputHero){
      this.selectedHero = this.inputHero;
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
      this.getData();
    },
  	async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents/", {
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