<template>
  <div>
    <!-- The league's heading with the tab's filters beside it, as on the player pages -->
    <player-page-top>
      <template #heading>
        <page-heading heading="Nexus Gaming Series" :subheading="$t('NGS · Community league · Season :season', { season: season })">
          <a href="/Esports/NGS" class="flex items-center justify-center w-28 h-28"><img src="/images/NGS/ngs_large.png" alt="NGS" class="w-full h-full object-contain"></a>
        </page-heading>
      </template>
      <div v-if="tabFilters" class="w-full">
        <filters
          :key="activeButton"
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="tabFilters"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>

    <!-- The sections, across the page; none is picked on the landing cards, and the others wait while one loads -->
    <div class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 mb-6">
      <tab-button :tabs="[{ key: 'standings', text: 'Standings' }, { key: 'divisions', text: 'Divisions' }, { key: 'teams', text: 'Teams' }, { key: 'playerSearch', text: 'Player Search' }, { key: 'recentMatches', text: 'Recent Matches' }, { key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :stretch="true" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
    </div>

    <esports-section-cards v-if="!activeButton" :sections="['standings', 'divisions', 'teams', 'playerSearch', 'recentMatches', 'overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>


      <div v-if="activeButton === 'standings'">
        <ngs-standings v-if="standingData" :data="standingData" :season="season"></ngs-standings>
      </div>

      <div v-if="activeButton === 'divisions'">
        <ngs-divisions v-if="divisionData" :data="divisionData" :season="season"></ngs-divisions>
      </div>

      <div v-if="activeButton === 'teams'">
        <esports-teams v-if="teamsData" :data="teamsData" :esport="'NGS'" :season="season" :division="division"></esports-teams>
      </div>

      <div v-if="activeButton === 'playerSearch'">
        <div class="max-w-[120rem] mx-auto px-4 md:px-8">
          <panel-box title="Find a player" subtitle="Search NGS players by BattleTag or name." :divider="false">
            <!-- The sub-header's Find Player box, searching NGS players instead -->
            <search-component :emit-search="true" button-text="Find Player" label-text="Search for NGS player" class="!mt-0 !py-0 w-full sm:w-[30rem]" @search="userinput = $event; filter()"></search-component>
            <!-- The main site's search: the table scans while it runs, then lists the matches; a single match opens straight away -->
            <table-search :embedded="true" v-if="searchRun && !searchFoundNone" :key="searchRun" :query="searchedFor" :results="battletagresponse" :profile-url="ngsPlayerUrl" :match-url="item => '/Esports/NGS/Match/Single/' + item.latestReplayID" loader-image="/images/NGS/no-image-clipped.png" :show-region="false" @select="item => openNgsPlayer(item)" @none="searchFoundNone = true"></table-search>
            <p v-else-if="searchFoundNone" class="text-subtext">{{ $t('No NGS player found for :searchedfor.', { searchedfor: searchedFor }) }}</p>
          </panel-box>
        </div>
      </div>

      <div v-if="activeButton === 'recentMatches'">

        <div v-if="recentMatchesData">
         
          <esports-recent-matches :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches" v-if="recentMatchesData" :data="recentMatchesData.data" :esport="'NGS'" :title="$t('Recent matches')" :subtitle="$t('Season :season · newest first', { season: season })"></esports-recent-matches>
        </div>

      </div>


      <div v-if="activeButton === 'overallHeroStats'">
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData" :subtitle="$t('Season :season', { season: season }) + ' · ' + (division ? $t('Division :division', { division: division }) : $t('All divisions').toLowerCase())"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: 'NGS' })"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>

    </div>
    <!-- Player Search shows its own scanning table instead. Each tab's cards in grey; the message sits on a tab of the first card -->
    <div v-if="isLoading && activeButton !== 'playerSearch'" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <!-- Standings: division cards two to a row, a rank / team / five-number table in each -->
      <div v-if="activeButton === 'standings'" class="grid xl:grid-cols-2 gap-6">
        <panel-box v-for="division in 4" :key="division" :title="$t('Division :divisionname', { divisionname: 'A' })" :subtitle="$t(':teams teams · :matches matches each', { teams: 11, matches: 10 })" link-text="View stats" :skeleton="true" :divider="false" :class="['relative', { 'md:!rounded-tl-none': division === 1 }]">
          <loading-component v-if="division === 1" :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
          <table-skeleton :bare="true" :rows="8" :columns="['rank', 'name', 'stat', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </panel-box>
      </div>
      <!-- Divisions: a tile per division with its games and bar -->
      <panel-box v-else-if="activeButton === 'divisions'" title="Divisions" :subtitle="$t('Season :season · :games games played across :count divisions.', { season: season, games: 682, count: 7 })" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <div v-for="division in 7" :key="division" class="flex flex-col gap-3 rounded bg-surface-1 px-5 py-4">
            <skeleton-box class="h-3 w-16"></skeleton-box>
            <skeleton-box class="h-6 w-24"></skeleton-box>
            <skeleton-box class="h-3 w-20"></skeleton-box>
            <skeleton-box class="h-5 w-12"></skeleton-box>
            <skeleton-box class="h-3 w-full"></skeleton-box>
          </div>
        </div>
      </panel-box>
      <!-- Teams: the logo tiles, the search at the top right -->
      <panel-box v-else-if="activeButton === 'teams'" title="Teams" :subtitle="$t('Season :season', { season: season }) + ' · ' + $t('All divisions').toLowerCase()" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
        <template #right><skeleton-box class="h-10 w-full sm:w-[18.125rem] sm:ml-auto rounded"></skeleton-box></template>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-4">
          <div v-for="team in 8" :key="team" class="flex flex-col items-center gap-3 rounded bg-surface-1 px-4 py-6">
            <skeleton-box class="w-20 h-20 !rounded-full"></skeleton-box>
            <skeleton-box class="h-4 w-32"></skeleton-box>
            <skeleton-box class="h-5 w-24"></skeleton-box>
          </div>
        </div>
      </panel-box>
      <!-- Recent Matches: the titled card, the toggle and page numbers in its header, the expanded rows -->
      <panel-box v-else-if="activeButton === 'recentMatches'" title="Recent matches" :subtitle="$t('Season :season · newest first', { season: season })" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
        <template #right>
          <div class="ml-auto flex flex-wrap items-center gap-4">
            <skeleton-box class="w-[10rem] h-9 rounded"></skeleton-box>
            <skeleton-box class="w-[17.75rem] h-[2.75rem] rounded"></skeleton-box>
          </div>
        </template>
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
              <skeleton-box class="h-8 w-20 rounded"></skeleton-box>
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
      <!-- Overall Hero Stats: the titled card with its search, then hero, win rate, popularity, ban rate and games -->
      <panel-box v-else-if="activeButton === 'overallHeroStats'" title="Overall hero stats" :subtitle="$t('Season :season', { season: season }) + ' · ' + $t('All divisions').toLowerCase()" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
        <template #right><skeleton-box class="h-10 w-full sm:w-[18.125rem] sm:ml-auto rounded"></skeleton-box></template>
        <table-skeleton :bare="true" :rows="12" :columns="['hero', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
      </panel-box>
      <!-- Overall Talent Stats: the global talent page's two cards -->
      <div v-else-if="activeButton === 'overallTalentStats'" class="flex flex-col gap-6">
        <panel-box title="Talent stats" subtitle="Win rate, popularity and games for every talent. The best win rate on each tier is highlighted." :skeleton="true" class="relative md:!rounded-tl-none">
          <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
          <compact-talent-grid :skeleton="true"></compact-talent-grid>
        </panel-box>
        <panel-box title="Talent builds" subtitle="Copy a build code and paste it in game chat." :skeleton="true">
          <table-skeleton :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
        </panel-box>
      </div>
      <table-skeleton v-else></table-skeleton>
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
    // The active tab's dropdowns, shown in the heading; Player Search has none, Talent Stats only once a hero is open
    tabFilters() {
      const season = { text: 'Seasons', values: this.filters.ngs_seasons, defaultValue: this.season };
      const division = { text: 'Divisions', values: this.filters.ngs_divisions, defaultValue: this.division };
      switch (this.activeButton) {
        case 'divisions':
          return [season];
        case 'standings':
        case 'teams':
        case 'overallHeroStats':
          return [season, division];
        case 'recentMatches':
          return [season, division, { text: 'Heroes', values: this.filters.heroes }];
        case 'overallTalentStats':
          return this.selectedHero ? [{ text: 'Heroes', values: this.filters.heroes, defaultValue: this.selectedHero.id }, season, division] : null;
        default:
          return null;
      }
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
      this.selectedHero = null;

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
