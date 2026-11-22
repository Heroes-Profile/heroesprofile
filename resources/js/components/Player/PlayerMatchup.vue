<template>
  <div>
    
    <player-page-top>
      <template #heading>
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infotext" -->
      <page-heading v-if="esport" :heading="'Matchups'" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
      <page-heading v-else :heading="'Matchups'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
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
      <filters v-else-if="esport"
        :filters="filters"
        :isLoading="isLoading"
        :onFilter="() => getNgsData()"
        :onChange="ngsFilterChange"
        :extrafilters="[
          { text: 'Seasons', values: ngsFilterOptions?.seasons || [], defaultValue: ngsSeason },
          { text: 'Divisions', values: ngsFilterOptions?.divisions || [], defaultValue: ngsDivision, show: esport == 'NGS' },
          { text: 'Teams', values: ngsTeamOptions, defaultValue: ngsTeam },
        ]"
        :excludetimeframes="true"
        :excludegametype="true"
        :hideadvancedfilteringbutton="true"
      >
      </filters>
      </div>
    </player-page-top>
  <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

  <div v-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-8">
    <!-- The five best allies and toughest enemies with 5+ games -->
    <div class="grid md:grid-cols-2 gap-8">
      <panel-box v-for="card in topCards" :key="card.title" :title="card.title" :subtitle="card.subtitle" :divider="false">
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
          <a v-for="item in card.heroes" :key="item.hero.id" :href="heroUrl(item.hero)" class="group/bt bg-surface-1 hover:bg-surface-2 transition-colors rounded-control px-2 pt-5 pb-5 flex flex-col items-center text-center min-w-0">
            <hero-image-wrapper size="big" :hero="item.hero" :includehover="false"></hero-image-wrapper>
            <span class="text-sm font-bold mt-1.5 truncate max-w-full group-hover/bt:underline">{{ $heroName(item.hero) }}</span>
            <span :class="['text-xl font-bold mt-1.5 tabular-nums', card.valueClass]">{{ item[card.rate].toFixed(2) }}%</span>
            <span class="text-xs text-subtext mt-1.5">{{ $t(card.games, { count: item[card.count].toLocaleString('en-US') }) }}</span>
          </a>
          <p v-if="!card.heroes.length" class="col-span-full text-sm text-subtext">{{ $t('No heroes with 5+ games for these filters.') }}</p>
        </div>
      </panel-box>
    </div>

    <panel-box title="All Matchups" subtitle="Your win rate with each hero on your team and against each hero on the enemy team." :divider="false">
      <div class="overflow-clip">
        <!-- Which columns are about the hero as your ally, which as your opponent; above the table so it reads the same in either table style -->
        <div class="grid grid-cols-[18.1%_40.95%_40.95%] max-md:text-xs">
          <div></div>
          <div class="py-3 px-3 text-center bg-teal/[0.18] rounded-tl-control">
            <span class="block text-sm font-bold uppercase tracking-wide text-teal">{{ $t('With them · On your team') }}</span>
            <span class="block text-xs text-subtext mt-1">{{ $t('Your win rate when this hero is your ally') }}</span>
          </div>
          <div class="py-3 px-3 text-center bg-red/[0.18] rounded-tr-control">
            <span class="block text-sm font-bold uppercase tracking-wide text-red">{{ $t('Against them · On enemy team') }}</span>
            <span class="block text-xs text-subtext mt-1">{{ $t('Your win rate when this hero is your opponent') }}</span>
          </div>
        </div>
        <table class="min-w-0 w-full responsive-table rounded-table table-fixed max-md:text-xs">
          <colgroup>
            <col class="w-[18.1%]">
            <col class="w-[20.475%]">
            <col class="w-[20.475%]">
            <col class="w-[20.475%]">
            <col class="w-[20.475%]">
          </colgroup>
          <thead>
            <tr>
              <th class="py-2 px-6 text-left text-sm leading-4">
                <div class="flex items-center gap-[1.125rem]">
                  <span class="cursor-pointer" @click="sortTable('name')">{{ $t('Hero') }}<sort-indicator :column="'name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                  <search-input v-model="heroSearch" :placeholder="$t('Search hero')" :collapsible="true" class="w-40"></search-input>
                </div>
              </th>
              <th @click="sortTable('ally_win_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'ally_win_rate' }">{{ $t('Win rate with') }}<sort-indicator :column="'ally_win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
              <th @click="sortTable('ally_games_played')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'ally_games_played' }">{{ $t('Games with') }}<sort-indicator :column="'ally_games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
              <th @click="sortTable('enemy_win_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'enemy_win_rate' }">{{ $t('Win rate against') }}<sort-indicator :column="'enemy_win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
              <th @click="sortTable('enemy_games_played')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'enemy_games_played' }">{{ $t('Games against') }}<sort-indicator :column="'enemy_games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in shownData" :key="row.hero.id">
              <td class="py-2.5 px-6">
                <a class="group/bt inline-flex items-center" :href="heroUrl(row.hero)">
                  <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="hidden md:block ml-3 font-medium group-hover/bt:underline">{{ $heroName(row.hero) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity hidden md:inline"></basil-icon>
                </a>
              </td>
              <td class="py-2.5 px-6">
                <div v-if="row.ally_games_played" class="flex items-center gap-3">
                  <stat-bar class="flex-1" :value="row.ally_win_rate ?? 0" display-text="" :color="$winRateColor(row.ally_win_rate)"></stat-bar>
                  <span :class="['w-[3.6rem] text-right font-semibold tabular-nums', rateClass(row.ally_win_rate)]">{{ (row.ally_win_rate ?? 0).toFixed(2) }}%</span>
                </div>
                <span v-else class="block text-center text-subtext">{{ $t('No data') }}</span>
              </td>
              <td class="py-2.5 px-3 text-center">{{ row.ally_games_played ? row.ally_games_played.toLocaleString('en-US') : '—' }}</td>
              <td class="py-2.5 px-6">
                <div v-if="row.enemy_games_played" class="flex items-center gap-3">
                  <stat-bar class="flex-1" :value="row.enemy_win_rate ?? 0" display-text="" :color="$winRateColor(row.enemy_win_rate)"></stat-bar>
                  <span :class="['w-[3.6rem] text-right font-semibold tabular-nums', rateClass(row.enemy_win_rate)]">{{ (row.enemy_win_rate ?? 0).toFixed(2) }}%</span>
                </div>
                <span v-else class="block text-center text-subtext">{{ $t('No data') }}</span>
              </td>
              <td class="py-2.5 px-3 text-center">{{ row.enemy_games_played ? row.enemy_games_played.toLocaleString('en-US') : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </panel-box>
  </div>
  <!-- The same cards in grey while the matchups load, the message on a tab of the first -->
  <div v-else-if="isLoading || asyncLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-8">
    <div class="grid md:grid-cols-2 gap-8">
      <panel-box v-for="(card, index) in topCards" :key="card.title" :title="card.title" :subtitle="card.subtitle" :divider="false" :skeleton="true" :class="{ 'relative md:!rounded-tl-none': index === 0 }">
        <template v-if="index === 0">
          <loading-component v-if="asyncLoading" :compact="true" :tab="true" :textoverride="true" :overrideimage="league?.loaderImage">{{ $t('This is taking longer than expected. A background task is computing your results. Please be patient.') }}</loading-component>
          <loading-component v-else :compact="true" :tab="true" :textoverride="true" :overrideimage="league?.loaderImage">{{ $t('Large amount of data. Please be patient.') }}</loading-component>
        </template>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
          <div v-for="tile in 5" :key="tile" class="bg-surface-1 rounded-control px-2 pt-5 pb-5 flex flex-col items-center text-center min-w-0">
            <skeleton-box class="w-20 h-20 !rounded-full"></skeleton-box>
            <span class="text-sm font-bold mt-1.5 skeleton-text">Samuro</span>
            <span class="text-xl font-bold mt-1.5 skeleton-text">00.00%</span>
            <span class="text-xs mt-1.5 skeleton-text">00 games together</span>
          </div>
        </div>
      </panel-box>
    </div>

    <panel-box title="All Matchups" subtitle="Your win rate with each hero on your team and against each hero on the enemy team." :divider="false" :skeleton="true">
      <div class="overflow-clip">
        <div class="grid grid-cols-[18.1%_40.95%_40.95%] max-md:text-xs">
          <div></div>
          <div class="py-3 px-3 text-center bg-teal/[0.18] rounded-tl-control">
            <span class="block text-sm font-bold uppercase tracking-wide"><span class="skeleton-text">{{ $t('With them · On your team') }}</span></span>
            <span class="block text-xs mt-1"><span class="skeleton-text">{{ $t('Your win rate when this hero is your ally') }}</span></span>
          </div>
          <div class="py-3 px-3 text-center bg-red/[0.18] rounded-tr-control">
            <span class="block text-sm font-bold uppercase tracking-wide"><span class="skeleton-text">{{ $t('Against them · On enemy team') }}</span></span>
            <span class="block text-xs mt-1"><span class="skeleton-text">{{ $t('Your win rate when this hero is your opponent') }}</span></span>
          </div>
        </div>
        <table class="min-w-0 w-full responsive-table rounded-table table-fixed max-md:text-xs">
          <colgroup>
            <col class="w-[18.1%]">
            <col class="w-[20.475%]">
            <col class="w-[20.475%]">
            <col class="w-[20.475%]">
            <col class="w-[20.475%]">
          </colgroup>
          <thead>
            <tr>
              <th class="py-2 px-6 text-left text-sm leading-4">
                <div class="flex items-center gap-[1.125rem]">
                  <span class="skeleton-text">{{ $t('Hero') }}</span>
                  <span class="invisible" aria-hidden="true"><search-input class="w-40" :collapsible="true"></search-input></span>
                </div>
              </th>
              <th class="py-2 px-3 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Win rate with') }}</span></th>
              <th class="py-2 px-3 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Games with') }}</span></th>
              <th class="py-2 px-3 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Win rate against') }}</span></th>
              <th class="py-2 px-3 text-center text-sm leading-4"><span class="skeleton-text">{{ $t('Games against') }}</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in 15" :key="row">
              <td class="py-2.5 px-6"><span class="inline-flex items-center"><skeleton-box class="w-10 h-10 !rounded-full shrink-0"></skeleton-box><span class="hidden md:block ml-3 font-medium skeleton-text">Alexstrasza</span></span></td>
              <td class="py-2.5 px-6"><div class="flex items-center gap-3"><stat-bar class="flex-1" value="0" display-text=""></stat-bar><span class="w-[3.6rem] text-right font-semibold"><span class="skeleton-text">00.00%</span></span></div></td>
              <td class="py-2.5 px-3 text-center"><span class="skeleton-text">000</span></td>
              <td class="py-2.5 px-6"><div class="flex items-center gap-3"><stat-bar class="flex-1" value="0" display-text=""></stat-bar><span class="w-[3.6rem] text-right font-semibold"><span class="skeleton-text">00.00%</span></span></div></td>
              <td class="py-2.5 px-3 text-center"><span class="skeleton-text">000</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </panel-box>
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
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
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
    league() {
      return this.esport ? this.$esportLeague(this.esport, this.esportfilters) : null;
    },
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    ngsPlayerUrl() {
      return `${this.league.root}/Player/${this.battletag}/${this.blizzid}`;
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
      // Best allies by your win rate with them; toughest enemies by your lowest win rate against them
      topCards() {
        return [
          { title: 'Best allies', subtitle: 'Highest win rate when on the same team · 5+ games', heroes: this.topfiveheroes || [], rate: 'ally_win_rate', count: 'ally_games_played', games: ':count games together', valueClass: 'text-win' },
          { title: 'Toughest enemies', subtitle: 'Lowest win rate when facing them · 5+ games', heroes: this.topfiveenemies || [], rate: 'enemy_win_rate', count: 'enemy_games_played', games: ':count games against', valueClass: 'text-loss' },
        ];
      },
      shownData() {
        const search = this.heroSearch.trim().toLowerCase();
        return search ? this.sortedData.filter(row => row.hero.name.toLowerCase().includes(search) || this.$heroName(row.hero).toLowerCase().includes(search)) : this.sortedData;
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
      return { ...this.league.params, battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam, ...extra };
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field) {
        this[field] = eventPayload.value;
      }
      const params = new URLSearchParams(this.league.params);
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
          const response = await this.$axios.post(`${this.league.api}/matchups`, this.ngsParams());
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
      heroUrl(hero) {
        if (this.esport) {
          return `${this.ngsPlayerUrl}/Hero/${hero.name}${window.location.search}`;
        }
        return '/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Hero/' + hero.name;
      },
      // Below 45% shows red, as the profile's bars do
      rateClass(rate) {
        return (rate ?? 0) < 45 ? 'text-loss' : 'text-header';
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