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
          :gametypedefault="gametypedefault"
          :includetimeframetype="true"
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
        <takeover-ad :patreon-user="patreonUser" ref="takeoverAddPlacement"></takeover-ad>

        
        <div  v-if="talentdetaildata" class="mx-auto  px-4">
          <div class="flex justify-between max-w-[1500px] mx-auto">
            
            <span class="flex gap-4 mb-2"> 
              {{ this.selectedHero.name }} {{ "Talent Stats"}} <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button>
            </span>
            <span><custom-button @click="scrollToBuilds" :text="'Scroll To Builds'" :alt="'Scroll To Builds'" size="small" :ignoreclick="true"></custom-button></span>
          </div>
          
            
              <global-talent-details-section class="mx-auto" :talentdetaildata="talentdetaildata" :statfilter="statfilter" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
           
         
        </div>

        <div v-else-if="isTalentsLoading">
          <loading-component v-if="determineIfLargeData()" @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
          <loading-component v-else @cancel-request="cancelAxiosRequest"></loading-component>
        </div>


        <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

        <div  v-if="talentbuilddata" class="flex justify-between max-w-[1500px] mx-auto px-4">
          <div id="builds" class="">
            <single-select-filter :values="buildtypes" :text="'Talent Build Type'" :defaultValue="this.talentbuildtype" @input-changed="buildtypechange"></single-select-filter>
            {{ this.selectedHero.name }} {{ "Talent Builds"}}
            <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="talentbuildtype" :statfilter="statfilter" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
          </div>
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
    },
    data(){
      return {
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
    this.preloadTalentImages(this.inputhero);
   },
   mounted() {
    this.timeframe = this.defaulttimeframe;
    this.gametype = this.gametypedefault;
    this.talentbuildtype = this.defaultbuildtype;
    this.timeframetype = this.defaulttimeframetype;

    if(this.inputhero){
      this.selectedHero = this.inputhero;
      Promise.allSettled([
        this.getTalentData(),
        this.getTalentBuildData(),
        ]).then(results => {
      });
      }
    },
    computed: {

    },
    watch: {
    },
    methods: {
      clickedHero(hero) {
        this.selectedHero = hero;
        this.preloadTalentImages(hero);

        //This isnt working
        if(!this.patreonUser){
          this.$nextTick(() => {
            (window.top).__vm_add = (window.top).__vm_add || [];
            (window.top).__vm_add.push(this.$refs.takeoverAddPlacement);
            //(window.top).__vm_add.push(this.$refs.dynamicAddPlacement);
          });
        }
   


        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        Promise.allSettled([
          this.getTalentData(),
          this.getTalentBuildData(),
          ]).then(results => {
        });

   
      },
      async getTalentData(){
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
          this.talentdetaildata = response.data;
        }catch(error){
          //Do something here
        }finally {
          this.cancelTalentsTokenSource = null;
          this.isTalentsLoading = false;
          
        }
      },
      
      async getTalentBuildData(){
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
          this.talentbuilddata = response.data;
        }catch(error){
          //Do something here
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
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : "win_rate";
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
        this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.defaultbuildtype;


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
          queryString += `&league_tier=${this.playerrank}`;
        }

        if(this.herorank){
          queryString += `&hero_league_tier=${this.herorank}`;
        }

        if(this.rolerank){
          queryString += `&role_league_tier=${this.rolerank}`;
        }

        queryString += `&statfilter=${this.statfilter}`;
        queryString += `&build_type=${this.talentbuildtype}`;
        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);

        this.talentdetaildata = null;
        this.talentbuilddata  = null;

        this.getTalentData();
        this.getTalentBuildData();
      },
      buildtypechange(eventPayload){
        this.talentbuildtype = eventPayload.value;
        this.talentbuilddata  = null;
        this.getTalentBuildData();
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
        if(this.timeframetype == "major" || this.timeframe.length >= 3 || this.statfilter != "win_rate"){
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
    }
  }
</script>