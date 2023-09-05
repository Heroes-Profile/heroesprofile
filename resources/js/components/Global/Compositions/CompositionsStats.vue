<template>
  <div>
    <h1>Compositional Statistics</h1>
    <infobox :input="infoText"></infobox>
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includeherolevel="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :includeminimumgames="true"
      >
    </filters>

    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Composition
          </th>
          <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate %
          </th>            
          <th @click="sortTable('popularity')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Popularity %
          </th>
          <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played
          </th>       
          <th></th>          
        </tr>
      </thead>
      <tbody>
        <template v-for="(row, index) in sortedData">
          <tr>
            <td class="flex flex-wrap gap-1">
              <role-box :role="row.role_one.name"></role-box>
              <role-box :role="row.role_two.name"></role-box>
              <role-box :role="row.role_three.name"></role-box>
              <role-box :role="row.role_four.name"></role-box>
              <role-box :role="row.role_five.name"></role-box>
            </td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
            <td class="py-2 px-3 border-b border-gray-200"><button @click="viewTopHeroes(row.composition_id, index)" class="mt-4 bg-blue-500 text-white p-2 rounded">View Top Heroes</button></td>
          </tr>
          <tr v-if="row.compositionheroes">
            <td>
              <table class="min-w-full bg-white">
                <thead>
                  <tr>
                    <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                      Top {{ row.role_one.name }}
                    </th>
                    <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                      Top {{ row.role_two.name }}
                    </th>
                    <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                      Top {{ row.role_three.name }}
                    </th>
                    <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                      Top {{ row.role_four.name }}
                    </th>
                    <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                      Top {{ row.role_five.name }}
                    </th>
                  </tr>
 
                </thead>
                <tbody>
                  <div v-for="index in range">
                    <td>
                      <hero-box-small :hero="getHeroData(1, row, row.compositionheroes[row.role_one.name], index)"></hero-box-small>
                    </td>
                    <td>
                      <hero-box-small :hero="getHeroData(2, row, row.compositionheroes[row.role_two.name], index)"></hero-box-small>
                    </td>
                    <td>
                      <hero-box-small :hero="getHeroData(3, row, row.compositionheroes[row.role_three.name], index)"></hero-box-small>
                    </td>
                    <td>
                      <hero-box-small :hero="getHeroData(4, row, row.compositionheroes[row.role_four.name], index)"></hero-box-small>
                    </td>
                    <td>
                      <hero-box-small :hero="getHeroData(5, row, row.compositionheroes[row.role_five.name], index)"></hero-box-small>
                    </td>
                  </div>
                </tbody>
              </table>
            </td>
          </tr> 
        </template>
      </tbody>
    </table>

  </div>
</template>

<script>
export default {
  name: 'CompositionsStats',
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
      infoText: "Composition stats based on differing increments, stat types, game type, or Rank. Click on a Composition to see detailed composition information.",
      sortKey: '',
      sortDir: 'asc',
      compositiondata: [],

      //Sending to filter
      timeframetype: null,
      timeframe: null,
      region: null,
      herolevel: null,
      hero: null,
      gametype: null,
      gamemap: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
      mirrormatch: null,
      minimumgames: 100,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;
  	this.getData();
  },
  mounted() {
  },
  computed: {
    sortedData() {
      if (!this.sortKey) return this.compositiondata;
      return this.compositiondata.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    range() {
      return Array.from({ length: 5 }, (_, i) => i);
    }
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/compositions", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          hero: this.hero,
          game_type: this.gametype,
          map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          minimum_games: this.minimumgames
        });
        this.compositiondata = response.data;
      }catch(error){
        console.log(error);
      }
    },
    async getTopHeroesData(compositionid, index){
      try{
        const response = await this.$axios.post("/api/v1/global/compositions/heroes", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          hero: this.hero,
          game_type: this.gametype,
          map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          minimum_games: this.minimumgames,
          composition_id: compositionid,
        });

        this.sortedData[index].compositionheroes = response.data;
      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : [];
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : "";
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : "";
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 100;

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
    viewTopHeroes(compositionid, index){
      this.getTopHeroesData(compositionid, index);
    },

    getHeroData(location, row, data, index) {
      let newindex = index;
      if(location == 2){
        if(row.role_one.name == row.role_two.name){
          newindex += 5;
        }
      }else if(location == 3){
        if (row.role_one.name == row.role_two.name && row.role_two.name == row.role_three.name) {
          newindex += 10;
        }else if(row.role_two.name == row.role_three.name){
          newindex += 5;
        }
      }else if(location == 4){
        if(row.role_one.name == row.role_two.name && row.role_two.name == row.role_three.name && row.role_three.name == row.role_four.name){
          newindex += 15;
        }else if (row.role_two.name == row.role_three.name && row.role_three.name == row.role_four.name) {
          newindex += 10;
        }else if(row.role_three.name == row.role_four.name){
          newindex += 5;
        }
      }else if(location == 5){
        if(row.role_one.name == row.role_two.name && row.role_two.name == row.role_three.name && row.role_three.name == row.role_four.name && row.role_four.name == row.role_five.name){
          newindex += 20;
        }else if(row.role_two.name == row.role_three.name && row.role_three.name == row.role_four.name && row.role_four.name == row.role_five.name){
          newindex += 15;
        }else if (row.role_three.name == row.role_four.name && row.role_four.name == row.role_five.name) {
          newindex += 10;
        }else if(row.role_four.name == row.role_five.name){
          newindex += 5;
        }
      }

      if (newindex >= data.length || typeof data[newindex] === 'undefined') {
        return null;
      }
      return data[newindex].herodata;
    },
    countSameRoles(row, current_row_name) {

    }
  }
}
</script>