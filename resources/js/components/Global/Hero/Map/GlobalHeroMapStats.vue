<template>
  <div>

    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Map Statistics' : 'Hero Map Statistics'">
      <hero-image-wrapper v-if="selectedHero" :hero="selectedHero" :size="'big'"></hero-image-wrapper>
    </page-heading>
    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>


    <div v-else>
      <filters 
      :onFilter="filterData" 
      :filters="filters"
      :isLoading="isLoading" 

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :herolevelinput="herolevel"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :rolerankinput="rolerank"
      :mirrormatchinput="mirrormatch"


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
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

    <div v-if="data">
      <div class="max-w-[1500px] mx-auto">
        <span class="flex gap-4 mb-2 mx-4"> 
          <single-select-filter
            :values="filters.heroes" 
            :text="'Change Hero'" 
            :defaultValue="selectedHero.id"
            @input-changed="handleInputChange"
          ></single-select-filter>
        </span>
      </div>


      <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw] max-sm:text-xs   2xl:mx-auto  " style=" ">
       <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
      
          <thead>
            <tr>
              <th 
                v-for="column in dynamicColumns" 
                :key="column.value" 
                :class="['py-2', 'px-3', 'border-b', 'border-gray-200', 'text-left', 'text-sm', 'leading-4', 'text-gray-500', 'tracking-wider', column.sortable ? 'cursor-pointer' : '']"
                @click="column.sortable ? sortTable(column.value) : null"
                >
                  {{ column.text }}
              </th>        
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in sortedData" :key="row.rank">
              <td v-for="column in dynamicColumns" :key="column.value" class="">
                <div v-if="column.value === 'battletag'">
                  <a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row[column.value] }}</a>
                </div>
                <div v-else-if="column.value === 'most_played_hero'">
                  <div  v-if="row.most_played_hero" class="flex gap-x-2 items-center">
                    <hero-image-wrapper :hero="row.most_played_hero">
                      <image-hover-box :title="row.most_played_hero.name" :paragraph-one="'Games Played:' + row.hero_build_games_played"></image-hover-box>
                    </hero-image-wrapper>
                  </div>
                </div>
                <div v-else>
                  {{ row[column.value] }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
      </div>

    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
      <loading-component @cancel-request="cancelAxiosRequest" v-else></loading-component>
    </div>
    <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
    </div>
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
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        dataError: false,
        windowWidth: window.innerWidth,
        isLoading: false,
        infoText: "Hero Maps provide information on which maps are good for each hero",
        selectedHero: null,
        data: null,
        sortKey: '',
        sortDir: 'desc',
        cancelTokenSource: null,
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        gametype: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,
      }
    },
    created(){
      this.gametype = this.gametypedefault;
      this.timeframe = this.defaulttimeframe;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      
      if(this.inputhero){
        this.selectedHero = this.inputhero;
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
       dynamicColumns() {
        if (this.gametype.includes("sl")) {
          return [
            { text: 'Map', value: 'name', sortable: true },
            { text: 'Win Rate %', value: 'win_rate', sortable: true },
            { text: 'Ban Rate %', value: 'ban_rate', sortable: true },
            { text: 'Games Played', value: 'games_played', sortable: true },
          ];
        } else {
          return [
            { text: 'Map', value: 'name', sortable: true },
            { text: 'Win Rate %', value: 'win_rate', sortable: true },
            { text: 'Games Played', value: 'games_played', sortable: true },
          ];
        }
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
      clickedHero(hero) {
        this.selectedHero = hero;

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Map Stats | Heroes Profile`;

        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        this.getData();
      },
      async getData(){
        this.dataError = false;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/global/hero/map", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirrormatch: this.mirrormatch,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          
          this.data = response.data;
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

        this.isLoading = false;
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
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;


        this.updateQueryString();
        this.data = null; 
        this.getData();
      },
      updateQueryString(){
        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
        queryString += `&game_type=${this.gametype}`;

        if(this.region){
          queryString += `&region=${this.region}`;
        }
        
        if(this.herolevel){
          queryString += `&hero_level=${this.herolevel}`;
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

        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframetype == "major_major" || this.timeframe.length >= 3){
          return  true;
        }
        return false;
      },
      redirectChangeHero(){
        window.location.href = "/Global/Hero/Maps";
      },
      sortTable(key) {
        if (key === this.sortKey) {
          this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortDir = 'desc';
        }
        this.sortKey = key;
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

        if (this.urlparameters["mirror"]) {
          this.mirrormatch = this.urlparameters["mirror"];
        }
      },
      handleInputChange(eventPayload){
        if(eventPayload.value != ""){
          this.selectedHero = this.heroes.find(hero => hero.id === eventPayload.value);

          // Update document title dynamically
          document.title = `${this.selectedHero.name} Map Stats | Heroes Profile`;

          let currentPath = window.location.pathname;
          let newPath = currentPath.replace(/\/[^/]*$/, `/${this.selectedHero.name}`);
          history.pushState(null, null, newPath);
          this.updateQueryString();

          this.data = null;

          //Have to use setTimeout to make this occur on next tic to allow header info/text to update properly.  
          setTimeout(() => {
            this.getData();
          }, .25);
        }


      },
    }
  }
</script>