<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Nut Cup'" :heading-image="'/images/NutCup/IMG_0265.PNG'" :heading-image-url="''"></page-heading>

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <div class="flex flex-wrap justify-center gap-1 bg-teal rounded p-1 w-fit max-w-[calc(100%-2rem)] mx-auto mb-6 max-md:text-xs">

        <button type="button" :disabled="isLoading && activeButton !== 'overallHeroStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallHeroStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallHeroStats')">Overall Hero Stats</button>

      <button type="button" :disabled="isLoading && activeButton !== 'overallTalentStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallTalentStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallTalentStats')">Overall Talent Stats</button>
      </div>


    <div v-if="!activeButton">
      <div class="flex p-10 md:p-20 bg-surface rounded max-w-[1500px] mx-4 md:mx-auto flex-wrap justify-center">
        
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
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="season"></single-select-filter>
          <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div></panel-box></div>
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
            <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
              <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange" :defaultValue="selectedHero.id"></single-select-filter>
              <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="season"></single-select-filter>
              <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
            </div></panel-box></div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <div v-if="isLoading">
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <loading-component :compact="true" :overrideimage="'/images/NutCup/logo-circular.png'"></loading-component>
        <!-- Overall Hero Stats: hero with name, then win rate, popularity, ban rate and games -->
        <div v-if="activeButton === 'overallHeroStats'" class="max-w-[1000px] w-full mx-auto">
          <table-skeleton :bare="true" :rows="12" :columns="['avatar', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </div>
        <!-- Overall Talent Stats: a block per talent level, then the builds table -->
        <div v-else-if="activeButton === 'overallTalentStats'" class="flex flex-col items-center">
          <div v-for="level in 3" :key="level" class="w-full bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-20"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="3" :columns="['hero', 'stat', 'stat', 'stat']"></table-skeleton>
          </div>
          <div class="w-full max-w-[800px] bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-16"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
          </div>
        </div>
        <table-skeleton v-else></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
const NUTCUP_TABS = ['overallHeroStats', 'overallTalentStats'];

export default {
  name: 'NutCupMain',
  components: {
  },
  props: {
    talentimages: Object,
    filters: Object,
    heroes: Object,
    urlparameters: [Object, Array],
  },
  data() {
    return {
      preloadedImage: new Image(),
      isLoading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Nut Cup",
      activeButton: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,
      season: null,
      cancelTokenSource: null,
    };
  },
  created(){
    this.preloadedImage.src = '/images/NutCup/IMG_0265.PNG';
    this.preloadedImage.src = '/images/NutCup/logo-circular.png';
    this.season = 2;
  },
  mounted() {
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (NUTCUP_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
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
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/nutcup/hero/stats", {
          season: this.season,
          esport: "NutCup",
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.heroStatsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getTalentStats(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/nutcup/hero/talents/stats", {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "NutCup",
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.talentStatsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    setButtonActive(buttonName) {
      // One tab loads at a time; the others wait until it's done
      if (this.isLoading) {
        return;
      }
      this.activeButton = buttonName;
      // The open tab goes in the URL, so it can be linked to or reloaded
      const url = new URL(window.location.href);
      url.searchParams.set('tab', buttonName);
      history.replaceState(history.state, '', url);
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
