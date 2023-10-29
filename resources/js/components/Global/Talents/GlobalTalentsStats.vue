<template>
  <div>

    <div class="grid gap-5 grid-cols-1">
      <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Talent Statistics' : 'Hero Talent Statistics'"></page-heading>


    
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>

        <div v-else>
          <filters 
          :onFilter="filterData" 
          :filters="filters" 
          :isLoading="isLoading"
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

        
        <div  v-if="talentdetaildata" class="mx-auto px-4">
         
         <span class="flex gap-4 mb-2"> {{ this.selectedHero.name }} {{ "Talent Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span>
          <global-talent-details-section :talentdetaildata="talentdetaildata" :statfilter="statfilter" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
        </div>
        <div v-else>
          <loading-component v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
          <loading-component v-else></loading-component>
        </div>


        <div  v-if="talentbuilddata" class=" mx-auto px-4 w-auto flex flex-col items-center">
        <div class="">
          <single-select-filter :values="buildtypes" :text="'Talent Build Type'" :defaultValue="this.talentbuildtype" @input-changed="buildtypechange"></single-select-filter>
          {{ this.selectedHero.name }} {{ "Talent Builds"}}
          <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="talentbuildtype" :statfilter="statfilter" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
        </div>
        </div>
        <div v-else>
          <loading-component v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
          <loading-component v-else></loading-component>
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
    },
    data(){
      return {
       isLoading: false,
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

        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        Promise.allSettled([
          this.getTalentData(),
          this.getTalentBuildData(),
          ]).then(results => {
        });
      },
      async getTalentData(){
        try{
          this.isLoading = true;
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
          });
          this.talentdetaildata = response.data;
        }catch(error){
          //Do something here
        }
        this.isLoading = false;
      },
      async getTalentBuildData(){
        try{
          this.isLoading = true;
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
          });
          this.talentbuilddata = response.data;
        }catch(error){
          //Do something here
        }
        this.isLoading = false;
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
      }
    }
  }
</script>