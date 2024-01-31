<template>
  <div>
    <page-heading :infoText1="infoText1" :infoText2="infoText2" :heading="'Hero Matchup Talents Statistics'"></page-heading>
    <filters 
    :onFilter="filterData" 
    :filters="filters" 
    :isLoading="isLoading"

    :timeframetypeinput="timeframetype"
    :timeframeinput="timeframe"
    :gametypeinput="gametype"
    :gamemapinput="gamemap"
    :playerrankinput="playerrank"

    :gametypedefault="gametypedefault"
    :includetimeframetype="true"
    :includetimeframe="true"
    :includegametype="true"
    :includegamemap="true"
    :includeplayerrank="true"
    :advancedfiltering="advancedfiltering"
    :advancedfilteringtexthide="true"
    >
  </filters>
  <takeover-ad :patreon-user="patreonUser"></takeover-ad>

  <div v-if="isLoading">
    <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
  </div>
  <div v-else>
    <div class="flex justify-center items-center md:gap-10">
      <div class="">
        <single-select-filter :values="firstHeroInputs" :text="'Choose Hero'" :trackclosure="true"  @dropdown-closed="dropdownClosed" @input-changed="herochanged" :defaultValue="hero.id"></single-select-filter>
      </div>
      <div class="">
        {{ vsorwith }}
      </div>
      <div class="">
        <single-select-filter :values="secondHeroInputs" :text="'Choose Hero'" :trackclosure="true"  @dropdown-closed="dropdownClosed" @input-changed="allyenemychanged" :defaultValue="enemyally.id"></single-select-filter>
      </div>
    </div>
    <div class="flex justify-center relative gap-10">
      <div 
      :class="[
        'absolute z-20 font-logo text-[1.5em] md:text-[5em] text-red drop-shadow-lg rotate-12 md:mt-[1em]', 
        fightoralliance == 'FIGHT' ? 'text-red' : 'text-teal', 
        
        ]"
        style="-webkit-text-stroke-width: 1px; -webkit-text-stroke-color: white;">
        {{ fightoralliance }}!!
      </div>
      <div>  
        <hero-image-wrapper class="" :rectangle="true" :size="'large'" :hero="hero"></hero-image-wrapper>
        <div v-if="this.firstwinratedata">
          {{ this.firstwinratedata }}{{"%"}}
        </div>
      </div>
      <div>
        <hero-image-wrapper class="" :rectangle="true" :size="'large'" :hero="enemyally"></hero-image-wrapper>
        <div v-if="this.secondwinratedata">
          {{ this.secondwinratedata }}{{"%"}}
        </div>
      </div>
    </div>

    <div class="flex max-w-[1500px] mx-auto mb-2">
      <div v-if="showTalentHeroToggle" class="text-center flex items-center justify-stretch gap-2 mx-2">
        Talents:    
        <tab-button  :tab1text="this.hero.name" :ignoreclick="true" :tab2text="this.enemyally.name" @tab-click="talentHeroOrEnemySideSelected" > </tab-button>
      </div>
      <div class="text-center mt-auto">
        <tab-button :tab1text="'Enemy'" :ignoreclick="true" :tab2text="'Ally'" @tab-click="heroOrEnemySideSelected" > </tab-button>
      </div>
    </div>
    <div class=" mx-auto px-4">
      <global-talent-details-section v-if="talentdetaildata" :talentdetaildata="talentdetaildata" :statfilter="null"></global-talent-details-section>
    </div>
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
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        isLoading: false,
        infoText1: 'This page allows you to look at talent win rates and popularity, for a hero against, or with another hero. If you click on the "enemy" button, you will see talent data for games where those two heroes played against each other. If you click on the "ally" button, you will see talent data for games where those two heroes were on the same team.',
        infoText2: "NOTICE: This page may take longer to load data than our normal pages.",
        cancelTokenSource: null,
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
      this.hero = this.inputhero;
      this.enemyally = this.inputenemyally;

      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      if(this.shouldFilterData){
        this.getData();
      }
    },
    mounted() {
     
    },
    computed: {
      showTalentHeroToggle(){
        if(!this.hero || !this.enemyally){
          return false;
        }
        return (this.hero.name != "Auto Select" && this.enemyally.name != "Auto Select") ? true : false;
      },
      firstHeroInputs(){
        if(this.enemyally){
          return this.filters.heroes.filter(hero => hero.name !== this.enemyally.name);
        }
        return this.filters.heroes;
      },
      secondHeroInputs(){
        if(this.hero){
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
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
        
        try{
          const response = await this.$axios.post("/api/v1/global/matchups/talents", {
            hero: this.hero.name,
            ally_enemy: this.enemyally.name,
            type: this.type,
            talent_view: this.talentview,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });
          this.talentdetaildata = response.data.data;
          this.firstwinratedata = response.data.first_win_rate;
          this.secondwinratedata = response.data.second_win_rate;
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
      herochanged(eventPayload){
        this.hero = this.heroes.find(hero => hero.id === eventPayload.value);

        let currentPath = window.location.pathname;
        const basePath = '/Global/Matchups/Talents';
        
        if (currentPath.startsWith(basePath + '/')) {
          currentPath = basePath;
        }

        //history.pushState(null, null, `${currentPath}/${this.hero.name}/${this.enemyally.name}`);
      },

      allyenemychanged(eventPayload){
        this.enemyally = this.heroes.find(hero => hero.id === eventPayload.value);

        let currentPath = window.location.pathname;
        const basePath = '/Global/Matchups/Talents';
        
        if (currentPath.startsWith(basePath + '/')) {
          currentPath = basePath;
        }

        history.pushState(null, null, `${currentPath}/${this.hero.name}/${this.enemyally.name}`);
      },

      dropdownClosed(eventPayload) {
        if(this.hero.name != "Auto Select" && this.enemyally.name != "Auto Select"){
          this.talentdetaildata = null;
          this.getData();
        }
      },

      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;


        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
        queryString += `&game_type=${this.gametype}`;
      
        if(this.gamemap){
          queryString += `&game_map=${this.gamemap}`;
        }

        if(this.playerrank){
          queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
        }

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);

        this.talentdetaildata = null;
        this.getData();
      },

      talentHeroOrEnemySideSelected(side){
        if(side == "left"){
          this.talentview = "hero";
        }else{
          this.talentview = "ally_enemy";
        }
        if(this.shouldFilterData){
          this.talentdetaildata = null;
          this.getData();
        }
      },  

      heroOrEnemySideSelected(side){
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
          this.talentdetaildata = null;
          this.getData();
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

        if(this.urlparameters["game_map"]){
          this.gamemap = this.urlparameters["game_map"].split(',');
        }

        if (this.urlparameters["league_tier"]) {
          this.playerrank = this.urlparameters["league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }
      },
    }
  }
</script>