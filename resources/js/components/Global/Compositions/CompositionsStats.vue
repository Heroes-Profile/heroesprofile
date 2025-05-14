<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Compositional Statistics'"></page-heading>
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
      :mirrormatchinput="mirrormatch"

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
      :minimumgamesdefault="minimumgames"
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    <div v-if="compositiondata">
      <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" ">
      <table id="responsive-table" class="responsive-table  relative max-w-[1500px]" ref="responsivetable">
     
    
        <thead>
          <tr>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              Composition
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate %
            </th>            
            <th @click="sortTable('popularity')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity %
            </th>
            <th @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>       
            <th></th>          
          </tr>
        </thead>
        <tbody>
          <template v-for="(row, index) in sortedData">
            <tr>
              <td >
                <div class="flex flex-wrap gap-1 justify-center w-auto max-md:flex-col ">
                <role-box :role="row.role_one.name"></role-box>
                <role-box :role="row.role_two.name"></role-box>
                <role-box :role="row.role_three.name"></role-box>
                <role-box :role="row.role_four.name"></role-box>
                <role-box :role="row.role_five.name"></role-box>
              </div>
              </td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.popularity.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.games_played.toLocaleString('en-US') }}</td>
              <td>
                <custom-button 
                  @click="viewTopHeroes(row.composition_id, index)" 
                  :text="'View Top Heroes'" 
                  :alt="'View Top Heroes'" 
                  size="small" 
                  :ignoreclick="true"
                  :loading="loadingStates[index]"
                  >
                  </custom-button>
              </td>
            </tr>
            <tr v-if="row.compositionheroes">
              <td colspan=5>
                <table class="min-w-0 ml-0 max-md:text-[.5em]  ">
                  <thead>
                    <tr>
                      <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                        Top {{ row.role_one.name }}
                      </th>
                      <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                        Top {{ row.role_two.name }}
                      </th>
                      <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                        Top {{ row.role_three.name }}
                      </th>
                      <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                        Top {{ row.role_four.name }}
                      </th>
                      <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                        Top {{ row.role_five.name }}
                      </th>
                    </tr>
   
                  </thead>
                  <tbody>
                    <tr v-for="index in range" class="mr-2">
                      <td>
                        <hero-image-wrapper v-if="getHeroData(1, row, row.compositionheroes[row.role_one.name], index)" :hero="getHeroData(1, row, row.compositionheroes[row.role_one.name], index)"></hero-image-wrapper><span class="max-md:hidden">{{ getHeroData(1, row, row.compositionheroes[row.role_one.name], index) ? getHeroData(1, row, row.compositionheroes[row.role_one.name], index).name : "" }}</span>
                      </td>

                      <td>
                        <hero-image-wrapper v-if="getHeroData(2, row, row.compositionheroes[row.role_two.name], index)" :hero="getHeroData(2, row, row.compositionheroes[row.role_two.name], index)"></hero-image-wrapper><span class="max-md:hidden">{{ getHeroData(2, row, row.compositionheroes[row.role_two.name], index) ? getHeroData(2, row, row.compositionheroes[row.role_two.name], index).name : "" }}</span>
                      </td>

                      <td>
                        <hero-image-wrapper v-if="getHeroData(3, row, row.compositionheroes[row.role_three.name], index)" :hero="getHeroData(3, row, row.compositionheroes[row.role_three.name], index)"></hero-image-wrapper><span class="max-md:hidden">{{ getHeroData(3, row, row.compositionheroes[row.role_three.name], index) ? getHeroData(3, row, row.compositionheroes[row.role_three.name], index).name : "" }}</span>
                      </td>

                      <td>
                        <hero-image-wrapper v-if="getHeroData(4, row, row.compositionheroes[row.role_four.name], index)" :hero="getHeroData(4, row, row.compositionheroes[row.role_four.name], index)"></hero-image-wrapper><span class="max-md:hidden">{{ getHeroData(4, row, row.compositionheroes[row.role_four.name], index) ? getHeroData(4, row, row.compositionheroes[row.role_four.name], index).name : "" }}</span>
                      </td>

                      <td>
                        <hero-image-wrapper v-if="getHeroData(5, row, row.compositionheroes[row.role_five.name], index)" :hero="getHeroData(5, row, row.compositionheroes[row.role_five.name], index)"></hero-image-wrapper><span class="max-md:hidden">{{ getHeroData(5, row, row.compositionheroes[row.role_five.name], index) ? getHeroData(5, row, row.compositionheroes[row.role_five.name], index).name : "" }}</span>
                      </td>

                    </tr>
                  </tbody>
                </table>
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
    <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
    </div>


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
    advancedfiltering: Boolean,
    patreonUser: Boolean,
    urlparameters: Object,
    heroes: Object,
  },
  data(){
    return {
      dataError: false,
      windowWidth: window.innerWidth,
      infoText: "Composition stats based on differing increments, stat types, game type, or Rank. Click on a Composition to see detailed composition information.",
      sortKey: '',
      sortDir: 'desc',
      compositiondata: null,
      cancelTokenSource: null,
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
      mirrormatch: 0,
      minimumgames: 100,
      isLoading: false,
      loadingStates: {},
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
      this.dataError = false;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/global/compositions", {
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
          mirrormatch: this.mirrormatch,
          minimum_games: this.minimumgames
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        this.compositiondata = response.data;
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
    async getTopHeroesData(compositionid, index){
      try{
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        this.loadingStates[index] = true;
        const response = await this.$axios.post("/api/v1/global/compositions/heroes", {
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
          mirrormatch: this.mirrormatch,
          minimum_games: this.minimumgames,
          composition_id: compositionid,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.sortedData[index].compositionheroes = response.data;
        this.loadingStates[index] = false;

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
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
      this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
      this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : this.minimumgames;
      this.loadingStates = {};

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

      if(this.minimumgames){
        queryString += `&minimum_games=${this.minimumgames}`;
      }

      queryString += `&mirror=${this.mirrormatch}`;

      const currentUrl = window.location.href;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}${queryString}`);

      this.compositiondata = null;
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
    viewTopHeroes(compositionid, index){
      this.loadingStates[index] = true;
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
      if (newindex > data.length || typeof data[newindex] === 'undefined') {
        return null;
      }

      return data[newindex].herodata;
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

      if(this.urlparameters["minimum_games"]){
        this.minimumgames = this.urlparameters["minimum_games"];
      }

      if (this.urlparameters["mirror"]) {
        this.mirrormatch = this.urlparameters["mirror"];
      }
    },
  }
}
</script>