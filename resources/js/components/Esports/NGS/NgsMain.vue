<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Nexus Gaming Series'" :heading-image="'/images/NGS/ngs_large.png'" :heading-image-url="'/Esports/NGS'"></page-heading>

      
      <!-- The sections; none is picked on the landing cards, and the others wait while one loads -->
      <div class="flex justify-center px-4 mb-6">
        <tab-button :tabs="[{ key: 'standings', text: 'Standings' }, { key: 'divisions', text: 'Divisions' }, { key: 'teams', text: 'Teams' }, { key: 'playerSearch', text: 'Player Search' }, { key: 'recentMatches', text: 'Recent Matches' }, { key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
      </div>


    <esports-section-cards v-if="!activeButton" :sections="['standings', 'divisions', 'teams', 'playerSearch', 'recentMatches', 'overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>


      <div v-if="activeButton === 'standings'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Divisions', values: filters.ngs_divisions },
            { text: 'Seasons', values: filters.ngs_seasons, defaultValue: defaultseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <ngs-standings v-if="standingData" :data="standingData" :season="season"></ngs-standings>
      </div>

      <div v-if="activeButton === 'divisions'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filters.ngs_seasons, defaultValue: defaultseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <ngs-divisions v-if="divisionData" :data="divisionData" :season="season"></ngs-divisions>
      </div>

      <div v-if="activeButton === 'teams'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Divisions', values: filters.ngs_divisions },
            { text: 'Seasons', values: filters.ngs_seasons, defaultValue: defaultseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <esports-teams v-if="teamsData" :data="teamsData" :esport="'NGS'" :season="season"></esports-teams>
      </div>

      <div v-if="activeButton === 'playerSearch'">
        <div class="flex flex-wrap gap-2 max-w-[93.75rem] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search for NGS player'" :aria-label="'Search for NGS player'" v-model="userinput" @keyup.enter="filter()">
          <custom-button @click="filter()" :text="'Search'" :size="'medium'" class="bg-teal ml-10 hover:bg-lteal" :ignoreclick="true"></custom-button>

        </div>
        <!-- The main site's search: the table scans while it runs, then lists the matches; a single match opens straight away -->
        <table-search v-if="searchRun && !searchFoundNone" :key="searchRun" :query="searchedFor" :results="battletagresponse" :profile-url="ngsPlayerUrl" :match-url="item => '/Esports/NGS/Match/Single/' + item.latestReplayID" loader-image="/images/NGS/no-image-clipped.png" :show-region="false" @select="item => openNgsPlayer(item)" @none="searchFoundNone = true"></table-search>
        <p v-else-if="searchFoundNone" class="text-center text-subtext mt-10">{{ $t('No NGS player found for :searchedfor.', { searchedfor: searchedFor }) }}</p>
      </div>

      <div v-if="activeButton === 'recentMatches'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Divisions', values: filters.ngs_divisions },
            { text: 'Seasons', values: filters.ngs_seasons, defaultValue: defaultseason },
            { text: 'Heroes', values: filters.heroes },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>

        <div v-if="recentMatchesData">
         
          <esports-recent-matches :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches" v-if="recentMatchesData" :data="recentMatchesData.data" :esport="'NGS'"></esports-recent-matches>
        </div>

      </div>


      <div v-if="activeButton === 'overallHeroStats'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Divisions', values: filters.ngs_divisions },
            { text: 'Seasons', values: filters.ngs_seasons, defaultValue: defaultseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: 'NGS' })"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
            <filters
              :filters="filters"
              :isLoading="isLoading"
              :onFilter="() => filter()"
              :onChange="handleInputChange"
              :extrafilters="[
                { text: 'Heroes', values: this.filters.heroes, defaultValue: selectedHero.id },
                { text: 'Divisions', values: filters.ngs_divisions },
                { text: 'Seasons', values: filters.ngs_seasons, defaultValue: defaultseason },
              ]"
              :excludetimeframes="true"
              :excludegametype="true"
              :hideadvancedfilteringbutton="true"
            >
            </filters>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <!-- Player Search shows its own scanning table instead -->
    <div v-if="isLoading && activeButton !== 'playerSearch'">
      <div class="relative max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
        <!-- The message sits on a tab of the first card, as on the global pages: lined up with the card past the side padding, the card's top-left corner squared to meet it -->
        <loading-component v-if="!['recentMatches', 'teams'].includes(activeButton)" :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
        <!-- Standings: a card per division, its title and link over a rank / team / five-number table -->
        <template v-if="activeButton === 'standings'">
          <panel-box v-for="division in 3" :key="division">
            <div class="flex justify-between items-baseline gap-4"><skeleton-box class="h-7 w-40"></skeleton-box><skeleton-box class="h-5 w-24"></skeleton-box></div>
            <div class="h-px bg-gray-dark"></div>
            <table-skeleton :bare="true" :rows="8" :columns="['rank', 'name', 'stat', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
          </panel-box>
        </template>
        <!-- Divisions: one row per division with its games played -->
        <panel-box v-else-if="activeButton === 'divisions'">
          <table-skeleton :bare="true" :rows="7" :columns="['stat', 'stat']"></table-skeleton>
        </panel-box>
        <!-- Recent Matches: expanded rows with the map, the two teams and each team's heroes -->
        <template v-else-if="activeButton === 'recentMatches'">
          <!-- The Compact / Expanded toggle and the page numbers, as on the loaded list -->
          <div class="flex flex-wrap justify-end items-center gap-4">
            <skeleton-box class="w-[13.25rem] h-[2.75rem]"></skeleton-box>
            <skeleton-box class="w-[17.75rem] h-[2.75rem]"></skeleton-box>
          </div>
          <panel-box class="relative md:!rounded-tl-none">
            <!-- The message sits on a tab of the card, as on the match histories -->
            <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
            <div v-for="row in 8" :key="row" class="flex flex-wrap 2xl:flex-nowrap items-center gap-6 rounded bg-surface-1 px-6 py-4">
              <div class="flex flex-col gap-2 w-[18.75rem] shrink-0">
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
              <div class="flex gap-2 w-[21.25rem] max-w-full shrink-0">
                <div v-for="team in 2" :key="team" class="flex flex-col gap-1.5 w-[9.375rem]">
                  <div v-for="player in 5" :key="player" class="flex items-center gap-1">
                    <skeleton-box class="w-4 h-4 shrink-0 !rounded-full"></skeleton-box>
                    <skeleton-box class="h-3 w-24"></skeleton-box>
                  </div>
                </div>
              </div>
            </div>
          </panel-box>
        </template>
        <!-- Overall Hero Stats: hero with name, then win rate, popularity, ban rate and games -->
        <panel-box v-else-if="activeButton === 'overallHeroStats'">
          <table-skeleton :bare="true" :rows="12" :columns="['avatar', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </panel-box>
        <!-- Overall Talent Stats: the global talent page's two cards, in grey -->
        <template v-else-if="activeButton === 'overallTalentStats'">
          <panel-box title="Talent stats" subtitle="Win rate, popularity and games for every talent. The best win rate on each tier is highlighted." :skeleton="true">
            <compact-talent-grid :skeleton="true"></compact-talent-grid>
          </panel-box>
          <panel-box title="Talent builds" subtitle="Copy a build code and paste it in game chat." :skeleton="true">
            <table-skeleton :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
          </panel-box>
        </template>
        <!-- Teams: the name filter over the team cards -->
        <template v-else-if="activeButton === 'teams'">
          <skeleton-box class="h-10 w-full sm:w-[18.125rem]"></skeleton-box>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 md:gap-8">
            <!-- The message sits on a tab of the first card, clear of the search box -->
            <div v-for="team in 8" :key="team" :class="['relative bg-surface rounded p-6 flex flex-col gap-5', { 'md:!rounded-tl-none': team === 1 }]">
              <loading-component v-if="team === 1" :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
              <skeleton-box class="h-[12.5rem] !rounded-control"></skeleton-box>
              <div class="flex items-end justify-between gap-4">
                <div class="flex flex-col gap-2"><skeleton-box class="h-6 w-40"></skeleton-box><skeleton-box class="h-4 w-56 max-w-full"></skeleton-box></div>
                <skeleton-box class="h-5 w-20"></skeleton-box>
              </div>
            </div>
          </div>
        </template>
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
