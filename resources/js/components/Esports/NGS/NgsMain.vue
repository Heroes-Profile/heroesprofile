<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'NGS'" :heading-image="'/images/NGS/600-600-ngs_large_header.png'" :heading-image-url="'/Esports/NGS'"></page-heading>

      
      <div class="flex flex-1 mx-auto justify-center mb-4 w-full bg-blue flex-wrap max-md:text-xs">
        <div class="md:border-r border-white max-md:border max-md:border-lighten">
          <custom-button   @click="setButtonActive('standings')" :text="'Standings'" :size="'big'" class="rounded-none max-md:text-xs" :color="activeButton === 'standings' ? 'lblue' : ''" :active="standingsClicked" :ignoreclick="true"></custom-button>
        </div>
        <div class="md:border-r border-white max-md:border max-md:border-lighten">
          <custom-button @click="setButtonActive('divisions')" :text="'Divisions'" :size="'big'" class=" rounded-none max-md:text-xs" :color="activeButton === 'divisions' ? 'lblue' : ''" :active="divisionsClicked" :ignoreclick="true"></custom-button>
        </div>
        <div class="md:border-r border-white max-md:border max-md:border-lighten">
          <custom-button @click="setButtonActive('teams')" :text="'Teams'" :size="'big'" class="rounded-none max-md:text-xs" :color="activeButton === 'teams' ? 'lblue' : ''" :active="teamsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="md:border-r border-white max-md:border max-md:border-lighten">
          <custom-button @click="setButtonActive('playerSearch')" :text="'Player Search'" :size="'big'" class="rounded-none max-md:text-xs" :color="activeButton === 'playerSearch' ? 'lblue' : ''" :active="playerSearchClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="md:border-r border-white max-md:border max-md:border-lighten">
          <custom-button @click="setButtonActive('recentMatches')" :text="'Recent Matches'" :size="'big'" class="rounded-none max-md:text-xs" :color="activeButton === 'recentMatches' ? 'lblue' : ''" :active="recentMatchesClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="md:border-r border-white max-md:border max-md:border-lighten">
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="rounded-none max-md:text-xs" :color="activeButton === 'overallHeroStats' ? 'lblue' : ''" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

      <div class=" border-white max-md:border max-md:border-lighten">
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="rounded-none max-md:text-xs" :color="activeButton === 'overallTalentStats' ? 'lblue' : ''" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>
      </div>


    <div class=" bg-lighten" v-if="!activeButton">
      <div class="flex mx-auto max-w-[1500px]  md:p-20 flex-wrap justify-center max-md:pt-4 gap-10">
        
        <div class="text-center md:w-[15em] mb-15  max-md:mb-10 ">
          <i class="fas fa-list-ol text-[50px] md:text-[100px]" ></i>
          
          <custom-button @click="setButtonActive('standings')" :text="'Standings'" :size="'big'" class="mt-10 max-md:text-xs" :active="standingsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15em] mb-15   max-md:mb-10">
          <i class="fas fa-boxes text-[50px] md:text-[100px] " ></i>
          
          <custom-button @click="setButtonActive('divisions')" :text="'Divisions'" :size="'big'" class="mt-10 max-md:text-xs" :active="divisionsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15em] mb-15  max-md:mb-10">
          <i class="fas fa-users text-[50px] md:text-[100px]" ></i>
          
          <custom-button @click="setButtonActive('teams')" :text="'Teams'" :size="'big'" class="mt-10 max-md:text-xs" :active="teamsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15em] mb-15 max-md:mb-10">
          <i class="fas fa-address-card text-[50px] md:text-[100px]" ></i>
          
          <custom-button @click="setButtonActive('playerSearch')" :text="'Player Search'" :size="'big'" class="mt-10 max-md:text-xs" :active="playerSearchClicked" :ignoreclick="true"></custom-button>
        </div>




        <div class="text-center md:w-[15em] mb-15  max-md:mb-10">
          <i class="fas fa-list text-[50px] md:text-[100px]" ></i>
          
          <custom-button @click="setButtonActive('recentMatches')" :text="'Recent Matches'" :size="'big'" class="mt-10 max-md:text-xs" :active="recentMatchesClicked" :ignoreclick="true"></custom-button>
        </div>
        
          <div class="text-center md:w-[15em] mb-15  max-md:mb-10">
          <i class="fa-solid fa-chart-bar text-[50px] md:text-[100px]" ></i>
          
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="mt-10 max-md:text-xs" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15em] mb-15  max-md:mb-10">
          <i class="fas fa-chart-line text-[50px] md:text-[100px]" ></i>
         
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="mt-10 max-md:text-xs" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>

      </div>



    </div>
    <div v-else>


      <div v-if="activeButton === 'standings'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="isLoading" @click="filter()" :text="'Filter'" :size="'medium'" color="teal" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>
        <ngs-standings v-if="standingData" :data="standingData" :season="season"></ngs-standings>
      </div>

      <div v-if="activeButton === 'divisions'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" color="teal" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>
        <ngs-divisions v-if="divisionData" :data="divisionData" :season="season"></ngs-divisions>
      </div>

      <div v-if="activeButton === 'teams'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>
        <esports-teams v-if="teamsData" :data="teamsData" :esport="'NGS'" :season="season"></esports-teams>
      </div>

      <div v-if="activeButton === 'playerSearch'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search for NGS player'" :aria-label="'Search for NGS player'" v-model="userinput" @keyup.enter="filter()">
          <custom-button @click="filter()" :text="'Search'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2  hover:bg-lteal" :ignoreclick="true"></custom-button>

        </div>
        <div v-if="battletagresponse">
          <div v-if="battletagresponse.length > 1">
            <div 
              class="bg-blue p-4 rounded mb-4 w-[500px] flex flex-col items-center cursor-pointer" 
              v-for="(item, index) in battletagresponse" 
              :key="index" 
              @click="redirectToProfile(item.battletag, item.blizz_id, item.region)"
            >
              <div>{{ item.battletagShort }} ({{ item.regionName }})</div>
              <div>{{ item.latest_game }}</div>
              <div>Games Played: {{ item.totalGamesPlayed }}</div>
              <div>{{ item.latestMap.name }}</div>
              <div><hero-image-wrapper :hero="item.latestHero"></hero-image-wrapper></div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="activeButton === 'recentMatches'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>

        <div v-if="recentMatchesData">
          <ul class="pagination flex max-w-[1500px] mx-auto justify-between mb-2">
            <li v-if="recentMatchesData.pagination.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !recentMatchesData.pagination.prev_page_url }">
              <a class="page-link" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page - 1)" href="#">
                Previous
              </a>
            </li>
            <li v-if="recentMatchesData.pagination.current_page != recentMatchesData.pagination.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !recentMatchesData.pagination.next_page_url }">
              <a class="page-link" @click.prevent="getRecentMatches(recentMatchesData.pagination.current_page + 1)" href="#">
                Next
              </a>
            </li>
          </ul>
          <esports-recent-matches v-if="recentMatchesData" :data="recentMatchesData.data" :esport="'NGS'"></esports-recent-matches>
        </div>

      </div>


      <div v-if="activeButton === 'overallHeroStats'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
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
              <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
              <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
              <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
            </div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <div v-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NgsMain',
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
      cancelTokenSource: null,

      isLoading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Nexus Gaming Series",
      activeButton: null,

      standingData: null,
      divisionData: null,
      teamsData: null,
      recentMatchesData: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,

      season: null,
      division: null,

      userinput: null,
      battletagresponse: null,
    };
  },
  created(){
    this.preloadedImage.src = '/images/NGS/no-image-clipped.png';
    this.season = this.defaultseason;
  },
  mounted() {
  },
  computed: {
    isBattletagReponseValid(){
      return this.battletagresponse[0] && this.battletagresponse[0].battletag && this.battletagresponse[0].blizz_id !== undefined && this.battletagresponse[0].region !== undefined;
    },
    standingsClicked() {
      return this.activeButton === 'standings';
    },
    divisionsClicked() {
      return this.activeButton === 'divisions';
    },
    teamsClicked() {
      return this.activeButton === 'teams';
    },
    playerSearchClicked() {
      return this.activeButton === 'playerSearch';
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
    async getStandingsData(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/standings", {
          season: this.season,
          division: this.division,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.standingData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getDivisionsData(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/divisions", {
          season: this.season,
          division: this.division,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.divisionData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getTeamsData(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/teams", {
          season: this.season,
          division: this.division,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.teamsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async searchedPlayer(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      ;
      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/player/search", {
          userinput: this.userinput
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.battletagresponse = response.data;
        if(this.isBattletagReponseValid) {
          if(this.battletagresponse.length == 1){
            this.redirectToProfile(this.battletagresponse[0].battletag, this.battletagresponse[0].blizz_id, this.battletagresponse[0].region);
          }
        } else {
          //Do something here
        }
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
        const response = await this.$axios.post("/api/v1/esports/ngs/matches", {
          season: this.season,
          division: this.division,
          pagination_page: page,
          esport: "NGS",
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
        const response = await this.$axios.post("/api/v1/esports/ngs/hero/stats", {
          season: this.season,
          division: this.division,
          esport: "NGS",
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
        const response = await this.$axios.post("/api/v1/esports/ngs/hero/talents/stats", {
          season: this.season,
          division: this.division,
          hero: this.selectedHero.name,
          esport: "NGS",
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
      this.division = null;

      if(this.activeButton === 'standings'){
        this.standingData = null;
        this.getStandingsData();
      }else if(this.activeButton === 'divisions'){
        this.divisionData = null;
        this.getDivisionsData();
      }else if(this.activeButton === 'teams'){
        this.teamsData = null;
        this.getTeamsData();
      }else if(this.activeButton === 'searchPlayer'){
        this.searchedPlayer();
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
      if(eventPayload.field == "Divisions"){
        this.division = eventPayload.value;
      }else if(eventPayload.field == "Seasons"){
        this.season = eventPayload.value;
      }else if(eventPayload.field == "Heroes"){
        this.selectedHero = this.heroes.find(value => value.id === eventPayload.value);
      }
    },

    filter(){
      if(this.activeButton === 'standings'){
        this.standingData = null;
        this.getStandingsData();
      }else if(this.activeButton === 'divisions'){
        this.divisionData = null;
        this.getDivisionsData();
      }else if(this.activeButton === 'teams'){
        this.teamsData = null;
        this.getTeamsData();
      }else if(this.activeButton === 'playerSearch'){
        this.searchedPlayer();
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
    redirectToProfile(battletag, blizz_id, region) {
      window.location.href = '/Esports/NGS/Player/' + battletag.split('#')[0] + "/" + blizz_id;
    }
  }
}
</script>
