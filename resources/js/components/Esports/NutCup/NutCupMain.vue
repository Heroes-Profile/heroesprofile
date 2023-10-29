<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'CCL'" :heading-image="'/images/NutCup/IMG_0265.PNG'" :heading-image-url="''"></page-heading>

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <div class="flex flex-1">

        <div class="mx-5">
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="mt-10" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

      <div class="mx-5">
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="mt-10" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>
      </div>


    <div v-if="!activeButton">
      <div class="flex   p-20 bg-lighten flex-wrap justify-center">
        
        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fa-solid fa-chart-bar" style="font-size: 100px"></i>
          <h3>Overall Hero Stats</h3>
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="mt-10" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-chart-line" style="font-size: 100px"></i>
          <h3>Overall Talent Stats</h3>
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="mt-10" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>
      </div>

    </div>
    <div v-else>

      <div v-if="activeButton === 'overallHeroStats'">
        <div class="flex flex-wrap gap-2">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="season"></single-select-filter>
          <custom-button :disabled="loading"  @click="filter()" :text="'Filter'" :size="'big'" class="mt-10" :ignoreclick="true"></custom-button>
        </div>
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
            <div class="flex flex-wrap gap-2">
              <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange" :defaultValue="selectedHero.id"></single-select-filter>
              <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="season"></single-select-filter>
              <custom-button :disabled="loading"  @click="filter()" :text="'Filter'" :size="'big'" class="mt-10" :ignoreclick="true"></custom-button>
            </div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <div v-if="loading">
      <loading-component :overrideimage="'/images/NutCup/logo-circular.png'"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NutCupMain',
  components: {
  },
  props: {
    talentimages: Object,
    filters: Object,
    heroes: Object,
  },
  data() {
    return {
      preloadedImage: new Image(),
      loading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Nut Cup",
      activeButton: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,
      season: null,
    };
  },
  created(){
    this.preloadedImage.src = '/images/NutCup/IMG_0265.PNG';
    this.preloadedImage.src = '/images/NutCup/logo-circular.png';
    this.season = 2;
  },
  mounted() {
  },
  computed: {
    overallHeroStatsClicked() {
      return this.activeButton === 'overallHeroStats';
    },
    overallTalentStatsClicked() {
      return this.activeButton === 'overallTalentStats';
    },
    
  },
  watch: {
  },
  methods: {
    async getHeroStats(){
      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/nutcup/hero/stats", {
          season: this.season,
          esport: "NutCup",
        });
        this.heroStatsData = response.data;
      }catch(error){
        //Do something here
      }
      this.loading = false;
    },
    async getTalentStats(){
      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/nutcup/hero/talents/stats", {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "NutCup",
        });
        this.talentStatsData = response.data;
      }catch(error){
        //Do something here
      }
      this.loading = false;
    },

    setButtonActive(buttonName) {
      this.activeButton = buttonName;
      this.season = 2;
      
      if(this.activeButton === 'overallHeroStats'){
        this.heroStatsData = null;
        this.getHeroStats();
      }else if(this.activeButton === 'overallTalentStats' && this.selectedHero){
        this.talentStatsData = null;
        this.getTalentStats();
      }
    },

    handleInputChange(eventPayload) {
      if(eventPayload.field == "Seasons"){
        this.season = eventPayload.value;
      }else if(eventPayload.field == "Heroes"){
        this.selectedHero = this.heroes.find(value => value.id === eventPayload.value);
      }
    },

    filter(){
      if(this.activeButton === 'overallHeroStats'){
        this.heroStatsData = null;
        this.getHeroStats();
      }else if(this.activeButton === 'overallTalentStats' && this.selectedHero){
        this.talentStatsData = null;
        this.getTalentStats();
      }
    },
    clickedHero(hero){
      this.selectedHero = hero;
      this.getTalentStats();
    },
  }
}
</script>
