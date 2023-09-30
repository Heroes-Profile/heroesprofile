<template>
  <div>

    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Map Statistics' : 'Hero Map Statistics'"></page-heading>
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
      :includeherolevel="true"
      :includegametype="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :advancedfiltering="advancedfiltering"

      >
    </filters>
    <div v-if="data">
      <div> 
        <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button>
      </div>
      <custom-table :columns="dynamicColumns" :data="data"></custom-table>
    </div>
    <div v-else>
      <loading-component v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
      <loading-component v-else></loading-component>
    </div>
  </div>



</div>
</template>

<script>
  export default {
    name: 'GlobalHeroMapStats',
    components: {
    },
    props: {
      inputhero: Object,
      heroes: Array,
      filters: {
        type: Object,
        required: true
      },
      gametypedefault: Array,
      defaulttimeframe: Array,
      defaulttimeframetype: String,
      advancedfiltering: String,
    },
    data(){
      return {
        isLoading: false,
        infoText: "Hero Maps provide information on which maps are good for each hero",
        selectedHero: null,
        data: null,

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        gametype: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,
      }
    },
    created(){
      this.gametype = this.gametypedefault;
      this.timeframe = this.defaulttimeframe;
      this.timeframetype = this.defaulttimeframetype;

      if(this.inputhero){
        this.selectedHero = this.inputhero;
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
       dynamicColumns() {
        if (this.gametype.includes("sl")) {
          return [
            { text: 'Map', value: 'name', sortable: true },
            { text: 'Win Rate %', value: 'win_rate', sortable: true },
            { text: 'Ban Rate %', value: 'ban_rate', sortable: true },
            { text: 'Games Played', value: 'games_played', sortable: true },
          ];
        } else {
          return [
            { text: 'Map', value: 'name', sortable: true },
            { text: 'Win Rate %', value: 'win_rate', sortable: true },
            { text: 'Games Played', value: 'games_played', sortable: true },
          ];
        }
    },
    },
    watch: {
    },
    methods: {
      clickedHero(hero) {
        this.selectedHero = hero;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        this.getData();
      },
      async getData(){
        this.isLoading = true;
        try{
          const response = await this.$axios.post("/api/v1/global/hero/map", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirrormatch: this.mirrormatch,
          });
          this.data = response.data;
        }catch(error){
          //Do something here
        }

        this.isLoading = false;
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;

        this.data = null;
        this.getData();
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframe.length >= 3){
          return  true;
        }
        return false;
      },
      redirectChangeHero(){
        window.location.href = "/Global/Hero/Maps";
      },
    }
  }
</script>