<template>
  <div>

    <div class="grid gap-5 grid-cols-1">
      <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Talent Statistics' : 'Hero Talent Statistics'">
        <hero-image-wrapper v-if="selectedHero" :hero="selectedHero" :size="'big'"></hero-image-wrapper>
      </page-heading>


    
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>

        <div v-else>
          <filters 
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




          :gametypedefault="gametypedefault"
          :includetimeframetypewithlastupdate="true"
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
          :advancedfiltering="advancedfiltering"

          >
        </filters>
        <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

  
        <div v-if="talentdetaildata" class="mx-auto  md:px-4">
          <div class="flex justify-between max-w-[1500px] mx-auto">
            <span class="flex gap-4 mb-2"> 
              <single-select-filter
                :values="filters.heroes" 
                :text="'Change Hero'" 
                :defaultValue="selectedHero.id"
                @input-changed="handleInputChange"
              ></single-select-filter>
            </span>
            <span><custom-button @click="scrollToBuilds" :text="'Scroll To Builds'" :alt="'Scroll To Builds'" size="small" :ignoreclick="true"></custom-button></span>
          </div>
          <global-talent-details-section class="mx-auto" :talentdetaildata="talentdetaildata" :statfilter="statfilter" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
        </div>
        <div v-else-if="isTalentsLoading">
          <loading-component v-if="determineIfLargeData()" @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
          <loading-component v-else @cancel-request="cancelAxiosRequest"></loading-component>
        </div>
        <div v-else-if="dataError" class="flex items-center justify-center">
          Error: Reload page/filter
        </div>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="4" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>


        <div v-if="talentbuilddata" class="flex justify-between max-w-[1500px] mx-auto md:px-4">
          <div id="builds" class="">
            <single-select-filter :values="buildtypes" :text="'Talent Build Type'" :defaultValue="this.talentbuildtype" @input-changed="buildtypechange"></single-select-filter>
            {{ this.selectedHero.name }} {{ "Talent Builds"}}
            <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="talentbuildtype" :statfilter="statfilter" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
          </div>
        </div>
        <div v-else-if="!isTalentsLoading && dataError" class="flex items-center justify-center">
          Error: Reload page/filter
        </div>

        <div v-else-if="isBuildsLoading">
          <loading-component v-if="determineIfLargeData()" @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
          <loading-component v-else @cancel-request="cancelAxiosRequest"></loading-component>
        </div>
      </div>
  </div>
</div>
</template>

<script>
  export default {
    name: 'GlobalTalentsStats',
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

    },
    data(){
      return {
        dataError: false,
       windowWidth: window.innerWidth,
       isTalentsLoading: false,
       isBuildsLoading: false,
       cancelTalentsTokenSource: null,
       cancelBuildsTokenSource: null,
       infoText: "Talent win rates and Talent Builds based on patches, hero, hero level, game type, game map, or Rank.",
       selectedHero: null,
       talentdetaildata: null,
       talentbuilddata: null,
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
       talentbuildtype: "Popular",
       tablewidth: null
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
      Promise.allSettled([
        this.getTalentData(),
        this.getTalentBuildData(),
        ]).then(results => {
      });
    }
   },
    mounted() {

    },
    computed: {

    },
    watch: {
    },
    methods: {
      clickedHero(hero) {
        this.selectedHero = hero;
        this.preloadTalentImages(hero);

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Talent Stats & Builds | Heroes Profile`;

        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
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
          const response = await this.$axios.post("/api/v1/global/talents", {
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
            mirrormatch: this.mirrormatch,
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
          const response = await this.$axios.post("/api/v1/global/talents/build", {
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
            mirrormatch: this.mirrormatch,
            talentbuildtype: this.talentbuildtype
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
      cancelAxiosRequest() {
        if (this.cancelTalentsTokenSource || this.cancelBuildsTokenSource) {
          this.cancelTalentsTokenSource.cancel('Request canceled by user');
          this.cancelBuildsTokenSource.cancel('Request canceled by user');
        }
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): (this.timeframetype == "last_update" ? null : this.defaulttimeframe);
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

        this.talentdetaildata = null;
        this.talentbuilddata  = null;

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
      updateQueryString(){
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

        queryString += `&statfilter=${this.statfilter}`;
        queryString += `&build_type=${this.talentbuildtype}`;
        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);
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
        if(this.timeframetype == "major" || this.timeframetype == "major_grouped" || (this.timeframetype == "last_update" || this.timeframe.length >= 3) || this.statfilter != "win_rate"){
          return  true;
        }
        return false;
      },
      redirectChangeHero(){
        window.location.href = "/Global/Talents";
      },
      scrollToBuilds(){
        const buildsSection = document.getElementById('builds');
        if (buildsSection) {
          buildsSection.scrollIntoView({ behavior: 'smooth' });
        }
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
      },
      handleInputChange(eventPayload){
        if(eventPayload.value != ""){
          this.selectedHero = this.heroes.find(hero => hero.id === eventPayload.value);
          this.preloadTalentImages(this.selectedHero);

          // Update document title dynamically
          document.title = `${this.selectedHero.name} Talent Stats & Builds | Heroes Profile`;

          let currentPath = window.location.pathname;
          let newPath = currentPath.replace(/\/[^/]*$/, `/${this.selectedHero.name}`);
          history.pushState(null, null, newPath);
          this.updateQueryString();

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