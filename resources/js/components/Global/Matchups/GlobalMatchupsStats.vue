<template>
  <div>
    <global-async-debug-banner page-label="Global Matchups" />

    <div v-if="!selectedHero">
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading :heading="'Hero Counters & Matchups'"></page-heading>
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <!-- As the other global hero pages: the hero's heading, its name the hero picker, with the filters beside it -->
      <player-page-top>
        <template #heading>
          <page-heading :heading="$heroName(selectedHero)" :subheading="'Counters & matchups'">
            <hero-image-wrapper :hero="selectedHero" size="heading" :includehover="false"></hero-image-wrapper>
            <template #aboveHeading>
              <a href="/Global/Matchups" class="inline-flex items-center gap-1.5 text-sm font-normal text-lteal hover:underline mb-1"><basil-icon name="arrow-small" class="rotate-180 text-xs text-white"></basil-icon>{{ $t('All heroes') }}</a>
            </template>
            <template #headingText>
              <hero-heading-select :heroes="heroes" :value="selectedHero.id" textclass="" @hero-changed="handleInputChange({ value: $event })"></hero-heading-select>
            </template>
          </page-heading>
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
          :roleinput="role"
          :gamemapinput="gamemap"
          :playerrankinput="playerrank"
          :herorankinput="herorank"
          :rolerankinput="rolerank"
          :mirrormatchinput="mirrormatch"

          :gametypedefault="gametypedefault"
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
      </player-page-top>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

      <div v-if="allyenemydata" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
        <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]"></patch-notes-panel>

        <!-- The five best allies and biggest threats, as on the player's Matchups page -->
        <div class="grid md:grid-cols-2 gap-6">
          <panel-box v-for="card in topCards" :key="card.key" :title="card.title" :subtitle="card.subtitle" :divider="false">
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
              <a v-for="item in card.heroes" :key="item.hero.id" :href="'/Global/Matchups/' + encodeURIComponent(item.hero.name) + currentQuery()" class="group/bt bg-surface-1 hover:bg-surface-2 transition-colors rounded-control px-2 pt-5 pb-5 flex flex-col items-center text-center min-w-0">
                <hero-image-wrapper size="big" :hero="item.hero" :includehover="false"></hero-image-wrapper>
                <span class="text-sm font-bold mt-1.5 truncate max-w-full group-hover/bt:underline">{{ $heroName(item.hero) }}</span>
                <span :class="['text-xl font-bold mt-1.5 tabular-nums', card.valueClass]">{{ item.rate.toFixed(2) }}%</span>
                <span :class="['text-xs mt-0.5 tabular-nums', item.difference >= 0 ? 'text-win' : 'text-loss']">{{ $t(':difference vs expected', { difference: (item.difference > 0 ? '+' : '') + item.difference.toFixed(2) }) }}</span>
                <span class="text-xs text-subtext mt-1.5">{{ $t(card.games, { count: item.games_played.toLocaleString('en-US') }) }}</span>
              </a>
              <p v-if="!card.heroes.length" class="col-span-full text-sm text-subtext">{{ $t('No heroes with enough games for these filters.') }}</p>
            </div>
          </panel-box>
        </div>

        <panel-box :title="$t(':hero matchups', { hero: $heroName(selectedHero) })" :subtitle="$t(':hero\'s win rate with and against each hero · in brackets, how that compares with what the other hero\'s own win rate would lead you to expect', { hero: $heroName(selectedHero) })" :divider="false">
          <!-- Phones have no room in the Hero heading; the search sits above the table there -->
          <search-input v-model="heroSearch" :placeholder="$t('Search hero')" class="md:hidden w-full"></search-input>
          <div id="table-container" ref="tablecontainer" class="overflow-clip">
            <!-- Which columns are about the hero as an ally, which as an opponent; above the table so it reads the same in either table style -->
            <div class="grid grid-cols-[30%_35%_35%] max-md:text-xs">
              <div></div>
              <div class="py-3 px-1 md:px-3 text-center bg-teal/[0.18] rounded-tl-control">
                <span class="block text-[0.625rem] md:text-sm font-bold uppercase md:tracking-wide text-teal">{{ $t('With them · Same team as :hero', { hero: $heroName(selectedHero) }) }}</span>
                <span class="block text-xs text-subtext mt-1">{{ $t(':hero\'s win rate with this hero', { hero: $heroName(selectedHero) }) }}</span>
              </div>
              <div class="py-3 px-1 md:px-3 text-center bg-red/[0.18] rounded-tr-control">
                <span class="block text-[0.625rem] md:text-sm font-bold uppercase md:tracking-wide text-red">{{ $t('Against them · Enemy team', { hero: $heroName(selectedHero) }) }}</span>
                <span class="block text-xs text-subtext mt-1">{{ $t(':hero\'s win rate against this hero', { hero: $heroName(selectedHero) }) }}</span>
              </div>
            </div>
            <table id="responsive-table" ref="responsivetable" class="min-w-0 w-full rounded-table table-fixed max-md:text-xs">
              <colgroup>
                <col class="w-[18%]">
                <col class="w-[12%]">
                <col class="w-[22%]">
                <col class="w-[13%]">
                <col class="w-[22%]">
                <col class="w-[13%]">
              </colgroup>
              <thead>
                <tr>
                  <th class="py-2 px-2 md:px-6 text-left text-xs md:text-sm leading-4">
                    <div class="relative flex items-center gap-[1.125rem]">
                      <span class="cursor-pointer" @click="sortTable('hero_name')">{{ $t('Hero') }}<sort-indicator :column="'hero_name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                      <search-input v-model="heroSearch" :placeholder="$t('Search hero')" :collapsible="true" align="left" class="w-40 max-md:hidden"></search-input>
                    </div>
                  </th>
                  <th @click="sortTable('base_win_rate')" class="py-2 px-1 md:px-3 text-center text-xs md:text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'base_win_rate' }">
                    {{ $t('Their win rate') }}<span class="inline-flex align-middle ml-1" @click.stop><round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true"><p class="text-sm p-2 text-left font-normal whitespace-normal">{{ $t("This hero's own win rate in all games that match the filters. The numbers in brackets compare :hero's win rate with what this would lead you to expect: the same as theirs on the same team, the opposite of theirs against them.", { hero: $heroName(selectedHero) }) }}</p></round-image></span><sort-indicator :column="'base_win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th @click="sortTable('win_rate_as_ally')" class="py-2 px-1 md:px-3 text-center text-xs md:text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate_as_ally' }">{{ $t('Win rate with') }}<sort-indicator :column="'win_rate_as_ally'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
                  <th @click="sortTable('games_played_as_ally')" class="py-2 px-1 md:px-3 text-center text-xs md:text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played_as_ally' }">{{ $t('Games with') }}<sort-indicator :column="'games_played_as_ally'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
                  <th @click="sortTable('win_rate_against')" class="py-2 px-1 md:px-3 text-center text-xs md:text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate_against' }">{{ $t('Win rate against') }}<sort-indicator :column="'win_rate_against'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
                  <th @click="sortTable('games_played_against')" class="py-2 px-1 md:px-3 text-center text-xs md:text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played_against' }">{{ $t('Games against') }}<sort-indicator :column="'games_played_against'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in shownData" :key="rowHero(row).id">
                  <td class="py-2.5 px-2 md:px-6">
                    <a class="group/bt inline-flex items-center" :href="'/Global/Matchups/' + encodeURIComponent(rowHero(row).name) + currentQuery()">
                      <hero-image-wrapper :hero="rowHero(row)"></hero-image-wrapper><span class="hidden md:block ml-3 font-medium group-hover/bt:underline">{{ $heroName(rowHero(row)) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity hidden md:inline"></basil-icon>
                    </a>
                  </td>
                  <td class="py-2.5 px-1 md:px-3 text-center tabular-nums">{{ baseRate(row) === null ? '—' : baseRate(row).toFixed(2) + '%' }}</td>
                  <template v-for="side in ['ally', 'enemy']" :key="side">
                  <td class="py-2.5 px-2 md:px-6">
                    <div v-if="row[side] && row[side].games_played" class="flex items-center gap-3">
                      <!-- Teal when the hero does better than expected, red when worse -->
                      <stat-bar class="flex-1 max-md:hidden" :value="heroRate(row, side)" display-text="" :color="baseRate(row) === null || difference(row, side) >= 0 ? 'teal' : 'red'"></stat-bar>
                      <span class="max-md:flex-1 max-md:text-center md:text-right font-semibold tabular-nums whitespace-nowrap">{{ heroRate(row, side).toFixed(2) }}%<span v-if="baseRate(row) !== null" :class="['ml-1.5 text-xs font-normal max-md:hidden', difference(row, side) >= 0 ? 'text-win' : 'text-loss']">({{ difference(row, side) > 0 ? '+' : '' }}{{ difference(row, side).toFixed(2) }})</span></span>
                    </div>
                    <span v-else class="block text-center text-subtext">{{ $t('No data') }}</span>
                  </td>
                  <td class="py-2.5 px-1 md:px-3 text-center tabular-nums">{{ row[side] && row[side].games_played ? row[side].games_played.toLocaleString('en-US') : '—' }}</td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
        </panel-box>
      </div>
      <!-- The same cards in grey while the matchups load, the message on a tab of the first -->
      <div v-else-if="isLoading" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
        <panel-box v-if="patchNotesUrl" title="Patch Notes" :skeleton="true" :divider="false" class="md:!py-4">
          <template #right>
            <span class="text-sm skeleton-text">{{ $t('Show') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
          </template>
        </panel-box>
        <div class="grid md:grid-cols-2 gap-6">
          <panel-box v-for="(card, index) in topCards" :key="card.key" :title="card.title" :subtitle="card.subtitle" :divider="false" :skeleton="true" :class="{ 'relative md:!rounded-tl-none': index === 0 }">
            <loading-component v-if="index === 0" :compact="true" :tab="true" :textoverride="true">{{ $t('Loading matchups...') }}</loading-component>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
              <div v-for="tile in 5" :key="tile" class="bg-surface-1 rounded-control px-2 pt-5 pb-5 flex flex-col items-center text-center min-w-0">
                <skeleton-box class="w-20 h-20 !rounded-full"></skeleton-box>
                <span class="text-sm font-bold mt-1.5 skeleton-text">Samuro</span>
                <span class="text-xl font-bold mt-1.5 skeleton-text">00.00%</span>
                <span class="text-xs mt-1.5 skeleton-text">000 games together</span>
              </div>
            </div>
          </panel-box>
        </div>
        <panel-box :title="$t(':hero matchups', { hero: $heroName(selectedHero) })" :subtitle="$t(':hero\'s win rate with and against each hero · in brackets, how that compares with what the other hero\'s own win rate would lead you to expect', { hero: $heroName(selectedHero) })" :divider="false" :skeleton="true">
          <table-skeleton :bare="true" :rows="15" :columns="['hero', 'stat', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </panel-box>
      </div>
      <div v-else-if="dataError" class="flex items-center justify-center">
        {{ $t('Error: Reload page/filter') }}
      </div>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';

// The top cards' minimum games, as in the redesign
const MIN_TOP_GAMES = 40;
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
        dataError: false,
        windowWidth: window.innerWidth,
        isLoading: false,
        heroSearch: '',
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
      // The five best allies and biggest threats with 40+ games, ranked by how far the hero's win rate is from expected,
      // so a strong or weak hero doesn't top a list just for being strong or weak
      topCards() {
        const hero = this.$heroName(this.selectedHero);
        const ranked = (side, best) => (this.combineddata || [])
          .filter(row => row[side] && row[side].games_played >= MIN_TOP_GAMES && this.baseRate(row) !== null)
          .map(row => ({ hero: this.rowHero(row), rate: this.heroRate(row, side), difference: this.difference(row, side), games_played: row[side].games_played }))
          .sort((a, b) => best ? b.difference - a.difference : a.difference - b.difference)
          .slice(0, 5);
        return [
          { key: 'allies', title: 'Best allies', subtitle: this.$t('Biggest lift to :hero\'s win rate on the same team · 40+ games', { hero }), heroes: ranked('ally', true), games: ':count games together', valueClass: 'text-win' },
          { key: 'threats', title: 'Biggest threats', subtitle: this.$t('Biggest drop in :hero\'s win rate when facing them · 40+ games', { hero }), heroes: ranked('enemy', false), games: ':count games against', valueClass: 'text-loss' },
        ];
      },
      shownData() {
        const search = this.heroSearch.trim().toLowerCase();
        const rows = this.sortedData || [];
        return search ? rows.filter(row => [this.rowHero(row).name, this.$heroName(this.rowHero(row))].some(name => name.toLowerCase().includes(search))) : rows;
      },
      patchNotesUrl() {
        if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
          return null;
        }
        const selectedVersion = this.timeframe[0];
        const match = this.filters.timeframes.find(t => t.code === selectedVersion);
        return match && match.patch_notes_url ? match.patch_notes_url : null;
      },
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
            valA = a.enemy && a.enemy.games_played ? 100 - a.enemy.win_rate : -1;
            valB = b.enemy && b.enemy.games_played ? 100 - b.enemy.win_rate : -1;
          } else if (this.sortKey === 'games_played_as_ally') {
            valA = a.ally && a.ally.games_played ? a.ally.games_played : 0;
            valB = b.ally && b.ally.games_played ? b.ally.games_played : 0;
          } else if (this.sortKey === 'games_played_against') {
            valA = a.enemy && a.enemy.games_played ? a.enemy.games_played : 0;
            valB = b.enemy && b.enemy.games_played ? b.enemy.games_played : 0;
          }else if (this.sortKey === 'base_win_rate') {
            valA = a.ally && a.ally.stats && a.ally.stats.win_rate ? a.ally.stats.win_rate : 0;
            valB = b.ally && b.ally.stats && b.ally.stats.win_rate ? b.ally.stats.win_rate : 0; 
          }else if (this.sortKey === 'ally_difference') {
            valA = a.ally && a.ally.stats && a.ally.stats.win_rate ? a.ally.stats.win_rate.toFixed(2) - a.ally.win_rate.toFixed(2) : 0;
            valB = b.ally && b.ally.stats && b.ally.stats.win_rate ? b.ally.stats.win_rate.toFixed(2) - b.ally.win_rate.toFixed(2) : 0; 
          }else if (this.sortKey === 'enemy_difference') {
            valA = a.enemy && a.enemy.stats && a.enemy.stats.win_rate ? a.enemy.stats.win_rate.toFixed(2) - a.enemy.win_rate.toFixed(2) : 0;
            valB = b.enemy && b.enemy.stats && b.enemy.stats.win_rate ? b.enemy.stats.win_rate.toFixed(2) - b.enemy.win_rate.toFixed(2) : 0; 
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
        this.allyenemydata = null;

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Matchups Stats | Heroes Profile`;

        let basePath = '/Global/Matchups';
        history.pushState(null, null, `${basePath}/${this.selectedHero.name}`);
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
          const response = await this.$globalAsyncPost("/api/v1/global/matchups", {
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
            mirror: this.mirrormatch,
            role: this.role,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });
          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          this.allyenemydata = response.data;
          this.combineddata = response.data.combined;
        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.$nextTick(() => {
            if (window.matchMedia('(max-width: 767px)').matches) {
              scaleTableToFit(this.$refs.responsivetable, this.$refs.tablecontainer);
            }
          });
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
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) :null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
        this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;



        this.updateQueryString();
        this.allyenemydata = null;
        this.combineddata = null;
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

        if(this.role){
          queryString += `&role=${this.role}`;
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
          document.title = `${this.selectedHero.name} Matchups Stats | Heroes Profile`;

          let currentPath = window.location.pathname;
          let newPath = currentPath.replace(/\/[^/]*$/, `/${this.selectedHero.name}`);
          history.pushState(null, null, newPath);
          this.updateQueryString();

          this.allyenemydata = null;
          this.combineddata = null;

          //Have to use setTimeout to make this occur on next tic to allow header info/text to update properly.  
          setTimeout(() => {
            this.getData();
          }, .25);
        }


      },
      rowHero(row) {
        return row.ally && row.ally.hero ? row.ally.hero : row.enemy.hero;
      },
      // The row hero's own win rate in these filters
      baseRate(row) {
        const rate = row.ally?.stats?.win_rate ?? row.enemy?.stats?.win_rate;
        return rate === undefined || rate === null ? null : Number(rate);
      },
      // The selected hero's win rate with or against the row's hero: against is the row hero's rate turned round
      heroRate(row, side) {
        return side === 'enemy' ? 100 - row.enemy.win_rate : row.ally.win_rate;
      },
      // That against what the row hero's own win rate would lead you to expect: the same with them, the opposite against them
      difference(row, side) {
        const expected = side === 'enemy' ? 100 - this.baseRate(row) : this.baseRate(row);
        return Number((this.heroRate(row, side) - expected).toFixed(2));
      },
      // The page's filters, kept on links to another hero's matchups
      currentQuery() {
        return window.location.search;
      },
      getDifference(baseWinRate, winrate){
        var difference = (baseWinRate - winrate).toFixed(2);

        if(difference > 0){
          difference = "+" + difference;
        }
        return difference;
      }
    }
  }
</script>