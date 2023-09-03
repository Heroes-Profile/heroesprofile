<template>
  <div>
    <!-- Ive been adding rxtra divs/classes to make it easier to interface with, feel free to take some out-->
    
    <div class="grid gap-5 grid-cols-1">
      <div>
        <h1>Global Talent Statistics</h1>
        <infobox :input="infoText"></infobox>
      </div>

      <div>
        <div v-if="!selectedHero">
          <div v-for="hero in heroes" :key="hero.id">
            <hero-box :hero="hero" @click="clickedHero(hero)"></hero-box>
          </div>
        </div>

        <div v-else>
          <filters 
            :onFilter="filterData" 
            :filters="filters" 
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
            >
          </filters>

          <div class="container mx-auto px-4">
                <global-talent-details-section v-if="talentdetaildata" :talentdetaildata="talentdetaildata"></global-talent-details-section>
          </div>



          <div class="container mx-auto px-4">

            <single-select-filter :values="buildtypes" :text="'Build Filter'" :defaultValue="'Popular'" @input-changed="handleInputChange"></single-select-filter>

            {{ this.inputhero.name }} {{ "Talent Builds"}}
            <global-talent-builds-section v-if="talentbuilddata" :talentbuilddata="talentbuilddata" :buildtype="selectedbuildtype"></global-talent-builds-section>
          </div>

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
  },
  data(){
    return {
    	infoText: "Talents",
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
      selectedbuildtype: "Popular",


      //Sending to filter
      timeframetype: "minor",
      timeframe: null,
      region: null,
      statfilter: null,
      herolevel: null,
      role: null,
      hero: null,
      gametype: null,
      gamemap: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
      mirrormatch: null,
      talentbuildtype: null,
    }
  },
  created(){    
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getTalentData();
      this.getTalentBuildData();
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
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      this.getTalentData();
      this.getTalentBuildData();
    },
  	async getTalentData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents", {
          hero: this.selectedHero.name,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          game_type: this.gametype,
          map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          talentbuildtype: this.talentbuildtype
        });

        this.talentdetaildata = response.data;
      }catch(error){
        console.log(error);
      }
    },
    async getTalentBuildData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents/build", {
          hero: this.selectedHero.name,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          game_type: this.gametype,
          map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          talentbuildtype: this.talentbuildtype
        });

        this.talentbuilddata = response.data;
      }catch(error){
        console.log(error);
      }
    },

    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : "win_rate";
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : [];
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : "";
      this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : "";

      this.getTalentData();
      this.getTalentBuildData();
    },
    handleInputChange(eventPayload){
      console.log("eventPayload.field = " + eventPayload.field);
      console.log("eventPayload.value = " + eventPayload.value);
    },
  }
}
</script>