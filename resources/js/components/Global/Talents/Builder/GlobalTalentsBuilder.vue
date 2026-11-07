<template>
  <div>
    <global-async-debug-banner page-label="Global Talent Builder" />
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" :infoText2="infoText2" -->
    <page-heading :heading="selectedHero ? selectedHero.name + ' Talent Calculator & Builder' : 'Talent Calculator & Builder'"></page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :herolevelinput="herolevel"
      :gamemapinput="gamemap"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :rolerankinput="rolerank"
      :mirrormatchinput="mirrormatch"

      :gametypedefault="gametypedefault"
      :includetimeframe="true"
      :includeregion="true"
      :includeherolevel="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

  </div>


  <div v-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
    <loading-component v-if="determineIfVeryLargeData()" :compact="true" :textoverride="true">Very large amount of data. Compiling can take 5-10 minutes. Please be patient.</loading-component>
    <loading-component v-else-if="determineIfLargeData()" :compact="true" :textoverride="true">Large amount of data. Please be patient.</loading-component>
    <loading-component v-else :compact="true" :textoverride="true">Loading talents...</loading-component>
    <table-skeleton :rows="7"></table-skeleton>
  </div>
  <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
  </div>
  <div v-if="data && !isLoading">
    <div class="flex justify-between max-w-[120rem] mx-auto px-4 md:px-8 max-md:flex-wrap max-md:gap-2">
        <span class="flex gap-4 mb-2"> 
          <single-select-filter
            :values="filters.heroes" 
            :text="'Change Hero'" 
            :defaultValue="selectedHero.id"
            @input-changed="handleInputChange"
          ></single-select-filter>
          <span @click="resetTalentData" class="mt-auto ml-auto link text-sm">Reset Talent data</span>
        </span>
        <span class="flex items-center gap-4">
          <tab-button :tab1text="'Auto Load'" :tab2text="'On Filter'" :ignoreclick="true" @tab-click="loadStyleSelected" :overridedefaultside="localLoad === 'filter' ? 'right' : 'left'"></tab-button>
          <tab-button v-if="!isMobile" :tab1text="'Vertical'" :tab2text="'Horizontal'" :ignoreclick="true" @tab-click="layoutStyleSelected" :overridedefaultside="effectiveStyle === 'horizontal' ? 'right' : 'left'"></tab-button>
          <custom-button v-if="patchNotesUrl" @click="togglePatchNotes" :text="timeframe[0] + ' Patch Notes'" :alt="timeframe[0] + ' Patch Notes'" size="small" :ignoreclick="true"></custom-button>
        </span>
        



      </div>

    <patch-notes-panel v-if="showPatchNotes && patchNotesUrl" :version="timeframe[0]"></patch-notes-panel>

    <!-- The chart takes the talent columns' width -->
    <div :class="effectiveStyle === 'horizontal' ? 'w-fit mx-auto' : 'max-w-[93.75rem] mx-auto'">
    <talent-build-paths v-if="paths && paths.length" :data="data" :paths="paths" v-model:open="pathsOpen"></talent-build-paths>

    <div :class="effectiveStyle === 'horizontal' ? 'flex px-3 gap-5 mx-auto justify-center' : 'flex px-3 gap-5 mx-auto justify-center flex-wrap flex-col max-w-[93.75rem]'">
      <talent-builder-column :data="data['1']" :level="1" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
      <talent-builder-column :data="data['4']" :level="4" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
      <talent-builder-column :data="data['7']" :level="7" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
      <talent-builder-column :data="data['10']" :level="10" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
      <talent-builder-column :data="data['13']" :level="13" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
      <talent-builder-column :data="data['16']" :level="16" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
      <talent-builder-column :data="data['20']" :level="20" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'"></talent-builder-column>
    </div>
    </div>
    <div class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 flex flex-col gap-6">
    <panel-box title="Selected Build">
      <template #title>
        <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
          <p class="font-normal">Build win rate and how many players have played that exact build. Win rate for builds to level 20 are inflated because teams that make it to level 20 win more. Therefore it is not an accurate representation of a builds viabllity. See table below for that calculation</p>
          <p class="font-normal mt-2">Calculated build win chance tries to gauge what the builds win rate will be at any point during the game.</p>
        </round-image>
      </template>
    <table v-if="builddata" class="min-w-0 w-full responsive-table">
      <thead>
        <tr>
          <th class="py-2 px-3 text-center text-sm leading-4">
            Talents
          </th>
          <th class="py-2 px-3 text-center text-sm leading-4">
            Copy Build to Game
          </th>
          <th class="py-2 px-3 text-center text-sm leading-4">
            Games Played with Build
          </th>
          <th class="py-2 px-3 text-center text-sm leading-4">
            Win rate
          </th>
        </tr>
      </thead>
      <tbody class="text-center">
        <tr>
          <td>
            <div class="flex gap-x-1 mx-2 items-center justify-center">
              <talent-image-wrapper v-if="builddata.level_one" :talent="builddata.level_one" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_four" :talent="builddata.level_four" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_seven" :talent="builddata.level_seven" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_ten" :talent="builddata.level_ten" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_thirteen" :talent="builddata.level_thirteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_sixteen" :talent="builddata.level_sixteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_twenty" :talent="builddata.level_twenty" :size="'medium'"></talent-image-wrapper>
            </div>
          </td>
          <td class="py-2 px-3 ">
            <build-code :build="builddata" :hero="selectedHero" show="code" align="start"></build-code>
          </td>
          <td>
            {{ builddata.games_played.toLocaleString('en-US') }}
          </td>
          <td>
            {{ builddata.win_rate.toFixed(2) }}
          </td>
        </tr>
      </tbody>
    </table>
    </panel-box>

    <panel-box title="Matching Replays">
      <template #title>
        <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
          <p class="font-normal">Possible Replays do not take into account Hero Level, Player Rank, Hero Rank, Role Rank, or Mirror Match Filter options</p>
        </round-image>
      </template>
    <div>
      <custom-button @click="showReplayList = !showReplayList" :text="'Get list of replays matching talent selection'" :alt="'Get list of replays matching talent selection'" size="small" :ignoreclick="true"></custom-button>
    </div>
    <table class="min-w-0 w-full responsive-table" v-if="showReplayList">
      <thead>
        <tr>
          <th class="py-2 px-3 text-center text-sm leading-4 w-[12.5rem]">
            Match ID
          </th>
          <th class="py-2 px-3 text-center text-sm leading-4">
            Talents
          </th>
        </tr>
      </thead>
      <tbody class="text-center">
        <tr v-for="(row, index) in replays" :key="index">
          <td>
            <a class="group/bt inline-flex items-center" :href="`/Match/Single/${row.replayID}`"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
          </td>
          <td>
            <div class="flex gap-x-1 mx-2 items-center justify-center">
              <talent-image-wrapper :talent="row.level_one" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_four" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_seven" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_ten" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_thirteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_sixteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_twenty" :size="'medium'"></talent-image-wrapper>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    </panel-box>
    </div>
  </div>


</div>
</template>

<script>
  import Cookies from 'js-cookie';

  export default {
    name: 'GlobalTalentsBuilder',
    components: {
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
      patreonUser: Boolean,
      urlparameters: Object,
      talentbuilderstyle: { type: String, default: 'vertical' },
      talentbuilderload: { type: String, default: 'auto' },
      authenticated: Boolean,
    },
    data(){
      return {
        dataError: false,
        cancelTokenSource: null,
        isLoading: false,
        selectedHero: null,
        data: null,
        replays: null,
        builddata: null,
        paths: null,
        pathsOpen: false,
        infoText1: "Pick a talent in any tier below to start. The tool will calculate talent win rates dynamically as you change your build choices.",
        infoText2: "Build win chance and win rate, along with games played by the build can be found below the talents.",
        clickedData: {
          1: null,
          4: null,
          7: null,
          10: null,
          13: null,
          16: null,
          20: null,
        },

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        role: null,
        hero: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,
        showReplayList: false,
        isMobile: window.innerWidth < 1024,
        localStyle: 'vertical',
        localLoad: 'auto',
        showPatchNotes: false,
      }
    },
    created(){
      this.localStyle = this.talentbuilderstyle;
      this.localLoad = this.talentbuilderload;
      this.selectedHero = this.inputhero;
      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      
      if(this.selectedHero){
        this.getData();
      }
    },
    mounted() {
      this._onResize = () => { this.isMobile = window.innerWidth < 1024; };
      window.addEventListener('resize', this._onResize);
    },
    beforeUnmount() {
      window.removeEventListener('resize', this._onResize);
    },
    computed: {
      effectiveStyle() {
        return this.isMobile ? 'vertical' : this.localStyle;
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
      showReplayList(newValue) {
        if (newValue) {
          this.getReplayData();
        } 
      }    
    },
    methods: {
      layoutStyleSelected(side) {
        this.localStyle = side === 'right' ? 'horizontal' : 'vertical';
        Cookies.set('talentbuilderstyle', this.localStyle, { expires: 365, path: '/' });

        if (this.authenticated) {
          this.$axios.post('/api/v1/profile/save/settings', { talentbuilderstyle: this.localStyle }).catch(() => {});
        }
      },
      loadStyleSelected(side) {
        this.localLoad = side === 'right' ? 'filter' : 'auto';
        Cookies.set('talentbuilderload', this.localLoad, { expires: 365, path: '/' });

        if (this.authenticated) {
          this.$axios.post('/api/v1/profile/save/settings', { talentbuilderload: this.localLoad }).catch(() => {});
        }
      },
      async getData(){
        if(this.showReplayList){
          this.showReplayList = false;
        }
        this.dataError = false;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
      
        this.replays = null;
        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents/builder", {
            hero: this.selectedHero.name,
            selectedtalents: this.clickedData,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
            build_paths: true,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }

          this.data = response.data.talentData;
          this.replays = response.data.replays;
          this.builddata = response.data.buildData;
          this.paths = response.data.paths;
        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
        }
      },
      async getReplayData(){
        this.dataError = false;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
      
        this.replays = null;
        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents/builder/replays", {
            hero: this.selectedHero.name,
            selectedtalents: this.clickedData,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          
          this.replays = response.data;
        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
        }
      },
      clearTalentSelections() {
        this.clickedData = {
          1: null,
          4: null,
          7: null,
          10: null,
          13: null,
          16: null,
          20: null,
        };
      },
      resetTalentData() {
        this.clearTalentSelections();
        if (this.localLoad === 'auto') {
          this.getData();
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      togglePatchNotes() {
        this.showPatchNotes = !this.showPatchNotes;
      },
      talentClicked(talent, index, level){
        this.clickedData[level] = talent.talent_id;
        if (this.localLoad === 'auto') {
          this.getData();
        }
      },

      clickedHero(hero) {
        this.selectedHero = hero;
        this.preloadTalentImages(hero);

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Talent Builder | Heroes Profile`;

        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);

        this.getData();
      },
      preloadTalentImages(hero) {
        if(hero){
          this.talentimages[hero.name].forEach((image) => {
            const img = new Image();
            img.src = image;
          });
        }
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
        this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.defaultbuildtype;

        this.updateQueryString();




        this.showPatchNotes = false;

        this.data = null;
        this.replays = null;
        this.builddata = null;
        this.getData();
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframetype == "major_grouped"  || this.timeframe.length >= 3){
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
      removeLevelSelections(level){
        this.clickedData[level] = null;

        if (this.localLoad === 'filter') {
          return;
        }

        this.data = null;
        this.replays = null;
        this.builddata = null;

        this.getData();
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

        if(this.urlparameters["hero"]){
          this.hero = this.urlparameters["hero"];
        }

        if(this.urlparameters["role"]){
          this.role = this.urlparameters["role"];
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
      },
      handleInputChange(eventPayload){
        if(eventPayload.value != ""){
          this.selectedHero = this.heroes.find(hero => hero.id === eventPayload.value);

          // Update document title dynamically
          document.title = `${this.selectedHero.name} Talent Builder | Heroes Profile`;

          let currentPath = window.location.pathname;
          let newPath = currentPath.replace(/\/[^/]*$/, `/${this.selectedHero.name}`);
          history.pushState(null, null, newPath);
          this.updateQueryString();

          this.data = null;

          //Have to use setTimeout to make this occur on next tic to allow header info/text to update properly.  
          setTimeout(() => {
            this.clearTalentSelections();
            this.getData();
          }, .25);
        }
      },
      updateQueryString(){
        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
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

        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);
      },
    }
  }
</script>