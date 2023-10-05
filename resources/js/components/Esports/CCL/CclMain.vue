<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'CCL'" :heading-image="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'" :heading-image-url="'https://heroeshearth.com/b/workhorse/read/everything-you-need-to-know-about-ccl/'"></page-heading>

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <div class="flex flex-1">
        <div class="mx-5">
          <custom-button @click="setButtonActive('organizations')" :text="'Organizations'" :size="'big'" class="mt-10" :active="organizationsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="mx-5">
          <custom-button @click="setButtonActive('recentMatches')" :text="'Recent Matches'" :size="'big'" class="mt-10" :active="recentMatchesClicked" :ignoreclick="true"></custom-button>
        </div>

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
          <i class="fas fa-users" style="font-size: 100px"></i>
          <h3>Organizations</h3>
          <custom-button @click="setButtonActive('organizations')" :text="'Organizations'" :size="'big'" class="mt-10" :active="organizationsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-list" style="font-size: 100px"></i>
          <h3>Recent Matches</h3>
          <custom-button @click="setButtonActive('recentMatches')" :text="'Recent Matches'" :size="'big'" class="mt-10" :active="recentMatchesClicked" :ignoreclick="true"></custom-button>
        </div>

        
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

      <div v-if="activeButton === 'organizations'">
        <div class="flex flex-wrap gap-2">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="loading"  @click="filter()" :text="'Filter'" :size="'big'" class="mt-10" :ignoreclick="true"></custom-button>
        </div>
        <ccl-organizations v-if="organizationsData" :data="organizationsData" :esport="'CCL'" :season="season"></ccl-organizations>
      </div>

      <div v-if="activeButton === 'recentMatches'">
        <div class="flex flex-wrap gap-2">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="loading"  @click="filter()" :text="'Filter'" :size="'big'" class="mt-10" :ignoreclick="true"></custom-button>
        </div>




        <div v-if="recentMatchesData">
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: !recentMatchesData.pagination.prev_page_url }">
              <a class="page-link" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page - 1)" href="#">
                Previous
              </a>
            </li>
            <li class="page-item" :class="{ disabled: !recentMatchesData.pagination.next_page_url }">
              <a class="page-link" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page + 1)" href="#">
                Next
              </a>
            </li>
          </ul>
        </div>


        <esports-recent-matches v-if="recentMatchesData" :data="recentMatchesData.data"></esports-recent-matches>
      </div>


      <div v-if="activeButton === 'overallHeroStats'">
        <div class="flex flex-wrap gap-2">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
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
              <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
              <custom-button :disabled="loading"  @click="filter()" :text="'Filter'" :size="'big'" class="mt-10" :ignoreclick="true"></custom-button>
            </div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <div v-if="loading">
      <loading-component :overrideimage="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CclMain',
  components: {
  },
  props: {
    defaultseason: Number,
    filters: Object,
    heroes: Object,
    talentimages: Object,
  },
  data() {
    return {
      preloadedImage: new Image(),

      loading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Community Clash League",
      activeButton: null,
      organizationsData: null,
      recentMatchesData: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,

      season: null,

      userinput: null,
      battletagresponse: null,
    };
  },
  created(){
    this.preloadedImage.src = '/images/CCL/600-600-HHE_CCL_Logo_rectangle.png';
    this.season = this.defaultseason;
  },
  mounted() {
  },
  computed: {
    isBattletagReponseValid(){
      return this.battletagresponse[0] && this.battletagresponse[0].battletag && this.battletagresponse[0].blizz_id !== undefined && this.battletagresponse[0].region !== undefined;
    },
    organizationsClicked() {
      return this.activeButton === 'organizations';
    },
    recentMatchesClicked() {
      return this.activeButton === 'recentMatches';
    },
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
    async getOrganizationsData(){
      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/ccl/organizations", {
          season: this.season,
        });
        this.organizationsData = response.data;
      }catch(error){
        //Do something here
      }
      this.loading = false;
    },
    async getRecentMatches(page){
      if (this.loading || page < 1 || (this.recentMatchesData && page > this.recentMatchesData.last_page)) {
        return;
      }

      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/ccl/matches", {
          season: this.season,
          pagination_page: page,
          esport: "CCL",
        });
        this.recentMatchesData = response.data;
      }catch(error){
        //Do something here
      }
      this.loading = false;
    },
    async getHeroStats(){
      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/ccl/hero/stats", {
          season: this.season,
          esport: "CCL",
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
        const response = await this.$axios.post("/api/v1/esports/ccl/hero/talents/stats", {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "CCL",
        });
        this.talentStatsData = response.data;
      }catch(error){
        //Do something here
      }
      this.loading = false;
    },

    setButtonActive(buttonName) {
      this.activeButton = buttonName;
      this.season = this.defaultseason;

      if(this.activeButton === 'organizations'){
        this.organizationsData = null;
        this.getOrganizationsData();
      }else if(this.activeButton === 'recentMatches'){
        this.recentMatchesData = null;
        this.getRecentMatches(1);
      }else if(this.activeButton === 'overallHeroStats'){
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
      if(this.activeButton === 'organizations'){
        this.organizationsData = null;
        this.getOrganizationsData();
      }else if(this.activeButton === 'recentMatches'){
        this.recentMatchesData = null;
        this.getRecentMatches(1);
      }else if(this.activeButton === 'overallHeroStats'){
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
