<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Community Clash League'" :heading-image="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'" :heading-image-url="'/Esports/CCL'"></page-heading>

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <!-- The sections; none is picked on the landing cards, and the others wait while one loads -->
      <div class="flex justify-center px-4 mb-6">
        <tab-button :tabs="[{ key: 'organizations', text: 'Organizations' }, { key: 'recentMatches', text: 'Recent Matches' }, { key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
      </div>


    <esports-section-cards v-if="!activeButton" :sections="['organizations', 'recentMatches', 'overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>

      <div v-if="activeButton === 'organizations'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filters.ccl_seasons, defaultValue: defaultseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <esports-organizations v-if="organizationsData" :data="organizationsData" :esport="'CCL'" :season="season"></esports-organizations>
      </div>

      <div v-if="activeButton === 'recentMatches'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filters.ccl_seasons, defaultValue: defaultseason },
            { text: 'Organizations', values: organizations },
            { text: 'Heroes', values: filters.heroes },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>




        <div v-if="recentMatchesData">
          <esports-recent-matches :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches" :data="recentMatchesData.data" :esport="'CCL'"></esports-recent-matches>
        </div>
      </div>
      
      <div v-if="activeButton === 'overallHeroStats'">
           <filters
             :filters="filters"
             :isLoading="isLoading"
             :onFilter="() => filter()"
             :onChange="handleInputChange"
             :extrafilters="[
               { text: 'Seasons', values: filters.ccl_seasons, defaultValue: defaultseason },
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
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: 'CCL' })"></hero-selection>
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
                   { text: 'Seasons', values: filters.ccl_seasons, defaultValue: defaultseason },
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
    <div v-if="isLoading">
      <div class="relative max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
        <!-- The message sits on a tab of the first card, as on the global pages: lined up with the card past the side padding, the card's top-left corner squared to meet it -->
        <loading-component v-if="!['recentMatches', 'organizations'].includes(activeButton)" :compact="true" :tab="true" :overrideimage="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'"></loading-component>
        <!-- Organizations: the name filter over the team cards; the message sits on a tab of the first card -->
        <template v-if="activeButton === 'organizations'">
          <skeleton-box class="h-10 w-full sm:w-[18.125rem]"></skeleton-box>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 md:gap-8">
            <div v-for="team in 8" :key="team" :class="['relative bg-surface rounded p-6 flex flex-col gap-5', { 'md:!rounded-tl-none': team === 1 }]">
              <loading-component v-if="team === 1" :compact="true" :tab="true" :overrideimage="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'"></loading-component>
              <skeleton-box class="h-[12.5rem] !rounded-control"></skeleton-box>
              <div class="flex items-end justify-between gap-4"><skeleton-box class="h-6 w-40"></skeleton-box><skeleton-box class="h-5 w-20"></skeleton-box></div>
            </div>
          </div>
        </template>
        <!-- Recent Matches: expanded rows with the map, the two teams and each team's heroes -->
        <template v-else-if="activeButton === 'recentMatches'">
          <!-- The Compact / Expanded toggle and the page numbers, as on the loaded list -->
          <div class="flex flex-wrap justify-end items-center gap-4">
            <skeleton-box class="w-[13.25rem] h-[2.75rem]"></skeleton-box>
            <skeleton-box class="w-[17.75rem] h-[2.75rem]"></skeleton-box>
          </div>
          <panel-box class="relative md:!rounded-tl-none">
            <!-- The message sits on a tab of the card, as on the match histories -->
            <loading-component :compact="true" :tab="true" :overrideimage="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'"></loading-component>
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
        <table-skeleton v-else></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
const CCL_TABS = ['organizations', 'recentMatches', 'overallHeroStats', 'overallTalentStats'];

export default {
  name: 'CclMain',
  components: {
  },
  props: {
    defaultseason: Number,
    filters: Object,
    heroes: Object,
    talentimages: Object,
    organizations: Object,
    urlparameters: [Object, Array],
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
      organization: null,
    };
  },
  created(){
    this.preloadedImage.src = '/images/CCL/600-600-HHE_CCL_Logo_rectangle.png';
    this.season = this.defaultseason;
  },
  mounted() {
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (CCL_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
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
      if (this.isLoading || page < 1 || (this.recentMatchesData && page > this.recentMatchesData.pagination.last_page)) {
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
          team: this.organization ? this.organization : null,
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
      }else if(eventPayload.field == "Organizations"){
        this.organization = eventPayload.value;
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
