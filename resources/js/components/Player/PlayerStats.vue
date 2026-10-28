<template>
  <div class="">
    <page-heading :heading="'Profile Data'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
    </page-heading>

    <div class="max-w-[1920px] mx-auto px-4 md:px-8">
      <panel-box>
        <div class="-m-2 flex flex-wrap items-end gap-2 max-md:flex-col max-md:items-center">
        <multi-select-filter class="md:flex-1" :values="filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="gametypedefault"></multi-select-filter>
        <multi-select-filter class="md:flex-1" :values="filters.seasons" :text="'Season'" @input-changed="handleInputChange" :defaultValue="seasonselection"></multi-select-filter>
        <date-range-filter class="md:flex-[2] md:min-w-[34rem]" :startDate="startdate" :endDate="enddate" @input-changed="handleInputChange"></date-range-filter>
        <custom-button class="font-semibold px-6 mb-2 mr-2 max-md:self-stretch max-md:mx-2" :text="'Filter'" :alt="'Filter'" :color="'red'" :disabled="disableFilterInput" :ignoreclick="true" @click="applyFilter"></custom-button>
        </div>
      </panel-box>
    </div>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data == ''" class="flex md:p-20 gap-10 mx-auto justify-center items-between">
      <div class="flex items-center">
        <span>No data for this player and filters</span>
      </div>
    </div>
    <div v-else-if="data" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">

      <panel-box>
      <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

        <!-- Player overview -->
        <panel-box ref="overviewCard" :inner="true" class="!gap-8">
          <div class="flex items-center gap-3">
            <icon-with-hover v-if="realRank && realRank.current && realRank.current.league != 'unranked'" popupsize="fit">
              <template #trigger>
                <real-rank-badge :rank="realRank.current" :width="48"></real-rank-badge>
              </template>
              <image-hover-box title="Storm League"></image-hover-box>
              <real-rank-badge :rank="realRank.current" :width="96" label="below" label-class="text-subtext mt-1"></real-rank-badge>
            </icon-with-hover>
            <div class="flex flex-col">
              <h2 class="text-3xl font-bold">{{ battletag }}</h2>
              <region-tag :region="regionsmap[region]" :full="true"></region-tag>
            </div>
            <div class="card-actions ml-auto flex gap-1 text-header">
              <button v-if="canCopyImage" type="button" class="p-1.5 hover:text-lteal" :title="cardCopied ? 'Copied' : 'Copy card image'" @click="copyCard"><i :class="cardCopied ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i></button>
              <button type="button" class="p-1.5 hover:text-lteal" title="Download card image" @click="downloadCard"><i class="fa-solid fa-download"></i></button>
            </div>
          </div>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="flex flex-col items-center gap-2 shrink-0">
              <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" :excludehover="inputHero.name == 'Auto Select'" size="large"></hero-image-wrapper>
              <div class="font-bold text-subtext">Level: <span class="text-header">{{ data.account_level.toLocaleString('en-US') }}</span></div>
            </div>

            <div class="flex-1 min-w-0 w-full flex flex-col gap-6">
              <div class="flex flex-col gap-1">
                <span class="text-header">Win Rate</span>
                <stat-bar size="big" :value="data.win_rate.toFixed(2)" suffix="%" :color="$winRateColor(data.win_rate)"></stat-bar>
              </div>

              <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                <stat-box v-for="stat in overviewStats" :key="stat.label" class="!m-0 !px-0 !min-w-0" :title="stat.label" :value="stat.value"></stat-box>
              </div>
            </div>
          </div>

          <div v-for="section in topSections" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
            <div class="flex justify-between items-baseline">
              <h3 class="text-2xl font-bold">{{ section.title }}</h3>
              <a class="underline text-header" :href="profileUrl + section.allPath">{{ section.allText }}</a>
            </div>
            <div class="flex flex-wrap gap-6">
              <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :playerlink="true" :text="group.label" :data="group.data"></group-box>
            </div>
          </div>

          <p class="flex flex-wrap gap-2"><span class="text-subtext">Total Time Played:</span><span class="text-header">{{ data.total_time_played }}</span></p>
        </panel-box>

        <div class="flex flex-col gap-6 min-w-0">
          <div class="grid md:grid-cols-2 gap-6">
            <!-- Win rate by role -->
            <panel-box :inner="true" title="Win Rate By Role" link-text="View all roles" :link-href="profileUrl + '/Role'">
              <div class="flex flex-col gap-3">
                <div v-for="role in roleWinRates" :key="role.label" class="flex flex-col gap-1.5">
                  <span class="text-header">{{ role.label }}</span>
                  <stat-bar :value="role.value.toFixed(2)" suffix="%" :color="$winRateColor(role.value)"></stat-bar>
                </div>
              </div>
            </panel-box>

            <!-- Party size -->
            <panel-box :inner="true" title="Party Size Win Rates">
              <div class="flex flex-col gap-3">
                <div v-for="stack in partySizes" :key="stack.label" class="flex flex-col gap-1.5">
                  <div class="flex justify-between">
                    <span class="text-header">{{ stack.label }}</span>
                    <span class="text-subtext">Games: {{ stack.games.toLocaleString('en-US') }}</span>
                  </div>
                  <stat-bar :value="stack.winRate.toFixed(2)" suffix="%" :color="$winRateColor(stack.winRate)"></stat-bar>
                </div>
              </div>
            </panel-box>
          </div>

          <!-- Heroes Profile MMR -->
          <panel-box :inner="true" title="Heroes Profile MMR" link-text="View HP MMR Breakdown" :link-href="profileUrl + '/MMR'">
            <template #title>
              <round-image size="small" icon="fas fa-info" title="info" popupsize="large">
                <div>
                  <p class="max-sm:text-xs">Heroes Profile MMR is a custom algorithm developed by Heroes Profile for approximating a player's skill. This MMR or rank does not correlate to your in-game rank or MMR.</p>
                </div>
              </round-image>
            </template>
            <div class="flex flex-col gap-2">
              <div class="flex gap-12 items-center font-medium text-subtext max-md:hidden">
                <span class="w-[200px] shrink-0">Game mode</span>
                <span class="flex-1">Win Rate</span>
                <span class="flex-1 max-w-[200px]">HP Rank Tier</span>
                <span class="flex-1 max-w-[100px] text-center">HP MMR</span>
              </div>
              <template v-for="mode in mmrModes" :key="mode.label">
                <div class="h-px bg-gray-dark"></div>
                <!-- Phones stack each mode: name, then bar, then tier and MMR -->
                <div class="flex flex-wrap md:flex-nowrap gap-x-6 md:gap-x-12 gap-y-2 items-center">
                  <span class="w-full md:w-[200px] shrink-0 font-semibold text-header truncate">{{ mode.label }}</span>
                  <stat-bar class="max-md:w-full md:flex-1 min-w-0" :value="mode.data ? mode.data.win_rate.toFixed(2) : '0.00'" suffix="%" :color="$winRateColor(mode.data ? mode.data.win_rate : 0)"></stat-bar>
                  <span class="md:flex-1 md:max-w-[200px]"><rank-tier-pill v-if="mode.data" :tier="mode.data.rank_tier"></rank-tier-pill></span>
                  <span class="md:flex-1 md:max-w-[100px] md:text-center font-semibold max-md:ml-auto">{{ mode.data ? mode.data.mmr.toLocaleString('en-US') : 0 }}</span>
                </div>
              </template>
            </div>
          </panel-box>
        </div>
      </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="grid xl:grid-cols-2 gap-6">
        <player-real-rank :blizzid="blizzid" :region="region" @loaded="realRank = $event"></player-real-rank>

        <panel-box v-if="data.weekday_data" title="Week Day Win Rates">
          <player-weekday-win-rates :data="data.weekday_data"></player-weekday-win-rates>
        </panel-box>
      </div>

      <div class="flex flex-wrap justify-center gap-6">
        <dynamic-square-ad :patreon-user="patreonUser" :index="1"></dynamic-square-ad>
        <dynamic-square-ad :patreon-user="patreonUser" :index="2"></dynamic-square-ad>
      </div>

      <panel-box v-if="data.matchData" title="Recent Matches" link-text="View Match History" :link-href="profileUrl + '/Match/History'" :divider="false">
        <div>
          <!-- Same column widths and padding as the game-summary-box rows -->
          <div class="flex justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext max-2xl:hidden">
            <span class="flex-1 max-w-[410px] pl-6">Match</span>
            <span class="w-[430px] shrink-0 px-4">Statistics</span>
            <span class="w-[126px] shrink-0"></span>
            <span class="w-[362px] shrink-0 px-4">Talents</span>
            <span class="w-[362px] shrink-0 pl-1.5">Players</span>
          </div>
          <game-summary-box v-for="(item, index) in data.matchData" :key="index" :data="item" :replay-viewer-allowed="replayViewerAllowed" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
        </div>
      </panel-box>

    </div>
    <!-- The page's layout with grey blocks while it loads; the nav logo spins meanwhile -->
    <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">
      <loading-component :compact="true" :textoverride="true">Large amount of data. Please be patient. Loading data...</loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[780px]">
            <h2 class="text-3xl font-bold">{{ battletag }}</h2>
            <div class="flex gap-8 max-sm:flex-col">
              <skeleton-box class="w-40 md:w-72 h-[23em] shrink-0 rounded-lg"></skeleton-box>
              <div class="flex-1 flex flex-col gap-6">
                <skeleton-box class="h-[30px]"></skeleton-box>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                  <skeleton-box v-for="index in 8" :key="index" class="h-11"></skeleton-box>
                </div>
              </div>
            </div>
            <div v-for="title in ['Heroes', 'Maps']" :key="title" class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ title }}</h3>
              <div class="flex flex-wrap gap-6">
                <div v-for="group in 3" :key="group" class="flex gap-3">
                  <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded-lg"></skeleton-box>
                </div>
              </div>
            </div>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0">
            <div class="grid md:grid-cols-2 gap-6">
              <panel-box v-for="title in ['Win Rate By Role', 'Party Size Win Rates']" :key="title" :inner="true" :title="title">
                <skeleton-box v-for="index in 6" :key="index" class="h-10"></skeleton-box>
              </panel-box>
            </div>
            <panel-box :inner="true" title="Heroes Profile MMR">
              <skeleton-box v-for="index in 6" :key="index" class="h-9"></skeleton-box>
            </panel-box>
          </div>
        </div>
      </panel-box>

      <div class="grid xl:grid-cols-2 gap-6">
        <panel-box v-for="title in ['Storm League Rank', 'Week Day Win Rates']" :key="title" :title="title">
          <skeleton-box class="h-[360px]"></skeleton-box>
        </panel-box>
      </div>

      <panel-box title="Recent Matches" :divider="false">
        <skeleton-box v-for="index in 5" :key="index" class="h-[136px] rounded-lg"></skeleton-box>
      </panel-box>
    </div>

  </div>

</template>

<script>
import { toBlob } from 'html-to-image';

  export default {
    name: 'PlayerStats',
    components: {
    },
    props: {
      playerloadsetting: {
        type: [String, Boolean]
      },
      filters: Object,
      battletag: String,
      blizzid: String, 
      region: String,
      season: Number,
      regionsmap: Object,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
      replayViewerAllowed: Boolean,
      analyticsCutover: String,
      replayDownloadBlocked: Boolean,

    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        data: null,
        modifiedgametype: null,
        modifiedseason: null,
        seasonselection: [],
        startdate: null,
        enddate: null,
        inputHero: null,
        disableFilterInput: null,
        realRank: null,
        cardCopied: false,
        canCopyImage: typeof window.ClipboardItem !== 'undefined' && !!(navigator.clipboard && navigator.clipboard.write),
      }
    },
    created(){
      if(this.season){
        // Comes from the query string, so it can arrive as a string
        this.seasonselection = [Number(this.season)];
        this.modifiedseason = [Number(this.season)];
      }

      if(this.gametypedefault && this.gametypedefault.length > 0){
        this.modifiedgametype = [...this.gametypedefault];
      }
    },
    mounted() {
      if(this.settinghero){
        this.inputHero = this.settinghero;
      }else{
        this.inputHero = {
          name: "Auto Select",
          short_name: "autoselect3",
          icon: "autoselect3.jpg",
        };
      }
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    },
    computed: {
     gameTypesWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.game_types_full];
        updatedList.unshift(newValue);
        return updatedList;
      },
      seasonsWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.seasons];
        updatedList.unshift(newValue);
        return updatedList;
      },
      profileUrl(){
        return `/Player/${this.battletag}/${this.blizzid}/${this.region}`;
      },
      overviewStats(){
        return [
          { label: 'First To Ten', value: this.data.first_to_ten_win_rate.toFixed(2) + '%' },
          { label: 'Second To Ten', value: this.data.second_to_ten_win_rate.toFixed(2) + '%' },
          { label: 'Wins', value: this.data.wins.toLocaleString('en-US') },
          { label: 'Losses', value: this.data.losses.toLocaleString('en-US') },
          { label: 'KDR', value: this.data.kdr },
          { label: 'KDA', value: this.data.kda },
          { label: 'MVP', value: this.data.mvp_rate.toFixed(2) + '%' },
          { label: 'AVG. Time On Fire', value: this.data.average_time_on_fire },
        ];
      },
      topSections(){
        return [
          { title: 'Heroes', allText: 'All Heroes', allPath: '/Hero', groups: [
            { label: 'Most Played', data: this.data.heroes_three_most_played },
            { label: 'Highest Win Rate', data: this.data.heroes_three_highest_win_rate },
            { label: 'Latest Played', data: this.data.heroes_three_latest_played },
          ] },
          { title: 'Maps', allText: 'All Maps', allPath: '/Map', groups: [
            { label: 'Most Played', data: this.data.maps_three_most_played },
            { label: 'Highest Win Rate', data: this.data.maps_three_highest_win_rate },
            { label: 'Latest Played', data: this.data.maps_three_latest_played },
          ] },
        ];
      },
      roleWinRates(){
        return [
          { label: 'Bruiser', value: this.data.bruiser_win_rate },
          { label: 'Healer', value: this.data.healer_win_rate },
          { label: 'Melee Assassin', value: this.data.melee_assassin_win_rate },
          { label: 'Ranged Assassin', value: this.data.ranged_assassin_win_rate },
          { label: 'Support', value: this.data.support_win_rate },
          { label: 'Tank', value: this.data.tank_win_rate },
        ];
      },
      partySizes(){
        return [
          { label: 'Solo', key: 'one' },
          { label: '2 - Stack', key: 'two' },
          { label: '3 - Stack', key: 'three' },
          { label: '4 - Stack', key: 'four' },
          { label: '5 - Stack', key: 'five' },
        ].map(stack => ({
          label: stack.label,
          games: this.data[`stack_${stack.key}_wins`] + this.data[`stack_${stack.key}_losses`],
          winRate: this.data[`stack_${stack.key}_win_rate`],
        }));
      },
      mmrModes(){
        return [
          { label: 'Quick Match', data: this.data.qm_mmr_data },
          { label: 'Unranked Draft', data: this.data.ud_mmr_data },
          { label: 'Hero League', data: this.data.hl_mmr_data },
          { label: 'Team League', data: this.data.tl_mmr_data },
          { label: 'Storm League', data: this.data.sl_mmr_data },
          { label: 'ARAM', data: this.data.ar_mmr_data },
        ];
      },
      isOwner(){
        if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
          return true;
        }
        return false;
      }
    },
    watch: {
    },
    methods: {
      async getData(){
        this.disableFilterInput = true;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/player", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            start_date: this.startdate,
            end_date: this.enddate,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });
          this.data = response.data;
        }catch(error){
        //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.disableFilterInput = false;
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      handleInputChange(eventPayload) {
        if(eventPayload.field == "Game Type"){
          this.modifiedgametype = eventPayload.value.length ? [...eventPayload.value] : null;
        }

        // Seasons and the date range are one or the other
        if(eventPayload.field == "Season"){
          this.modifiedseason = eventPayload.value.length ? [...eventPayload.value] : null;
          if(eventPayload.value.length){
            this.startdate = null;
            this.enddate = null;
          }
        }

        if(eventPayload.field == "From Date" || eventPayload.field == "To Date"){
          if(eventPayload.field == "From Date"){
            this.startdate = eventPayload.value;
          }else{
            this.enddate = eventPayload.value;
          }
          if(eventPayload.value && this.modifiedseason){
            this.modifiedseason = null;
            this.seasonselection = [];
          }
        }
      },
      applyFilter(){
        if(!this.isLoading){
          this.data = null;
          this.getData();
        }
      },
    },

  }
</script>