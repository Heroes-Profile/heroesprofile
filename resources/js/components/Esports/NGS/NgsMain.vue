<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="''" :heading-image="'/images/NGS/ngs_large.png'" :heading-image-url="'/Esports/NGS'"></page-heading>

      
      <div class="flex flex-wrap justify-center gap-1 bg-teal rounded p-1 w-fit max-w-[calc(100%-2rem)] mx-auto mb-6 max-md:text-xs">
        <button type="button" :disabled="isLoading && activeButton !== 'standings'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'standings' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('standings')">Standings</button>
        <button type="button" :disabled="isLoading && activeButton !== 'divisions'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'divisions' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('divisions')">Divisions</button>
        <button type="button" :disabled="isLoading && activeButton !== 'teams'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'teams' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('teams')">Teams</button>

        <button type="button" :disabled="isLoading && activeButton !== 'playerSearch'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'playerSearch' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('playerSearch')">Player Search</button>

        <button type="button" :disabled="isLoading && activeButton !== 'recentMatches'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'recentMatches' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('recentMatches')">Recent Matches</button>

        <button type="button" :disabled="isLoading && activeButton !== 'overallHeroStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallHeroStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallHeroStats')">Overall Hero Stats</button>

      <button type="button" :disabled="isLoading && activeButton !== 'overallTalentStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallTalentStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallTalentStats')">Overall Talent Stats</button>
      </div>


    <div class="bg-surface rounded max-w-[1500px] mx-4 md:mx-auto" v-if="!activeButton">
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
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div></panel-box></div>
        <ngs-standings v-if="standingData" :data="standingData" :season="season"></ngs-standings>
      </div>

      <div v-if="activeButton === 'divisions'">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div></panel-box></div>
        <ngs-divisions v-if="divisionData" :data="divisionData" :season="season"></ngs-divisions>
      </div>

      <div v-if="activeButton === 'teams'">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div></panel-box></div>
        <esports-teams v-if="teamsData" :data="teamsData" :esport="'NGS'" :season="season"></esports-teams>
      </div>

      <div v-if="activeButton === 'playerSearch'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search for NGS player'" :aria-label="'Search for NGS player'" v-model="userinput" @keyup.enter="filter()">
          <custom-button @click="filter()" :text="'Search'" :size="'medium'" class="bg-teal ml-10 hover:bg-lteal" :ignoreclick="true"></custom-button>

        </div>
        <!-- The main site's search: the table scans while it runs, then lists the matches; a single match opens straight away -->
        <table-search v-if="searchRun && !searchFoundNone" :key="searchRun" :query="searchedFor" :results="battletagresponse" :profile-url="ngsPlayerUrl" :match-url="item => '/Esports/NGS/Match/Single/' + item.latestReplayID" loader-image="/images/NGS/no-image-clipped.png" :show-region="false" @select="item => openNgsPlayer(item)" @none="searchFoundNone = true"></table-search>
        <p v-else-if="searchFoundNone" class="text-center text-subtext mt-10">No NGS player found for {{ searchedFor }}.</p>
      </div>

      <div v-if="activeButton === 'recentMatches'">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
          <single-select-filter :values="filters.heroes" :text="'Heroes'" @input-changed="handleInputChange"></single-select-filter>
          <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div></panel-box></div>

        <div v-if="recentMatchesData">
          <div v-if="recentMatchesData.pagination.last_page > 1" class="max-w-[1920px] mx-auto px-4 md:px-8 flex justify-end">
            <pagination-bar :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches"></pagination-bar>
          </div>
         
          <esports-recent-matches v-if="recentMatchesData" :data="recentMatchesData.data" :esport="'NGS'"></esports-recent-matches>
          <div v-if="recentMatchesData.pagination.last_page > 1" class="max-w-[1920px] mx-auto px-4 md:px-8 flex justify-end">
            <pagination-bar :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches"></pagination-bar>
          </div>
        </div>

      </div>


      <div v-if="activeButton === 'overallHeroStats'">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box :filters="true"><div class="filter-row justify-center">
          <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
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
              <single-select-filter :values="filters.ngs_divisions" :text="'Divisions'" @input-changed="handleInputChange"></single-select-filter>
              <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
              <button :disabled="isLoading" @click="filter()" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
            </div></panel-box></div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <!-- Player Search shows its own scanning table instead -->
    <div v-if="isLoading && activeButton !== 'playerSearch'">
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <loading-component :compact="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
        <!-- Standings: a block per division, its title bar over a rank / team / five-number table -->
        <template v-if="activeButton === 'standings'">
          <div v-for="division in 3" :key="division" class="bg-surface rounded overflow-hidden">
            <div class="bg-surface-3 px-3 py-2.5"><skeleton-box class="h-4 w-32 !bg-surface-1"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="8" :columns="['rank', 'name', 'stat', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
          </div>
        </template>
        <!-- Divisions: one row per division with its games played -->
        <table-skeleton v-else-if="activeButton === 'divisions'" :bare="true" :rows="7" :columns="['stat', 'stat']"></table-skeleton>
        <!-- Recent Matches: expanded rows with the map, the two teams and each team's heroes -->
        <panel-box v-else-if="activeButton === 'recentMatches'">
          <div v-for="row in 8" :key="row" class="flex flex-wrap 2xl:flex-nowrap items-center gap-6 rounded bg-surface-1 px-6 py-4">
            <div class="flex flex-col gap-2 w-[300px] shrink-0">
              <skeleton-box class="h-4 w-40"></skeleton-box>
              <skeleton-box class="h-4 w-32"></skeleton-box>
              <skeleton-box class="h-3 w-36"></skeleton-box>
              <skeleton-box class="h-3 w-28"></skeleton-box>
            </div>
            <div class="flex-1 grid grid-cols-[1fr_auto_1fr] items-center gap-6">
              <div class="flex flex-row-reverse items-center gap-3">
                <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
                <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
              </div>
              <div class="flex flex-col items-center gap-2">
                <skeleton-box class="h-8 w-20"></skeleton-box>
                <skeleton-box class="h-3 w-28"></skeleton-box>
              </div>
              <div class="flex items-center gap-3">
                <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
                <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
              </div>
            </div>
            <div class="flex gap-2 w-[340px] max-w-full shrink-0">
              <div v-for="team in 2" :key="team" class="flex flex-col gap-1.5 w-[150px]">
                <div v-for="player in 5" :key="player" class="flex items-center gap-1">
                  <skeleton-box class="w-4 h-4 shrink-0 !rounded-full"></skeleton-box>
                  <skeleton-box class="h-3 w-24"></skeleton-box>
                </div>
              </div>
            </div>
          </div>
        </panel-box>
        <!-- Overall Hero Stats: hero with name, then win rate, popularity, ban rate and games -->
        <div v-else-if="activeButton === 'overallHeroStats'" class="max-w-[1000px] w-full mx-auto">
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
        <!-- Teams: the name filter over a wrapping grid of round team logos -->
        <div v-else-if="activeButton === 'teams'" class="flex flex-col items-center gap-6">
          <skeleton-box class="h-10 w-72 max-w-full"></skeleton-box>
          <div class="max-w-[1500px] w-full flex flex-wrap justify-center gap-4">
            <skeleton-box v-for="team in 45" :key="team" class="w-20 h-20 !rounded-full"></skeleton-box>
          </div>
        </div>
        <table-skeleton v-else></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
const NGS_TABS = ['standings', 'divisions', 'teams', 'playerSearch', 'recentMatches', 'overallHeroStats', 'overallTalentStats'];

export default {
  name: 'NgsMain',
  components: {
  },
  props: {
    defaultseason: Number,
    filters: Object,
    heroes: Object,
    talentimages: Object,
    urlparameters: [Object, Array],
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
      // Bumped per search so the scanning table starts fresh
      searchRun: 0,
      searchedFor: '',
      searchFoundNone: false,
    };
  },
  created(){
    this.preloadedImage.src = '/images/NGS/no-image-clipped.png';
    this.season = this.defaultseason;
  },
  mounted() {
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (NGS_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
  },
  computed: {
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
      this.battletagresponse = null;
      this.searchFoundNone = false;
      this.searchedFor = this.userinput;
      this.searchRun++;
      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/player/search", {
          userinput: this.userinput
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        // table-search opens a single match itself and reports none
        this.battletagresponse = Array.isArray(response.data) ? response.data : [];
      }catch(error){
        if (!this.$axios.isCancel(error)) {
          this.battletagresponse = [];
        }
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getRecentMatches(page){
      if (this.isLoading || page < 1 || (this.recentMatchesData && page > this.recentMatchesData.pagination.last_page)) {
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
          hero: this.selectedHero ? this.selectedHero.name : null,
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
      // One tab loads at a time; the others wait until it's done
      if (this.isLoading) {
        return;
      }
      this.activeButton = buttonName;
      // The open tab goes in the URL, so it can be linked to or reloaded
      const url = new URL(window.location.href);
      url.searchParams.set('tab', buttonName);
      history.replaceState(history.state, '', url);
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
    ngsPlayerUrl(item) {
      return '/Esports/NGS/Player/' + item.battletag.split('#')[0] + '/' + item.blizz_id;
    },
    openNgsPlayer(item) {
      window.location.href = this.ngsPlayerUrl(item);
    },
  }
}
</script>
