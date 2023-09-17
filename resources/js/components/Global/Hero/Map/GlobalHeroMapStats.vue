<template>
  <div>
    <h1>Hero Map Statistics</h1>
    <infobox :input="infoText"></infobox>
    
    <!-- Should turn into a component for easy styling? -->
    <div class="flex flex-wrap gap-1" v-if="!selectedHero">
      <div v-for="hero in heroes" :key="hero.id">
        <round-box-small :hero="hero" @click="clickedHero(hero)"></round-box-small>
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
      :includeherolevel="true"
      :includegametype="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      >
    </filters>




    <custom-table :columns="columns" :data="data"></custom-table>
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
    },
    data(){
      return {
        columns: [
          { text: 'Map', value: 'name', sortable: true },
          { text: 'Win Rate %', value: 'win_rate', sortable: true },
          { text: 'Ban Rate %', value: 'ban_rate', sortable: true },
          { text: 'Games Played', value: 'games_played', sortable: true },
          ],
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
        mirrormatch: null,
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
        try{
          const response = await this.$axios.post("/api/v1/global/hero/map", {
            userinput: this.selectedHero.name,
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
          console.log(error);
        }
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : [];
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : "";

        this.getData();
      },
    }
  }
</script>