<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading :heading="'Replay Search'">
      <template #headingInfo>
        <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
          <p class="font-normal">Searches replays played since {{ formatDay(oldestdate) }} (the current and previous season), newest first, {{ pageSize }} at a time. Several heroes or players use their OR / AND switch. HP MMR and rank filters (under Advanced Filters) must all hold for the same player: with heroes on OR, one player on any chosen hero; on AND, the player on every chosen hero. Ranks use the current league boundaries for each game type.</p>
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

      <template v-if="rows">
        <p v-if="rows.length == 0 && !isLoading && !nextBeforeReplayID">No replays match those filters.</p>
        <panel-box v-if="rows.length">
        <div class="w-full overflow-x-auto">
          <table class="min-w-0 w-full responsive-table max-md:text-xs">
            <thead>
              <tr>
                <th class="py-2 px-3 text-center text-sm leading-4">Match ID</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Game Date</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Game Type</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Game Map</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Length</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Region</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Version</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Team 1</th>
                <th class="py-2 px-3 text-center text-sm leading-4">Team 2</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr v-for="row in rows" :key="row.replayID">
                <td><a class="group/bt inline-flex items-center" :href="'/Match/Single/' + row.replayID"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></td>
                <td>{{ formatDate(row.game_date) }}</td>
                <td>{{ row.game_type ? row.game_type.name : '' }}</td>
                <td>{{ row.game_map ? row.game_map.name : '' }}</td>
                <td>{{ formatLength(row.game_length) }}</td>
                <td>{{ row.region }}</td>
                <td>{{ row.game_version }}</td>
                <td v-for="team in [0, 1]" :key="team">
                  <div class="flex items-center justify-center gap-1 max-md:flex-wrap">
                    <hero-image-wrapper v-for="(hero, heroIndex) in row.heroes[team]" :key="heroIndex" :hero="hero" :mobileClick="true"></hero-image-wrapper>
                    <span v-if="row.winner === team" class="bg-teal rounded px-1 text-xs ml-1">Won</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        </panel-box>

        <div v-if="nextBeforeReplayID && !isLoading" class="flex flex-col items-center gap-2">
          <!-- A search that ran out of time before filling a page says how far it got -->
          <p v-if="!pageFull && searchedBackTo" class="text-sm">
            {{ rows.length ? 'Found ' + rows.length.toLocaleString('en-US') + ' so far.' : 'No matches yet.' }} Searched back to {{ formatDay(searchedBackTo) }}.
          </p>
          <custom-button @click="loadMore" :text="pageFull ? 'Load ' + pageSize + ' More' : 'Keep Searching'" :alt="'Load More'" size="small" :ignoreclick="true"></custom-button>
        </div>
      </template>

      <template v-if="isLoading">
        <loading-component :compact="true" :textoverride="true">Searching replays...</loading-component>
        <table-skeleton v-if="!rows || !rows.length"></table-skeleton>
      </template>
    </div>
  </div>
</template>

<script>
  import moment from 'moment-timezone';

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
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        infoText: "Search recent replays by game type, map, heroes, players, patch and more.",
        pageSize: 1000,
        rows: null,
        nextBeforeReplayID: null,
        pageFull: false,
        searchedBackTo: null,
        errors: [],
        searchParams: null,
        playerSearching: false,
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
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
        this.nextBeforeReplayID = null;
        this.pageFull = false;
        this.searchedBackTo = null;
        this.getData(null);
      },
      setPlayerSearching(searching){
        this.playerSearching = searching;
      },
      loadMore(){
        if(!this.isLoading && this.nextBeforeReplayID){
          this.getData(this.nextBeforeReplayID);
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
