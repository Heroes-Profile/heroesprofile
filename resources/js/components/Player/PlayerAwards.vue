<template>
  <div>
    <player-page-top>
      <template #heading>
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading :heading="'Awards'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
      <div class="w-full">
      <filters
        :onFilter="filterData"
        :filters="filters"
        :isLoading="isLoading"
        :gametypedefault="gametype"
        :includegametypefull="true"
        :includehero="true"
        :includerole="true"
        :includegamemap="true"
        :includemultiseason="true"
        :includegamedaterange="true"
        :hideadvancedfilteringbutton="true"
        >
      </filters>
      </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">
      <panel-box :title="$t('Awards')" :subtitle="$t('Earned in :games games · :earned of :total award types unlocked', { games: data.total_games.toLocaleString('en-US'), earned: earnedCount, total: data.awards.length })">
        <template #right>
          <div class="flex flex-wrap items-center gap-4 max-md:w-full">
            <tab-button :small="true" tab1text="Earned" tab2text="All awards" :ignoreclick="true" :overridedefaultside="awardView === 'all' ? 'right' : 'left'" @tab-click="awardView = $event === 'right' ? 'all' : 'earned'"></tab-button>
            <search-input v-model="awardSearch" :placeholder="$t('Search awards')" class="w-full md:w-[20rem]"></search-input>
          </div>
        </template>

        <p v-if="filteredAwards.length == 0" class="text-subtext">{{ awardSearch ? $t('No awards match ":awardsearch".', { awardsearch: awardSearch }) : $t('No awards earned for these filters.') }}</p>
        <!-- Click an award to list its games below -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <button v-for="award in shownAwards" :key="award.award_id" type="button" @click="selectAward(award)" :title="$t('Show :award games', { award: award.title })"
            :class="['flex gap-4 p-5 rounded-control text-left transition-colors min-w-0', selectedAward && selectedAward.award_id == award.award_id ? 'bg-surface-2 ring-2 ring-inset ring-teal' : 'bg-surface-1 hover:bg-surface-2']">
            <img :src="`/images/awards/${award.icon}_blue.png`" :alt="award.title" class="w-14 h-14 shrink-0" />
            <div class="flex-1 min-w-0 flex flex-col gap-1">
              <span class="text-lg font-medium text-header leading-tight">{{ award.title }}</span>
              <span v-if="award.description" class="text-sm text-subtext">{{ award.description }}</span>
              <!-- The bar is against the most-earned award, so the top one fills it -->
              <stat-bar class="mt-auto pt-2" :value="maxRate ? (award.rate / maxRate) * 100 : 0" :display-text="award.rate.toFixed(2)" suffix="%" color="teal"></stat-bar>
              <span class="text-sm text-subtext">{{ $t(':count games', { count: award.count.toLocaleString('en-US') }) }}</span>
            </div>
          </button>
        </div>
        <div v-if="!awardSearch && filteredAwards.length > awardPreviewCount" class="flex justify-center">
          <custom-button @click="showAllAwards = !showAllAwards" :text="showAllAwards ? $t('Show fewer awards') : $t('Show all :count awards', { count: filteredAwards.length })" :alt="showAllAwards ? 'Show fewer awards' : 'Show all awards'" :ignoreclick="true"></custom-button>
        </div>
      </panel-box>

      <div ref="gamestable" class="scroll-mt-4">
        <panel-box :title="selectedAward ? $t(':award Games', { award: selectedAward.title }) : $t('Recent Awards')" :subtitle="selectedAward ? null : $t('Awards from your latest matches.')">
          <template #right>
            <div class="flex flex-wrap items-center gap-4">
              <custom-button v-if="selectedAward" @click="clearAward" :text="'Show Recent Awards'" :alt="'Show Recent Awards'" size="small" :ignoreclick="true"></custom-button>
              <pagination-bar v-if="awardGames && awardGames.last_page > 1" :current="awardGames.current_page" :last="awardGames.last_page" @page="getAwardGames"></pagination-bar>
            </div>
          </template>

          <p v-if="awardGames && !awardGamesLoading && awardGames.data.length == 0" class="text-subtext">{{ selectedAward ? 'No ' + selectedAward.title + ' games' : $t('No awards') }} for the selected filters.</p>
          <div v-else class="min-w-0 max-md:overflow-clip">
            <table class="w-full responsive-table rounded-table table-fixed">
              <colgroup>
                <col class="w-[55%] md:w-[28%]">
                <col class="max-md:hidden w-[21.5%]">
                <col class="max-md:hidden w-[19.5%]">
                <col class="w-[45%] md:w-[31%]">
              </colgroup>
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm">{{ $t('Match') }}</th>
                  <th class="max-md:hidden py-2 px-3 text-center text-sm">{{ $t('Game Type') }}</th>
                  <th class="max-md:hidden py-2 px-3 text-center text-sm">{{ $t('Game ID') }}</th>
                  <th class="py-2 px-3 text-left text-sm">{{ $t('Award') }}</th>
                </tr>
              </thead>
              <tbody>
                <!-- Grey rows of the same height while a page loads -->
                <template v-if="awardGamesLoading || !awardGames">
                  <tr v-for="row in 8" :key="row">
                    <td class="py-2 px-3"><div class="flex items-center gap-3"><skeleton-box class="w-[2.6rem] h-[2.6rem] shrink-0 !rounded-full"></skeleton-box><div class="flex flex-col items-start"><span class="font-bold skeleton-text">Map name</span><span class="text-xs skeleton-text mt-px">November 11, 2026</span></div></div></td>
                    <td class="max-md:hidden py-2 px-3"><skeleton-box class="h-4 w-24 mx-auto"></skeleton-box></td>
                    <td class="max-md:hidden py-2 px-3"><skeleton-box class="h-4 w-20 mx-auto"></skeleton-box></td>
                    <td class="py-2 px-3"><div class="flex items-center gap-3"><skeleton-box class="w-9 h-9 shrink-0 !rounded-full"></skeleton-box><skeleton-box class="h-4 w-32"></skeleton-box></div></td>
                  </tr>
                </template>
                <template v-else>
                  <tr v-for="row in tableRows" :key="row.replayID">
                    <td class="py-2 px-3">
                      <div class="flex items-center gap-3 min-w-0">
                        <hero-image-wrapper size="row" :hero="row.hero" :excludehover="true"></hero-image-wrapper>
                        <div class="flex flex-col min-w-0">
                          <span class="font-bold truncate md:hidden"><a class="inline-flex items-center" :href="'/Match/Single/' + row.replayID">{{ $mapName(row.game_map) }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a></span>
                          <span class="font-bold truncate max-md:hidden">{{ $mapName(row.game_map) }}</span>
                          <span class="text-xs text-subtext">{{ formatRowDate(row.game_date) }}</span>
                        </div>
                      </div>
                    </td>
                    <td class="max-md:hidden py-2 px-3 text-center">{{ $t(row.game_type.name) }}</td>
                    <td class="max-md:hidden py-2 px-3 text-center"><a class="group/bt inline-flex items-center" :href="'/Match/Single/' + row.replayID"><span class="group-hover/bt:underline">{{ row.replayID }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a></td>
                    <td class="py-2 px-3">
                      <div class="flex items-center gap-3">
                        <img :src="`/images/awards/${row.award.icon}_${row.winner == 1 ? 'blue' : 'red'}.png`" :alt="row.award.title" class="w-9 h-9 shrink-0" />
                        <span class="font-medium">{{ row.award.title }}</span>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </panel-box>
      </div>
    </div>

    <!-- The same layout in grey while the awards load, the message on a tab of the first panel. Placeholder words sit under
         grey blocks (skeleton-text) so every line is exactly as tall as the real text. -->
    <div v-else-if="isLoading || asyncLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">
      <panel-box :title="$t('Awards')" :subtitle="'Earned in 0,000 games · 00 of 00 award types unlocked'" :skeleton="true" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true">{{ asyncLoading ? $t('This is taking longer than expected. A background task is computing your results. Please be patient.') : $t('Loading awards...') }}</loading-component>
        <template #right>
          <div class="flex flex-wrap items-center gap-4 max-md:w-full">
            <div class="relative">
              <tab-button class="invisible" :small="true" tab1text="Earned" tab2text="All awards" aria-hidden="true"></tab-button>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
            <div class="relative w-full md:w-[20rem]">
              <search-input class="invisible" placeholder="Search awards" aria-hidden="true"></search-input>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
          </div>
        </template>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <div v-for="card in awardPreviewCount" :key="card" class="flex gap-4 p-5 rounded-control bg-surface-1 min-w-0">
            <skeleton-box class="w-14 h-14 shrink-0 !rounded-full"></skeleton-box>
            <div class="flex-1 min-w-0 flex flex-col gap-1 items-start">
              <span class="text-lg font-medium leading-tight skeleton-text">Award name</span>
              <!-- After the first row most rows have a description that wraps, so those cards get two lines -->
              <span class="text-sm skeleton-text">Most damage dealt<template v-if="card > 4"><br>to enemy heroes.</template></span>
              <stat-bar class="mt-auto pt-2 w-full" value="0" display-text="00.00" suffix="%" :hide-value="true"></stat-bar>
              <span class="text-sm skeleton-text">000 games</span>
            </div>
          </div>
        </div>
        <div class="flex justify-center">
          <div class="relative">
            <custom-button class="invisible" text="Show all 00 awards" alt="" :ignoreclick="true" aria-hidden="true"></custom-button>
            <skeleton-box class="absolute inset-0"></skeleton-box>
          </div>
        </div>
      </panel-box>

      <panel-box :title="$t('Recent Awards')" :subtitle="$t('Awards from your latest matches.')" :skeleton="true">
        <template #right>
          <!-- The pager's size as a grey block -->
          <div class="relative">
            <pagination-bar class="invisible" :current="1" :last="10" aria-hidden="true"></pagination-bar>
            <skeleton-box class="absolute inset-0"></skeleton-box>
          </div>
        </template>
        <div class="min-w-0 max-md:overflow-clip">
          <table class="w-full responsive-table rounded-table table-fixed">
            <colgroup>
              <col class="w-[55%] md:w-[28%]">
              <col class="max-md:hidden w-[21.5%]">
              <col class="max-md:hidden w-[19.5%]">
              <col class="w-[45%] md:w-[31%]">
            </colgroup>
            <thead>
              <tr>
                <th class="py-2 px-3 text-left text-sm"><span class="skeleton-text">{{ $t('Match') }}</span></th>
                <th class="max-md:hidden py-2 px-3 text-center text-sm"><span class="skeleton-text">{{ $t('Game Type') }}</span></th>
                <th class="max-md:hidden py-2 px-3 text-center text-sm"><span class="skeleton-text">{{ $t('Game ID') }}</span></th>
                <th class="py-2 px-3 text-left text-sm"><span class="skeleton-text">{{ $t('Award') }}</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in 8" :key="row">
                <td class="py-2 px-3"><div class="flex items-center gap-3"><skeleton-box class="w-[2.6rem] h-[2.6rem] shrink-0 !rounded-full"></skeleton-box><div class="flex flex-col items-start"><span class="font-bold skeleton-text">Map name</span><span class="text-xs skeleton-text mt-px">November 11, 2026</span></div></div></td>
                <td class="max-md:hidden py-2 px-3"><skeleton-box class="h-4 w-24 mx-auto"></skeleton-box></td>
                <td class="max-md:hidden py-2 px-3"><skeleton-box class="h-4 w-20 mx-auto"></skeleton-box></td>
                <td class="py-2 px-3"><div class="flex items-center gap-3"><skeleton-box class="w-9 h-9 shrink-0 !rounded-full"></skeleton-box><skeleton-box class="h-4 w-32"></skeleton-box></div></td>
              </tr>
            </tbody>
          </table>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
import { siteLocale } from '../../utils/translate';
  import moment from 'moment-timezone';

  export default {
    name: 'PlayerAwards',
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
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        asyncLoading: false,
        infoText: "How often " + this.battletag + " earns each end of match award. Awards are only tracked for games after award tracking began.",
        data: null,
        gametype: null,
        hero: null,
        role: null,
        gamemap: null,
        season: null,
        startdate: null,
        enddate: null,
        awardSearch: '',
        // 'earned' hides the awards never earned under these filters; 'all' shows every award
        awardView: 'earned',
        // The first rows of awards, then a button to show the rest
        awardPreviewCount: 12,
        showAllAwards: false,
        selectedAward: null,
        awardGames: null,
        awardGamesLoading: false,
        awardGamesRequestId: 0,
      }
    },
    created(){
      this.gametype = this.gametypedefault;
    },
    mounted() {
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    },
    computed: {
      filteredAwards(){
        const search = this.awardSearch.trim().toLowerCase();
        const awards = this.awardView === 'earned' ? this.data.awards.filter(award => award.count > 0) : this.data.awards;
        if(!search){
          return awards;
        }
        return awards.filter(award => award.title.toLowerCase().includes(search) || (award.description || '').toLowerCase().includes(search));
      },
      // While searching every match shows; otherwise the first rows until Show all
      shownAwards(){
        return this.awardSearch.trim() || this.showAllAwards ? this.filteredAwards : this.filteredAwards.slice(0, this.awardPreviewCount);
      },
      earnedCount(){
        return this.data.awards.filter(award => award.count > 0).length;
      },
      maxRate(){
        return Math.max(0, ...this.data.awards.map(award => award.rate));
      },
      tableRows(){
        return this.awardGames && !this.awardGamesLoading ? this.awardGames.data : [];
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

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/player/awards", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            hero: this.hero,
            role: this.role,
            game_map: this.gamemap,
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

          this.data = response.data;
        }catch(error){
          //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.asyncLoading = false;
        }
        if (this.data) {
          this.loadRecentAwards();
        }
      },
      // Recent Awards: every award's games, newest first
      loadRecentAwards(){
        this.awardGames = null;
        this.awardGamesRequestId++;
        this.awardGamesLoading = false;
        this.getAwardGames(1);
      },
      async getAwardGames(page){
        if (this.awardGamesLoading || page < 1 || (this.awardGames && page > this.awardGames.last_page)) {
          return;
        }

        this.awardGamesLoading = true;
        // Bumped whenever the selected award changes, so a slower response for the previous award is dropped.
        const requestId = this.awardGamesRequestId;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/player/awards/games", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            hero: this.hero,
            role: this.role,
            game_map: this.gamemap,
            season: this.season,
            start_date: this.startdate,
            end_date: this.enddate,
            award_id: this.selectedAward ? this.selectedAward.award_id : null,
            pagination_page: page,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if (requestId !== this.awardGamesRequestId) return;
          this.awardGames = response.data;
        }catch(error){
          //Do something here
        }finally {
          if (requestId === this.awardGamesRequestId) {
            this.cancelTokenSource = null;
            this.awardGamesLoading = false;
          }
        }
      },
      selectAward(award){
        if(this.selectedAward && this.selectedAward.award_id == award.award_id){
          this.clearAward();
          return;
        }
        this.selectedAward = award;
        this.awardGames = null;
        this.awardGamesRequestId++;
        this.awardGamesLoading = false;
        this.getAwardGames(1);
        this.$nextTick(() => {
          this.$refs.gamestable?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      },
      clearAward(){
        this.selectedAward = null;
        this.loadRecentAwards();
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      filterData(filteredData){
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
        this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
        this.role = filteredData.single.Role ? filteredData.single.Role : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
        this.startdate = filteredData.single["From Date"] || null;
        this.enddate = filteredData.single["To Date"] || null;

        this.data = null;
        // getData loads Recent Awards for the new filters once it's done
        this.selectedAward = null;
        this.awardGames = null;
        this.awardGamesRequestId++;
        this.getData();
      },
      // In the viewer's own date format and timezone, like the match history rows
      formatRowDate(dateString) {
        return moment.utc(dateString).toDate().toLocaleString(siteLocale(), {
          dateStyle: 'long',
          timeStyle: 'short',
          timeZone: moment.tz.guess(),
        });
      },
      // From the bottom bar, bring the top of the list back into view
      goToAwardPage(page) {
        this.getAwardGames(page);
        this.$nextTick(() => this.$refs.gamestable?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      },
    }
  }
</script>
