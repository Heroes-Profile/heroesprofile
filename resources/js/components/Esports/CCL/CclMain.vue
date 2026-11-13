<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'CCL'" :heading-image="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'" :heading-image-url="'/Esports/CCL'"></page-heading>

      <!---You are going to have to design this better, I am going to use buttons for now -->
      <div class="flex flex-wrap justify-center gap-1 bg-teal rounded p-1 w-fit max-w-[calc(100%-2rem)] mx-auto mb-6 max-md:text-xs">
        <button type="button" :disabled="isLoading && activeButton !== 'organizations'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'organizations' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('organizations')">{{ $t('Organizations') }}</button>

        <button type="button" :disabled="isLoading && activeButton !== 'recentMatches'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'recentMatches' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('recentMatches')">{{ $t('Recent Matches') }}</button>

        <button type="button" :disabled="isLoading && activeButton !== 'overallHeroStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallHeroStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallHeroStats')">{{ $t('Overall Hero Stats') }}</button>

      <button type="button" :disabled="isLoading && activeButton !== 'overallTalentStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallTalentStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallTalentStats')">{{ $t('Overall Talent Stats') }}</button>
      </div>


    <div v-if="!activeButton">
      <div class="flex p-10 md:p-20 bg-surface rounded max-w-[93.75rem] mx-4 md:mx-auto flex-wrap justify-center">

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-users" style="font-size: 6.25rem"></i>
          <h3>{{ $t('Organizations') }}</h3>
          <custom-button @click="setButtonActive('organizations')" :text="'Organizations'" :size="'big'" class="mt-10" :active="organizationsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-list" style="font-size: 6.25rem"></i>
          <h3>{{ $t('Recent Matches') }}</h3>
          <custom-button @click="setButtonActive('recentMatches')" :text="'Recent Matches'" :size="'big'" class="mt-10" :active="recentMatchesClicked" :ignoreclick="true"></custom-button>
        </div>

        
        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fa-solid fa-chart-bar" style="font-size: 6.25rem"></i>
          <h3>{{ $t('Overall Hero Stats') }}</h3>
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="mt-10" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-chart-line" style="font-size: 6.25rem"></i>
          <h3>{{ $t('Overall Talent Stats') }}</h3>
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="mt-10" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>
      </div>

    </div>
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
          <div v-if="recentMatchesData.pagination.last_page > 1" class="max-w-[120rem] mx-auto px-4 md:px-8 flex justify-end">
            <pagination-bar :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches"></pagination-bar>
          </div>
          <esports-recent-matches :data="recentMatchesData.data" :esport="'CCL'"></esports-recent-matches>
          <div v-if="recentMatchesData.pagination.last_page > 1" class="max-w-[120rem] mx-auto px-4 md:px-8 flex justify-end">
            <pagination-bar :current="recentMatchesData.pagination.current_page" :last="recentMatchesData.pagination.last_page" @page="getRecentMatches"></pagination-bar>
          </div>
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
          <hero-selection :heroes="heroes"></hero-selection>
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
      <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <loading-component :compact="true" :overrideimage="'/images/CCL/600-600-HHE_CCL_Logo_rectangle.png'"></loading-component>
        <!-- Organizations: a wrapping grid of logos with the name underneath -->
        <div v-if="activeButton === 'organizations'" class="flex p-10 md:p-20 bg-surface rounded max-w-[93.75rem] w-full mx-auto flex-wrap justify-center gap-10">
          <div v-for="team in 12" :key="team" class="flex flex-col items-center gap-3 md:w-[15%]">
            <skeleton-box class="w-32 h-32"></skeleton-box>
            <skeleton-box class="h-5 w-28 max-w-full"></skeleton-box>
          </div>
        </div>
        <!-- Recent Matches: expanded rows with the map, the two teams and each team's heroes -->
        <panel-box v-else-if="activeButton === 'recentMatches'">
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
        <!-- Overall Hero Stats: hero with name, then win rate, popularity, ban rate and games -->
        <div v-else-if="activeButton === 'overallHeroStats'" class="max-w-[62.5rem] w-full mx-auto">
          <table-skeleton :bare="true" :rows="12" :columns="['avatar', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </div>
        <!-- Overall Talent Stats: a block per talent level, then the builds table -->
        <div v-else-if="activeButton === 'overallTalentStats'" class="flex flex-col items-center">
          <div v-for="level in 3" :key="level" class="w-full bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-20"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="3" :columns="['hero', 'stat', 'stat', 'stat']"></table-skeleton>
          </div>
          <div class="w-full max-w-[50rem] bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-16"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
          </div>
        </div>
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
