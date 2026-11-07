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

    <div v-if="data" class="max-w-[93.75rem] mx-auto px-4 max-md:px-2">
      <p class="text-center mb-4">Awards across {{ data.total_games.toLocaleString('en-US') }} games</p>

      <div class="flex justify-center mb-4">
        <input type="text" v-model="awardSearch" placeholder="Search awards" class="md:w-[18.75rem] w-full h-[2.5rem] border-solid border-[1px] border-white bg-blue p-2 text-white focus:outline-none" />
      </div>

      <div class="flex flex-wrap justify-center gap-4 mb-10">
        <p v-if="filteredAwards.length == 0">No awards match "{{ awardSearch }}".</p>
        <div v-for="award in filteredAwards" :key="award.award_id" @click="selectAward(award)" :class="['bg-gray-dark rounded p-4 w-[12em] max-md:w-[45%] flex flex-col items-center text-center cursor-pointer border-2 hover:border-lteal', selectedAward && selectedAward.award_id == award.award_id ? 'border-teal' : 'border-gray-dark']">
          <img :src="`/images/awards/${award.icon}_blue.png`" :alt="award.title" class="w-14 h-14" />
          <span class="mt-2">{{ award.title }}</span>
          <span v-if="award.description" class="text-xs opacity-75 mb-1">{{ award.description }}</span>
          <span class="text-2xl font-bold">{{ award.rate.toFixed(2) }}%</span>
          <span class="text-sm">{{ award.count.toLocaleString('en-US') }} games</span>
        </div>
      </div>

      <div ref="gamestable" class="flex items-center gap-4 mb-2 scroll-mt-4">
        <h2 class="text-xl flex items-center gap-2">
          <img v-if="selectedAward" :src="`/images/awards/${selectedAward.icon}_blue.png`" :alt="selectedAward.title" class="w-10 h-10" />
          {{ selectedAward ? selectedAward.title + ' Games' : 'Recent Awards' }}
        </h2>
        <custom-button v-if="selectedAward" @click="clearAward" :text="'Show Recent Awards'" :alt="'Show Recent Awards'" size="small" :ignoreclick="true"></custom-button>
      </div>

      <!-- One award's games, or every award's (Recent Awards), a page at a time -->
      <loading-component v-if="awardGamesLoading" :compact="true" :textoverride="true">Loading {{ selectedAward ? selectedAward.title + ' games' : 'recent awards' }}...</loading-component>
      <template v-else-if="awardGames">
        <p v-if="awardGames.data.length == 0">{{ selectedAward ? 'No ' + selectedAward.title + ' games' : 'No awards' }} for the selected filters.</p>
        <div v-if="awardGames.last_page > 1" class="flex justify-end mb-4">
          <pagination-bar :current="awardGames.current_page" :last="awardGames.last_page" @page="getAwardGames"></pagination-bar>
        </div>
      </template>
      <!-- Styled like the match history's compact rows -->
      <panel-box v-if="tableRows.length">
        <!-- Wide rows scroll sideways on narrow screens -->
        <div class="overflow-x-auto">
          <div class="md:min-w-max w-full">
            <div class="max-md:hidden flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext">
              <span class="w-[20rem] shrink-0 pl-5">Match</span>
              <span class="w-[10rem] shrink-0 px-4">Game Type</span>
              <span class="w-[8.75rem] shrink-0 px-4">Game ID</span>
              <span class="w-[18.75rem] shrink-0 px-4">Award</span>
            </div>
            <template v-for="(row, index) in tableRows" :key="row.replayID">
            <match-row-mobile class="md:hidden" :bg="index % 2 ? 'bg-surface-2' : 'bg-surface-1'" :href="'/Match/Single/' + row.replayID" :hero="row.hero" :title="row.game_map.name" :date="formatRowDate(row.game_date)" :winner="row.winner == 1" :result="row.winner == 1 ? 'Win' : 'Loss'" :map-art="row.game_map.sanitized_map_name">
              <span class="text-white font-medium">{{ row.award.title }}</span> · {{ row.game_type.name }}
            </match-row-mobile>
<div :class="['max-md:hidden group/row relative flex items-center justify-between rounded overflow-hidden mb-1', index % 2 ? 'bg-surface-2' : 'bg-surface-1']">
              <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
              <a :href="'/Match/Single/' + row.replayID" class="absolute inset-0 z-[1]" aria-label="Open match"></a>
              <div class="relative w-[20rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
                <div class="absolute inset-0 [mask-image:linear-gradient(to_right,black_60%,transparent)]" :class="row.winner == 1 ? 'bg-[rgba(0,139,139,0.35)]' : 'bg-[rgba(179,54,22,0.13)]'"></div>
                <div class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="{ backgroundImage: `url('/images/maps/match/match-${row.game_map.sanitized_map_name}.jpg')` }"></div>
                <div class="absolute inset-y-0 left-0 w-1.5" :class="row.winner == 1 ? 'bg-win' : 'bg-red'"></div>
                <div class="relative flex items-center gap-4">
                  <hero-image-wrapper size="row" :hero="row.hero" :excludehover="true"></hero-image-wrapper>
                  <div class="flex flex-col whitespace-nowrap">
                    <span class="font-bold">{{ row.game_map.name }}</span>
                    <span class="text-xs">{{ formatRowDate(row.game_date) }}</span>
                  </div>
                </div>
              </div>
              <span class="relative w-[10rem] shrink-0 px-4 font-medium text-header">{{ row.game_type.name }}</span>
              <span class="relative z-[2] w-[8.75rem] shrink-0 px-4"><a class="group/bt inline-flex items-center" :href="'/Match/Single/' + row.replayID"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></span>
              <div class="relative w-[18.75rem] shrink-0 px-4 flex items-center gap-3">
                <img :src="`/images/awards/${row.award.icon}_${row.winner == 1 ? 'blue' : 'red'}.png`" :alt="row.award.title" class="w-10 h-10" />
                <span class="font-medium">{{ row.award.title }}</span>
              </div>
            </div>
</template>
          </div>
        </div>
      </panel-box>
      <div v-if="selectedAward && awardGames && !awardGamesLoading && awardGames.last_page > 1" class="flex justify-end mt-4">
        <pagination-bar :current="awardGames.current_page" :last="awardGames.last_page" @page="goToAwardPage"></pagination-bar>
      </div>
    </div>
    <div v-if="asyncLoading">
      <loading-component :textoverride="true">This is taking longer than expected.<br/>A background task is computing your results.<br/>Please be patient.</loading-component>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
  </div>
</template>

<script>
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
        if(!search){
          return this.data.awards;
        }
        return this.data.awards.filter(award => award.title.toLowerCase().includes(search) || (award.description || '').toLowerCase().includes(search));
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
        return moment.utc(dateString).toDate().toLocaleString(undefined, {
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
