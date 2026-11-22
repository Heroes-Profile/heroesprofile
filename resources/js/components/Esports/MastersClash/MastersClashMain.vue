<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Masters Clash'" :heading-image="'/images/MCL/no-image.png'" :heading-image-url="'/Esports/MastersClash'"></page-heading>

    
      <!-- The sections; none is picked on the landing cards, and the others wait while one loads -->
      <div class="flex justify-center px-4 mb-6">
        <tab-button :tabs="[{ key: 'teams', text: 'Teams' }, { key: 'recentMatches', text: 'Recent Matches' }, { key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
      </div>


    <esports-section-cards v-if="!activeButton" :sections="['teams', 'recentMatches', 'overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>


      <div v-if="activeButton === 'teams'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filters.mcl_seasons, defaultValue: defaultseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <esports-organizations v-if="teamsData" :data="teamsData" :esport="'MastersClash'" :season="season"></esports-organizations>
      </div>


      <div v-if="activeButton === 'recentMatches'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filters.mcl_seasons, defaultValue: defaultseason },
            { text: 'Heroes', values: filters.heroes },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>




        <div v-if="recentMatchesData">
          <esports-recent-matches :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches" :data="recentMatchesData.data" :esport="'MastersClash'"></esports-recent-matches>
        </div>



      </div>


      <div v-if="activeButton === 'overallHeroStats'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filters.mcl_seasons, defaultValue: defaultseason },
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
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: 'Masters Clash' })"></hero-selection>
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
                { text: 'Seasons', values: filters.mcl_seasons, defaultValue: defaultseason },
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
        <loading-component v-if="activeButton !== 'recentMatches'" :compact="true" :tab="true" :overrideimage="'/images/MCL/no-image.png'"></loading-component>
        <!-- Teams: a wrapping grid of logos with the name underneath -->
        <div v-if="activeButton === 'teams'" class="flex p-10 md:p-20 bg-surface rounded max-w-[93.75rem] w-full mx-auto flex-wrap justify-center gap-10">
          <div v-for="team in 12" :key="team" class="flex flex-col items-center gap-3 md:w-[15%]">
            <skeleton-box class="w-32 h-32"></skeleton-box>
            <skeleton-box class="h-5 w-28 max-w-full"></skeleton-box>
          </div>
        </div>
        <!-- Recent Matches: expanded rows with the map, the two teams and each team's heroes -->
        <template v-else-if="activeButton === 'recentMatches'">
          <!-- The Compact / Expanded toggle and the page numbers, as on the loaded list -->
          <div class="flex flex-wrap justify-end items-center gap-4">
            <skeleton-box class="w-[13.25rem] h-[2.75rem]"></skeleton-box>
            <skeleton-box class="w-[17.75rem] h-[2.75rem]"></skeleton-box>
          </div>
          <panel-box class="relative md:!rounded-tl-none">
            <!-- The message sits on a tab of the card, as on the match histories -->
            <loading-component :compact="true" :tab="true" :overrideimage="'/images/MCL/no-image.png'"></loading-component>
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
const MCL_TABS = ['teams', 'recentMatches', 'overallHeroStats', 'overallTalentStats'];

export default {
  name: 'MastersClashMain',
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

      isLoading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Masters Clash",
      activeButton: null,
      cancelTokenSource: null,
      teamsData: null,
      recentMatchesData: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,
      season: null,

    };
  },
  created(){
    this.preloadedImage.src = '/images/MCL/no-image.png';
    this.season = this.defaultseason;
  },
  mounted() {
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (MCL_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
  },
  computed: {
    teamsClicked() {
      return this.activeButton === 'teams';
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
    async getTeamsData(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/mastersclash/teams", {
          season: this.season,
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
        const response = await this.$axios.post("/api/v1/esports/mastersclash/matches", {
          season: this.season,
          pagination_page: page,
          hero: this.selectedHero ? this.selectedHero.name : null,
          esport: "MastersClash",
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
        const response = await this.$axios.post("/api/v1/esports/mastersclash/hero/stats", {
          season: this.season,
          esport: "MastersClash",
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
        const response = await this.$axios.post("/api/v1/esports/mastersclash/hero/talents/stats", {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "MastersClash",
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
      }else if(eventPayload.field == "Heroes"){
        this.selectedHero = this.heroes.find(value => value.id === eventPayload.value);
      }
    },

    filter(){
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
    clickedHero(hero){
      this.selectedHero = hero;
      this.getTalentStats();
    },
  }
}
</script>
