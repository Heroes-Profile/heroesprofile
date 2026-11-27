<template>
  <div>
    <!-- The series' heading with the tab's filters beside it, as on the league pages -->
    <player-page-top>
      <template #heading>
        <page-heading :heading="series.name" :subheading="$t('HGC and More Esports')">
          <a :href="'/Esports/Other/' + series.name" class="flex items-center justify-center w-28 h-28"><img :src="'/images/EsportOther/' + series.icon" :alt="series.name" class="w-full h-full object-contain"></a>
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
      <tab-button :tabs="[{ key: 'teams', text: 'Teams' }, { key: 'recentMatches', text: 'Recent Matches' }, { key: 'players', text: 'Players' }, { key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :stretch="true" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
    </div>

    <esports-section-cards v-if="!activeButton" :sections="['teams', 'recentMatches', 'players', 'overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>
      <div v-if="activeButton === 'teams'">

        <!-- Team names only, so a list of row links in a card rather than logo cards -->
        <div v-if="filteredTeams" class="max-w-[120rem] w-full mx-auto px-4 md:px-8">
          <panel-box :title="$t('Teams')" :divider="false">
            <template #right>
              <search-input v-model="userinput" :placeholder="$t('Filter by team name')" class="w-full sm:w-[18.125rem] sm:ml-auto"></search-input>
            </template>
            <p v-if="!filteredTeams.length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: userinput }) }}</p>
            <div v-else class="grid sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-2">
              <a v-for="row in filteredTeams" :key="row" :href="'/Esports/Other/' + series.name + '/Team/' + row" class="group/bt flex items-center gap-3 rounded bg-surface-1 hover:bg-surface-2 px-3 py-2 min-w-0">
                <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-2 text-[0rem]" :src="'/images/EsportOther/' + series.icon" :alt="series.name"/>
                <span class="font-semibold truncate min-w-0"><span class="group-hover/bt:underline">{{ row }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
              </a>
            </div>
          </panel-box>
        </div>
      </div>

      <!-- The series' latest games, as on the league pages -->
      <div v-if="activeButton === 'recentMatches'">
        <esports-recent-matches v-if="recentMatchesData" :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches" :data="recentMatchesData.data" :esport="'Other'" :series="series.name" :title="$t('Recent matches')" :subtitle="recentMatchesSubtitle"></esports-recent-matches>
      </div>

      <div v-if="activeButton === 'players'">
        <div class="max-w-[120rem] mx-auto px-4 md:px-8">
          <panel-box title="Find a player" :subtitle="$t('Search :series players by BattleTag or name.', { series: series.name })" :divider="false">
            <div class="flex flex-wrap items-center gap-3">
              <search-input v-model="userinput" :placeholder="$t('Search for a player')" class="w-full sm:w-[20rem]" @keyup.enter="filter()"></search-input>
              <custom-button @click="filter()" text="Search" color="teal" class="font-semibold" :ignoreclick="true"></custom-button>
            </div>
            <!-- The NGS search's scanning table; this search returns only names, so just that column -->
            <table-search :embedded="true" v-if="searchRun && !searchFoundNone" :key="searchRun" :query="searchedFor" :results="battletagresponse" :profile-url="otherPlayerUrl" :loader-image="'/images/EsportOther/' + series.icon" :show-region="false" :names-only="true" @select="item => openOtherPlayer(item)" @none="searchFoundNone = true"></table-search>
            <p v-else-if="searchFoundNone" class="text-subtext">{{ $t('No player found for :searchedfor.', { searchedfor: searchedFor }) }}</p>
          </panel-box>
        </div>
      </div>

      <div v-if="activeButton === 'overallHeroStats'">
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: series.name })"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>



    </div>

    <!-- The load failed: the error page's card in place of the data -->
    <div v-if="loadError && !isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
    <!-- Players shows its own scanning table instead -->
    <!-- Each tab's cards in grey; the message sits on a tab of the first card -->
    <div v-if="isLoading && activeButton !== 'players'" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <!-- Teams: the list card's rows, the search at the top right -->
      <panel-box v-if="activeButton === 'teams'" :title="$t('Teams')" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/EsportOther/' + series.icon"></loading-component>
        <template #right><skeleton-box class="h-10 w-full sm:w-[18.125rem] sm:ml-auto rounded"></skeleton-box></template>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-2">
          <skeleton-box v-for="index in 16" :key="index" class="h-14 rounded-lg"></skeleton-box>
        </div>
      </panel-box>
      <!-- Recent Matches: the titled card, the toggle and page numbers in its header, the expanded rows -->
      <panel-box v-else-if="activeButton === 'recentMatches'" title="Recent matches" :subtitle="recentMatchesSubtitle" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/EsportOther/' + series.icon"></loading-component>
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
      <panel-box v-else-if="activeButton === 'overallHeroStats'" title="Overall hero stats" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/EsportOther/' + series.icon"></loading-component>
        <template #right><skeleton-box class="h-10 w-full sm:w-[18.125rem] sm:ml-auto rounded"></skeleton-box></template>
        <table-skeleton :bare="true" :rows="12" :columns="['hero', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
      </panel-box>
      <!-- Overall Talent Stats: the global talent page's two cards -->
      <div v-else-if="activeButton === 'overallTalentStats'" class="flex flex-col gap-6">
        <panel-box title="Talent stats" subtitle="Win rate, popularity and games for every talent. The best win rate on each tier is highlighted." :skeleton="true" class="relative md:!rounded-tl-none">
          <loading-component :compact="true" :tab="true" :overrideimage="'/images/EsportOther/' + series.icon"></loading-component>
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
const OTHER_TABS = ['teams', 'recentMatches', 'players', 'overallHeroStats', 'overallTalentStats'];

export default {
  name: 'OtherSeries',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    series: Array,
    seasons: Array,
    regions: Array,
    tournaments: Array,
    heroes: Object,
    talentimages: Object,
    urlparameters: [Object, Array],
  },
  data(){
    return {
      loadError: null,
      infoText1: "Heroes of the Storm statistics and comparison for " + this.series.name,
      activeButton: null,
      teamsData: null,
      isLoading: false,
      season: null,
      region: null,
      tournament: null,
      userinput: '',
      battletagresponse: null,
      searchRun: 0,
      searchedFor: '',
      searchFoundNone: false,
      heroStatsData: null,
      recentMatchesData: null,
      selectedHero: null,
    }
  },
  created(){
  },
  mounted() {
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (OTHER_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
  },
  computed: {
    // The active tab's dropdowns, shown in the heading; Players has none, Talent Stats only once a hero is open
    tabFilters() {
      const all = [
        { text: 'Seasons', values: this.seasons, defaultValue: this.season },
        { text: 'Regions', values: this.regions, defaultValue: this.region },
        { text: 'Tournaments', values: this.tournaments, defaultValue: this.tournament },
      ];
      if (this.activeButton === 'teams' || this.activeButton === 'overallHeroStats') return all;
      // The match list filters by season only
      if (this.activeButton === 'recentMatches') return all.slice(0, 1);
      if (this.activeButton === 'overallTalentStats' && this.selectedHero) return all;
      return null;
    },
    recentMatchesSubtitle() {
      return this.season ? this.$t('Season :season · newest first', { season: this.season }) : this.$t('Newest first');
    },
    filteredTeams() {
      if(this.teamsData){
        return this.teamsData.filter((team) => {
          return team.toLowerCase().includes(this.userinput.toLowerCase());
        });
      }
      return null;
    },

    teamsClicked() {
      return this.activeButton === 'teams';
    },
    matchesClicked() {
      return this.activeButton === 'matches';
    },
    playersClicked() {
      return this.activeButton === 'players';
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
    async getTeamsData(){
      this.loadError = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/other/teams", {
          series: this.series.name,
          season: this.season,
          region: this.region,
          tournament: this.tournament,
        }, 
        {
        });
        this.teamsData = response.data;
      }catch(error){
        this.loadError = this.$loadError(error, () => this.getTeamsData());
      }finally {
        this.isLoading = false;
      }
    },

    // The series' games, newest first, a page at a time (the team pages' match history, for every team)
    async getRecentMatches(page){
      this.loadError = null;
      if (this.isLoading || page < 1 || (this.recentMatchesData && page > this.recentMatchesData.pagination.last_page)) {
        return;
      }

      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/other/team/match/history", {
          esport: "Other",
          series: this.series.name,
          season: this.season,
          pagination_page: page,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.recentMatchesData = response.data;
      }catch(error){
        this.loadError = this.$loadError(error, () => this.getRecentMatches(page));
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
      this.battletagresponse = null;
      this.searchFoundNone = false;
      this.searchedFor = this.userinput;
      this.searchRun++;
      try{
        const response = await this.$axios.post(`/api/v1/esports/other/${this.series}/player/search`, {
          userinput: this.userinput,
          series: this.series.name
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        // table-search opens a single match itself and reports none
        this.battletagresponse = Array.isArray(response.data) ? response.data.map(row => ({ ...row, battletagShort: row.battletag })) : [];
      }catch(error){
        if (!this.$axios.isCancel(error)) {
          this.battletagresponse = [];
        }
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },

    async getHeroStats(){
      this.loadError = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post(`/api/v1/esports/other/${this.series.name}/hero/stats`, {
          season: this.season,
          esport: "Other",
          series: this.series.name,
          region: this.region,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.heroStatsData = response.data;
      }catch(error){
        this.loadError = this.$loadError(error, () => this.getHeroStats());
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getTalentStats(){
      this.loadError = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post(`/api/v1/esports/other/${this.series.name}/hero/talents/stats`, {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "Other",
          series: this.series.name,
          region: this.region,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.talentStatsData = response.data;
      }catch(error){
        this.loadError = this.$loadError(error, () => this.getTalentStats());
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
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
      this.selectedHero = null;
      if(this.activeButton === 'teams'){
        this.teamsData = null;
        this.getTeamsData();

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
      }else if(eventPayload.field == "Regions"){
        this.region = eventPayload.value;
      }else if(eventPayload.field == "Tournaments"){
        this.tournament = eventPayload.value;
      }
    },

    filter(){
      if(this.activeButton === 'teams'){
      this.teamsData = null;
        this.getTeamsData();
      }else if(this.activeButton === 'recentMatches'){
        this.recentMatchesData = null;
        this.getRecentMatches(1);
      }else if(this.activeButton === 'players'){
        this.searchedPlayer();
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
    otherPlayerUrl(item) {
      return '/Esports/Other/' + this.series.name + '/Player/' + item.battletag + '/' + item.blizz_id;
    },
    openOtherPlayer(item) {
      window.location.href = this.otherPlayerUrl(item);
    },
  }
}
</script>