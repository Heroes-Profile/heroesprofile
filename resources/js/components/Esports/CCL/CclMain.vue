<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'CCL'" :heading-image="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'" :heading-image-url="'/Esports/CCL'"></page-heading>

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <div class="flex flex-1 mx-auto justify-center mb-4 w-full bg-blue">
        <div class="border-r border-white">
          <custom-button @click="setButtonActive('organizations')" :text="'Organizations'" :size="'big'" class="rounded-none " :color="activeButton === 'organizations' ? 'lblue' : ''" :active="organizationsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="border-r border-white">
          <custom-button @click="setButtonActive('recentMatches')" :text="'Recent Matches'" :size="'big'" class="rounded-none" :color="activeButton === 'recentMatches' ? 'lblue' : ''" :active="recentMatchesClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="border-r border-white">
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="rounded-none" :active="overallHeroStatsClicked" :color="activeButton === 'overallHeroStats' ? 'lblue' : ''" :ignoreclick="true"></custom-button>
        </div>

      <div class="">
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="rounded-none" :active="overallTalentStatsClicked" :color="activeButton === 'overallTalentStats' ? 'lblue' : ''" :ignoreclick="true"></custom-button>
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
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>
        <esports-organizations v-if="organizationsData" :data="organizationsData" :esport="'CCL'" :season="season"></esports-organizations>
      </div>

      <div v-if="activeButton === 'recentMatches'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <single-select-filter :values="filters.heroes" :text="'Heroes'" @input-changed="handleInputChange"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>




        <div v-if="recentMatchesData">
          <ul class="pagination flex max-w-[1500px] mx-auto px-2 justify-between mb-2 text-sm">
            <li v-if="recentMatchesData.pagination.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !recentMatchesData.pagination.prev_page_url }">
              <a class="page-link mr-auto" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page - 1)" href="#">
                Previous
              </a>
            </li>
            <li v-if="recentMatchesData.pagination.current_page != recentMatchesData.pagination.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !recentMatchesData.pagination.next_page_url }">
              <a class="page-link ml-auto" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page + 1)" href="#">
                Next
              </a>
            </li>
          </ul>
          <esports-recent-matches :data="recentMatchesData.data" :esport="'CCL'"></esports-recent-matches>
          <ul class="pagination flex max-w-[1500px] mx-auto px-2 justify-between mb-2 text-sm">
            <li v-if="recentMatchesData.pagination.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !recentMatchesData.pagination.prev_page_url }">
              <a class="page-link mr-auto" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page - 1)" href="#">
                Previous
              </a>
            </li>
            <li v-if="recentMatchesData.pagination.current_page != recentMatchesData.pagination.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !recentMatchesData.pagination.next_page_url }">
              <a class="page-link ml-auto" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page + 1)" href="#">
                Next
              </a>
            </li>
          </ul>
        </div>
      </div>
      
      <div v-if="activeButton === 'overallHeroStats'">
           <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
               <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
              <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange" :defaultValue="selectedHero.id"></single-select-filter>
              <single-select-filter :values="filters.ccl_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
              <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
            </div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <div v-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'"></loading-component>
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

      isLoading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Community Clash League",
      activeButton: null,
      organizationsData: null,
      recentMatchesData: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,
      cancelTokenSource: null,
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
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/ccl/organizations", {
          season: this.season,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.organizationsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getRecentMatches(page){
      if (this.isLoading || page < 1 || (this.recentMatchesData && page > this.recentMatchesData.last_page)) {
        return;
      }

      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/ccl/matches", {
          season: this.season,
          pagination_page: page,
          hero: this.selectedHero ? this.selectedHero.name : null,
          esport: "CCL",
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.recentMatchesData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getHeroStats(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$axios.post("/api/v1/esports/ccl/hero/stats", {
          season: this.season,
          esport: "CCL",
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
        const response = await this.$axios.post("/api/v1/esports/ccl/hero/talents/stats", {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "CCL",
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
