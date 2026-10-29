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
      <!-- Previous on the left, the layout toggle centred, Next on the right -->
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 grid grid-cols-[1fr_auto_1fr] items-center gap-4 text-sm">
        <div>
          <custom-button v-if="data.current_page != 1" size="small" color="teal" alt="Previous page" :disabled="!data.prev_page_url" :ignoreclick="true" @click="getData(data.current_page - 1)"><i class="fa-solid fa-arrow-left mr-2"></i>Previous</custom-button>
        </div>
        <tab-button :tab1text="'Table'" :tab2text="'Compact'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
        <div class="justify-self-end">
          <custom-button v-if="data.current_page != data.last_page" size="small" color="teal" alt="Next page" :disabled="!data.next_page_url" :ignoreclick="true" @click="getData(data.current_page + 1)">Next<i class="fa-solid fa-arrow-right ml-2"></i></custom-button>
        </div>
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
        <div id="table-container" ref="tablecontainer" class="min-w-0">
      <table id="responsive-table" class="min-w-0 w-full responsive-table relative" ref="responsivetable">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Match ID
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Date
            </th>            
            <th @click="sortTable('game_type_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Type
            </th>
            <th @click="sortTable('game_map')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Map
            </th>
            <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>    
            <th @click="sortTable('winner')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Winner
            </th>    
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              Talents
            </th>                   
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a class="link" :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a>
            </td>
            <td>
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ row.game_type.name }}
            </td>
            <td>
              {{ row.game_map.name }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
            </td>
            <td>
              {{ row.winner == 1 ? 'True' : 'False' }}
            </td>
            <td>

              <div class="flex gap-x-1 mx-2 items-center">
                <talent-image-wrapper v-if="row.level_one && row.level_one !=0" :talent="row.level_one" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_four && row.level_four !=0" :talent="row.level_four" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_seven && row.level_seven !=0" :talent="row.level_seven" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_ten && row.level_ten !=0" :talent="row.level_ten" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_thirteen && row.level_thirteen !=0" :talent="row.level_thirteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_sixteen && row.level_sixteen !=0" :talent="row.level_sixteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_twenty && row.level_twenty !=0" :talent="row.level_twenty" :size="'small'"></talent-image-wrapper>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
        </div>
        </panel-box>
      </div>
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 pb-4 flex justify-between text-sm">
        <div>
          <custom-button v-if="data.current_page != 1" size="small" color="teal" alt="Previous page" :disabled="!data.prev_page_url" :ignoreclick="true" @click="getData(data.current_page - 1)"><i class="fa-solid fa-arrow-left mr-2"></i>Previous</custom-button>
        </div>
        <div>
          <custom-button v-if="data.current_page != data.last_page" size="small" color="teal" alt="Next page" :disabled="!data.next_page_url" :ignoreclick="true" @click="getData(data.current_page + 1)">Next<i class="fa-solid fa-arrow-right ml-2"></i></custom-button>
        </div>
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
        <div v-else class="min-w-0">
          <table class="min-w-0 w-full responsive-table">
            <thead>
              <tr>
                <th v-for="label in ['Match ID', 'Game Date', 'Game Type', 'Game Map', 'Hero', 'Winner', 'Talents']" :key="label" class="py-2 px-3 text-left text-sm">{{ label }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in 12" :key="row">
                <td><skeleton-box class="w-20 h-4"></skeleton-box></td>
                <td><skeleton-box class="w-36 h-4"></skeleton-box></td>
                <td><skeleton-box class="w-24 h-4"></skeleton-box></td>
                <td><skeleton-box class="w-32 h-4"></skeleton-box></td>
                <td class="py-2 px-3">
                  <div class="flex items-center gap-2">
                    <skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
                    <skeleton-box class="w-20 h-4 max-md:hidden"></skeleton-box>
                  </div>
                </td>
                <td><skeleton-box class="w-12 h-4"></skeleton-box></td>
                <td>
                  <div class="flex gap-x-1 mx-2 items-center">
                    <skeleton-box v-for="talent in 7" :key="talent" class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../utils/scaleTable';
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
      windowWidth: window.innerWidth,
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
      return this.data.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
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
    // Like the Talent Builder's layout toggle: a cookie for everyone, and the account setting when logged in
    setStyle(side) {
      this.compact = side === 'right';
      Cookies.set('playerhistorytable', this.compact ? '1' : '0', { expires: 365, path: '/' });
      if (this.authenticated) {
        this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.compact }).catch(() => {});
      }
      this.$nextTick(() => this.scaleTable());
    },
    // Narrow screens scale the table to fit
    scaleTable() {
      const responsivetable = this.$refs.responsivetable;
      if (responsivetable && this.windowWidth < 1500) {
        scaleTableToFit(responsivetable, this.$refs.tablecontainer);
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
        this.$nextTick(() => this.scaleTable());
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
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
  }
}
</script>