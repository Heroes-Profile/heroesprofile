<template>
  <div>
    <h1>Hero Map Statistics</h1>
    <infobox :input="infoText"></infobox>


    <div v-if="!selectedHero">
      <div v-for="hero in heroes" :key="hero.id">
        <hero-box-small :hero="hero" @click="clickedHero(hero)"></hero-box-small>
      </div>
    </div>

    <div v-else>
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :gametypedefault="gametypedefault"
        :includetimeframetype="true"
        :includetimeframe="true"
        :includeregion="true"
        :includeherolevel="true"
        :includegametype="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
        :includemirror="true"
        >
      </filters>



      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th @click="sortTable('talent_name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Map
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate %
            </th>            
            <th @click="sortTable('ban_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Ban Rate %
            </th>
            <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>                  
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sortedData" :key="row.map.map_id">
            <td class="py-2 px-3 border-b border-gray-200">{{ row.map.name }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.ban_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
          </tr>
        </tbody>
      </table>
    </div>



  </div>
</template>

<script>
export default {
  name: 'GlobalHeroMapStats',
  components: {
  },
  props: {
    inputhero: Object,
    heroes: Array,
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaulttimeframe: Array,
    defaulttimeframetype: String,
  },
  data(){
    return {
      infoText: "Hero Maps provide information on which maps are good for each hero",
      selectedHero: null,
      mapdata: null,
      sortKey: '',
      sortDir: 'asc',

      //Sending to filter
      timeframetype: null,
      timeframe: null,
      region: null,
      herolevel: null,
      gametype: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
      mirrormatch: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;

    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getData();
    }
  },
  mounted() {
  },
  computed: {
    sortedData() {
      if (!this.sortKey) return this.mapdata;
      return this.mapdata.slice().sort((a, b) => {
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
    clickedHero(hero) {
      this.selectedHero = hero;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      this.getData();
    },
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/hero/map", {
          userinput: this.selectedHero.name,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          game_type: this.gametype,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
        });
        this.mapdata = response.data;

        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : [];
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : "";

      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'asc';
      }
      this.sortKey = key;
    },
  }
}
</script>