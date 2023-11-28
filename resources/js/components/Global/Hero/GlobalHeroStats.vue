<template>
  <div>
    <page-heading :infoText1="infoText" heading="Global Hero Statistics"></page-heading>
    
    

    <filters 
      :isLoading="isLoading"
      :onFilter="filterData" 
      :filters="filters" 
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
      >
    </filters>

    
    <div v-if="this.data.data">
      <div class="max-w-[1500px] mx-auto flex justify-end mb-2">
        <custom-button @click="toggleChartValue" text="Toggle Chart" alt="Toggle Chart" size="small" :ignoreclick="true"></custom-button>
      </div>

      <div v-if="togglechart">
        <bubble-chart :heroData="this.data.data"></bubble-chart>
      </div>
      <div class="min-w-[1500px] px-20">
     <table class="">
        <thead>
          <th class="py-2 px-3  border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Avg
          </th>
          <th class="py-2 px-3  border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_win_rate.toFixed(2) }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ "&#177;" }}{{ this.data.average_confidence_interval.toFixed(2) }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_positive_win_rate_change.toFixed(2) }}{{ "|" }}{{ this.data.average_negative_win_rate_change.toFixed(2) }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_popularity.toFixed(2) }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_pick_rate.toFixed(2) }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_ban_rate.toFixed(2) }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_positive_influence.toLocaleString() }}{{ "|" }}{{ this.data.average_negative_influence.toLocaleString() }}
          </th>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_games_played.toLocaleString() }}
          </th>

          <th  v-if="this.showStatTypeColumn"  class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ data.averaege_total_filter_type.toFixed(2).toLocaleString() }}
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
            <th @click="sortTable('win_rate_change')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Change
            </th>
            <th @click="sortTable('popularity')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity %
            </th>
            <th @click="sortTable('pick_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Pick Rate %
            </th>   
            <th @click="sortTable('ban_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Ban Rate %
            </th>    
            <th @click="sortTable('influence')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              <div class="flex items-center">
                <div class="">
                  Influence
                </div>
                <round-image class="" size="small" icon="fas fa-info" title="info" popupsize="large">
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
              <td class="py-2 px-3 flex items-center gap-1">
                <a class="flex w-full items-center" :href="'/Global/Talents/' + row.name" >
                <hero-image-wrapper class="mr-2" :hero="row" :includehover="false"></hero-image-wrapper>{{ row.name }}
                </a>
              </td>
              <td class="  ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 "><span v-html="'&#177;'"></span>{{ row.confidence_interval.toFixed(2) }}</td>
              <td v-if="row.win_rate_change < 0" class="py-2 px-3 ">{{ row.win_rate_change.toFixed(2) }}</td>
              <td v-else-if="row.win_rate_change >= 0" class="py-2 px-3 "><span v-html="'&plus;'"></span>{{ row.win_rate_change.toFixed(2) }}</td>
              <td class="py-2 px-3">{{ row.popularity.toFixed(2) }}</td>
              <td class="py-2 px-3">{{ row.pick_rate.toFixed(2) }}</td>
              <td class="py-2 px-3">{{ row.ban_rate.toFixed(2) }}</td>
              <td class="py-2 px-3">{{ row.influence.toLocaleString() }}</td>
              <td class="py-2 px-3 ">{{ row.games_played.toLocaleString() }}</td>
              <td v-if="this.showStatTypeColumn" class="py-2 px-3 ">{{ row.total_filter_type.toFixed(2).toLocaleString() }}</td>
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
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
      <loading-component @cancel-request="cancelAxiosRequest" v-else></loading-component>
    </div>
  </div>
</template>

<script>
import GlobalTalentBuildsSection from '../Talents/GlobalTalentBuildsSection.vue';

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
    defaultbuildtype: String,
    defaulttimeframetype: String,
    defaulttimeframe: Array,
    advancedfiltering: Boolean,
  },
  data(){
    return {
      isLoading: false,
    	infoText: "Hero win rates based on differing increments, stat types, game type, or rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'desc',
      data: [],
      togglechart: false,
      toggletalentbuilds: {},
      talentbuilddata: {},
      selectedbuildtype: "Popular",
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
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.talentbuildtype = this.defaultbuildtype;
    this.timeframetype = this.defaulttimeframetype;
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
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
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

        this.sortedData[index].talentbuilddata = response.data;

        this.talentbuilddata[hero] = response.data;
        this.loadingStates[hero] = false;

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
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : this.statfilter;
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
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

      if(this.role){
        queryString += `&role=${this.role}`;
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
  }
}
</script>