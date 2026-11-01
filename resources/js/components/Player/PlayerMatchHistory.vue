<template>
  <div>
    <page-heading  :heading="'Match History'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>

    <filters
      :onFilter="filterData"
      :onFfSearching="setFfSearching"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :minimumgamesdefault="'0'"
      :includehero="true"
      :includerole="true"
      :heroinput="getHeroID()"
      :roleinput="role"
      :includegametypefull="showcustomgames ? false : true"
      :includegametypefullcustom="showcustomgames"
      :includemultiseason="true"
      :includegamedaterange="true"
      :includegamemap="true"
      :includegroupsize="true"
      :groupSizeDefaultValue="'All'"
      :hideadvancedfilteringbutton="true"
      :matchhistorybattletagsearch="true"
      :ffbattletaginput="ffBattletag"
      :ffblizzidinput="ffBlizzid"
      :ffregioninput="ffRegion || region"
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    <div v-if="data">
      <!-- `compact` means the game rows, labelled Expanded -->
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-wrap justify-end items-center gap-4">
        <tab-button :tab1text="'Compact'" :tab2text="'Expanded'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getData"></pagination-bar>
      </div>
      <div v-if="compact">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 py-4">
          <panel-box>
            <div>
              <game-summary-header></game-summary-header>
              <game-summary-box v-for="(item, index) in data.data" :key="item.replayID" :data="item" :replay-viewer-allowed="authenticated" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
            </div>
          </panel-box>
        </div>
      </div>
      <div v-else class="max-w-[1920px] mx-auto px-4 md:px-8 py-4">
        <panel-box>
          <!-- Wide rows scroll sideways on narrow screens -->
          <div class="overflow-x-auto">
            <div class="min-w-max w-full">
              <game-summary-header :compact="true" :sort-key="sortKey" :sort-dir="sortDir" @sort="sortTable"></game-summary-header>
              <game-summary-box v-for="item in sortedData" :key="item.replayID" :compact="true" :data="item" :replay-viewer-allowed="authenticated" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
            </div>
          </div>
        </panel-box>
      </div>
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 pb-4 flex justify-end">
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getData"></pagination-bar>
      </div>
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading || asyncLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 py-4 space-y-8">
      <loading-component :compact="true" :textoverride="true">{{ asyncLoading ? 'This is taking longer than expected. A background task is computing your results. Please be patient.' : 'Loading data...' }}</loading-component>
      <panel-box>
        <div v-if="compact">
          <game-summary-header></game-summary-header>
          <game-summary-skeleton v-for="index in 8" :key="index"></game-summary-skeleton>
        </div>
        <div v-else class="overflow-x-auto">
          <div class="min-w-max w-full">
            <game-summary-header :compact="true"></game-summary-header>
            <game-summary-skeleton v-for="row in 12" :key="row" :compact="true"></game-summary-skeleton>
          </div>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
import moment from 'moment-timezone';
import Cookies from 'js-cookie';

export default {
  name: 'PlayerMatchHistory',
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
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    gametypedefault: Array,
    showcustomgames: Boolean,
    playermatchtablestyle: {
      type: [String, Boolean]
    },
    authenticated: Boolean,
    analyticsCutover: String,
    replayDownloadBlocked: Boolean,
    urlparameters: {
      type: Object,
      default: () => ({}),
    },
  },
  data(){
    return {
      compact: this.playermatchtablestyle == 1,
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      asyncLoading: false,
      data: null,
      role: null,
      hero: null,
      gamemap: null,
      season: null,
      startdate: null,
      enddate: null,
      stack_size: null,
      sortKey: '',
      sortDir: 'desc',
      gametype: null,
      ffBattletag: null,
      ffBlizzid: null,
      ffRegion: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;

    if (this.urlparameters) {
      if (this.urlparameters['hero']) {
        this.hero = this.urlparameters['hero'];
      }
      if (this.urlparameters['role']) {
        this.role = this.urlparameters['role'];
      }
      if (this.urlparameters['ff_battletag']) {
        this.ffBattletag = this.urlparameters['ff_battletag'];
        this.ffBlizzid = this.urlparameters['ff_blizzid'] ?? null;
        this.ffRegion = this.urlparameters['ff_region'] ?? null;
      }
    }
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData(1);
    }
  },
  computed: {
    sortedData() {
      if (!this.sortKey) return this.data.data;
      // Map, hero and game type sort by the name the column shows, not the object or id behind it
      const value = (row) => ({
        game_map: row.game_map?.name,
        hero_id: row.hero?.name,
        game_type_id: row.game_type?.name,
      }[this.sortKey] ?? row[this.sortKey]);
      const direction = this.sortDir === 'asc' ? 1 : -1;
      return this.data.data.slice().sort((a, b) => {
        const valA = value(a);
        const valB = value(b);
        const order = typeof valA === 'string' ? valA.localeCompare(valB) : valA - valB;
        return order * direction;
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
    // Like the Talent Builder's layout toggle: a cookie for everyone, and the account setting when logged in
    setStyle(side) {
      this.compact = side === 'right';
      Cookies.set('playerhistorytable', this.compact ? '1' : '0', { expires: 365, path: '/' });
      if (this.authenticated) {
        this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.compact }).catch(() => {});
      }
    },
    async getData(page){      
     if (this.isLoading || page < 1 || (this.data && page > this.data.last_page)) {
        return;
      }
    
      this.data = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$globalAsyncPost("/api/v1/player/match/history", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          game_type: this.gametype,
          role: this.role,
          hero: this.getHeroID(),
          game_map: this.gamemap,
          pagination_page: page,
          season: this.season,
          start_date: this.startdate,
          end_date: this.enddate,
          stack_size: this.stack_size,
          ff_blizzid: this.ffBlizzid,
          ff_region: this.ffRegion,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
          onLoadStatus: (status, meta) => {
            if (meta.phase === 'polling') {
              this.asyncLoading = true;
            }
          },
        });
        this.data = response.data;
        this.$loadMatchDetails(this.data.data, this.blizzid, this.region);
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.asyncLoading = false;
      }
    },
    getHeroID() {
      if (this.hero) {
        const match = this.filters.heroes.find(h => h.name === this.hero);
        return match ? match.code : null;
      }
      return null;
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.filters.heroes.find(h => h.code === this.hero)?.name : null;
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;
      this.stack_size = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.ffBattletag = filteredData.ffBattletag || null;
      this.ffBlizzid = filteredData.ffBlizzid || null;
      this.ffRegion = filteredData.ffRegion || null;

      this.data = null;
      this.getData(1);
    },
    setFfSearching(val) {
      this.isLoading = val;
      if (val) this.data = null;
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
  }
}
</script>