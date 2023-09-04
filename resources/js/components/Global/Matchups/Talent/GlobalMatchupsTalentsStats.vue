<template>
  <div>
    <h1>Hero Matchup Talents Statistics</h1>
    <infobox :input="infoText1"></infobox>
    <infobox :input="infoText2"></infobox>

      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :gametypedefault="gametypedefault"
        :includetimeframetype="true"
        :includetimeframe="true"
        :includegametype="true"
        :includegamemap="true"
        :includeplayerrank="true"
        >
      </filters>

      <div class="grid grid-cols-3 grid-rows-4 gap-4">
        <!-- First column, single item taking up 4 rows -->
        <div class="col-start-1 row-start-1 row-span-4 bg-blue-200">
          <hero-box-large :hero="hero"></hero-box-large>

          <div v-if="this.firstwinratedata">
            {{ this.firstwinratedata }}{{"%"}}
          </div>
        </div>

        <!-- Second column, 4 individual items -->
        <div class="col-start-2 row-start-1 bg-green-200">
          <single-select-filter :values="firstHeroInputs" :text="'Choose Hero'" :trackclosure="true"  @dropdown-closed="dropdownClosed" @input-changed="herochanged"></single-select-filter>

        </div>
        <div class="col-start-2 row-start-2 bg-green-300">
          {{ vsorwith }}
        </div>
        <div class="col-start-2 row-start-3 bg-green-400">
          <single-select-filter :values="secondHeroInputs" :text="'Choose Hero'" :trackclosure="true"  @dropdown-closed="dropdownClosed" @input-changed="allyenemychanged"></single-select-filter>
        </div>
        <div class="col-start-2 row-start-4 bg-green-500">
          {{ fightoralliance }}
        </div>

        <!-- Third column, single item taking up 4 rows -->
        <div class="col-start-3 row-start-1 row-span-4 bg-red-200">
          <hero-box-large :hero="enemyally"></hero-box-large>
          <div v-if="this.secondwinratedata">
            {{ this.secondwinratedata }}{{"%"}}
          </div>
        </div>
      </div>


      <div v-if="showTalentHeroToggle" class="container">
        Talents:    
        <button :class="talentheroselected === 'left' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-black'" @click="talentHeroOrEnemySideSelected(this.hero, 'left')">
          {{ this.hero.name }}
        </button>

        <button :class="talentheroselected === 'right' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-black'" @click="talentHeroOrEnemySideSelected(this.enemyally, 'right')">
          {{ this.enemyally.name }}
        </button>
      </div>

      <div class="container">
        <button :class="enemyorallyselected === 'left' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-black'" @click="heroOrEnemySideSelected(this.hero, 'left')">
          Enemy
        </button>

        <button :class="enemyorallyselected === 'right' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-black'" @click="heroOrEnemySideSelected(this.enemyally, 'right')">
          Ally
        </button>
      </div>



      <div class="container mx-auto px-4">
            <global-talent-details-section v-if="talentdetaildata" :talentdetaildata="talentdetaildata" :statfilter="null"></global-talent-details-section>
      </div>


  </div>
</template>

<script>
export default {
  name: 'GlobalMatchupsTalentStats',
  components: {
  },
  props: {
    heroes: Array,
    filters: Object,
    gametypedefault: Array,
    defaulttimeframetype: String,
    defaulttimeframe: Array,
    inputhero: Object,
    inputenemyally: Object,
  },
  data(){
    return {
      loading: false,
      infoText1: 'This page allows you to look at talent win rates and popularity, for a hero against, or with another hero. If you click on the "enemy" button, you will see talent data for games where those two heroes played against each other. If you click on the "ally" button, you will see talent data for games where those two heroes were on the same team.',
      infoText2: "NOTICE: This page may take longer to load data than our normal pages.",

      talentdetaildata: null,

      //Sending to filter
      hero: null,
      enemyally: null,
      timeframetype: null,
      timeframe: null,
      gametype: null,
      gamemap: null,
      playerrank: null,
      vsorwith: "vs",
      fightoralliance: "FIGHT",
      talentheroselected: "left",
      talentview: "hero",
      enemyorallyselected: "left",
      type: "Enemy",
      firstwinratedata: null,
      secondwinratedata: null,
    }
  },
  created(){
    this.timeframe = this.defaulttimeframe;
    this.gametype = this.gametypedefault;
    this.timeframetype = this.defaulttimeframetype;

    this.hero = this.inputhero;
    this.enemyally = this.inputenemyally;

    if(this.shouldFilterData){
      this.getData();
    }
  },
  mounted() {
  },
  computed: {
    showTalentHeroToggle(){
      return (this.hero.name != "Auto Select" && this.enemyally.name != "Auto Select") ? true : false;
    },
    firstHeroInputs(){
      if(this.enemyally.name){
        return this.filters.heroes.filter(hero => hero.name !== this.enemyally.name);
      }
      return this.filters.heroes;
    },
    secondHeroInputs(){
      if(this.hero.name){
        return this.filters.heroes.filter(hero => hero.name !== this.hero.name);
      }
      return this.filters.heroes;
    },
    shouldFilterData(){
      if(this.hero.name != "Auto Select" && this.enemyally.name != "Auto Select"){
        return true;
      }
      return false;
    },
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        this.loading = true;
        const response = await this.$axios.post("/api/v1/global/matchups/talents", {
          hero: this.hero.name,
          ally_enemy: this.enemyally.name,
          type: this.type,
          talent_view: this.talentview,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          game_type: this.gametype,
          map: this.gamemap,
          league_tier: this.playerrank,
        });
        console.log(response.data);
        this.talentdetaildata = response.data.data;
        this.firstwinratedata = response.data.first_win_rate;
        this.secondwinratedata = response.data.second_win_rate;
        this.loading = false;
      }catch(error){
        console.log(error);
        this.loading = false;
      }
    },

    herochanged(eventPayload){
      this.hero = this.heroes.find(hero => hero.id === eventPayload.value);
    },
    allyenemychanged(eventPayload){
      this.enemyally = this.heroes.find(hero => hero.id === eventPayload.value);
    },

    dropdownClosed(eventPayload) {
      if(this.hero.name != "Auto Select" && this.enemyally.name != "Auto Select"){
        this.getData();
      }
    },

    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
      this.getData();
    },
    talentHeroOrEnemySideSelected(hero, side){
      let tempHero = this.hero;
      let tempEnemyAlly = this.enemyally;

      this.hero = tempEnemyAlly;
      this.enemyally = tempHero;
      if(this.shouldFilterData){
        this.getData();
      }

      if(side == "left"){
        this.talentview = "hero";
      }else if(side == "left"){
        this.talentview = "ally_enemy";
      }
      this.talentheroselected = side;
      this.firstwinratedata = "";
      this.secondwinratedata = "";
    },
    heroOrEnemySideSelected(hero, side){
      if(side == "left"){
        this.type = "Enemy";
        this.vsorwith = "vs";
        this.fightoralliance = "FIGHT";
      }else if(side == "right"){
        this.type = "Ally";
        this.vsorwith = "with";
        this.fightoralliance = "ALLIANCE";
      }
      this.enemyorallyselected = side;
      this.firstwinratedata = "";
      this.secondwinratedata = "";
      
      if(this.shouldFilterData){
        this.getData();
      }
    },
  }
}
</script>