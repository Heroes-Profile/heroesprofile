<template>
  <div>
    <player-page-top>
      <template #heading>
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading v-if="esport" :heading="'Friend vs Foe'" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
      <page-heading v-else :heading="'Friend vs Foe'"  :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
      <div class="w-full">
      <filters v-if="!esport"
        :onFilter="filterData"  
        :filters="filters" 
        :isLoading="friendIsLoading || enemyIsLoading"
        :gametypedefault="gametype"
        :includehero="true"
        :includegamemap="true"
        :includegametypefull="true"
        :includemultiseason="true"
        :includegamedaterange="true"
        :includegroupsize="true"
        :hideadvancedfilteringbutton="true"
        :groupSizeDefaultValue="'All'"
        :overrideGroupSizeRemoval="true"
        >
      </filters>
      <filters v-else-if="esport && ngsFilterOptions"
        :filters="filters"
        :isLoading="(friendIsLoading || enemyIsLoading)"
        :onFilter="() => getNgsData()"
        :onChange="ngsFilterChange"
        :extrafilters="[
          { text: 'Seasons', values: ngsFilterOptions.seasons, defaultValue: ngsSeason },
          { text: 'Divisions', values: ngsFilterOptions.divisions, defaultValue: ngsDivision, show: !!(ngsFilterOptions.divisions) },
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

    <div v-if="frienddata && enemydata" class="max-w-[120rem] mx-auto px-4 md:px-8 flex gap-6 max-md:flex-col max-md:text-sm max-md:max-w-[100vw]">
      <div ref="friendcontainer" class="flex-1 min-w-0 max-md:overflow-hidden">
        <table ref="friendtable" class="min-w-0 w-full">
          <thead >
            <tr>
              <th colspan="5" class="py-2 px-3 text-center text-base font-bold tracking-wider">Friends</th>
            </tr>
            <tr >
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                <div class="flex items-center gap-3">
                  <span class="cursor-pointer" @click="sortTableFriend('battletag')">Player<sort-indicator :column="'battletag'" :sort-key="friendSortKey" :sort-dir="friendSortDir"></sort-indicator></span>
                  <search-input v-model="friendSearch" placeholder="Search player" :collapsible="true" class="w-40"></search-input>
                </div>
              </th>
              <th @click="sortTableFriend('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Favorite Hero<sort-indicator :column="'hero'" :sort-key="friendSortKey" :sort-dir="friendSortDir"></sort-indicator>
              </th>            
              <th @click="sortTableFriend('total_games_played')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games With<sort-indicator :column="'total_games_played'" :sort-key="friendSortKey" :sort-dir="friendSortDir"></sort-indicator>
              </th>
              <th  @click="sortTableFriend('win_rate')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer ">
                Win Rate with<sort-indicator :column="'win_rate'" :sort-key="friendSortKey" :sort-dir="friendSortDir"></sort-indicator>
              </th>
              <th v-if="!esport" class="py-2 px-3 text-center text-sm leading-4 text-gray-500 tracking-wider w-px whitespace-nowrap">
                Matches
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in shownFriends" :key="row.blizz_id">
              <td class="py-2 px-3 ">
                <player-name :battletag="row.battletag" :blizz-id="row.blizz_id" :region="row.region" :link="esport ? row.link : null" :redirect="!esport" :void-eye="!esport" :hp-owner="!!row.hp_owner" :patreon="!!row.patreon"></player-name>
              </td>
              <td class="py-2 px-3">
                <div class="flex items-center">
                  <hero-image-wrapper class="mr-2" :hero="row.heroData.hero" :mobile-click="true">
                    <image-hover-box :title="row.heroData.hero.name" :paragraph-one="'Games Played: ' + row.hero_games.toLocaleString('en-US')"></image-hover-box>
                  </hero-image-wrapper>
                  <a :href="heroLink(row)" class="group/bt hidden md:inline-flex items-center"><span class="font-bold group-hover/bt:underline">{{ row.heroData.hero.name }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                </div>
              </td>
              <td class="py-2 px-3 text-center">{{ row.total_games_played.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 text-center">{{ row.win_rate.toFixed(2) }}</td>
              <td v-if="!esport" class="py-2 px-3">
                <a :href="`/Player/${battletag}/${blizzid}/${region}/Match/History?ff_battletag=${row.battletag}&ff_blizzid=${row.blizz_id}&ff_region=${row.region}`" :aria-label="`Matches with ${row.battletag}`" title="Matches" class="flex items-center justify-center min-h-[2rem] hover:text-lteal transition-colors"><i class="fas fa-chevron-right"></i></a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div ref="enemycontainer" class="flex-1 min-w-0 max-md:overflow-hidden">
        <table ref="enemytable" class="min-w-0 w-full">
          <thead class="bg-red">
            <tr>
              <th colspan="5" class="py-2 px-3 text-center text-base font-bold tracking-wider">Foes</th>
            </tr>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                <div class="flex items-center gap-3">
                  <span class="cursor-pointer" @click="sortTableEnemy('battletag')">Player<sort-indicator :column="'battletag'" :sort-key="enemySortKey" :sort-dir="enemySortDir"></sort-indicator></span>
                  <search-input v-model="enemySearch" placeholder="Search player" :collapsible="true" class="w-40"></search-input>
                </div>
              </th>
              <th @click="sortTableEnemy('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Favorite Hero<sort-indicator :column="'hero'" :sort-key="enemySortKey" :sort-dir="enemySortDir"></sort-indicator>
              </th>            
              <th @click="sortTableEnemy('total_games_played')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Against<sort-indicator :column="'total_games_played'" :sort-key="enemySortKey" :sort-dir="enemySortDir"></sort-indicator>
              </th>
              <th @click="sortTableEnemy('win_rate')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Win Rate Against<sort-indicator :column="'win_rate'" :sort-key="enemySortKey" :sort-dir="enemySortDir"></sort-indicator>
              </th>
              <th v-if="!esport" class="py-2 px-3 text-center text-sm leading-4 text-gray-500 tracking-wider w-px whitespace-nowrap">
                Matches
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in shownEnemies" :key="row.blizz_id">
              <td class="py-2 px-3 ">

                <player-name :battletag="row.battletag" :blizz-id="row.blizz_id" :region="row.region" :link="esport ? row.link : null" :redirect="!esport" :void-eye="!esport" :hp-owner="!!row.hp_owner" :patreon="!!row.patreon"></player-name>
              </td>

              <td class="py-2 px-3">
                <div class="flex items-center">
                  <hero-image-wrapper class="mr-2" :hero="row.heroData.hero" :mobile-click="true">
                    <image-hover-box :title="row.heroData.hero.name" :paragraph-one="'Games Played: ' + row.hero_games.toLocaleString('en-US')"></image-hover-box>
                  </hero-image-wrapper>
                  <a :href="heroLink(row)" class="group/bt hidden md:inline-flex items-center"><span class="font-bold group-hover/bt:underline">{{ row.heroData.hero.name }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                </div>
              </td>
              <td class="py-2 px-3 text-center">{{ row.total_games_played.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 text-center">{{ row.win_rate.toFixed(2) }}</td>
              <td v-if="!esport" class="py-2 px-3">
                <a :href="`/Player/${battletag}/${blizzid}/${region}/Match/History?ff_battletag=${row.battletag}&ff_blizzid=${row.blizz_id}&ff_region=${row.region}`" :aria-label="`Matches with ${row.battletag}`" title="Matches" class="flex items-center justify-center min-h-[2rem] hover:text-lteal transition-colors"><i class="fas fa-chevron-right"></i></a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else-if="friendIsLoading || enemyIsLoading || asyncLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <loading-component v-if="asyncLoading" :compact="true" :textoverride="true" :overrideimage="league?.loaderImage">This is taking longer than expected. A background task is computing your results. Please be patient.</loading-component>
      <loading-component v-else :compact="true" :textoverride="true" :overrideimage="league?.loaderImage">Large amount of data. Please be patient.</loading-component>
      <div class="grid md:grid-cols-2 gap-6">
        <table-skeleton v-for="side in 2" :key="side" :bare="true" :rows="10" :columns="['text', 'hero', 'stat', 'stat', 'rank']"></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../utils/scaleTable';

export default {
  name: 'FriendFoe',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    playerloadsetting: {
      type: [String, Boolean]
    },
    battletag: String,
    blizzid: String, 
    region: String,
    gametypedefault: Array,
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
    esport: String,
    esportfilters: Object,
  },
  data(){
    return {
      friendIsLoading: false,
      enemyIsLoading: false,
      infoText: "Friends and Foe data showing who " + this.battletag + " plays the most games with and against",
      frienddata: null,
      enemydata: null,
      friendSortKey: '',
      friendSortDir: 'desc',

      enemySortKey: '',
      enemySortDir: 'desc',
      friendSearch: '',
      enemySearch: '',

      gametype: null,
      gamemap: null,
      season: null,
      startdate: null,
      enddate: null,
      friendCancelTokenSource: null,
      enemyCancelTokenSource: null,
      groupsize: null,
      asyncLoading: false,
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
      Promise.allSettled([
      this.getData("friend"),
      this.getData("enemy"),
    ]).then(results => {
      if (results[0].status === "fulfilled") {
        this.frienddata = results[0].value;
      } else {
      }
      
      if (results[1].status === "fulfilled") {
        this.enemydata = results[1].value;
      } else {
      }
    });
  }



  },
  updated() {
    if (window.matchMedia('(max-width: 767px)').matches) {
      scaleTableToFit(this.$refs.friendtable, this.$refs.friendcontainer);
      scaleTableToFit(this.$refs.enemytable, this.$refs.enemycontainer);
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
    shownFriends() {
      return this.filterByBattletag(this.sortedDataFriends, this.friendSearch);
    },
    shownEnemies() {
      return this.filterByBattletag(this.sortedDataEnemies, this.enemySearch);
    },
    sortedDataFriends() {
      if (!this.friendSortKey) return this.frienddata;
      return this.frienddata.slice().sort((a, b) => {
        const valA = a[this.friendSortKey];
        const valB = b[this.friendSortKey];
        if (this.friendSortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    sortedDataEnemies() {
      if (!this.enemySortKey) return this.enemydata;
      return this.enemydata.slice().sort((a, b) => {
        const valA = a[this.enemySortKey];
        const valB = b[this.enemySortKey];
        if (this.enemySortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
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
    // That row's player, on their page for this hero
    heroLink(row) {
      const hero = encodeURIComponent(row.heroData.hero.name);
      if (this.esport) {
        const [path, query] = row.link.split('?');
        return `${path}/Hero/${hero}${query ? '?' + query : ''}`;
      }
      return `/Player/${row.battletag}/${row.blizz_id}/${row.region}/Hero/${hero}`;
    },
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
      this.frienddata = null;
      this.enemydata = null;
      this.friendIsLoading = true;
      this.enemyIsLoading = true;
      try {
        const response = await this.$axios.post(`${this.league.api}/friendfoe`, this.ngsParams());
        this.ngsFilterOptions = response.data.filters;
        this.frienddata = response.data.friend;
        this.enemydata = response.data.enemy;
      } catch(error) {
        //Do something here
      } finally {
        this.friendIsLoading = false;
        this.enemyIsLoading = false;
      }
    },
    filterByBattletag(rows, search) {
      const term = search.trim().toLowerCase();
      return term ? rows.filter(row => row.battletag.toLowerCase().includes(term)) : rows;
    },
    async getData(type){
      let cancelTokenSource;

      if (type === 'friend') {
        this.friendCancelTokenSource = this.$axios.CancelToken.source();
        cancelTokenSource = this.friendCancelTokenSource;
        this.friendIsLoading = true;
      } else if (type === 'enemy') {
        this.enemyCancelTokenSource = this.$axios.CancelToken.source();
        cancelTokenSource = this.enemyCancelTokenSource;
        this.enemyIsLoading = true;
      }

      try {
        const response = await this.$globalAsyncPost(
          "/api/v1/player/friendfoe",
          {
            type: type,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            season: this.season,
            start_date: this.startdate,
            end_date: this.enddate,
            hero: this.hero,
            game_map: this.gamemap,
            groupsize: this.groupsize,
          },
          {
            cancelToken: cancelTokenSource.token,
            onLoadStatus: (status, meta) => {
              if (meta.phase === 'polling') {
                this.asyncLoading = true;
              }
            },
          }
        );

        return response.data;
      } catch(error) {
        //Do something here
      } finally {
        if (type === 'friend') {
          this.friendCancelTokenSource = null;
          this.friendIsLoading = false;
        } else if (type === 'enemy') {
          this.enemyCancelTokenSource = null;
          this.enemyIsLoading = false;
        }
        if (!this.friendIsLoading && !this.enemyIsLoading) {
          this.asyncLoading = false;
        }
      }
    },
    cancelAxiosRequest() {
      if (this.friendCancelTokenSource || this.enemyCancelTokenSource) {
        this.friendCancelTokenSource.cancel('Request canceled by user');
        this.enemyCancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.hero = filteredData.single["Heroes"] ? filteredData.single["Heroes"] : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;
      this.groupsize = filteredData.single["Group Size"] && (filteredData.single["Group Size"] != 'All') ? filteredData.single["Group Size"] : null;

      this.frienddata = null;
      this.enemydata = null;
      Promise.allSettled([
        this.getData("friend"),
        this.getData("enemy"),
      ]).then(results => {
        if (results[0].status === "fulfilled") {
          this.frienddata = results[0].value;
        } else {
          console.error('Friend data retrieval failed', results[0].reason);
        }
        
        if (results[1].status === "fulfilled") {
          this.enemydata = results[1].value;
        } else {
          console.error('Enemy data retrieval failed', results[1].reason);
        }
      });
    },
    sortTableFriend(key) {
      if (key === this.friendSortKey) {
        this.friendSortDir = this.friendSortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.friendSortDir = 'desc';
      }
      this.friendSortKey = key;
    },
    sortTableEnemy(key) {
      if (key === this.enemySortKey) {
        this.enemySortDir = this.enemySortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.enemySortDir = 'desc';
      }
      this.enemySortKey = key;
    },
  }
}
</script>