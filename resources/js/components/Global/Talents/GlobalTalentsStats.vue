<template>
  <div>
    <global-async-debug-banner page-label="Global Talents" />

    <div class="grid gap-5 grid-cols-1">
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading scope="Global" v-if="!selectedHero" :heading="'Hero Builds & Talent Stats'" :subheading="defaulttimeframe && defaulttimeframe.length ? $t('Most popular builds · Patch :patch', { patch: defaulttimeframe[0] }) : 'Most popular builds'">
      </page-heading>


    
        <div v-if="!selectedHero" class="flex flex-col gap-6">
          <hero-selection :heroes="heroes" linkbase="/Global/Talents" subtitle="Open a hero to see talent win rates and all builds." @search="heroSearchQuery = $event" @role="heroRole = $event"></hero-selection>

          <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false"></dynamic-banner-ad>

          <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8">
            <panel-box title="Most Popular Builds" subtitle="Latest patch, per hero and game type. Copy a build code and paste it in game chat.">
              <div v-if="!talentbuilddataall" class="flex flex-col gap-4">
                <loading-component :compact="true" :textoverride="true">{{ $t('Loading all talent builds...') }}</loading-component>
                <!-- Laid out at the loaded table's full width and shrunk to fit like it (scaleTableToFit), so on a phone
                     the grey rows look like the shrunk table instead of three game types squeezed together -->
                <div ref="buildsskeletonbox" class="w-full overflow-clip">
                  <div ref="buildsskeleton" class="min-w-[72rem]">
                    <table-skeleton :bare="true" :rows="10" :columns="['avatar', 'talents', 'talents', 'talents']" :mobile-columns="['avatar', 'talents', 'talents', 'talents']"></table-skeleton>
                  </div>
                </div>
              </div>
              <global-talent-builds-all v-else :heroes="filteredHeroesForTable" :talentbuilddataall="talentbuilddataall"></global-talent-builds-all>
            </panel-box>
          </div>
        </div>

        <div v-else>
          <player-page-top>
          <template #heading>
            <page-heading scope="Global" :heading="$heroName(selectedHero)" :subheading="'Builds & talent stats'" :portrait-desktop-only="true">
              <hero-image-wrapper :hero="selectedHero" size="heading" :includehover="false"></hero-image-wrapper>
              <!-- The hero name is the hero picker (no Heroes filter on this page) -->
              <template #headingText>
                <hero-heading-select :heroes="heroes" :value="selectedHero.id" textclass="" @hero-changed="handleInputChange({ value: $event })"></hero-heading-select>
              </template>
              <template v-if="isSLRank" #headingInfo>
                <round-image :popup-hoverable="true" class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                  <p class="font-normal">
                    {{ $t("In-game rank uses Storm League ranks read off loading screens by the uploader's opt-in rank reader. Only games with a rank read for a player count, so sample sizes are much smaller than HP rank.") }}
                    <a href="/Upload/Uploader#rank-reading" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">How rank reading works</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
                  </p>
                </round-image>
              </template>
            </page-heading>
          </template>
          <filters
            :key="filtersKey"
            :onFilter="filterData"
            :filters="filters"
            :isLoading="isTalentsLoading || isBuildsLoading"

            :timeframetypeinput="timeframetype"
            :timeframeinput="timeframe"
            :gametypeinput="gametype"
            :regioninput="region"
            :statfilterinput="statfilter"
            :herolevelinput="herolevel"
            :gamemapinput="gamemap"
            :playerrankinput="playerrank"
            :herorankinput="herorank"
            :rolerankinput="rolerank"
            :mirrormatchinput="mirrormatch"
            :ranksourceinput="ranksource"
            :slrankinput="slrank"




            :gametypedefault="gametypedefault"
            :includetimeframe="true"
            :includeregion="true"
            :includestatfilter="true"
            :includeherolevel="true"
            :includegametype="true"
            :includegamemap="true"
            :includeplayerrank="true"
            :includeherorank="true"
            :includerolerank="true"
            :includemirror="true"
            :includeranksource="true"
            :advancedfiltering="advancedfiltering"

            >
          </filters>
        </player-page-top>
        <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

        <div v-if="talentdetaildata" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
          <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]"></patch-notes-panel>
          <panel-box title="Talent stats" subtitle="Win rate, popularity and games for every talent. The best win rate on each tier is highlighted.">
            <compact-talent-grid :talent-data="talentdetaildata" :stat-filter="statfilter" :layout-style="talentstatsstyle"></compact-talent-grid>
          </panel-box>
        </div>
        <div v-else-if="isTalentsLoading" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
          <!-- The collapsed Patch Notes card, so the page doesn't move when it loads -->
          <panel-box v-if="patchNotesUrl" title="Patch Notes" :skeleton="true" :divider="false" class="md:!py-4">
            <template #right>
              <span class="text-sm skeleton-text">{{ $t('Show') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
            </template>
          </panel-box>
          <panel-box title="Talent stats" subtitle="Win rate, popularity and games for every talent. The best win rate on each tier is highlighted." :skeleton="true" class="relative md:!rounded-tl-none">
            <loading-component v-if="determineIfVeryLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Very large amount of data. Compiling can take 5-10 minutes. Please be patient.') }}</loading-component>
            <loading-component v-else-if="determineIfLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Large amount of data. Please be patient.') }}</loading-component>
            <loading-component v-else :compact="true" :tab="true" :textoverride="true">{{ $t('Loading talent stats...') }}</loading-component>
            <compact-talent-grid :skeleton="true" :layout-style="talentstatsstyle"></compact-talent-grid>
          </panel-box>
        </div>
        <div v-else-if="dataError" class="flex items-center justify-center">
          {{ $t('Error: Reload page/filter') }}
        </div>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="4" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>


        <div v-if="talentbuilddata" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 mt-6">
          <panel-box id="builds" title="Talent builds" subtitle="Copy a build code and paste it in game chat." class="scroll-mt-4">
            <template #right>
              <div class="w-full sm:w-[11rem]">
                <single-select-filter :values="buildtypes" :text="'Build type'" :compact="true" :nosearch="true" :defaultValue="talentbuildtype" @input-changed="buildtypechange"></single-select-filter>
              </div>
            </template>
            <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="talentbuildtype" :statfilter="statfilter" :hero-win-rate="heroWinRate"></global-talent-builds-section>
            <div class="flex flex-wrap items-center justify-between gap-4">
              <span class="text-sm text-subtext">{{ $t('Want something else? Make your own build in the Talent Builder.') }}</span>
              <custom-button :href="talentBuilderUrl()" :text="'Open Talent Builder'" :alt="'Open Talent Builder'" color="teal" :targetblank="true"></custom-button>
            </div>
          </panel-box>
        </div>
        <div v-else-if="!isTalentsLoading && dataError" class="flex items-center justify-center">
          {{ $t('Error: Reload page/filter') }}
        </div>

        <!-- The loaded card in grey: the table from md up, a card per build on phones; the message on its tab -->
        <div v-else-if="isBuildsLoading" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 mt-6">
          <panel-box title="Talent builds" subtitle="Copy a build code and paste it in game chat." :skeleton="true" class="relative md:!rounded-tl-none">
            <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading talent builds...') }}</loading-component>
            <table-skeleton class="max-md:hidden" :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
            <div class="md:hidden flex flex-col gap-2">
              <div v-for="row in 5" :key="row" class="rounded-control bg-surface-1 p-3 flex flex-col gap-3">
                <div class="flex gap-1.5 overflow-clip"><skeleton-box v-for="talent in 7" :key="talent" class="w-[2.625rem] h-[2.625rem] shrink-0 rounded-lg"></skeleton-box></div>
                <div class="grid grid-cols-3 gap-2">
                  <span v-for="stat in 3" :key="stat" class="flex flex-col gap-1"><skeleton-box class="h-3 w-12"></skeleton-box><skeleton-box class="h-4 w-16"></skeleton-box></span>
                </div>
                <skeleton-box class="h-8 w-40 rounded"></skeleton-box>
              </div>
            </div>
          </panel-box>
        </div>
      </div>
  </div>
</div>
</template>

<script>
  import CompactTalentGrid from './CompactTalentGrid.vue';
  import { scaleTableToFit } from '../../../utils/scaleTable';

  export default {
    name: 'GlobalTalentsStats',
    components: {
      CompactTalentGrid,
    },
    props: {
      filters: Object,
      inputhero: Object,
      heroes: Array,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
      defaultbuildtype: String,
      talentimages: Object,
      advancedfiltering: Boolean,
      talentstatsstyle: { type: String, default: null },
      patreonUser: Boolean,
      urlparameters: Object,
      initialtalentdata: { type: [Object, Array], default: null },
      initialbuilddata: { type: [Object, Array], default: null },
    },
    data(){
      return {
        dataError: false,
       isTalentsLoading: false,
       isBuildsLoading: false,
       cancelTalentsTokenSource: null,
       cancelBuildsTokenSource: null,
       infoText: "Talent win rates and Talent Builds based on patches, hero, hero level, game type, game map, or Rank.",
       selectedHero: null,
       talentdetaildata: null,
       talentbuilddata: null,
       talentbuilddataall: null,
       heroSearchQuery: "",
       heroRole: 'All',
       buildtypes: [
        { code: 'Popular', name: 'Popular' },
        { code: 'HP Algorithm', name: 'HP Algorithm' },
        { code: 'Unique Lvl 1', name: 'Unique Lvl 1' },
        { code: 'Unique Lvl 4', name: 'Unique Lvl 4' },
        { code: 'Unique Lvl 7', name: 'Unique Lvl 7' },
        { code: 'Unique Lvl 10', name: 'Unique Lvl 10' },
        { code: 'Unique Lvl 13', name: 'Unique Lvl 13' },
        { code: 'Unique Lvl 16', name: 'Unique Lvl 16' },
        { code: 'Unique Lvl 20', name: 'Unique Lvl 20' }
        ],

      //Sending to filter
       timeframetype: null,
       timeframe: null,
       region: null,
       statfilter: "win_rate",
       herolevel: null,
       role: null,
       hero: null,
       gametype: null,
       gamemap: null,
       playerrank: null,
       herorank: null,
       rolerank: null,
       mirrormatch: 0,
       ranksource: 'hp',
       slrank: null,
       talentbuildtype: "Popular",
       tablewidth: null,
       filtersKey: 0,
     }
   },
   created(){
    this.timeframe = this.defaulttimeframe;
    this.gametype = this.gametypedefault;
    this.talentbuildtype = this.defaultbuildtype;
    this.timeframetype = this.defaulttimeframetype;

    if(this.urlparameters){
      this.setURLParameters();
    }
    this.preloadTalentImages(this.inputhero);
    

    if(this.inputhero){
      this.selectedHero = this.inputhero;
      // Server hands over the cached default view so it renders without the API round trip.
      if(this.initialtalentdata){
        this.talentdetaildata = this.initialtalentdata;
      }else{
        this.getTalentData();
      }
      if(this.initialbuilddata){
        this.talentbuilddata = this.initialbuilddata;
      }else{
        this.getTalentBuildData();
      }
    }
   },
    mounted() {
      if (this.$refs.buildsskeleton) {
        scaleTableToFit(this.$refs.buildsskeleton, this.$refs.buildsskeletonbox);
      }
      window.addEventListener('popstate', this.restoreHistoryState);
      history.replaceState(this.historyState(), '');
      if(!this.inputhero){
        this.getTalentBuildDataAll();
      }
    },
    beforeUnmount() {
      window.removeEventListener('popstate', this.restoreHistoryState);
    },
    computed: {
      isSLRank() {
        return this.ranksource === 'sl';
      },
      filteredHeroesForTable() {
        const q = this.heroSearchQuery.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();
        return this.heroes.filter(h => (this.heroRole === 'All' || h.new_role === this.heroRole)
          && (!q || [h.name, this.$heroName(h)].some(name => name.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase().includes(q))));
      },
      // The hero's win rate in these filters: every game has one level 1 talent, so level 1's talents cover them all
      heroWinRate() {
        const talents = this.talentdetaildata?.[1] || [];
        const games = talents.reduce((sum, talent) => sum + (Number(talent.games_played) || 0), 0);
        if (!games) return null;
        return talents.reduce((sum, talent) => sum + (Number(talent.win_rate) || 0) * (Number(talent.games_played) || 0), 0) / games;
      },
      patchNotesUrl() {
        if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
          return null;
        }
        const selectedVersion = this.timeframe[0];
        const match = this.filters.timeframes.find(t => t.code === selectedVersion);
        return match && match.patch_notes_url ? match.patch_notes_url : null;
      },
    },
    watch: {
    },
    methods: {
      clickedHero(hero) {
        this.selectedHero = hero;
        this.talentdetaildata = null;
        this.talentbuilddata = null;
        this.preloadTalentImages(hero);

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Talent Stats & Builds | Heroes Profile`;

        let basePath = '/Global/Talents';
        history.pushState(this.historyState(), '', `${basePath}/${this.selectedHero.name}`);
        Promise.allSettled([
          this.getTalentData(),
          this.getTalentBuildData(),
          ]).then(results => {
        });

   
      },
      async getTalentData(){
        this.dataError = false;
        this.isTalentsLoading = true;

        if (this.cancelTalentsTokenSource) {
          this.cancelTalentsTokenSource.cancel('Request canceled');
        }
        this.cancelTalentsTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            statfilter: this.statfilter,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
            rank_source: this.isSLRank ? 'sl' : null,
            sl_rank: this.isSLRank ? this.slrank : null,
          },
          {
            cancelToken: this.cancelTalentsTokenSource.token,
          });
          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          this.talentdetaildata = response.data;

        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTalentsTokenSource = null;
          this.isTalentsLoading = false;
          
        }
      },
      
      async getTalentBuildData(){
        this.dataError = false;
        this.isBuildsLoading = true;

        if (this.cancelBuildsTokenSource) {
          this.cancelBuildsTokenSource.cancel('Request canceled');
        }
        this.cancelBuildsTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents/build", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            statfilter: this.statfilter,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
            talentbuildtype: this.talentbuildtype,
            rank_source: this.isSLRank ? 'sl' : null,
            sl_rank: this.isSLRank ? this.slrank : null,
          },
          {
            cancelToken: this.cancelBuildsTokenSource.token,
          });

          
          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          
          this.talentbuilddata = response.data;

        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelBuildsTokenSource = null;
          this.isBuildsLoading = false;
        }
      },

      async getTalentBuildDataAll(){
        this.dataError = false;
        this.isBuildsLoading = true;

        if (this.cancelBuildsTokenSource) {
          this.cancelBuildsTokenSource.cancel('Request canceled');
        }
        this.cancelBuildsTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents/build/all", {},
          {
            cancelToken: this.cancelBuildsTokenSource.token,
          });

          
          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          
          this.talentbuilddataall = response.data;

        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelBuildsTokenSource = null;
          this.isBuildsLoading = false;
        }
      },


      cancelAxiosRequest() {
        if (this.cancelTalentsTokenSource || this.cancelBuildsTokenSource) {
          this.cancelTalentsTokenSource.cancel('Request canceled by user');
          this.cancelBuildsTokenSource.cancel('Request canceled by user');
        }
      },
      filterData(filteredData){
        const heroId = filteredData.single.Heroes;
        if (heroId && heroId !== this.selectedHero.id) {
          this.selectedHero = this.heroes.find(hero => hero.id === heroId) || this.selectedHero;
          this.preloadTalentImages(this.selectedHero);
          document.title = `${this.selectedHero.name} Talent Stats & Builds | Heroes Profile`;
          history.pushState(null, null, `/Global/Talents/${this.selectedHero.name}${window.location.search}`);
        }
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : "win_rate";
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
        this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.defaultbuildtype;
        this.ranksource = filteredData.single["Rank Source"] ? filteredData.single["Rank Source"] : 'hp';
        this.slrank = this.isSLRank && filteredData.multi["SL Rank"] ? Array.from(filteredData.multi["SL Rank"]) : null;

        this.talentdetaildata = null;
        this.talentbuilddata  = null;
        this.talentbuilddataall  = null;

        this.updateQueryString();
        this.getTalentData();
        this.getTalentBuildData();
      },
      buildtypechange(eventPayload){
        this.talentbuildtype = eventPayload.value;
        this.talentbuilddata  = null;

        this.updateQueryString();
        this.getTalentBuildData();
      },
      // The Builder takes the same filters, so they come along
      talentBuilderUrl(){
        return `/Global/Talents/Builder/${this.selectedHero.name}${window.location.search}`;
      },
      updateQueryString(path = window.location.pathname){
        let queryString = `?timeframe_type=${this.timeframetype}`;

        if(this.timeframe){
          queryString += `&timeframe=${this.timeframe}`;
        }
        queryString += `&game_type=${this.gametype}`;

        if(this.region){
          queryString += `&region=${this.region}`;
        }

        if(this.herolevel){
          queryString += `&hero_level=${this.herolevel}`;
        }

        if(this.gamemap){
          queryString += `&game_map=${this.gamemap}`;
        }

        if(this.playerrank){
          queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
        }

        if(this.herorank){
          queryString += `&hero_league_tier=${this.convertRankIDtoName(this.herorank)}`;
        }

        if(this.rolerank){
          queryString += `&role_league_tier=${this.convertRankIDtoName(this.rolerank)}`;
        }

        if(this.isSLRank){
          queryString += `&rank_source=sl`;

          if(this.slrank){
            queryString += `&sl_rank=${this.slrank}`;
          }
        }

        queryString += `&statfilter=${this.statfilter}`;
        queryString += `&build_type=${this.talentbuildtype}`;
        queryString += `&mirror=${this.mirrormatch}`;

        history.pushState(this.historyState(), '', `${path}${queryString}`);
      },
      // Saved with each history entry so back/forward can put the page back without a reload.
      historyState(){
        return JSON.parse(JSON.stringify({
          hero: this.selectedHero ? this.selectedHero.name : null,
          timeframetype: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          herolevel: this.herolevel,
          gametype: this.gametype,
          gamemap: this.gamemap,
          playerrank: this.playerrank,
          herorank: this.herorank,
          rolerank: this.rolerank,
          mirrormatch: this.mirrormatch,
          ranksource: this.ranksource,
          slrank: this.slrank,
          talentbuildtype: this.talentbuildtype,
        }));
      },
      restoreHistoryState(event){
        const state = event.state;
        if(!state){
          window.location.reload();
          return;
        }

        this.timeframetype = state.timeframetype;
        this.timeframe = state.timeframe;
        this.region = state.region;
        this.statfilter = state.statfilter;
        this.herolevel = state.herolevel;
        this.gametype = state.gametype;
        this.gamemap = state.gamemap;
        this.playerrank = state.playerrank;
        this.herorank = state.herorank;
        this.rolerank = state.rolerank;
        this.mirrormatch = state.mirrormatch;
        this.ranksource = state.ranksource || 'hp';
        this.slrank = state.slrank || null;
        this.talentbuildtype = state.talentbuildtype;
        this.selectedHero = state.hero ? this.heroes.find(hero => hero.name === state.hero) : null;

        this.talentdetaildata = null;
        this.talentbuilddata = null;
        this.filtersKey++;

        if(this.selectedHero){
          document.title = `${this.selectedHero.name} Talent Stats & Builds | Heroes Profile`;
          this.preloadTalentImages(this.selectedHero);
          this.getTalentData();
          this.getTalentBuildData();
        }else{
          document.title = 'Talent Stats & Builds | Heroes Profile';
          if(!this.talentbuilddataall){
            this.getTalentBuildDataAll();
          }
        }
      },
      preloadTalentImages(hero) {
        if(hero){
          this.talentimages[hero.name].forEach((image) => {
            const img = new Image();
            img.src = image;
          });
        }
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframetype == "major_grouped" || this.timeframe.length >= 3 || this.statfilter != "win_rate"){
          return  true;
        }
        return false;
      },
      determineIfVeryLargeData(){
        if((this.timeframetype == "major" || this.timeframetype == "major_grouped") && this.timeframe && this.timeframe.length > 1){
          return true;
        }
        return false;
      },
      redirectChangeHero(){
        window.location.href = "/Global/Talents";
      },
      convertRankIDtoName(rankIDs) {
        return rankIDs.map(rankID => this.filters.rank_tiers.find(tier => tier.code == rankID).name);
      },
      setURLParameters(){
        if(this.urlparameters["timeframe_type"]){
          this.timeframetype = this.urlparameters["timeframe_type"];
        }
        
        if(this.urlparameters["timeframe"]){
          this.timeframe = this.urlparameters["timeframe"].split(',');
        }

        if(this.urlparameters["game_type"]){
          this.gametype = this.urlparameters["game_type"].split(',');
        }

        if(this.urlparameters["region"]){
          this.region = this.urlparameters["region"].split(',');
        }

        if(this.urlparameters["statfilter"]){
          this.statfilter = this.urlparameters["statfilter"];
        }
        
        if(this.urlparameters["hero_level"]){
          this.herolevel = this.urlparameters["hero_level"].split(',');
        }

        if(this.urlparameters["game_map"]){
          this.gamemap = this.urlparameters["game_map"].split(',');
        }

        if (this.urlparameters["league_tier"]) {
          this.playerrank = this.urlparameters["league_tier"]
            .split(',')
            .map(tierName => {
                const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
                const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
                return tier?.code;
            });
        }

        if (this.urlparameters["hero_league_tier"]) {
          this.herorank = this.urlparameters["hero_league_tier"]
          .split(',')
          .map(tierName => {
              const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
              const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
              return tier?.code;
          });
        }

        if (this.urlparameters["role_league_tier"]) {
          this.rolerank = this.urlparameters["role_league_tier"]
          .split(',')
          .map(tierName => {
              const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
              const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
              return tier?.code;
          });
        }


        if (this.urlparameters["build_type"]) {
          this.talentbuildtype = this.urlparameters["build_type"];
        }

        if (this.urlparameters["mirror"]) {
          this.mirrormatch = this.urlparameters["mirror"];
        }

        if (this.urlparameters["rank_source"]) {
          this.ranksource = this.urlparameters["rank_source"];
        }

        if (this.isSLRank) {
          this.gametype = ['sl'];
        }

        if (this.urlparameters["sl_rank"] && this.isSLRank) {
          this.slrank = this.urlparameters["sl_rank"].split(',');
        }
      },
      handleInputChange(eventPayload){
        if(eventPayload.value != ""){
          this.selectedHero = this.heroes.find(hero => hero.id === eventPayload.value);
          this.preloadTalentImages(this.selectedHero);

          // Update document title dynamically
          document.title = `${this.selectedHero.name} Talent Stats & Builds | Heroes Profile`;

          this.updateQueryString(`/Global/Talents/${this.selectedHero.name}`);

          this.talentdetaildata = null;
          this.talentbuilddata = null;

          //Have to use setTimeout to make this occur on next tic to allow header info/text to update properly.  
          setTimeout(() => {
              Promise.allSettled([
                  this.getTalentData(),
                  this.getTalentBuildData(),
              ]).then(results => {
              });
          }, 0);

        }

      },
    }
  }
</script>
