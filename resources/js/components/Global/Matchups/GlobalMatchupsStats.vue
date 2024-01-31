<template>
  <div>
    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Matchups Statistics' : 'Hero Matchups Statistics'">
      <hero-image-wrapper v-if="selectedHero" :hero="selectedHero" :size="'big'"></hero-image-wrapper>
    </page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <filters 
      :onFilter="filterData" 
      :filters="filters" 

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :herolevelinput="herolevel"
      :roleinput="role"
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
      :includerole="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    <takeover-ad :patreon-user="patreonUser"></takeover-ad>

    <div v-if="allyenemydata" class="flex flex-wrap gap-4  ">
      <group-box :text="'TOP 5 ALLIES ON HEROS TEAM'" :data="allyenemydata.ally.slice(0, 5)" :type="'Matchups'" color="blue"></group-box>
      <group-box :text="'TOP 5 THREATS ON ENEMIES TEAM'" :data="allyenemydata.enemy.slice(0, 5)" :type="'Matchups'" color="red"></group-box>

      <div class="md:min-w-[1500px] md:px-20">

      <span class="flex gap-4 mb-2"> {{ this.selectedHero.name }} {{ "Talent Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span>
      <div id="table-container" ref="tablecontainer" class="  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" ">
      <table id="responsive-table" class="responsive-table  relative max-sm:text-xs" ref="responsivetable">
      
        <thead>
          <tr>
            <th @click="sortTable('hero_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('win_rate_as_ally')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate as Ally %
            </th>            
            <th @click="sortTable('win_rate_against')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Against  {{ this.selectedHero.name }} %
            </th>
            <th @click="sortTable('games_played_as_ally')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played As Ally
            </th>
            <th @click="sortTable('games_played_against')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played Against {{ this.selectedHero.name }}
            </th>                 
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td class="py-2 px-3 ">
              <div class="flex items-center ">
                <hero-image-wrapper :hero="row.ally ? row.ally.hero : row.enemy.hero " ></hero-image-wrapper>
                <span class="ml-left px-3 hidden md:block">{{ row.ally && row.ally.hero ? row.ally.hero.name : row.enemy.hero.name }}</span>
              </div>
            </td>
            <td class="py-2 px-3 ">
              {{ row.ally && row.ally.win_rate.toFixed(2) ? row.ally.win_rate.toFixed(2) : 0 }}
            </td>
            <td class="py-2 px-3 ">
              {{ row.enemy && row.enemy.win_rate.toFixed(2) ? row.enemy.win_rate.toFixed(2) : 0 }}
            </td>
            <td class="py-2 px-3 ">
              {{ row.ally && row.ally.games_played.toLocaleString() ? row.ally.games_played.toLocaleString() : 0 }}
            </td>

            <td class="py-2 px-3 ">
              {{ row.enemy && row.enemy.games_played.toLocaleString() ? row.enemy.games_played.toLocaleString() : 0 }}
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
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
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        windowWidth: window.innerWidth,
        isLoading: false,
        infoText: "Hero Matchups provide information on which heroes are good with and against for a particular hero",
        selectedHero: null,
        cancelTokenSource: null,
        allyenemydata: null,
        sortKey: '',
        sortDir: 'desc',
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
        mirrormatch: 0,
        role: null,
      }
    },
    created(){
      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
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
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
        try{
          const response = await this.$axios.post("/api/v1/global/matchups", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirrormatch: this.mirrormatch,
            role: this.role,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });
          this.allyenemydata = response.data;
          this.combineddata = response.data.combined;
        }catch(error){
          //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
         /* this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            const newTableWidth = this.windowWidth /responsivetable.clientWidth;
            responsivetable.style.transformOrigin = 'top left';
            responsivetable.style.transform = `scale(${newTableWidth})`;
            const container = this.$refs.tablecontainer;
            this.tablewidth = newTableWidth;
            container.style.height = (responsivetable.clientHeight * newTableWidth) + 'px';
          }
        });*/
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
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) :null;
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;

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

        this.allyenemydata = null;
        this.combineddata = null;
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
      redirectChangeHero(){
        window.location.href = "/Global/Matchups";
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

        if(this.urlparameters["role"]){
          this.role = this.urlparameters["role"];
        }

        if(this.urlparameters["game_map"]){
          this.gamemap = this.urlparameters["game_map"].split(',');
        }

        if (this.urlparameters["league_tier"]) {
          this.playerrank = this.urlparameters["league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }

        if (this.urlparameters["hero_league_tier"]) {
          this.herorank = this.urlparameters["hero_league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }

        if (this.urlparameters["role_league_tier"]) {
          this.rolerank = this.urlparameters["role_league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }

        if (this.urlparameters["mirror"]) {
          this.mirrormatch = this.urlparameters["mirror"];
        }
      },
    }
  }
</script>