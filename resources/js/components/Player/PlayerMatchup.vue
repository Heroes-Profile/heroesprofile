<template>
  <div>
    
    <div class="flex max-w-[1920px] mx-auto">
      <div class="">
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infotext" -->
      <page-heading v-if="esport" :heading="'Matchups'" :battletag="battletag" :esport="esport" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>
      <page-heading v-else :heading="'Matchups'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </div>
      <div class="w-full">
      <filters v-if="!esport"
      :onFilter="filterData"  
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :includegamemap="true"
      :includegametypefull="true"
      :hideadvancedfilteringbutton="true"
      :includehero="true"
      :includemultiseason="true"
      :includegamedaterange="true"
      >
    </filters>
      <panel-box v-else-if="esport && ngsFilterOptions">
        <div class="filter-row justify-center">
          <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" @dropdown-closed="getNgsData" :trackclosure="true" :defaultValue="ngsSeason"></single-select-filter>
          <single-select-filter :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" @dropdown-closed="getNgsData" :trackclosure="true" :defaultValue="ngsDivision"></single-select-filter>
          <single-select-filter :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" @dropdown-closed="getNgsData" :trackclosure="true" :defaultValue="ngsTeam"></single-select-filter>
        </div>
      </panel-box>
      </div>
    </div>
  <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

  <div v-if="data">
    <div class="flex flex-wrap justify-center">
      <group-box :text="'Top 5 Allies with more than 5 games'" :data="topfiveheroes"></group-box>
      <group-box :text="'Top 5 Enemies with more than 5 games'" :data="topfiveenemies"></group-box>
    </div>
    <table class="max-md:text-xs">
      <thead>
        <tr>
          <th class="py-2 px-3 text-center text-sm leading-4 text-gray-500 tracking-wider">
            <div class="flex items-center justify-center gap-3">
              <span class="cursor-pointer" @click="sortTable('name')">Hero<sort-indicator :column="'name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
              <search-input v-model="heroSearch" placeholder="Search hero" :collapsible="true" class="w-40"></search-input>
            </div>
          </th>
          <th @click="sortTable('ally_win_rate')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate as Ally %<sort-indicator :column="'ally_win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>            
          <th @click="sortTable('enemy_win_rate')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate Against Hero %<sort-indicator :column="'enemy_win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('ally_games_played')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played As Ally<sort-indicator :column="'ally_games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('enemy_games_played')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played Against<sort-indicator :column="'enemy_games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>                 
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in shownData" :key="row.hero.id">
          <td class="py-2 px-3">
            <a class="flex justify-center" :href="(esport ? ngsPlayerUrl : '/Player/' + battletag + '/' + blizzid + '/' + region) + '/Hero/' + row.hero.name">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>
            </a>
          </td>
           <td class="py-2 px-3 text-center">
            {{ row.ally_games_played ? (row.ally_win_rate ?? 0).toFixed(2) : "No data" }}
          </td>
          <td class="py-2 px-3 text-center">
            {{ row.enemy_games_played ? (row.enemy_win_rate ?? 0).toFixed(2) : "No data" }}
          </td>
          <td class="py-2 px-3 text-center">
            {{ row.ally_games_played ? row.ally_games_played.toLocaleString('en-US') : "No data" }}
          </td>
          <td class="py-2 px-3 text-center">
            {{ row.enemy_games_played ? row.enemy_games_played.toLocaleString('en-US') : "No data" }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div v-else-if="isLoading || asyncLoading" class="max-w-[1500px] mx-auto px-4 flex flex-col gap-6">
    <loading-component v-if="asyncLoading" :compact="true" :textoverride="true" :overrideimage="esport ? '/images/NGS/no-image-clipped.png' : undefined">This is taking longer than expected. A background task is computing your results. Please be patient.</loading-component>
    <loading-component v-else :compact="true" :textoverride="true" :overrideimage="esport ? '/images/NGS/no-image-clipped.png' : undefined">Large amount of data. Please be patient.</loading-component>
    <table-skeleton :bare="true" :rows="10" :columns="['avatar', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
  </div>



</div>
</template>

<script>
  export default {
    name: 'PlayerMatchup',
    components: {
    },
    props: {
      filters: Object,
      playerloadsetting: {
        type: [String, Boolean]
      },
      battletag: String,
      blizzid: String, 
      region: String,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
      regionsmap: Object,
    // 'NGS' on the NGS player pages, with the season, division and team from the URL
    esport: String,
    esportfilters: Object,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        asyncLoading: false,
        heroSearch: '',
        infotext: "Hero Matchups provide information on which heroes " + this.battletag + " is good with and against",
        gametype: null,
        data: null,
        sortKey: '',
        sortDir: 'desc',
        topfiveheroes: [],
        topfiveenemies: [],
        season: null,
        startdate: null,
        enddate: null,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
      }
    },
    created(){
      this.gametype = this.gametypedefault;

    },
    mounted() {
      if(this.esport){
        this.getNgsData();
        return;
      }
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    },
    computed: {
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    ngsPlayerUrl() {
      return `/Esports/NGS/Player/${this.battletag}/${this.blizzid}`;
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
      shownData() {
        const search = this.heroSearch.trim().toLowerCase();
        return search ? this.sortedData.filter(row => row.hero.name.toLowerCase().includes(search)) : this.sortedData;
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
    ngsParams(extra = {}) {
      return { battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam, ...extra };
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field) {
        this[field] = eventPayload.value;
      }
      const params = new URLSearchParams();
      if (this.ngsSeason) params.set('season', this.ngsSeason);
      if (this.ngsDivision) params.set('division', this.ngsDivision);
      if (this.ngsTeam) params.set('team', this.ngsTeam);
      const query = params.toString();
      history.pushState({}, '', window.location.pathname + (query ? `?${query}` : ''));
    },
      async getNgsData(){
        this.data = null;
        this.isLoading = true;
        try{
          const response = await this.$axios.post("/api/v1/esports/ngs/player/matchups", this.ngsParams());
          this.ngsFilterOptions = response.data.filters;
          this.data = response.data.tabledata;
          this.topfiveheroes = response.data.top_five_heroes;
          this.topfiveenemies = response.data.top_five_enemies;
        }catch(error){
          //Do something here
        }finally {
          this.isLoading = false;
        }
      },
      async getData(){
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
      
        try{
          const response = await this.$globalAsyncPost("/api/v1/player/matchups", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.gametype, 
            game_map: this.gamemap,
            hero: this.hero,
            season: this.season,
            start_date: this.startdate,
            end_date: this.enddate,
          },
          {
            cancelToken: this.cancelTokenSource.token,
            onLoadStatus: (status, meta) => {
              if (meta.phase === 'polling') {
                this.asyncLoading = true;
              }
            },
          });
          this.data = response.data.tabledata;
          this.topfiveheroes = response.data.top_five_heroes;
          this.topfiveenemies = response.data.top_five_enemies;
        }catch(error){
          //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.asyncLoading = false;
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      sortTable(key) {
        if (key === this.sortKey) {
          this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortDir = 'desc';
        }
        this.sortKey = key;
      },
      filterData(filteredData){
        this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
        this.startdate = filteredData.single["From Date"] || null;
        this.enddate = filteredData.single["To Date"] || null;

        this.data = null;
        this.topfiveheroes = null;
        this.topfiveenemies = null;
        this.getData();
      },

    }
  }
</script>