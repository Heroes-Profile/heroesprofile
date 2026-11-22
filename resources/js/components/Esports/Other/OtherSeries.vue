<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="series.name" :heading-image="'/images/EsportOther/' + this.series.icon" :heading-image-url="'/Esports/Other/' + this.series.name"></page-heading>
    
    <!-- The sections; none is picked on the landing cards, and the others wait while one loads -->
    <div class="flex justify-center px-4 mb-6">
      <tab-button :tabs="[{ key: 'teams', text: 'Teams' }, { key: 'players', text: 'Players' }, { key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
    </div>


    
    <esports-section-cards v-if="!activeButton" :sections="['teams', 'players', 'overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>
      <div v-if="activeButton === 'teams'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: seasons },
            { text: 'Regions', values: regions },
            { text: 'Tournaments', values: tournaments },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>

        <!-- Team names only, so a list of row links in a card rather than logo cards -->
        <div v-if="filteredTeams" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
          <search-input v-model="userinput" :placeholder="$t('Filter by team name')" class="w-full sm:w-[18.125rem]"></search-input>
          <p v-if="!filteredTeams.length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: userinput }) }}</p>
          <panel-box v-else :title="$t('Teams')">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-2">
              <a v-for="row in filteredTeams" :key="row" :href="'/Esports/Other/' + series.name + '/Team/' + row" class="group/bt flex items-center gap-3 rounded bg-surface-1 hover:bg-surface-2 px-3 py-2 min-w-0">
                <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-2 text-[0rem]" :src="'/images/EsportOther/' + series.icon" :alt="series.name"/>
                <span class="font-semibold truncate min-w-0"><span class="group-hover/bt:underline">{{ row }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
              </a>
            </div>
          </panel-box>
        </div>
      </div>

      <div v-if="activeButton === 'players'">
        <div class="flex flex-wrap gap-2 max-w-[93.75rem] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search for a player'" :aria-label="'Search for a player'" v-model="userinput" @keyup.enter="filter()">
          <custom-button @click="filter()" :text="'Search'" :size="'medium'" class="bg-teal ml-10 hover:bg-lteal" :ignoreclick="true"></custom-button>

        </div>


        <!-- The NGS search's scanning table; this search returns only names, so just that column -->
        <table-search v-if="searchRun && !searchFoundNone" :key="searchRun" :query="searchedFor" :results="battletagresponse" :profile-url="otherPlayerUrl" :loader-image="'/images/EsportOther/' + series.icon" :show-region="false" :names-only="true" @select="item => openOtherPlayer(item)" @none="searchFoundNone = true"></table-search>
        <p v-else-if="searchFoundNone" class="text-center text-subtext mt-10">{{ $t('No player found for :searchedfor.', { searchedfor: searchedFor }) }}</p>
      </div>

      <div v-if="activeButton === 'overallHeroStats'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: seasons },
            { text: 'Regions', values: regions },
            { text: 'Tournaments', values: tournaments },
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
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: series.name })"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
            <filters
              :filters="filters"
              :isLoading="isLoading"
              :onFilter="() => filter()"
              :onChange="handleInputChange"
              :extrafilters="[
                { text: 'Seasons', values: seasons },
                { text: 'Regions', values: regions },
                { text: 'Tournaments', values: tournaments },
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

    <!-- Players shows its own scanning table instead -->
    <div v-if="isLoading && activeButton !== 'players'">
      <div class="relative max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
        <!-- The message sits on a tab of the first card, as on the global pages: lined up with the card past the side padding, the card's top-left corner squared to meet it -->
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/EsportOther/' + series.icon"></loading-component>
        <!-- Teams: the list card's rows -->
        <panel-box v-if="activeButton === 'teams'" :title="$t('Teams')">
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-2">
            <skeleton-box v-for="index in 16" :key="index" class="h-14"></skeleton-box>
          </div>
        </panel-box>
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
        <table-skeleton v-else></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
const OTHER_TABS = ['teams', 'players', 'overallHeroStats', 'overallTalentStats'];

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
        //Do something here
      }finally {
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
        //Do something here
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