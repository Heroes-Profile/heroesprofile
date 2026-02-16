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
    :hideadvancedfilteringbutton="true"
    :disablefilter="!this.shouldFilterData"
    >
  </filters>
  <takeover-ad :patreon-user="patreonUser"></takeover-ad>

  <div v-if="isLoading">
    <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
  </div>
  <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
  </div>
  <div v-else>
    <div class="flex justify-center items-center md:gap-10">
      <div v-if="hero" class="">
        <single-select-filter :values="firstHeroInputs" :text="'Choose Hero'" :trackclosure="true"  @dropdown-closed="dropdownClosed" @input-changed="herochanged" :defaultValue="hero.id"></single-select-filter>
      </div>
      <div class="">
        {{ vsorwith }}
      </div>
      <div v-if="enemyally" class="">
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

    <div class="flex justify-between max-w-[1500px] mx-auto mb-2">
      <div class="flex">
        <div v-if="showTalentHeroToggle" class="text-center flex items-center justify-stretch gap-2 mx-2">
          Talents:    
          <tab-button  :tab1text="this.hero.name" :ignoreclick="true" :tab2text="this.enemyally.name" @tab-click="talentHeroOrEnemySideSelected" > </tab-button>
        </div>
        <div class="text-center mt-auto">
          <tab-button :tab1text="'Enemy'" :ignoreclick="true" :tab2text="'Ally'" @tab-click="heroOrEnemySideSelected" > </tab-button>
        </div>
      </div>
      <span>
        <custom-button v-if="patchNotesUrl" @click="togglePatchNotes" :text="timeframe[0] + ' Patch Notes'" :alt="timeframe[0] + ' Patch Notes'" size="small" :ignoreclick="true"></custom-button>
      </span>
    </div>

    <div v-if="showPatchNotes" class="max-w-[1500px] mx-auto mb-4 p-4 rounded bg-gray-800 text-gray-200">
      <div v-if="patchNotesLoading" class="text-center py-4">Loading patch notes...</div>
      <div v-else-if="patchNotesContent" v-html="patchNotesContent"></div>
      <div v-else class="text-center py-4 text-gray-400">No summary available for this patch.</div>
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
        dataError: false,
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
        showPatchNotes: false,
        patchNotesContent: null,
        patchNotesLoading: false,
        patchNotesLoadedVersion: null,
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
      patchNotesUrl() {
        if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
          return null;
        }
        const selectedVersion = this.timeframe[0];
        const match = this.filters.timeframes.find(t => t.code === selectedVersion);
        return match && match.patch_notes_url ? match.patch_notes_url : null;
      },
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
        if((this.hero && this.hero.name != "Auto Select") && (this.enemyally && this.enemyally.name != "Auto Select")){
          return true;
        }
        return false;
      },
    },
    watch: {
    },
    methods: {
      async getData(){
        this.dataError = false;
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

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          this.talentdetaildata = response.data.data;
          this.firstwinratedata = response.data.first_win_rate;
          this.secondwinratedata = response.data.second_win_rate;
        }catch(error){
          this.dataError = true;
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
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;


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

        this.showPatchNotes = false;
        this.patchNotesContent = null;
        this.patchNotesLoadedVersion = null;

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
      async togglePatchNotes() {
        this.showPatchNotes = !this.showPatchNotes;
        if (this.showPatchNotes && this.timeframe.length === 1 && this.patchNotesLoadedVersion !== this.timeframe[0]) {
          this.patchNotesLoading = true;
          this.patchNotesContent = null;
          try {
            const response = await fetch(`/patch-notes/${this.timeframe[0]}.html`);
            if (response.ok) {
              this.patchNotesContent = await response.text();
            }
          } catch (e) {
            this.patchNotesContent = null;
          } finally {
            this.patchNotesLoadedVersion = this.timeframe[0];
            this.patchNotesLoading = false;
          }
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
          this.playerrank = this.urlparameters["league_tier"]
            .split(',')
            .map(tierName => {
                const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
                const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
                return tier?.code;
            });
        }

      },
    }
  }
</script>