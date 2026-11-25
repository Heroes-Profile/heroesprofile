<template>
  <div>
    <global-async-debug-banner page-label="Global Compositions" />
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <!-- As Hero Stats: the heading with the filters beside it -->
    <player-page-top>
      <template #heading>
        <page-heading scope="Global" heading="Composition Stats" subheading="All players"></page-heading>
      </template>
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
    </player-page-top>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    <div v-if="compositiondata" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]"></patch-notes-panel>

      <!-- Headline tiles -->
      <div v-if="summaryTiles.length" class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div v-for="tile in summaryTiles" :key="tile.label" class="bg-surface rounded p-6 flex flex-col gap-2 min-w-0">
          <span class="text-sm text-subtext">{{ $t(tile.label) }}</span>
          <span :class="['text-3xl font-semibold leading-[1.2]', tile.class || 'text-header']">{{ tile.value }}</span>
          <div v-if="tile.row" class="flex gap-1.5">
            <icon-with-hover v-for="(role, slot) in rowRoles(tile.row)" :key="slot" popupsize="fit" :popup-hoverable="false">
              <template #trigger><img :src="roleImage(role)" :alt="$t(role)" class="role-icon h-7 w-auto"></template>
              <p class="font-bold text-white whitespace-nowrap">{{ $t(role) }}</p>
            </icon-with-hover>
          </div>
          <span class="text-sm text-subtext">{{ tile.caption }}</span>
        </div>
      </div>

      <panel-box title="Team Compositions" subtitle="Win rate and popularity of every 5-role team composition. Roles are listed Tank → Support; win rate bars span 40–60%.">
        <template #right>
          <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <tab-button tab1text="Win rate" tab2text="Popularity" :ignoreclick="true" :overridedefaultside="sortKey === 'popularity' ? 'right' : 'left'" @tab-click="sortBy($event === 'right' ? 'popularity' : 'win_rate')"></tab-button>
            <pagination-bar v-if="lastPage > 1" :current="page" :last="lastPage" @page="page = $event"></pagination-bar>
          </div>
        </template>

        <div id="table-container" ref="tablecontainer" class="overflow-clip">
          <!-- Fixed columns never overflow, so on phones it's laid out wide and scaleTableToFit shrinks it -->
          <table id="responsive-table" class="min-w-0 max-md:min-w-[60rem] w-full rounded-table table-fixed relative" ref="responsivetable">
            <colgroup>
              <!-- Wide enough for the "#" box beside the rank once sorted by another column -->
              <col class="w-[calc(7.75rem+1.5px)]">
              <col :class="showWinRateChange ? 'w-[30%]' : 'w-[34%]'">
              <col :class="showWinRateChange ? 'w-[22.9%]' : 'w-[26.9%]'">
              <col v-if="showWinRateChange" class="w-[8%]">
              <col class="w-[10.8%]">
              <col class="w-[12.9%]">
              <col class="w-[7.6%]">
            </colgroup>
            <thead>
              <tr>
                <!-- As on Global Hero Stats: sorted by anything but win rate, "#" is the place in that sort and Rank the win rate rank.
                     Wide enough for both, so sorting doesn't widen it -->
                <th @click="sortTable('rank')" class="py-2 px-4 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'rank' }">
                  <span v-if="rankChanged" class="inline-grid grid-cols-[2.75rem_2.5rem] gap-2 items-center"><span class="text-center">#</span><span class="text-left whitespace-nowrap">{{ $t('Rank') }}<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span></span>
                  <template v-else>#<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></template>
                </th>
                <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Composition') }}</th>
                <th @click="sortTable('win_rate')" class="py-2 px-4 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate' }">
                  {{ $t('Win Rate') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th v-if="showWinRateChange" @click="sortTable('win_rate_change')" class="py-2 px-4 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate_change' }">
                  {{ $t('Change') }}<sort-indicator :column="'win_rate_change'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('popularity')" class="py-2 px-4 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'popularity' }">
                  {{ $t('Popularity') }}<sort-indicator :column="'popularity'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('games_played')" class="py-2 px-4 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played' }">
                  {{ $t('Games') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('Top heroes') }}</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(row, index) in pagedData" :key="row.composition_id">
                <tr>
                  <td class="py-3.5 px-4 text-center text-lg font-semibold text-subtext"><div v-if="rankChanged" class="inline-grid grid-cols-[2.75rem_2.5rem] gap-2 items-center"><div class="bg-blue text-white text-base py-1 rounded-control text-center tabular-nums">{{ sortDir == 'desc' ? pageStart + index + 1 : sortedData.length - pageStart - index }}</div><span class="text-left tabular-nums">{{ row.rank }}</span></div><span v-else>{{ row.rank }}</span></td>
                  <td class="py-3.5 px-4">
                    <div class="grid grid-cols-5 gap-2 max-w-[33.5rem] mx-auto">
                      <div v-for="(role, slot) in rowRoles(row)" :key="slot" class="flex flex-col items-center gap-1.5 min-w-0">
                        <img :src="roleImage(role)" :alt="$t(role)" class="role-icon h-8 w-auto">
                        <span class="text-xs text-subtext text-center leading-tight max-md:hidden">{{ $t(role) }}</span>
                      </div>
                    </div>
                  </td>
                  <!-- Win rate bars run from 40% to 60% -->
                  <td class="py-3.5 px-8 text-lg"><stat-bar :value="Math.min(100, Math.max(0, (row.win_rate - 40) * 5))" :display-text="row.win_rate.toFixed(2)" suffix="%" color="teal"></stat-bar></td>
                  <td v-if="showWinRateChange" :class="['py-3.5 px-4 text-center text-lg whitespace-nowrap tabular-nums', row.win_rate_change > 0 ? 'text-win' : row.win_rate_change < 0 ? 'text-loss' : 'text-subtext']">
                    <template v-if="row.win_rate_change === null || row.win_rate_change === undefined">-</template>
                    <template v-else-if="row.win_rate_change > 0">&#9650; +{{ row.win_rate_change.toFixed(2) }}</template>
                    <template v-else-if="row.win_rate_change < 0">&#9660; {{ row.win_rate_change.toFixed(2) }}</template>
                    <template v-else>0.00</template>
                  </td>
                  <td class="py-3.5 px-4 text-center text-lg font-semibold text-header tabular-nums">{{ row.popularity.toFixed(2) }}%</td>
                  <td class="py-3.5 px-4 text-center text-lg">{{ row.games_played.toLocaleString('en-US') }}</td>
                  <td class="py-3.5 px-4 text-center">
                    <button type="button" class="w-9 h-9 inline-flex items-center justify-center rounded-control hover:bg-surface-3 transition-colors" :aria-expanded="!!expanded[row.composition_id]" :aria-label="expanded[row.composition_id] ? $t('Hide heroes') : $t('Top heroes')" :title="expanded[row.composition_id] ? $t('Hide heroes') : $t('Top heroes')" @click="viewTopHeroes(row)">
                      <img v-if="loadingStates[row.composition_id]" class="logo-spin h-4 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
                      <basil-icon v-else name="arrow-small" :class="['text-xs transition-transform', expanded[row.composition_id] ? 'rotate-90' : '']"></basil-icon>
                    </button>
                  </td>
                </tr>
                <!-- The five most played heroes in each of the composition's roles -->
                <tr v-if="expanded[row.composition_id] && row.compositionheroes">
                  <td :colspan="showWinRateChange ? 7 : 6" class="p-4">
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
      <!-- The collapsed Patch Notes card (patch-notes-panel) -->
      <panel-box v-if="patchNotesUrl" title="Patch Notes" :skeleton="true" :divider="false" class="md:!py-4">
        <template #right>
          <span class="text-sm skeleton-text">{{ $t('Show') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
        </template>
      </panel-box>
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
            <div class="relative">
              <tab-button class="invisible" tab1text="Win rate" tab2text="Popularity" aria-hidden="true"></tab-button>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
            <div class="relative">
              <pagination-bar class="invisible" :current="1" :last="3" aria-hidden="true"></pagination-bar>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
          </div>
        </template>

        <div class="overflow-clip">
          <table class="min-w-0 w-full rounded-table table-fixed">
            <colgroup>
              <!-- Wide enough for the "#" box beside the rank once sorted by another column -->
              <col class="w-[calc(7.75rem+1.5px)]">
              <col class="w-[34%]">
              <col class="w-[26.9%]">
              <col class="w-[10.8%]">
              <col class="w-[12.9%]">
              <col class="w-[7.6%]">
            </colgroup>
            <thead>
              <tr>
                <th class="py-2 px-4 text-center text-sm leading-4"><span class="skeleton-text">#</span></th>
                <th class="py-2 px-4 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Composition') }}</span></th>
                <th class="py-2 px-4 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Win Rate') }}</span></th>
                <th class="py-2 px-4 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Popularity') }}</span></th>
                <th class="py-2 px-4 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Games') }}</span></th>
                <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap"><span class="skeleton-text">{{ $t('Top heroes') }}</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in perPage" :key="row">
                <td class="py-3.5 px-4 text-center text-lg font-semibold"><span class="skeleton-text">00</span></td>
                <td class="py-3.5 px-4">
                  <div class="grid grid-cols-5 gap-2 max-w-[33.5rem] mx-auto">
                    <div v-for="slot in 5" :key="slot" class="flex flex-col items-center gap-1.5 min-w-0">
                      <skeleton-box class="h-8 w-[1.375rem]"></skeleton-box>
                      <span class="text-xs leading-tight skeleton-text max-md:hidden">Bruiser</span>
                    </div>
                  </div>
                </td>
                <td class="py-3.5 px-8 text-lg"><stat-bar value="0" display-text="00.00" suffix="%" :hide-value="true"></stat-bar></td>
                <td class="py-3.5 px-4 text-center text-lg font-semibold"><span class="skeleton-text">00.00%</span></td>
                <td class="py-3.5 px-4 text-center text-lg"><span class="skeleton-text">0,000</span></td>
                <td class="py-3.5 px-4 text-center"><skeleton-box class="inline-block align-middle w-9 h-9"></skeleton-box></td>
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
      // By composition id
      loadingStates: {},
      expanded: {},
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
    // The server only sends change for one unfiltered patch and game type
    showWinRateChange() {
      return Array.isArray(this.compositiondata) && this.compositiondata.some(row => row.win_rate_change !== null && row.win_rate_change !== undefined);
    },
    // Sorted by something other than win rate (or its rank), the # column shows the place in that sort beside the rank
    rankChanged() {
      return !!this.sortKey && this.sortKey !== 'rank' && this.sortKey !== 'win_rate';
    },
    // Each composition's rank by win rate, fixed whatever the table is sorted by; unsorted, the table is in rank order
    rankedData() {
      return this.compositiondata.slice().sort((a, b) => b.win_rate - a.win_rate).map((row, index) => ({ ...row, rank: index + 1 }));
    },
    sortedData() {
      if (!this.sortKey) return this.rankedData;
      return this.rankedData.slice().sort((a, b) => {
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


      this.compositiondata = null;
      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        // Rank 1 is the best, so rank starts low to high; everything else starts highest first
        this.sortDir = key === 'rank' ? 'asc' : 'desc';
      }
      this.sortKey = key;
      this.page = 1;
    },
    // The Win rate / Popularity toggle: always highest first
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