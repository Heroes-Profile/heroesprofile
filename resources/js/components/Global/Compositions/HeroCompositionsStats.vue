<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Hero Compositional Statistics'"></page-heading>
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :heroinput="getHeroID()"
      :gamemapinput="gamemap"
      :mirrormatchinput="mirrormatch"

      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
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
              Hero Composition
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
          </tr>
        </thead>
        <tbody>
          <template v-for="(row, index) in sortedData">
            <tr>
              <td >
                <div class="flex flex-wrap gap-1 justify-center w-auto max-md:flex-col">
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row.hero_one" :includehover="false" size="big"></hero-image-wrapper><span class="hidden md:block"></span>
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row.hero_two" :includehover="false" size="big"></hero-image-wrapper><span class="hidden md:block"></span>
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row.hero_three" :includehover="false" size="big"></hero-image-wrapper><span class="hidden md:block"></span>
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row.hero_four" :includehover="false" size="big"></hero-image-wrapper><span class="hidden md:block"></span>
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row.hero_five" :includehover="false" size="big"></hero-image-wrapper><span class="hidden md:block"></span>
              </div>
              </td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.popularity.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.games_played.toLocaleString('en-US') }}</td>
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
  name: 'HeroCompositionsStats',
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
      infoText: "Hero Composition stats based on differing increments, stat types, game type, or Rank. Click on a Composition to see detailed composition information.",
      sortKey: '',
      sortDir: 'desc',
      compositiondata: null,
      cancelTokenSource: null,
      timeframetype: null,
      timeframe: null,
      region: null,
      hero: null,
      gametype: null,
      gamemap: null,
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
        const response = await this.$axios.post("/api/v1/global/hero/compositions", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero: this.hero,
          game_type: this.gametype,
          game_map: this.gamemap,
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
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : this.minimumgames;
      this.loadingStates = {};

      let queryString = `?timeframe_type=${this.timeframetype}`;
      queryString += `&timeframe=${this.timeframe}`;
      queryString += `&game_type=${this.gametype}`;

      if(this.region){
        queryString += `&region=${this.region}`;
      }
      
      if(this.gamemap){
        queryString += `&game_map=${this.gamemap}`;
      }

      if(this.hero){
        queryString += `&hero=${this.hero}`;
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

      if(this.urlparameters["hero"]){
        this.hero = this.urlparameters["hero"];
      }

      if(this.urlparameters["game_map"]){
        this.gamemap = this.urlparameters["game_map"].split(',');
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