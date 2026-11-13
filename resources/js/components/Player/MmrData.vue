<template>
  <div>
    <player-page-top>
      <template #heading>
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading :heading="'Heroes Profile MMR Data'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
      <div class="w-full">
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypedefault="startingGameType"
        :playertypeinput="type"
        :heroinput="hero"
        :roleinput="role"
        :includesinglegametypefull="true"
        :playerheroroletype="true"
        :hideadvancedfilteringbutton="true"
        :rolerequired="true"
        :herorequired="true"
        :includemultiseason="true"
        :includegamedaterange="true"
        >
      </filters>
      </div>
    </player-page-top>
    
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <p v-if="data === ''" class="text-center text-subtext mt-10">{{ $t('No data for this player and filters') }}</p>
    <div v-else-if="data">

      <line-chart class="max-w-[93.75rem] mx-auto px-4" :data="reversedData" :dataAttribute="'mmr'" :title="`${battletag} HP MMR Graph for ${gametype}`" :tooltip-label="`HP ${gameTypeName} MMR`"></line-chart>

      <div class="max-w-[93.75rem] mx-auto mt-2 px-4">
        {{ $t(':touppercase - League Tier Breakdowns | HP Player MMR: :mmr', { touppercase: this.gametype.toUpperCase(), mmr: data[0].mmr }) }}
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>
      <table class="max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              {{ $t('League') }}
            </th>
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              {{ $t('Min HP MMR') }}
            </th>            
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
              {{ $t('Max HP MMR') }}
            </th>                
          </tr>
        </thead>
        <tbody>
          <tr  v-for="(row, index) in leaguedata" :key="index" :class="{ 'bg-yellow':  checkIfTierFound(row.min_mmr, row.max_mmr, row.tierFound) }">            
            <td>
              {{ printLeagueName(row.tier, row.league_tier) }}
            </td>
            <td>
              {{ row.min_mmr.toLocaleString('en-US') }}
            </td>
            <td>
              {{ row.max_mmr ? row.max_mmr.toLocaleString('en-US') : "" }}
            </td>
          </tr>
        </tbody>
      </table>
      <!-- All rows are already loaded; the bar only picks which 100 to show -->
      <div v-if="lastPage > 1" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 flex justify-end">
        <pagination-bar :current="currentPage" :last="lastPage" @page="goToPage"></pagination-bar>
      </div>
      <!-- Styled like the match history's compact rows -->
      <div ref="tablecontainer" class="max-w-[120rem] mx-auto px-4 md:px-8 py-4">
        <panel-box>
          <!-- Wide rows scroll sideways on narrow screens -->
          <div class="overflow-x-auto">
            <div class="md:min-w-max w-full">
              <div class="max-md:hidden flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext">
                <span class="w-[21.25rem] shrink-0 pl-5 cursor-pointer" @click="sortTable('game_date')">{{ $t('Match') }}<sort-indicator column="game_date" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[8.75rem] shrink-0 px-4 cursor-pointer" @click="sortTable('replayID')">{{ $t('Game ID') }}<sort-indicator column="replayID" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[16.25rem] shrink-0 px-4 cursor-pointer" @click="sortTable('mmr_date_parsed')">{{ $t('HP MMR Date Parsed') }}<sort-indicator column="mmr_date_parsed" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[8.75rem] shrink-0 px-4 text-center cursor-pointer" @click="sortTable('mmr')">{{ $t('HP MMR') }}<sort-indicator column="mmr" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[11.25rem] shrink-0 px-4 text-center cursor-pointer" @click="sortTable('mmr_change')">{{ $t('HP MMR Change') }}<sort-indicator column="mmr_change" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[7.5rem] shrink-0 px-4 text-center cursor-pointer" @click="sortTable('winner')">{{ $t('Result') }}<sort-indicator column="winner" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
              </div>
              <template v-for="(row, index) in pagedData" :key="row.replayID">
                <div v-if="!patreonUser && index != 0 && index % 50 === 0" class="my-2">
                  <dynamic-banner-ad :patreon-user="patreonUser" :index="index + 3" :mobile-override="false"></dynamic-banner-ad>
                </div>
                <match-row-mobile class="md:hidden" :bg="index % 2 ? 'bg-surface-2' : 'bg-surface-1'" :href="'/Match/Single/' + row.replayID" :hero="row.hero" :title="row.hero.name" :date="formatRowDate(row.game_date)" :winner="row.winner === 'True'" :result="row.winner === 'True' ? 'Win' : 'Loss'">
                  <template v-if="notCalculated(row)">{{ $t('HP MMR not calculated yet') }}</template>
                  <template v-else>HP MMR <span class="text-white font-medium">{{ row.mmr.toLocaleString('en-US') }}</span> <span :class="row.mmr_change >= 0 ? 'text-win' : 'text-red'">({{ row.mmr_change > 0 ? '+' : '' }}{{ row.mmr_change.toFixed(2) }})</span></template>
                </match-row-mobile>
                <div :class="['max-md:hidden group/row relative flex items-center justify-between rounded overflow-hidden mb-1', index % 2 ? 'bg-surface-2' : 'bg-surface-1']">
                  <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
                  <a :href="'/Match/Single/' + row.replayID" class="absolute inset-0 z-[1]" :aria-label="$t('Open match')"></a>
                  <div class="relative w-[21.25rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
                    <div class="absolute inset-0 [mask-image:linear-gradient(to_right,black_60%,transparent)]" :class="row.winner === 'True' ? 'bg-[rgba(0,139,139,0.35)]' : 'bg-[rgba(179,54,22,0.13)]'"></div>
                    <div class="absolute inset-y-0 left-0 w-1.5" :class="row.winner === 'True' ? 'bg-win' : 'bg-red'"></div>
                    <div class="relative flex items-center gap-4">
                      <hero-image-wrapper size="row" :hero="row.hero" :excludehover="true"></hero-image-wrapper>
                      <div class="flex flex-col whitespace-nowrap">
                        <span class="font-bold">{{ $heroName(row.hero) }}</span>
                        <span class="text-xs">{{ formatRowDate(row.game_date) }}</span>
                      </div>
                    </div>
                  </div>
                  <span class="relative z-[2] w-[8.75rem] shrink-0 px-4"><a class="group/bt inline-flex items-center" :href="'/Match/Single/' + row.replayID"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></span>
                  <!-- 1800 is the starting value before the game's MMR has been calculated -->
                  <template v-if="notCalculated(row)">
                  <span class="relative w-[16.25rem] shrink-0 px-4 text-sm text-subtext italic">{{ $t('Pending') }}</span>
                  <span class="relative w-[8.75rem] shrink-0 px-4 text-center text-sm text-subtext italic">{{ $t('Not calculated yet') }}</span>
                  <span class="relative w-[11.25rem] shrink-0 px-4 text-center text-subtext">-</span>
                  </template>
                  <template v-else>
                  <span class="relative w-[16.25rem] shrink-0 px-4 text-sm text-subtext whitespace-nowrap">{{ formatRowDate(row.mmr_date_parsed) }}</span>
                  <span class="relative w-[8.75rem] shrink-0 px-4 text-center text-2xl font-semibold">{{ row.mmr.toLocaleString('en-US') }}</span>
                  <div class="relative z-[2] w-[11.25rem] shrink-0 px-4 flex items-center justify-center gap-2">
                    <span class="font-medium" :class="row.mmr_change >= 0 ? 'text-win' : 'text-red'">{{ row.mmr_change > 0 ? '+' : '' }}{{ row.mmr_change.toFixed(2) }}</span>
                    <!-- Down after a win or up after a loss: the FAQ explains why -->
                    <round-image v-if="isUnexpectedChange(row)" class="text-xs" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                      <p class="font-normal">{{ $t('HP MMR measures your skill against everyone else, so it can :players. The system is finding the bracket you belong in, not only rewarding wins.', { players: row.winner === 'True' ? $t('drop after a win against much lower rated players') : $t('rise after a loss against much higher rated players') }) }}</p>
                      <a href="/FAQ#mmr-change-direction" class="group/bt inline-flex items-center mt-2 font-normal"><span class="group-hover/bt:underline">Read more in the FAQ</span><i class="fas fa-chevron-right text-xs ml-2"></i></a>
                    </round-image>
                  </div>
                  </template>
                  <span class="relative w-[7.5rem] shrink-0 px-4 text-center font-medium" :class="row.winner === 'True' ? 'text-win' : 'text-red'">{{ row.winner === 'True' ? $t('Win') : $t('Loss') }}</span>
                </div>
              </template>
            </div>
          </div>
        </panel-box>
      </div>
      <div v-if="lastPage > 1" class="max-w-[120rem] mx-auto px-4 md:px-8 pb-4 flex justify-end">
        <pagination-bar :current="currentPage" :last="lastPage" @page="goToPage($event, true)"></pagination-bar>
      </div>
    </div>
    <!-- The graph, tier line, league table and match table in grey while it loads -->
    <div v-else-if="isLoading" class="max-w-[93.75rem] mx-auto px-4 flex flex-col gap-6">
      <loading-component :compact="true" :textoverride="true">{{ $t('Loading HP MMR...') }}</loading-component>
      <skeleton-box class="w-full aspect-[2/1]"></skeleton-box>
      <skeleton-box class="h-5 w-96 max-w-full"></skeleton-box>
      <table-skeleton :bare="true" :rows="7" :columns="['text', 'text', 'text']"></table-skeleton>
      <table-skeleton :rows="10" :columns="['portrait', 'text', 'text', 'stat', 'stat', 'stat']"></table-skeleton>
    </div>

  </div>

</template>

<script>
import { siteLocale } from '../../utils/translate';
import moment from 'moment-timezone';

export default {
  name: 'MmrData',
  components: {
  },
  props: {
    filters: Object,
    playerloadsetting: {
      type: [String, Boolean]
    },
    gametypedefault: Array,
    urlparameters: { type: Object, default: () => ({}) },
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    region: {
      type: [String, Number]
    },
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
  },
  data(){
    return {
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      gametype: null,
      season: null,
      startdate: null,
      enddate: null,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      currentPage: 1,
      pageSize: 100,
      leaguedata: null,
      type: "Player",
      // Hero id and role name
      hero: null,
      role: null,
      infoText: "",
    }
  },
  created(){
    this.gametype = this.startingGameType[0];

    // ?hero= or ?role= by name, from the hero and role pages' MMR panels
    const hero = this.filters.heroes.find(item => item.name === this.urlparameters?.hero);
    const role = this.filters.role.find(item => item.code === this.urlparameters?.role);
    if (hero) {
      this.type = 'Hero';
      this.hero = hero.code;
    } else if (role) {
      this.type = 'Role';
      this.role = role.code;
    }
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData();
    }
  },
  computed: {
    // A ?game_type= from an MMR panel link wins over the saved default
    startingGameType() {
      const requested = this.urlparameters?.game_type;
      return requested && this.filters.game_types_full.some(type => type.code === requested) ? [requested] : this.gametypedefault;
    },
    reversedData() {
      if (!Array.isArray(this.data)) {
        console.error("this.data is not an array.");
        return [];
      }
      return [...this.data].reverse();
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
    gameTypeName() {
      return this.filters.game_types_full.find(type => type.code === this.gametype)?.name ?? this.gametype.toUpperCase();
    },
    lastPage() {
      return Math.max(1, Math.ceil((this.data ? this.data.length : 0) / this.pageSize));
    },
    pagedData() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.sortedData.slice(start, start + this.pageSize);
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
    async getData(){
      this.isLoading = true;
      this.currentPage = 1;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/player/mmr", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          battletag: this.battletag,
          game_type: this.gametype,
          type: this.type,
          hero: this.hero,
          role: this.role,
          season: this.season,
          start_date: this.startdate,
          end_date: this.enddate,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data.tableData || '';
        this.leaguedata = response.data.leagueData;
      }catch(error){
        //Do something here
      }finally {
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
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      this.type = filteredData.single["Type"] ? filteredData.single["Type"] : "Player";
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;

      this.data = null;
      this.sortKey = '';
      this.sortDir ='asc';
      this.getData();

    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
      this.currentPage = 1;
    },
    isUnexpectedChange(row) {
      return (row.winner === 'True' && row.mmr_change < 0) || (row.winner === 'False' && row.mmr_change > 0);
    },
    // From the bottom bar, bring the top of the table back into view
    goToPage(page, fromBottom = false) {
      this.currentPage = Math.min(Math.max(page, 1), this.lastPage);
      if (fromBottom) {
        this.$nextTick(() => this.$refs.tablecontainer?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      }
    },
    printLeagueName(tier, tierID){
      if(tier){
        return tier;
      }
      if(tierID == 1){
        return "Bronze";
      }else if(tierID == 2){
        return "Silver";
      }else if(tierID == 3){
        return "Gold";
      }else if(tierID == 4){
        return "Platinum";
      }else if(tierID == 5){
        return "Diamond";
      }else if(tierID == 6){
        return "Master";
      }
    },
    notCalculated(row){
      return row.mmr === 1800;
    },
    checkIfTierFound(min_mmr, max_mmr, tierFound){
      if(tierFound){
        return true;
      }

      if(this.data[0].mmr > min_mmr && (!max_mmr || max_mmr == 0 || max_mmr == "")){
        return true;
      }

      return false;
    },
    // In the viewer's own date format and timezone, like the match history rows
    formatRowDate(dateString) {
      return moment.utc(dateString).toDate().toLocaleString(siteLocale(), {
        dateStyle: 'long',
        timeStyle: 'short',
        timeZone: this.userTimezone,
      });
    },
  }
}
</script>