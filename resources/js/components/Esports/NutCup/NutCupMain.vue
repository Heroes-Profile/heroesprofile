<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <!-- The league's heading with the tab's filters beside it, as on the other league pages -->
    <player-page-top>
      <template #heading>
        <page-heading :heading="'Nut Cup'" :heading-image="'/images/NutCup/IMG_0265.PNG'" :heading-image-url="''"></page-heading>
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

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <!-- The sections; none is picked on the landing cards, and the others wait while one loads -->
      <div class="flex justify-center px-4 mt-6 mb-6">
        <tab-button :tabs="[{ key: 'overallHeroStats', text: 'Overall Hero Stats' }, { key: 'overallTalentStats', text: 'Overall Talent Stats' }]" :no-default="true" :disabled="isLoading" :overridedefaultside="activeButton" :ignoreclick="true" @tab-click="setButtonActive"></tab-button>
      </div>


    <esports-section-cards v-if="!activeButton" :sections="['overallHeroStats', 'overallTalentStats']" @select="setButtonActive"></esports-section-cards>
    <div v-else>

      <div v-if="activeButton === 'overallHeroStats'">
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData" :low-sample-games="2"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes" :subtitle="$t('Open a hero to see their talent win rates and builds in :league games.', { league: 'Nut Cup' })"></hero-selection>
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
    <div v-if="isLoading">
      <div class="relative max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
        <!-- The message sits on a tab of the first card, as on the global pages: lined up with the card past the side padding, the card's top-left corner squared to meet it -->
        <loading-component :compact="true" :tab="true" :overrideimage="'/images/NutCup/logo-circular.png'"></loading-component>
        <!-- Overall Hero Stats: hero with name, then win rate, popularity, ban rate and games -->
        <panel-box v-if="activeButton === 'overallHeroStats'">
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
const NUTCUP_TABS = ['overallHeroStats', 'overallTalentStats'];

export default {
  name: 'NutCupMain',
  components: {
  },
  props: {
    talentimages: Object,
    filters: Object,
    heroes: Object,
    urlparameters: [Object, Array],
  },
  data() {
    return {
      loadError: null,
      preloadedImage: new Image(),
      isLoading: false,
      infoText1: "Heroes of the Storm statistics and comparison for the Nut Cup",
      activeButton: null,
      heroStatsData: null,
      talentStatsData: null,
      selectedHero: null,
      season: null,
      cancelTokenSource: null,
    };
  },
  created(){
    this.preloadedImage.src = '/images/NutCup/IMG_0265.PNG';
    this.preloadedImage.src = '/images/NutCup/logo-circular.png';
    this.season = this.filters.nut_cup_seasons[0].code;
  },
  mounted() {
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (NUTCUP_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
  },
  computed: {
    // The filters beside the heading for the open tab: the season, and the hero on a hero's talent stats
    tabFilters() {
      const season = { text: 'Seasons', values: this.filters.nut_cup_seasons, defaultValue: this.season };
      switch (this.activeButton) {
        case 'overallHeroStats':
          return [season];
        case 'overallTalentStats':
          return this.selectedHero ? [{ text: 'Heroes', values: this.filters.heroes, defaultValue: this.selectedHero.id }, season] : null;
        default:
          return null;
      }
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
    async getHeroStats(){
      this.loadError = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/nutcup/hero/stats", {
          season: this.season,
          esport: "NutCup",
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
        const response = await this.$axios.post("/api/v1/esports/nutcup/hero/talents/stats", {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "NutCup",
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
      this.season = this.filters.nut_cup_seasons[0].code;
      
      if(this.activeButton === 'overallHeroStats'){
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
      if(this.activeButton === 'overallHeroStats'){
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
