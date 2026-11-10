<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading :heading="'Replay Search'">
      <template #headingInfo>
        <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
          <ul class="font-normal list-disc pl-5 space-y-1">
            <li v-for="line in searchInfo" :key="line">{{ line }}</li>
          </ul>
        </round-image>
      </template>
    </page-heading>

    <!-- Blocks the page while a battletag lookup runs -->
    <div v-if="playerSearching" class="fixed inset-0 z-[60] bg-black/80 flex items-center justify-center">
      <loading-component :textoverride="true">Searching for players...</loading-component>
    </div>

    <filters
      :onFilter="filterData"
      :onFfSearching="setPlayerSearching"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :includegametypefull="true"
      :includegamemap="true"
      :includeregion="true"
      :includegameversion="true"
      :includegamedaterange="true"
      :includemultihero="true"
      :includemmrranges="true"
      :includeplayerpicker="true"
      :excludetimeframes="true"
      :advancedfiltering="advancedfiltering"
      >
    </filters>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <p v-if="errors.length" class="text-red">{{ errors.join(' ') }}</p>

      <!-- Before the first search, the heading's info sits where the matches will load -->
      <panel-box v-if="!rows && !isLoading">
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="line in searchInfo" :key="line">{{ line }}</li>
        </ul>
      </panel-box>

      <template v-if="rows">
        <p v-if="rows.length == 0 && !isLoading && !nextBeforeReplayID">No replays match those filters.</p>
        <div v-if="rows.length" class="flex flex-wrap justify-end items-center gap-4">
          <tab-button :tab1text="'Compact'" :tab2text="'Expanded'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="expanded ? 'right' : 'left'"></tab-button>
          <pagination-bar :current="currentPage" :last="lastPage" @page="goToPage"></pagination-bar>
        </div>
        <!-- Laid out like the esports team match history: match rows without a player, so a teal strip and both teams -->
        <panel-box v-if="shownRows.length && expanded">
          <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-dark font-medium text-subtext max-2xl:hidden">
            <span class="flex-1 max-w-[25.625rem] pl-6">Match</span>
            <span class="flex-1 px-4 text-center">Scoreboard</span>
            <span class="w-[22.625rem] shrink-0 px-4">Players</span>
          </div>

          <div v-for="row in shownRows" :key="row.replayID" class="group/replay relative mb-1.5">
            <div class="group/row relative flex flex-wrap items-center justify-between gap-y-4 rounded overflow-hidden 2xl:flex-nowrap group-odd/replay:bg-surface-1 group-even/replay:bg-surface-2">
              <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
              <a :href="matchUrl(row)" class="absolute inset-0 z-[1]" aria-label="Open match"></a>

              <div class="relative flex-1 md:max-w-[25.625rem] min-w-[18.75rem] self-stretch flex items-center max-md:justify-center pl-6 py-4">
                <div v-if="row.game_map" class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="mapArt(row)"></div>
                <div class="absolute inset-y-0 left-0 w-1.5 bg-teal"></div>
                <div class="relative flex flex-col gap-1.5 whitespace-nowrap">
                  <span class="font-bold">{{ row.game_map ? row.game_map.name : '' }}</span>
                  <span class="text-base font-medium text-header">{{ row.game_type ? row.game_type.name : '' }}</span>
                  <span class="text-sm">{{ Math.round(row.game_length / 60) }} minutes · {{ row.region }} · {{ row.game_version }}</span>
                  <span class="text-xs">{{ formatDate(row.game_date) }}</span>
                  <span class="relative z-[2] text-sm">
                    Match ID
                    <a class="group/bt inline-flex items-center" :href="matchUrl(row)"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                  </span>
                </div>
              </div>

              <!-- Each team's kills | deaths | assists, drawn like the match history's KDA column -->
              <div class="relative z-[2] flex-1 flex flex-wrap justify-center gap-x-16 gap-y-4 px-4 py-4 min-w-0">
                <div v-for="side in sides(row)" :key="side.team" class="flex flex-col gap-0.5 items-center text-center">
                  <span :class="['text-sm font-semibold', side.won ? 'text-win' : 'text-red']">{{ side.won ? 'Winner' : 'Loser' }}</span>
                  <span class="grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] gap-x-[0.4375rem] w-[8.75rem] items-center justify-items-center my-1">
                    <kda-icon v-for="(stat, index) in ['kills', 'deaths', 'assists']" :key="stat" :stat="stat" :style="{ gridColumn: index * 2 + 1 }"></kda-icon>
                  </span>
                  <span class="grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] gap-x-[0.4375rem] w-[8.75rem] text-2xl font-semibold whitespace-nowrap tabular-nums">
                    <span class="text-right text-win">{{ teamTotal(row, side.team, 'kills') }}</span><span>|</span><span class="font-normal text-red">{{ teamTotal(row, side.team, 'deaths') }}</span><span>|</span><span class="text-left font-normal">{{ teamTotal(row, side.team, 'assists') }}</span>
                  </span>
                  <span class="text-sm font-medium text-subtext">{{ teamKda(row, side.team) }} KDA</span>
                </div>
              </div>

              <!-- The match history's players column, winners first -->
              <div class="relative z-[2] flex gap-2 max-md:justify-center pl-4 2xl:pl-1.5 pr-12 py-4 w-full 2xl:w-[22.625rem] shrink-0">
                <div v-for="side in sides(row)" :key="side.team" class="flex flex-col gap-1.5 w-[9.375rem]">
                  <span :class="['text-sm font-semibold h-4 flex items-center', side.won ? 'text-win' : 'text-red']">{{ side.won ? 'Winner' : 'Loser' }}</span>
                  <!-- A party's icon sits centred beside a line in its colour running down the group -->
                  <div v-for="group in partyGroups(teamPlayers(row, side.team))" :key="group.start" class="flex items-center">
                    <span class="w-4 shrink-0 flex items-center justify-center">
                      <img v-if="group.party" class="w-4 h-4" :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${group.party}.png`" alt=""/>
                    </span>
                    <span class="w-[2px] ml-[0.1875rem] self-stretch shrink-0 rounded-l-full" :style="group.party && group.players.length > 1 ? { backgroundColor: partyColors[group.party] } : null"></span>
                    <div class="flex flex-col gap-1.5 flex-1 min-w-0 ml-0.5">
                      <div v-for="(player, groupIndex) in group.players" :key="groupIndex" class="flex gap-1 h-4 items-center">
                        <img v-if="player.hero" class="w-4 h-4 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${player.hero.short_name}.png`" :alt="player.hero.name"/>
                        <span v-if="player.check" class="flex-1 min-w-0 truncate text-sm font-medium text-subtext">Hidden</span>
                        <icon-with-hover v-else class="flex-1" popupsize="fit">
                          <template #trigger>
                            <a :href="`/Player/${player.battletag}/${player.blizz_id}/${player.region}`" @click="$redirectToProfile(player.battletag, player.blizz_id, player.region, false)" class="group/bt min-w-0 inline-flex items-center text-sm font-medium text-subtext hover:text-lteal"><span class="truncate group-hover/bt:underline">{{ player.battletag }}</span><i class="fas fa-chevron-right text-[0.625rem] ml-1 shrink-0 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                          </template>
                          <div class="flex items-center gap-2 text-base font-bold text-white">
                            <img v-if="player.hero" class="w-6 h-6 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${player.hero.short_name}.png`" alt=""/>
                            <span class="min-w-0 break-words">{{ player.battletag }}</span>
                            <img v-if="player.party" class="w-5 h-5" :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${player.party}.png`" alt=""/>
                          </div>
                          <image-hover-box
                            :paragraph-one="player.hero ? `Hero: ${player.hero.name}` : null"
                            :paragraph-two="player.account_level != null ? `Account Level: ${player.account_level}` : null"
                            :paragraph-three="player.hero_level != null ? `Hero Level: ${player.hero_level}` : null"
                            :paragraph-four="player.player_mmr != null ? `Player MMR: ${player.player_mmr.toLocaleString('en-US')}` : null"
                          ></image-hover-box>
                        </icon-with-hover>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="row.downloadable || isWatchable(row)" class="absolute z-[3] flex transition-opacity right-2 top-2 flex-col gap-2 md:opacity-0 md:group-hover/replay:opacity-100 md:focus-within:opacity-100">
                    <a v-if="row.downloadable && authenticated && !replayDownloadBlocked" class="max-md:hidden hover:text-lteal" :href="'/Match/Single/' + row.replayID + '/Download'" title="Download Replay"><i class="fa-solid fa-download"></i></a>
                    <span v-else-if="row.downloadable && !authenticated" class="max-md:hidden opacity-60" title="Log in to download replays"><i class="fa-solid fa-download"></i></span>
                    <a v-if="isWatchable(row) && authenticated" class="hover:text-lteal" :href="'/Analytics/Match/Single/' + row.replayID" title="Watch Replay"><i class="fa-solid fa-video"></i></a>
                    <span v-else-if="isWatchable(row)" class="opacity-60" title="Log in with Battle.net to watch replays"><i class="fa-solid fa-video"></i></span>
                  </div>
          </div>
        </panel-box>

        <!-- One line per game -->
        <panel-box v-else-if="shownRows.length">
          <div class="overflow-x-auto">
            <div class="md:min-w-max w-full">
              <div class="max-md:hidden flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext">
                <span class="w-[20rem] shrink-0 pl-5">Match</span>
                <span class="w-[10rem] shrink-0 px-4">Game Type</span>
                <span class="w-[8.75rem] shrink-0 px-4">Match ID</span>
                <span class="w-[5.5rem] shrink-0 px-4">Length</span>
                <span class="w-[5.5rem] shrink-0 px-4">Region</span>
                <span class="w-[8rem] shrink-0 px-4">Version</span>
                <span class="w-[18rem] shrink-0 px-4">Winner</span>
                <span class="w-[18rem] shrink-0 px-4">Loser</span>
                <span class="w-20 shrink-0"></span>
              </div>
              <div v-for="(row, index) in shownRows" :key="row.replayID" class="group/replay relative">
                <match-row-mobile class="md:hidden" :bg="index % 2 ? 'bg-surface-2' : 'bg-surface-1'" :href="matchUrl(row)" :title="row.game_map ? row.game_map.name : ''" :date="formatDate(row.game_date)" :map-art="row.game_map ? row.game_map.sanitized_map_name : null">
                  {{ row.game_type ? row.game_type.name : '' }} · {{ formatLength(row.game_length) }} · {{ row.region }}
                </match-row-mobile>
                <div :class="['max-md:hidden group/row relative flex items-center justify-between rounded overflow-hidden mb-1', index % 2 ? 'bg-surface-2' : 'bg-surface-1']">
                  <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
                  <a :href="matchUrl(row)" class="absolute inset-0 z-[1]" aria-label="Open match"></a>
                  <div class="relative w-[20rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
                    <div v-if="row.game_map" class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="mapArt(row)"></div>
                    <div class="absolute inset-y-0 left-0 w-1.5 bg-teal"></div>
                    <div class="relative flex flex-col whitespace-nowrap">
                      <span class="font-bold">{{ row.game_map ? row.game_map.name : '' }}</span>
                      <span class="text-xs">{{ formatDate(row.game_date) }}</span>
                    </div>
                  </div>
                  <span class="relative w-[10rem] shrink-0 px-4 font-medium text-header">{{ row.game_type ? row.game_type.name : '' }}</span>
                  <span class="relative z-[2] w-[8.75rem] shrink-0 px-4"><a class="group/bt inline-flex items-center" :href="matchUrl(row)"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></span>
                  <span class="relative w-[5.5rem] shrink-0 px-4">{{ formatLength(row.game_length) }}</span>
                  <span class="relative w-[5.5rem] shrink-0 px-4">{{ row.region }}</span>
                  <span class="relative w-[8rem] shrink-0 px-4 text-sm text-subtext">{{ row.game_version }}</span>
                  <div v-for="side in sides(row)" :key="side.team" class="relative z-[2] w-[18rem] shrink-0 px-4 py-2 flex items-center gap-1.5">
                    <hero-image-wrapper v-for="(hero, heroIndex) in row.heroes[side.team]" :key="heroIndex" size="row" :hero="hero" :mobileClick="true"></hero-image-wrapper>
                  </div>
                  <span class="w-20 shrink-0"></span>
                </div>
                <div v-if="row.downloadable || isWatchable(row)" class="absolute z-[3] flex transition-opacity right-4 top-1/2 -translate-y-1/2 flex-row-reverse gap-4 md:opacity-0 md:group-hover/replay:opacity-100 md:focus-within:opacity-100">
                    <a v-if="row.downloadable && authenticated && !replayDownloadBlocked" class="max-md:hidden hover:text-lteal" :href="'/Match/Single/' + row.replayID + '/Download'" title="Download Replay"><i class="fa-solid fa-download"></i></a>
                    <span v-else-if="row.downloadable && !authenticated" class="max-md:hidden opacity-60" title="Log in to download replays"><i class="fa-solid fa-download"></i></span>
                    <a v-if="isWatchable(row) && authenticated" class="hover:text-lteal" :href="'/Analytics/Match/Single/' + row.replayID" title="Watch Replay"><i class="fa-solid fa-video"></i></a>
                    <span v-else-if="isWatchable(row)" class="opacity-60" title="Log in with Battle.net to watch replays"><i class="fa-solid fa-video"></i></span>
                  </div>
              </div>
            </div>
          </div>
        </panel-box>
        <div v-if="rows.length" class="flex justify-end">
          <pagination-bar :current="currentPage" :last="lastPage" @page="goToPage"></pagination-bar>
        </div>

        <!-- A search that ran out of time before filling a page says how far it got -->
        <p v-if="nextBeforeReplayID && !pageFull && searchedBackTo && !isLoading" class="text-sm text-center">
          {{ rows.length ? 'Found ' + rows.length.toLocaleString('en-US') + ' so far.' : 'No matches yet.' }} Searched back to {{ formatDay(searchedBackTo) }}.
          <button type="button" class="underline" @click="loadMore">Keep Searching</button>
        </p>
      </template>

      <template v-if="isLoading">
        <loading-component :compact="true" :textoverride="true">Searching replays...</loading-component>
        <panel-box v-if="!rows || !shownRows.length">
          <!-- The rows above with grey blocks, same column widths -->
          <template v-if="expanded">
            <div v-for="row in 6" :key="row" class="mb-1.5 flex flex-wrap items-center justify-between gap-y-4 rounded overflow-hidden bg-surface-1 2xl:flex-nowrap">
              <div class="relative flex-1 md:max-w-[25.625rem] min-w-[18.75rem] self-stretch flex items-center pl-6 py-4">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-surface-3"></div>
                <div class="flex flex-col gap-2">
                  <skeleton-box class="w-36 h-4"></skeleton-box>
                  <skeleton-box class="w-24 h-4"></skeleton-box>
                  <skeleton-box class="w-48 h-3"></skeleton-box>
                  <skeleton-box class="w-36 h-3"></skeleton-box>
                  <skeleton-box class="w-32 h-3"></skeleton-box>
                </div>
              </div>
              <div class="flex-1 flex flex-wrap justify-center gap-x-16 gap-y-4 px-4 py-4 min-w-0">
                <!-- Label, the three scoreboard icons, kills | deaths | assists, KDA -->
                <div v-for="side in 2" :key="side" class="flex flex-col gap-0.5 items-center">
                  <skeleton-box class="w-14 h-4"></skeleton-box>
                  <span class="grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] gap-x-[0.4375rem] w-[8.75rem] justify-items-center my-1">
                    <skeleton-box v-for="(icon, index) in 3" :key="icon" class="w-6 h-6" :style="{ gridColumn: index * 2 + 1 }"></skeleton-box>
                  </span>
                  <skeleton-box class="w-[8.75rem] h-8"></skeleton-box>
                  <skeleton-box class="w-16 h-4 mt-1"></skeleton-box>
                </div>
              </div>
              <div class="flex gap-2 pl-4 2xl:pl-1.5 pr-12 py-4 w-full 2xl:w-[22.625rem] shrink-0">
                <div v-for="side in 2" :key="side" class="flex flex-col gap-1.5 w-[9.375rem]">
                  <skeleton-box class="w-14 h-4"></skeleton-box>
                  <div v-for="player in 5" :key="player" class="flex gap-1 h-4 items-center">
                    <span class="w-[1.1875rem] shrink-0"></span>
                    <skeleton-box class="w-4 h-4 shrink-0 !rounded-full"></skeleton-box>
                    <skeleton-box class="flex-1 h-3"></skeleton-box>
                  </div>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="overflow-x-auto">
            <div class="md:min-w-max w-full">
              <div v-for="row in 12" :key="row" class="md:hidden mb-1 flex flex-col gap-1.5 rounded bg-surface-1 pl-5 pr-3 py-2.5">
                <skeleton-box class="w-32 h-4"></skeleton-box>
                <skeleton-box class="w-40 h-3"></skeleton-box>
                <skeleton-box class="w-48 max-w-full h-3"></skeleton-box>
              </div>
              <div v-for="row in 12" :key="'desktop' + row" class="max-md:hidden mb-1 flex items-center justify-between rounded overflow-hidden bg-surface-1">
                <div class="relative w-[20rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
                  <div class="absolute inset-y-0 left-0 w-1.5 bg-surface-3"></div>
                  <div class="flex flex-col gap-2">
                    <skeleton-box class="w-32 h-4"></skeleton-box>
                    <skeleton-box class="w-40 h-3"></skeleton-box>
                  </div>
                </div>
                <div class="w-[10rem] shrink-0 px-4"><skeleton-box class="w-24 h-4"></skeleton-box></div>
                <div class="w-[8.75rem] shrink-0 px-4"><skeleton-box class="w-20 h-4"></skeleton-box></div>
                <div class="w-[5.5rem] shrink-0 px-4"><skeleton-box class="w-12 h-4"></skeleton-box></div>
                <div class="w-[5.5rem] shrink-0 px-4"><skeleton-box class="w-8 h-4"></skeleton-box></div>
                <div class="w-[8rem] shrink-0 px-4"><skeleton-box class="w-20 h-3"></skeleton-box></div>
                <div v-for="side in 2" :key="side" class="w-[18rem] shrink-0 px-4 py-2 flex items-center gap-1.5">
                  <skeleton-box v-for="hero in 5" :key="hero" class="w-[2.625rem] h-[2.625rem] shrink-0 !rounded-full"></skeleton-box>
                </div>
                <span class="w-20 shrink-0"></span>
              </div>
            </div>
          </div>
        </panel-box>
      </template>
    </div>
  </div>
</template>

<script>
  import moment from 'moment-timezone';
  import Cookies from 'js-cookie';

  export default {
    name: 'MatchSearch',
    components: {
    },
    props: {
      filters: Object,
      gametypedefault: Array,
      oldestdate: String,
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      playermatchtablestyle: {
        type: [String, Boolean]
      },
      authenticated: Boolean,
      analyticsCutover: String,
      replayDownloadBlocked: Boolean,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        infoText: "Search recent replays by game type, map, heroes, players, patch and more.",
        pageSize: 100,
        rows: null,
        nextBeforeReplayID: null,
        pageFull: false,
        searchedBackTo: null,
        errors: [],
        searchParams: null,
        playerSearching: false,
        // Shares Match History's Compact / Expanded setting
        expanded: this.playermatchtablestyle == 1,
        // One page per search batch; the page after the last loaded one fetches the next
        currentPage: 1,
        partyColors: { purple: '#9b2bed', blue: '#2060ae', red: '#ae2023', yellow: '#c88b00' },
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
      searchInfo() {
        return [
          `Searches replays played since ${this.formatDay(this.oldestdate)} (the current and previous season), newest first, ${this.pageSize} at a time.`,
          'Several heroes or players use their OR / AND switch.',
          'HP MMR and rank filters (under Advanced Filters) must all hold for the same player: with heroes on OR, one player on any chosen hero; on AND, the player on every chosen hero.',
          'Ranks use the current league boundaries for each game type.',
        ];
      },
      loadedPages() {
        return Math.ceil((this.rows?.length || 0) / this.pageSize);
      },
      lastPage() {
        return Math.max(1, this.loadedPages + (this.nextBeforeReplayID ? 1 : 0), this.currentPage);
      },
      shownRows() {
        return (this.rows || []).slice((this.currentPage - 1) * this.pageSize, this.currentPage * this.pageSize);
      },
    },
    watch: {
    },
    methods: {
      async getData(beforeReplayID){
        this.isLoading = true;
        this.errors = [];

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/match/search", {
            ...this.searchParams,
            before_replayID: beforeReplayID,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            this.errors = response.data.errors || ['Those filters are not valid.'];
            return;
          }

          this.rows = beforeReplayID ? [...this.rows, ...response.data.data] : response.data.data;
          this.nextBeforeReplayID = response.data.next_before_replayID;
          this.pageFull = response.data.page_full;
          this.searchedBackTo = response.data.searched_back_to;
        }catch(error){
          if (error.response?.status === 429) {
            this.errors = ['Too many searches. Please wait a minute and try again.'];
          }
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
        this.searchParams = {
          game_type: filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametypedefault,
          game_map: filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null,
          region: filteredData.multi.Regions ? Array.from(filteredData.multi.Regions) : null,
          game_version: filteredData.multi["Game Version"] ? Array.from(filteredData.multi["Game Version"]) : null,
          heroes: filteredData.multi.Heroes ? Array.from(filteredData.multi.Heroes) : null,
          heroes_match: filteredData.single["Heroes Match"] || 'any',
          players: filteredData.multi.Players ? filteredData.multi.Players.map(player => ({ blizz_id: player.blizz_id, region: player.region })) : null,
          players_match: filteredData.single["Players Match"] || 'any',
          start_date: filteredData.single["From Date"] || null,
          end_date: filteredData.single["To Date"] || null,
          player_mmr_min: filteredData.single["HP Player MMR Min"] ?? null,
          player_mmr_max: filteredData.single["HP Player MMR Max"] ?? null,
          hero_mmr_min: filteredData.single["HP Hero MMR Min"] ?? null,
          hero_mmr_max: filteredData.single["HP Hero MMR Max"] ?? null,
          role_mmr_min: filteredData.single["HP Role MMR Min"] ?? null,
          role_mmr_max: filteredData.single["HP Role MMR Max"] ?? null,
          player_rank: filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null,
          hero_rank: filteredData.multi["HP Hero Rank"] && filteredData.multi.Heroes ? Array.from(filteredData.multi["HP Hero Rank"]) : null,
          role_rank: filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null,
        };

        this.rows = null;
        this.currentPage = 1;
        this.nextBeforeReplayID = null;
        this.pageFull = false;
        this.searchedBackTo = null;
        this.getData(null);
      },
      // Like Match History: a cookie for everyone, and the account setting when logged in
      setStyle(side) {
        this.expanded = side === 'right';
        Cookies.set('playerhistorytable', this.expanded ? '1' : '0', { expires: 365, path: '/' });
        if (this.authenticated) {
          this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.expanded }).catch(() => {});
        }
      },
      // Same rules as the match history's Watch Replay icon
      isWatchable(row) {
        return !!this.analyticsCutover && row.game_date >= this.analyticsCutover;
      },
      // Winners first; a game without a recorded winner keeps team order
      sides(row) {
        const first = row.winner === 1 ? 1 : 0;
        return [first, 1 - first].map(team => ({ team, won: row.winner === team }));
      },
      // The match history's KDA, for the team's totals
      teamKda(row, team) {
        const totals = row.totals?.[team] ?? {};
        return parseFloat((((totals.kills ?? 0) + (totals.assists ?? 0)) / Math.max(totals.deaths ?? 0, 1)).toFixed(2));
      },
      teamTotal(row, team, key) {
        return (row.totals?.[team]?.[key] ?? 0).toLocaleString('en-US');
      },
      teamPlayers(row, team) {
        return (row.players && row.players[team]) || [];
      },
      // Runs of players in the same party, as the match history groups them
      partyGroups(team){
        return team.reduce((groups, player, index) => {
          const last = groups[groups.length - 1];
          if(last && player.party && last.party === player.party){
            last.players.push(player);
          }else{
            groups.push({ party: player.party, start: index, players: [player] });
          }
          return groups;
        }, []);
      },
      matchUrl(row) {
        return '/Match/Single/' + row.replayID;
      },
      mapArt(row) {
        return { backgroundImage: `url('/images/maps/match/match-${row.game_map.sanitized_map_name}.jpg')` };
      },
      setPlayerSearching(searching){
        this.playerSearching = searching;
      },
      async goToPage(page){
        this.currentPage = page;
        if(page * this.pageSize > this.rows.length && this.nextBeforeReplayID){
          await this.loadMore();
        }
      },
      async loadMore(){
        if(!this.isLoading && this.nextBeforeReplayID){
          await this.getData(this.nextBeforeReplayID);
          // Back to the last page with results if the search ended short
          this.currentPage = Math.min(this.currentPage, Math.max(1, this.loadedPages));
        }
      },
      formatDate(dateString) {
        const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik');
        const localDate = originalDate.clone().tz(moment.tz.guess());

        return localDate.format('MM/DD/YYYY h:mm:ss a');
      },
      formatDay(dateString) {
        return moment(dateString).format('MM/DD/YYYY');
      },
      formatLength(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainder = String(seconds % 60).padStart(2, '0');
        return `${minutes}:${remainder}`;
      },
    }
  }
</script>
