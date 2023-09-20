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
      >
    </filters>

    
    <div v-if="this.data.data">
      <div class="float-right mr-20">
        <custom-button @click="toggleChartValue"  text="Toggle Chart" alt="Toggle Chart" size="small" :ignoreclick="true"></custom-button>
      </div>

      <div v-if="togglechart">
        <bubble-chart :heroData="this.data.data"></bubble-chart>
      </div>
      <div class="min-w-full px-20">
     <table class="min-w-full bg-white">
        <thead>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Avg
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_win_rate }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ "&#177;" }}{{ this.data.average_confidence_interval }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_positive_win_rate_change }}{{ "|" }}{{ this.data.average_negative_win_rate_change }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_popularity }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_pick_rate }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_ban_rate }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_positive_influence }}{{ "|" }}{{ this.data.average_negative_influence }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_games_played }}
          </th>

          <th  v-if="this.showStatTypeColumn"  class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ data.averaege_total_filter_type }}
          </th>

          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
          </th>

        </thead>
        <thead>
          <tr>
            <th @click="sortTable('name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate
            </th>
            <th @click="sortTable('confidence_interval')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Confidence
            </th>
            <th @click="sortTable('win_rate_change')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Change
            </th>
            <th @click="sortTable('popularity')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity
            </th>
            <th @click="sortTable('pick_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Pick Rate
            </th>   
            <th @click="sortTable('ban_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Ban Rate
            </th>    
            <th @click="sortTable('influence')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Influence
            </th>                  
            <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>       
            <th v-if="this.showStatTypeColumn" @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              {{ this.statfilter }}
            </th>
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            </th>                              
          </tr>
        </thead>
        <tbody>
          <template v-for="(row, index) in sortedData">
            <tr>
              <a :href="'/Global/Talents/' + row.name" ><td class="py-2 px-3 border-b border-gray-200 flex items-center gap-1"><hero-image-wrapper :hero="row" :includehover="false"></hero-image-wrapper>{{ row.name }}</td></a>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200"><span v-html="'&#177;'"></span>{{ row.confidence_interval }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate_change }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.pick_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.ban_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.influence }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
              <td v-if="this.showStatTypeColumn" class="py-2 px-3 border-b border-gray-200">{{ row.total_filter_type }}</td>
              <td class="py-2 px-3 border-b border-gray-200">
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
    <div v-else>
      <loading-component></loading-component>
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
  },
  data(){
    return {
      isLoading: false,
    	infoText: "Hero win rates based on differing increments, stat types, game type, or Rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'asc',
      data: [],
      togglechart: false,
      toggletalentbuilds: {},
      talentbuilddata: {},
      selectedbuildtype: "Popular",



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
      mirrormatch: null,
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
      if(this.statfilter && this.statfilter != "win_rate" && !this.loading){
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
      try{
        this.isLoading = true;
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
          map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          talentbuildtype: this.talentbuildtype
        });

        this.data = response.data;
        this.isLoading = false;
        this.loadingStates = this.sortedData.map(() => false);
      }catch(error){
        console.log(error)
      }
    },
    async getTalentBuildData(hero, index){
      try{
        this.loadingStates[hero] = true;


        console.log(this.gametype);
        const response = await this.$axios.post("/api/v1/global/talents/build", {
          hero: hero,
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

        this.sortedData[index].talentbuilddata = response.data;

        this.talentbuilddata[hero] = response.data;
        this.loadingStates[hero] = false;

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
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : "";
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : "";
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : "";
      this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : "";

      this.talentbuilddata = {};
      this.loadingStates = {};
      //this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'asc';
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
  }
}
</script>