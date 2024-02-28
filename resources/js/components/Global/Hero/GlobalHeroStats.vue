<template>
  <div>
    <page-heading :infoText1="infoText" heading="Global Hero Statistics"></page-heading>
    
    

    <filters 
      :isLoading="isLoading"
      :onFilter="filterData" 
      :filters="filters" 
      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :statfilterinput="statfilter"
      :herolevelinput="herolevel"
      :heroinput="getHeroID()"
      :roleinput="role"
      :gamemapinput="gamemap"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :rolerankinput="rolerank"
      :talentbuildtypeinput="talentbuildtype"
      :mirrormatchinput="mirrormatch"

      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includestatfilter="true"
      :includeherolevel="true"
      :includerole="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :includetalentbuildtype="true"
      :advancedfiltering="advancedfiltering"
      :buildtypedefault="defaultbuildtype"
  
      >
    </filters>
    <takeover-ad :patreon-user="patreonUser"></takeover-ad>
    
    <div v-if="this.data.data">
      <div class="max-w-[1500px] mx-auto flex justify-end mb-2">
        <custom-button @click="toggleChartValue" text="Toggle Chart" alt="Toggle Chart" size="small" :ignoreclick="true"></custom-button>
      </div>

      <div v-if="togglechart">
        <bubble-chart :heroData="this.data.data"></bubble-chart>
      </div>
      <div >
        <div id="table-container" ref="tablecontainer" class="w-auto   w-[100vw]   2xl:mx-auto  " style=" ">
      <table id="responsive-table" class="responsive-table  relative" ref="responsivetable">
        <thead class="top-0 w-full sticky z-40">
          <th class="py-2 px-3 border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Avg
          </th>
          <th class="py-2 px-3 border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueFixed(data.average_win_rate) }}
          </th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ "&#177;" }}{{ getValueFixed(data.average_confidence_interval)}}
          </th>
          <th v-if="showWinRateChange" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueFixed(data.average_positive_win_rate_change) }}{{ "|" }}{{ getValueFixed(data.average_negative_win_rate_change) }}
          </th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueFixed(data.average_popularity) }}
          </th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueFixed(data.average_pick_rate) }}
          </th>
          <th v-if="this.gametype.includes('sl')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueFixed(data.average_ban_rate) }}
          </th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueLocal(data.average_positive_influence) }}{{ "|" }}{{ getValueLocal(data.average_negative_influence) }}
          </th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueLocal(data.average_games_played) }}
          </th>

          <th  v-if="this.showStatTypeColumn"  class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ getValueLocal(getValueFixed(data.averaege_total_filter_type)) }}
          </th>

          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
          </th>

        </thead>
        <thead>
          <tr>
            <th @click="sortTable('name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate %
            </th>
            <th @click="sortTable('confidence_interval')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Confidence
            </th>
            <th v-if="showWinRateChange" @click="sortTable('win_rate_change')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Change
            </th>
            <th @click="sortTable('popularity')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity %
            </th>
            <th @click="sortTable('pick_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Pick Rate %
            </th>   
            <th v-if="this.gametype.includes('sl')" @click="sortTable('ban_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Ban Rate %
            </th>    
            <th @click="sortTable('influence')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              <div class="flex items-center">
                <div class="">
                  Influence
                </div>
                <round-image class="hidden md:block" size="small" icon="fas fa-info" title="info" popupsize="large">
                  <slot>
                    <div>
                      <p>Influence is an integer scaled from -1000 to 1000 that combines Win Rate, Games Played, Pick Rate, and Ban Rate to determine the impact a hero will have on a particular team.</p>
                    </div>
                  </slot>
                </round-image>
              </div>
            </th>                  
            <th @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>       
            <th v-if="this.showStatTypeColumn" @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              {{ this.statfilter }}
            </th>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            </th>                              
          </tr>
        </thead>
        <tbody>
          <template v-for="(row, index) in sortedData">
            <tr>
              <td class="py-2 px-3 flex items-center gap-1 max-md:w-[150px]">
                <a class="flex w-full items-center max-md:justify-center" :href="getGlobalTalentsURL(row)" >
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row" :includehover="false"></hero-image-wrapper><span class="hidden md:block">{{ row.name }}</span>
                </a>
              </td>
              <td class="  ">{{ getValueFixed(row.win_rate) }}</td>
              <td class="py-2 px-3 "><span v-html="'&#177;'"></span>{{ getValueFixed(row.confidence_interval) }}</td>
              <td v-if="showWinRateChange && row.win_rate_change < 0" class="py-2 px-3 ">{{ getValueFixed(row.win_rate_change) }}</td>
              <td v-else-if="showWinRateChange && row.win_rate_change >= 0" class="py-2 px-3 "><span v-html="'&plus;'"></span>{{ getValueFixed(row.win_rate_change) }}</td>
              <td class="py-2 px-3">{{ getValueFixed(row.popularity) }}</td>
              <td class="py-2 px-3">{{ getValueFixed(row.pick_rate) }}</td>
              <td  v-if="this.gametype.includes('sl')" class="py-2 px-3">{{ getValueFixed(row.ban_rate) }}</td>
              <td class="py-2 px-3">{{ getValueLocal(row.influence) }}</td>
              <td class="py-2 px-3 ">{{ getValueLocal(row.games_played) }}</td>
              <td v-if="this.showStatTypeColumn" class="py-2 px-3 ">{{ getValueLocal(getValueFixed(row.total_filter_type)) }}</td>
              <td class="py-2 px-3 ">
                <custom-button
                  @click="viewtalentbuilds(row.name, index)"
                  text="View Talent Builds"
                  alt="View Talent Builds"
                  size="small"
                  :ignoreclick="true"
                  :loading="loadingStates[row.name]"
                >
                  View Talent Builds
                </custom-button>
              </td>
            </tr>
            <tr v-if="toggletalentbuilds[row.name] && talentbuilddata[row.name]">
              <td colspan="11">
                <global-talent-builds-section v-if="toggletalentbuilds[row.name] && talentbuilddata[row.name]" :talentbuilddata="talentbuilddata[row.name]" :buildtype="selectedbuildtype"></global-talent-builds-section>
              </td>
            </tr> 
          </template>
        </tbody>
      </table>
    </div>
    </div>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
      <loading-component @cancel-request="cancelAxiosRequest" v-else></loading-component>
    </div>
    <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
    </div>
  </div>
</template>

<script>

export default {
  name: 'GlobalHeroStats',
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
    defaultbuildtype: String,
    heroes: Object,
    urlparameters: Object,
  },
  data(){
    return {
      dataError: false,
      windowWidth: window.innerWidth,
      isLoading: false,
    	infoText: "Hero win rates based on differing increments, stat types, game type, or rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'desc',
      data: [],
      togglechart: false,
      toggletalentbuilds: {},
      talentbuilddata: {},
      selectedbuildtype: null,
      cancelTokenSource: null,


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
      talentbuildtype: null,
      loadingStates: {},
      tablewidth: null,
      queryString: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;
    this.talentbuildtype = this.defaultbuildtype;

    if(this.urlparameters){
      this.setURLParameters();
    }

  	this.getData();
  },
  mounted() {
  },
  computed: {
    showStatTypeColumn(){
      if(this.statfilter && this.statfilter != "win_rate" && !this.isLoading){
        return true;      
      }
      return false;
    },
    showWinRateChange(){
      return (
        this.timeframe.length === 1 &&
        !this.region &&
        this.gametype.length === 1 &&
        this.gametype[0] !== 'br' &&
        this.gametype[0] !== 'cu' &&
        !this.gamemap &&
        !this.playerrank &&
        !this.herorank &&
        !this.rolerank &&
        !this.herolevel &&
        this.statfilter === 'win_rate' &&
        this.mirrormatch === 0
      );

    },
    sortedData() {
      if (!this.sortKey) return this.data.data;
      return this.data.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
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
        this.data = [];

        const response = await this.$axios.post("/api/v1/global/hero", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          role: this.role,
          hero: this.hero,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirror: this.mirrormatch,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        this.data = response.data;
        this.loadingStates = this.sortedData.map(() => false);
      }catch(error){
        this.dataError = true;
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            const newTableWidth = this.windowWidth /responsivetable.clientWidth;
            responsivetable.style.transformOrigin = 'top left';
            responsivetable.style.transform = `scale(${newTableWidth})`;
            const container = this.$refs.tablecontainer;
            this.tablewidth = newTableWidth;
            container.style.height = (responsivetable.clientHeight * newTableWidth) + 'px';
          }
        });
      }
    },
    async getTalentBuildData(hero, index){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        this.loadingStates[hero] = true;
        const response = await this.$axios.post("/api/v1/global/talents/build", {
          hero: hero,
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
          mirror: this.mirrormatch,
          talentbuildtype: this.talentbuildtype
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.sortedData[index].talentbuilddata = response.data;
        this.talentbuilddata[hero] = response.data;
        this.loadingStates[hero] = false;

      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            const newTableWidth = this.windowWidth /responsivetable.clientWidth;
            responsivetable.style.transformOrigin = 'top left';
            responsivetable.style.transform = `scale(${newTableWidth})`;
            const container = this.$refs.tablecontainer;
            this.tablewidth = newTableWidth;
            container.style.height = (responsivetable.clientHeight * newTableWidth) + 'px';
          }
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : this.statfilter;
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.talentbuildtype;
      
      this.talentbuilddata = {};
      this.toggletalentbuilds = {};
      this.loadingStates = {};
      this.sortKey = '';
      this.sortDir = 'desc';

      this.queryString  = `?timeframe_type=${this.timeframetype}`;
      this.queryString += `&timeframe=${this.timeframe}`;

      this.queryString += `&game_type=${this.gametype}`;

      if(this.region){
        this.queryString += `&region=${this.region}`;
      }

      if(this.herolevel){
        this.queryString += `&hero_level=${this.herolevel}`;
      }

      if(this.gamemap){
        this.queryString += `&game_map=${this.gamemap}`;
      }

      if(this.hero){
        this.queryString += `&hero=${this.hero}`;
      }

      if(this.role){
        this.queryString += `&role=${this.role}`;
      }

    
      if(this.playerrank){
        this.queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
      }

      if(this.herorank){
        this.queryString += `&hero_league_tier=${this.convertRankIDtoName(this.herorank)}`;
      }

      if(this.rolerank){
        this.queryString += `&role_league_tier=${this.convertRankIDtoName(this.rolerank)}`;
      }

      this.queryString += `&statfilter=${this.statfilter}`;
      this.queryString += `&build_type=${this.talentbuildtype}`;
      this.queryString += `&mirror=${this.mirrormatch}`;

      const currentUrl = window.location.href;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}${this.queryString}`);
   
      this.data = null;

      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
    toggleChartValue() {
      this.togglechart = !this.togglechart;
    },
    viewtalentbuilds(hero, index){
      if (!this.talentbuilddata[hero]) {
        this.loadingStates[hero] = true;
        this.getTalentBuildData(hero, index);
      }
      this.toggletalentbuilds[hero] = !this.toggletalentbuilds[hero];
    },
    determineIfLargeData(){
      if(this.timeframetype == "major" || this.timeframe.length >= 3 || this.statfilter != "win_rate"){
        return  true;
      }
      return false;
    },
    getGlobalTalentsURL(hero){
      var url = "";
      if(hero){
        if(this.queryString){
          url = '/Global/Talents/' + hero.name + this.queryString;
        }else{
          url = '/Global/Talents/' + hero.name;
        }
      }
      return url;
    },
    getValueFixed(value){
      return value ? value.toFixed(2) : "";
    },
    getValueLocal(value){
      return value ? value.toLocaleString('en-US') : "";
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

      if(this.urlparameters["statfilter"]){
        this.statfilter = this.urlparameters["statfilter"];
      }
      
      if(this.urlparameters["hero_level"]){
        this.herolevel = this.urlparameters["hero_level"].split(',');
      }

      if(this.urlparameters["hero"]){
        this.hero = this.urlparameters["hero"];
      }

      if(this.urlparameters["role"]){
        this.role = this.urlparameters["role"];
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
  }
}
</script>