<template>
  <div>
    <global-async-debug-banner page-label="Global Party" />
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading :heading="'Global Party Statistics'"></page-heading>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :herolevelinput="herolevel"
      :heroinput="getHeroID()"
      :gamemapinput="gamemap"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :rolerankinput="rolerank"
      :teamonepartyinput="teamoneparty"      
      :teamtwopartyinput="teamtwoparty"      

      :gametypedefault="gametypedefault"
      :includetimeframe="true"
      :includeregion="true"
      :includeherolevel="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includeteamoneparty="true"
      :includeteamtwoparty="true"
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="partydata" class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <div class="flex justify-end">
        <custom-button v-if="patchNotesUrl" @click="togglePatchNotes" :text="timeframe[0] + ' Patch Notes'" :alt="timeframe[0] + ' Patch Notes'" size="small" :ignoreclick="true"></custom-button>
      </div>

      <patch-notes-panel v-if="showPatchNotes && patchNotesUrl" :version="timeframe[0]"></patch-notes-panel>

      <party-stats-display :partydata="partydata"></party-stats-display>

    </div>
    <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <loading-component :compact="true" :textoverride="true">Loading party stats...</loading-component>
      <party-stats-skeleton></party-stats-skeleton>
    </div>
    <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
    </div>
  </div>
</template>

<script>
  export default {
  name: 'GlobalPartyStats',
  components: {
  },
  props: {
     filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaulttimeframetype: String,
    defaulttimeframe: Array,
    advancedfiltering: Boolean,
    patreonUser: Boolean,
    urlparameters: Object,
    heroes: Object,

  },
  data(){
    return {
      dataError: false,
      isLoading: false,
      infoText: "Party win rates based on differing increments, stat types, game type, rank, and more. The hero filter allows you to see party data for games that only contained that hero.",
      partydata: null,
      cancelTokenSource: null,
      //Sending to filter
      timeframetype: null,
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
      mirrormatch: 0,
      heropartysize: null,
      teamoneparty: null,
      teamtwoparty: null,
      showPatchNotes: false,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;

    if(this.urlparameters){
      this.setURLParameters();
    }

    this.getData();
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
        const response = await this.$globalAsyncPost("/api/v1/global/party", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          hero: this.hero,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirror: this.mirrormatch,
          teamoneparty: this.teamoneparty,
          teamtwoparty: this.teamtwoparty,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.partydata = response.data;
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
    togglePatchNotes() {
      this.showPatchNotes = !this.showPatchNotes;
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
      this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
      this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      
      //this.heropartysize = filteredData.single["Hero Party Size"] ? filteredData.single["Hero Party Size"] : null;
      
      this.teamoneparty = filteredData.single["Team One Party"] ? filteredData.single["Team One Party"] : null;
      this.teamtwoparty = filteredData.single["Team Two Party"] ? filteredData.single["Team Two Party"] : null;
      

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

      if(this.hero){
        queryString += `&hero=${this.hero}`;
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

      if(this.teamoneparty){
        queryString += `&teamoneparty=${this.teamoneparty}`;
      }

      if(this.teamtwoparty){
        queryString += `&teamtwoparty=${this.teamtwoparty}`;
      }

      const currentUrl = window.location.href;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}${queryString}`);

      this.showPatchNotes = false;

      this.partydata = null;
      this.getData();
    },
    getStackName(ally_combo, enemy_combo){
      return "temp";
    },
    getHeroID(){
      if(this.hero){
        return this.heroes.find(hero => hero.name === this.hero).id
      }
      return null;
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
      
      if(this.urlparameters["hero_level"]){
        this.herolevel = this.urlparameters["hero_level"].split(',');
      }

      if(this.urlparameters["hero"]){
        this.hero = this.urlparameters["hero"];
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

      if(this.urlparameters["teamoneparty"]){
        this.teamoneparty = this.urlparameters["teamoneparty"];
      }

      if(this.urlparameters["teamtwoparty"]){
        this.teamtwoparty = this.urlparameters["teamtwoparty"];
      }

    },
  }
}
</script>
