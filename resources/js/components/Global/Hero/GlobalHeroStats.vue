GlobalHeroStats<template>
  <div>
    <h1>Global Hero Statistics</h1>
    <infobox :input="infoText"></infobox>

    <filters :onFilter="filterData" :filters="filters" :gametypedefault="gametypedefault"></filters>

    <div v-if="this.data">
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
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sortedData" :key="row.hero_id">
            <td class="py-2 px-3 border-b border-gray-200">{{ row.name }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ "&#177;" }}{{ row.confidence_interval }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate_change }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.pick_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.ban_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.influence }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
            <td v-if="this.showStatTypeColumn" class="py-2 px-3 border-b border-gray-200">{{ row.total_filter_type }}</td>
          </tr>
        </tbody>
      </table>
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
  },
  data(){
    return {
      loading: false,
    	infoText: "Hero win rates based on differing increments, stat types, game type, or Rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'asc',
      data: [],

      //Sending to filter
      timeframetype: "minor",
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
      talentbuildtype: null
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaultMinor;
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
    defaultMinor() {
      return [this.filters.timeframes[0]?.code || ''];
    },
  },
  watch: {
  },
  methods: {
  	async getData(){
      try{
        this.loading = true;
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
        this.loading = false;
      }catch(error){
        console.log(error)
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
      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'asc';
      }
      this.sortKey = key;
    }
  }
}
</script>