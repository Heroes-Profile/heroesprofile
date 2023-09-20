<template>
  <div>
    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Matchups Statistics' : 'Hero Matchups Statistics'"></page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
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
      :includerole="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      >
    </filters>
    <div v-if="allyenemydata" class="flex flex-wrap gap-4 justify-center items-center">
      <group-box :text="'TOP 5 ALLIES ON HEROS TEAM'" :data="allyenemydata.ally.slice(0, 5)"></group-box>
      <group-box :text="'TOP 5 THREATS ON ENEMIES TEAM'" :data="allyenemydata.enemy.slice(0, 5)"></group-box>



      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th @click="sortTable('hero_name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('win_rate_as_ally')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate as Ally %
            </th>            
            <th @click="sortTable('win_rate_against')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Against  {{ this.selectedHero.name }} %
            </th>
            <th @click="sortTable('games_played_as_ally')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played As Ally
            </th>
            <th @click="sortTable('games_played_against')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played Against {{ this.selectedHero.name }}
            </th>                 
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td class="py-2 px-3 border-b border-gray-200">
              <div class="flex items-center">
                <hero-image-wrapper :hero="row.ally ? row.ally.hero : row.enemy.hero "></hero-image-wrapper>
                <span class="ml-left px-3">{{ row.ally && row.ally.hero ? row.ally.hero.name : row.enemy.hero.name }}</span>
              </div>
            </td>
            <td class="py-2 px-3 border-b border-gray-200">
              {{ row.ally && row.ally.win_rate ? row.ally.win_rate : 0 }}
            </td>
            <td class="py-2 px-3 border-b border-gray-200">
              {{ row.enemy && row.enemy.win_rate ? row.enemy.win_rate : 0 }}
            </td>
            <td class="py-2 px-3 border-b border-gray-200">
              {{ row.ally && row.ally.games_played ? row.ally.games_played : 0 }}
            </td>

            <td class="py-2 px-3 border-b border-gray-200">
              {{ row.enemy && row.enemy.games_played ? row.enemy.games_played : 0 }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <loading-component></loading-component>
    </div>
  </div>


</div>
</template>

<script>
  export default {
    name: 'GlobalMatchupsStats',
    components: {
    },
    props: {
      filters: Object,
      inputhero: Object,
      heroes: Array,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
    },
    data(){
      return {
        infoText: "Hero Matchups provide information on which heroes are good with and against for a particular hero",
        selectedHero: null,
        allyenemydata: null,
        sortKey: '',
        sortDir: 'asc',
        combineddata: null,

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        role: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: null,
        role: null,
      }
    },
    created(){
      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
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
        if (!this.sortKey || !this.combineddata){
          return this.combineddata;
        } 
        return this.combineddata.slice().sort((a, b) => {
          let valA, valB;
          
          if (this.sortKey === 'hero_name') {
            valA = a.ally && a.ally.hero ? a.ally.hero.name : (a.enemy && a.enemy.hero ? a.enemy.hero.name : '');
            valB = b.ally && b.ally.hero ? b.ally.hero.name : (b.enemy && b.enemy.hero ? b.enemy.hero.name : '');
          } else if (this.sortKey === 'win_rate_as_ally') {
            valA = a.ally && a.ally.win_rate ? a.ally.win_rate : 0;
            valB = b.ally && b.ally.win_rate ? b.ally.win_rate : 0;
          } else if (this.sortKey === 'win_rate_against') {
            valA = a.enemy && a.enemy.win_rate ? a.enemy.win_rate : 0;
            valB = b.enemy && b.enemy.win_rate ? b.enemy.win_rate : 0;
          } else if (this.sortKey === 'games_played_as_ally') {
            valA = a.ally && a.ally.games_played ? a.ally.games_played : 0;
            valB = b.ally && b.ally.games_played ? b.ally.games_played : 0;
          } else if (this.sortKey === 'games_played_against') {
            valA = a.enemy && a.enemy.games_played ? a.enemy.games_played : 0;
            valB = b.enemy && b.enemy.games_played ? b.enemy.games_played : 0;
          }
          
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
          const response = await this.$axios.post("/api/v1/global/matchups", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirrormatch: this.mirrormatch,
            role: this.role,
          });
          this.allyenemydata = response.data;
          this.combineddata = response.data.combined;

        }catch(error){
          console.log(error);
        }
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : [];
        this.role = filteredData.single["Role"] ? filteredData.single["Role"] : "";
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
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