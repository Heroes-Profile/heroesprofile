<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Heroes Profile MMR Data'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :includesinglegametypefull="true"
      :playerheroroletype="true"
      :hideadvancedfilteringbutton="true"
      :rolerequired="true"
      :herorequired="true"
      >
    </filters>
    
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data">

      <line-chart class="max-w-[1500px] mx-auto px-4" :data="reversedData" :dataAttribute="'mmr'" :title="`${battletag} HP MMR Graph for ${gametype}`"></line-chart>

      <div class="max-w-[1500px] mx-auto mt-2 px-4">
        {{ this.gametype.toUpperCase() }} - League Tier Breakdowns | HP Player MMR: {{ data[0].mmr }}
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>
      <table class="max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              League
            </th>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              Min HP MMR
            </th>            
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              Max HP MMR
            </th>                
          </tr>
        </thead>
        <tbody>
          <tr  v-for="(row, index) in leaguedata" :key="index" :class="{ 'bg-yellow':  checkIfTierFound(row.min_mmr, row.max_mmr, row.tierFound) }">            
            <td>
              {{ printLeagueName(row.tier, row.league_tier) }}
            </td>
            <td>
              {{ row.min_mmr.toLocaleString('en-US') }}
            </td>
            <td>
              {{ row.max_mmr ? row.max_mmr.toLocaleString('en-US') : "" }}
            </td>
          </tr>
        </tbody>
      </table>
      <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" ">
      <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game ID
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Date
            </th>            
            <th @click="sortTable('mmr_date_parsed')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              HP MMR Date Parsed
            </th>
            <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('mmr')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              HP MMR
            </th>    
            <th @click="sortTable('mmr_change')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              HP MMR Change
            </th>      
            <th @click="sortTable('winner')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Winner
            </th>                 
          </tr>
        </thead>
        <tbody>
          <template v-for="(row, index) in sortedData">
            <tr v-if="!patreonUser && index != 0 && index % 50 === 0">
              <td colspan="7" class="align-content-center">
                <dynamic-banner-ad :patreon-user="patreonUser" :index="index + 3" :mobile-override="false"></dynamic-banner-ad>
              </td>
            </tr>
            <tr>
              <td>
                <a class="link" :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a>
              </td>
              <td>
                {{ formatDate(row.game_date) }}
              </td>
              <td>
                {{ formatDate(row.mmr_date_parsed) }}
              </td>
              <td class="py-2 px-3  flex items-center gap-1">
                <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
              </td>
              <td>
                {{ row.mmr.toLocaleString('en-US') }}
              </td>
              <td>
                {{ row.mmr_change.toFixed(2) }}
              </td>
              <td>
                {{ row.winner }}
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>

  </div>

</template>

<script>
import moment from 'moment-timezone';

export default {
  name: 'MmrData',
  components: {
  },
  props: {
    filters: Object,
    playerloadsetting: {
      type: [String, Boolean]
    },
    gametypedefault: Array,
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    region: {
      type: [String, Number]
    },
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
  },
  data(){
    return {
      windowWidth: window.innerWidth,
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      gametype: null,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      leaguedata: null,
      type: "Player",
      infoText: "",
    }
  },
  created(){
    this.gametype = this.gametypedefault[0];
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData();
    }
  },
  computed: {
    reversedData() {
      if (!Array.isArray(this.data)) {
        console.error("this.data is not an array.");
        return [];
      }
      return [...this.data].reverse();
    },
    sortedData() {
      if (!this.sortKey) return this.data;
      return this.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
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
        const response = await this.$axios.post("/api/v1/player/mmr", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          battletag: this.battletag,
          game_type: this.gametype,
          type: this.type,
          hero: this.hero,
          role: this.role,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data.tableData;
        this.leaguedata = response.data.leagueData;
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
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      this.type = filteredData.single["Type"] ? filteredData.single["Type"] : "Player";

      this.data = null;
      this.sortKey = '';
      this.sortDir ='asc';
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
    printLeagueName(tier, tierID){
      if(tier){
        return tier;
      }
      if(tierID == 1){
        return "Bronze";
      }else if(tierID == 2){
        return "Silver";
      }else if(tierID == 3){
        return "Gold";
      }else if(tierID == 4){
        return "Platinum";
      }else if(tierID == 5){
        return "Diamond";
      }else if(tierID == 6){
        return "Master";
      }
    },
    checkIfTierFound(min_mmr, max_mmr, tierFound){
      if(tierFound){
        return true;
      }

      if(this.data[0].mmr > min_mmr && (!max_mmr || max_mmr == 0 || max_mmr == "")){
        return true;
      }

      return false;
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
  }
}
</script>