<template>
  <div>
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :includesinglegametypefull="true"
      :includeseason="true"
      :playerheroroletype="true"
      :hideadvancedfilteringbutton="true"
      >
    </filters>

    <div v-if="data">

      <line-chart class="max-w-[1500px] mx-auto" :data="reversedData" :dataAttribute="'mmr'"></line-chart>

      <div class="max-w-[1500px] mx-auto mt-2">
        {{ this.gametype.toUpperCase() }} - League Tier Breakdowns | Player MMR: {{ data[0].mmr }}
      </div>
      <table class="">
        <thead>
          <tr>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              League
            </th>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              Min MMR
            </th>            
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              Max MMR
            </th>                
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in leaguedata" :key="index" :class="{ 'bg-yellow-200':  checkIfTierFound(row.min_mmr, row.max_mmr, row.tierFound) }">            
            <td>
              {{ printLeagueName(row.tier, row.league_tier) }}
            </td>
            <td>
              {{ row.min_mmr }}
            </td>
            <td>
              {{ row.max_mmr }}
            </td>
          </tr>
        </tbody>
      </table>

      <table class="">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game ID
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Date
            </th>            
            <th @click="sortTable('mmr_date_parsed')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              MMR Date Parsed
            </th>
            <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('mmr')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              MMR
            </th>    
            <th @click="sortTable('mmr_change')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              MMR Change
            </th>      
            <th @click="sortTable('winner')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Winner
            </th>                 
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a>
            </td>
            <td>
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ formatDate(row.mmr_date_parsed) }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}
            </td>
            <td>
              {{ row.mmr }}
            </td>
            <td>
              {{ row.mmr_change }}
            </td>
            <td>
              {{ row.winner }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <loading-component></loading-component>
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
    gametypedefault: Array,
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    region: {
      type: [String, Number]
    },
  },
  data(){
    return {
      userTimezone: moment.tz.guess(),
      isLoading: false,
      gametype: null,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      leaguedata: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault[0];
  },
  mounted() {
    this.getData();
  },
  computed: {
    reversedData() {
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
  },
  watch: {
  },
  methods: {
    async getData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/player/mmr", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          battletag: this.battletag,
          game_type: this.gametype,
          type: "Player",
        });
        this.data = response.data.tableData;
        this.leaguedata = response.data.leagueData;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    filterData(filteredData){
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      this.data = [];
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