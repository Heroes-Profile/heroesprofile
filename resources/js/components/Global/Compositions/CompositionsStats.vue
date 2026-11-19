<template>
  <div>
    <global-async-debug-banner page-label="Global Compositions" />
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading :heading="'Composition Statistics'"></page-heading>
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 -mt-2 mb-4 flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
      <span class="uppercase text-gray-medium">{{ selectedPatch ? $t('All players · Patch :patch', { patch: selectedPatch }) : $t('All players') }}</span>
      <custom-button v-if="patchNotesUrl" @click="togglePatchNotes" :text="$t(':patch Patch Notes', { patch: timeframe[0] })" :alt="$t(':patch Patch Notes', { patch: timeframe[0] })" size="small" :ignoreclick="true"></custom-button>
    </div>
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
    <div v-if="compositiondata" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <patch-notes-panel v-if="showPatchNotes && patchNotesUrl" :version="timeframe[0]"></patch-notes-panel>

      <!-- Headline tiles -->
      <div v-if="summaryTiles.length" class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div v-for="tile in summaryTiles" :key="tile.label" class="bg-surface rounded p-6 flex flex-col gap-2 min-w-0">
          <span class="text-sm text-subtext">{{ $t(tile.label) }}</span>
          <span :class="['text-3xl font-semibold leading-[1.2]', tile.class || 'text-header']">{{ tile.value }}</span>
          <div v-if="tile.row" class="flex gap-1.5">
            <img v-for="(role, slot) in rowRoles(tile.row)" :key="slot" :src="roleImage(role)" :alt="$t(role)" :title="$t(role)" class="role-icon h-7 w-auto">
          </div>
          <span class="text-sm text-subtext">{{ tile.caption }}</span>
        </div>
      </div>

      <panel-box title="Team Compositions" subtitle="Win rate and popularity of every 5-role team composition. Roles are listed Tank → Support; win rate bars span 40–60%.">
        <template #right>
          <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <div class="flex items-center gap-4">
              <span class="text-sm text-subtext">{{ $t('Sort by') }}</span>
              <tab-button :small="true" tab1text="Win rate" tab2text="Popularity" :ignoreclick="true" :overridedefaultside="sortKey === 'popularity' ? 'right' : 'left'" @tab-click="sortBy($event === 'right' ? 'popularity' : 'win_rate')"></tab-button>
            </div>
            <pagination-bar v-if="lastPage > 1" :current="page" :last="lastPage" @page="page = $event"></pagination-bar>
          </div>
        </template>

        <div id="table-container" ref="tablecontainer" class="overflow-clip">
          <table id="responsive-table" class="min-w-0 w-full responsive-table rounded-table table-fixed relative" ref="responsivetable">
            <colgroup>
              <col class="w-[4%]">
              <col class="w-[42%]">
              <col class="w-[17%]">
              <col class="w-[17%]">
              <col class="w-[9%]">
              <col class="w-[11%]">
            </colgroup>
            <thead>
              <tr>
                <th class="py-2 px-4 text-left text-sm leading-4">#</th>
                <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Composition') }}</th>
                <th @click="sortTable('win_rate')" class="py-2 px-4 text-left text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate' }">
                  {{ $t('Win Rate') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('popularity')" class="py-2 px-4 text-left text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'popularity' }">
                  {{ $t('Popularity') }}<sort-indicator :column="'popularity'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('games_played')" class="py-2 px-4 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played' }">
                  {{ $t('Games') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(row, index) in pagedData" :key="row.composition_id">
                <tr>
                  <td class="py-3 px-4">{{ pageStart + index + 1 }}</td>
                  <td class="py-3 px-4">
                    <div class="grid grid-cols-5 gap-2 max-w-[36rem]">
                      <div v-for="(role, slot) in rowRoles(row)" :key="slot" class="flex flex-col items-center gap-1.5 min-w-0">
                        <img :src="roleImage(role)" :alt="$t(role)" class="role-icon h-8 w-auto">
                        <span class="text-xs text-subtext text-center leading-tight max-md:hidden">{{ $t(role) }}</span>
                      </div>
                    </div>
                  </td>
                  <!-- Win rate bars run from 40% to 60%; popularity against the most popular composition -->
                  <td class="py-3 px-4"><stat-bar :value="Math.min(100, Math.max(0, (row.win_rate - 40) * 5))" :display-text="row.win_rate.toFixed(2)" suffix="%" color="teal"></stat-bar></td>
                  <td class="py-3 px-4"><stat-bar :value="maxPopularity ? (row.popularity / maxPopularity) * 100 : 0" :display-text="row.popularity.toFixed(2)" suffix="%" color="teal"></stat-bar></td>
                  <td class="py-3 px-4 text-center">{{ row.games_played.toLocaleString('en-US') }}</td>
                  <td class="py-3 px-4 text-right">
                    <button type="button" class="group/bt inline-flex items-center whitespace-nowrap" :aria-expanded="!!expanded[row.composition_id]" @click="viewTopHeroes(row)">
                      <img v-if="loadingStates[row.composition_id]" class="logo-spin h-4 w-auto mr-2" src="/images/logo/heroesprofilelogo.png" alt="" />
                      <span class="group-hover/bt:underline">{{ expanded[row.composition_id] ? $t('Hide heroes') : $t('Top heroes') }}</span>
                      <basil-icon name="arrow-small" :class="['text-xs ml-2 transition-transform', expanded[row.composition_id] ? 'rotate-90' : '']"></basil-icon>
                    </button>
                  </td>
                </tr>
                <!-- The five most played heroes in each of the composition's roles -->
                <tr v-if="expanded[row.composition_id] && row.compositionheroes">
                  <td colspan="6" class="p-4">
                    <div class="grid grid-cols-5 gap-4">
                      <div v-for="(role, slot) in rowRoles(row)" :key="slot" class="flex flex-col gap-2 min-w-0">
                        <span class="text-sm text-subtext">{{ $t('Top :role', { role: $t(role) }) }}</span>
                        <template v-for="place in range" :key="place">
                          <div v-if="getHeroData(slot + 1, row, row.compositionheroes[role], place)" class="flex items-center gap-2 min-w-0">
                            <hero-image-wrapper :hero="getHeroData(slot + 1, row, row.compositionheroes[role], place)"></hero-image-wrapper>
                            <span class="truncate max-md:hidden">{{ $heroName(getHeroData(slot + 1, row, row.compositionheroes[role], place)) }}</span>
                          </div>
                        </template>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
          <span class="text-sm text-subtext">{{ $t('Showing :from–:to of :total compositions', { from: sortedData.length ? pageStart + 1 : 0, to: pageStart + pagedData.length, total: sortedData.length }) }}</span>
          <pagination-bar v-if="lastPage > 1" :current="page" :last="lastPage" @page="page = $event"></pagination-bar>
        </div>
      </panel-box>
    </div>
    <!-- The same layout in grey while the compositions load, the message on a tab of the table's panel -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div v-for="tile in 4" :key="tile" class="bg-surface rounded p-6 flex flex-col gap-2 min-w-0">
          <span class="text-sm skeleton-text w-fit">Highest win rate</span>
          <span class="text-3xl font-semibold leading-[1.2] skeleton-text w-fit">00.00%</span>
          <div v-if="tile > 1" class="flex gap-1.5">
            <skeleton-box v-for="slot in 5" :key="slot" class="h-7 w-[1.2rem]"></skeleton-box>
          </div>
          <span class="text-sm skeleton-text w-fit">0,000 games · 1,000+ games only</span>
        </div>
      </div>

      <panel-box title="Team Compositions" subtitle="Win rate and popularity of every 5-role team composition. Roles are listed Tank → Support; win rate bars span 40–60%." :skeleton="true" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading compositions...') }}</loading-component>
        <template #right>
          <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <div class="flex items-center gap-4">
              <span class="text-sm skeleton-text">{{ $t('Sort by') }}</span>
              <div class="relative">
                <tab-button class="invisible" :small="true" tab1text="Win rate" tab2text="Popularity" aria-hidden="true"></tab-button>
                <skeleton-box class="absolute inset-0"></skeleton-box>
              </div>
            </div>
            <div class="relative">
              <pagination-bar class="invisible" :current="1" :last="3" aria-hidden="true"></pagination-bar>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
          </div>
        </template>

        <div class="overflow-clip">
          <table class="min-w-0 w-full responsive-table rounded-table table-fixed">
            <colgroup>
              <col class="w-[4%]">
              <col class="w-[42%]">
              <col class="w-[17%]">
              <col class="w-[17%]">
              <col class="w-[9%]">
              <col class="w-[11%]">
            </colgroup>
            <thead>
              <tr>
                <th class="py-2 px-4 text-left text-sm leading-4"><span class="skeleton-text">#</span></th>
                <th class="py-2 px-4 text-left text-sm leading-4"><span class="skeleton-text">{{ $t('Composition') }}</span></th>
                <th class="py-2 px-4 text-left text-sm leading-4"><span class="skeleton-text">{{ $t('Win Rate') }}</span></th>
                <th class="py-2 px-4 text-left text-sm leading-4"><span class="skeleton-text">{{ $t('Popularity') }}</span></th>
                <th class="py-2 px-4 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Games') }}</span></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in perPage" :key="row">
                <td class="py-3 px-4"><span class="skeleton-text">00</span></td>
                <td class="py-3 px-4">
                  <div class="grid grid-cols-5 gap-2 max-w-[36rem]">
                    <div v-for="slot in 5" :key="slot" class="flex flex-col items-center gap-1.5 min-w-0">
                      <skeleton-box class="h-8 w-[1.375rem]"></skeleton-box>
                      <span class="text-xs leading-tight skeleton-text max-md:hidden">Bruiser</span>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4"><stat-bar value="0" display-text="00.00" suffix="%" :hide-value="true"></stat-bar></td>
                <td class="py-3 px-4"><stat-bar value="0" display-text="00.00" suffix="%" :hide-value="true"></stat-bar></td>
                <td class="py-3 px-4 text-center"><span class="skeleton-text">0,000</span></td>
                <td class="py-3 px-4 text-right"><span class="skeleton-text whitespace-nowrap">Top heroes<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
          <span class="text-sm skeleton-text">Showing 1–15 of 00 compositions</span>
          <div class="relative">
            <pagination-bar class="invisible" :current="1" :last="3" aria-hidden="true"></pagination-bar>
            <skeleton-box class="absolute inset-0"></skeleton-box>
          </div>
        </div>
      </panel-box>
    </div>
    <div v-else-if="dataError" class="flex items-center justify-center">
      {{ $t('Error: Reload page/filter') }}
    </div>


  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
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
      sortKey: 'win_rate',
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
      // By composition id
      loadingStates: {},
      expanded: {},
      showPatchNotes: false,
      page: 1,
      perPage: 15,
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
    patchNotesUrl() {
      if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
        return null;
      }
      const selectedVersion = this.timeframe[0];
      const match = this.filters.timeframes.find(t => t.code === selectedVersion);
      return match && match.patch_notes_url ? match.patch_notes_url : null;
    },
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
    },
    lastPage() {
      return Math.max(1, Math.ceil(this.sortedData.length / this.perPage));
    },
    pageStart() {
      return (this.page - 1) * this.perPage;
    },
    pagedData() {
      return this.sortedData.slice(this.pageStart, this.pageStart + this.perPage);
    },
    maxPopularity() {
      return Math.max(0, ...this.compositiondata.map(row => row.popularity));
    },
    selectedPatch() {
      return this.timeframetype === 'minor' && this.timeframe && this.timeframe.length === 1 ? this.timeframe[0] : null;
    },
    // Popularity is a composition's share of every team in the filters, so any row gives the total back
    gamesAnalysed() {
      const top = this.compositiondata.find(row => row.popularity > 0);
      return top ? Math.round(top.games_played / (top.popularity / 100)) : 0;
    },
    // Highest/lowest only count compositions with 1% of the games, up to 1,000
    cardMinimumGames() {
      return Math.min(1000, Math.max(1, Math.round(this.gamesAnalysed / 100)));
    },
    summaryTiles() {
      const rows = this.compositiondata;
      if (!rows.length) {
        return [];
      }
      const byPopularity = rows.slice().sort((a, b) => b.popularity - a.popularity);
      let eligible = rows.filter(row => row.games_played >= this.cardMinimumGames);
      if (!eligible.length) {
        eligible = rows;
      }
      const byWinRate = eligible.slice().sort((a, b) => b.win_rate - a.win_rate);
      const minimum = this.$t(':count+ games only', { count: this.cardMinimumGames.toLocaleString('en-US') });
      const best = byWinRate[0];
      const worst = byWinRate[byWinRate.length - 1];
      return [
        { label: 'Games analysed', value: this.gamesAnalysed.toLocaleString('en-US'), caption: this.$t(':count team compositions', { count: rows.length }) },
        { label: 'Most popular', value: byPopularity[0].popularity.toFixed(2) + '%', row: byPopularity[0], caption: this.$t(':games games · :rate% win rate', { games: byPopularity[0].games_played.toLocaleString('en-US'), rate: byPopularity[0].win_rate.toFixed(2) }) },
        { label: 'Highest win rate', value: best.win_rate.toFixed(2) + '%', class: 'text-win', row: best, caption: best.games_played.toLocaleString('en-US') + ' ' + this.$t('games') + ' · ' + minimum },
        { label: 'Lowest win rate', value: worst.win_rate.toFixed(2) + '%', class: 'text-loss', row: worst, caption: worst.games_played.toLocaleString('en-US') + ' ' + this.$t('games') + ' · ' + minimum },
      ];
    },
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
        const response = await this.$globalAsyncPost("/api/v1/global/compositions", {
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
          mirror: this.mirrormatch,
          minimum_games: this.minimumgames
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if (response.data?.status === "failure to validate inputs") {
          throw new Error("Failure to validate inputs");
        }
        this.compositiondata = response.data;
        this.loadingStates = {};
        this.expanded = {};
        this.page = 1;
      }catch(error){
        this.dataError = true;
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            this.tablewidth = scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
    async getTopHeroesData(row){
      const compositionid = row.composition_id;
      try{
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        this.loadingStates[compositionid] = true;
        const response = await this.$globalAsyncPost("/api/v1/global/compositions/heroes", {
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
          mirror: this.mirrormatch,
          minimum_games: this.minimumgames,
          composition_id: compositionid,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        row.compositionheroes = response.data;
        this.expanded[compositionid] = true;

      }catch(error){
        //Do something here
      }finally {
        this.loadingStates[compositionid] = false;
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

      this.showPatchNotes = false;

      this.compositiondata = null;
      this.getData();
    },
    togglePatchNotes() {
      this.showPatchNotes = !this.showPatchNotes;
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
      this.page = 1;
    },
    // The Sort by toggle: always highest first
    sortBy(key) {
      this.sortKey = key;
      this.sortDir = 'desc';
      this.page = 1;
    },
    // Opens the row's top heroes, fetching them the first time; a second click closes them
    viewTopHeroes(row){
      if (this.expanded[row.composition_id]) {
        this.expanded[row.composition_id] = false;
      } else if (row.compositionheroes) {
        this.expanded[row.composition_id] = true;
      } else {
        this.getTopHeroesData(row);
      }
    },
    rowRoles(row) {
      return [row.role_one.name, row.role_two.name, row.role_three.name, row.role_four.name, row.role_five.name];
    },
    roleImage(role) {
      return `/images/roles/${role.toLowerCase()}.PNG`;
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