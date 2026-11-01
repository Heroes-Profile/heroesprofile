<template>
  <div>
    <page-heading :infoText1="'Role data for ' + battletag + ' on ' + role" :heading="'Role Stats: '+ role" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
      <slot>
        <round-image :image="`/images/roles/${role.toLowerCase()}.PNG`" :excludehover="true"></round-image>
      </slot>
    </page-heading>

    <div class="max-w-[1920px] mx-auto px-4 md:px-8">
      <panel-box>
        <div class="filter-row">
          <multi-select-filter :values="filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="gametypedefault"></multi-select-filter>
          <multi-select-filter :values="filters.seasons" :text="'Season'" @input-changed="handleInputChange" :defaultValue="seasonselection"></multi-select-filter>
          <date-range-filter :startDate="startdate" :endDate="enddate" @input-changed="handleInputChange"></date-range-filter>
          <single-select-filter :values="gameMapWithAll" :text="'Game Map'" @input-changed="handleInputChange" :trackclosure="true" :defaultValue="'All'" :disabled="disableFilterInput"></single-select-filter>
          <custom-button class="font-semibold text-center m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :text="'Filter'" :alt="'Filter'" :color="'blue'" :disabled="disableFilterInput" :ignoreclick="true" @click="applyFilter"></custom-button>
        </div>
      </panel-box>
    </div>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    
    <div v-if="data" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">

      <panel-box>
      <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

        <!-- Role overview, laid out like the profile's player card -->
        <panel-box :inner="true" class="!gap-8">
          <h2 class="text-3xl font-bold">{{ role }}</h2>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="shrink-0 w-40 md:w-72 self-center [&_img]:!object-contain">
              <round-image :image="`/images/roles/${role.toLowerCase()}.PNG`" :excludehover="true" size="large"></round-image>
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
            <div class="flex justify-between items-baseline gap-6">
              <h3 class="text-2xl font-bold">{{ section.title }}</h3>
              <a class="text-header whitespace-nowrap hover:underline" :href="profileUrl + section.allPath">{{ section.allText }}<i class="fas fa-chevron-right text-sm ml-3"></i></a>
            </div>
            <div class="flex flex-wrap gap-6">
              <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :playerlink="true" :text="group.label" :data="group.data"></group-box>
            </div>
          </div>
        </panel-box>

        <div class="flex flex-col gap-6 min-w-0">
          <player-party-size-panel :data="data" prefix="stack_size_"></player-party-size-panel>

          <player-mmr-panel :data="data" :link-href="profileUrl + '/MMR'" :link-query="{ role: role }"></player-mmr-panel>

          <div class="grid md:grid-cols-2 gap-6">
            <panel-box :inner="true" :title="'Heroes Played As ' + role">
              <div class="flex flex-wrap gap-2">
                <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" v-for="(item, index) in data.hero_data_all_heroes" :key="index">
                  <hero-image-wrapper :hero="item.hero">
                    <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
                  </hero-image-wrapper>
                </a>
              </div>
            </panel-box>

            <panel-box :inner="true" :title="'Maps Played As ' + role">
              <div class="flex flex-wrap gap-2">
                <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" v-for="(item, index) in data.map_data" :key="index">
                  <map-image-wrapper :map="item.game_map">
                    <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
                  </map-image-wrapper>
                </a>
              </div>
            </panel-box>
          </div>
        </div>
      </div>
      </panel-box>

      <player-season-win-rate-panel :data="data" :title="`${battletag} Win Rate Data Per Season with ${role}`"></player-season-win-rate-panel>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="data.latestGames" title="Recent Matches" link-text="Match History" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Match/History/?role=' + role" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :data="item" :replay-viewer-allowed="replayViewerAllowed" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
        </div>
      </panel-box>
    </div>
    <div v-else-if="typeof data === 'undefined'"  class="flex items-center justify-center">
      No data
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading || asyncLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">
      <loading-component :compact="true" :textoverride="true">{{ asyncLoading ? 'This is taking longer than expected. A background task is computing your results. Please be patient.' : 'Loading data...' }}</loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[780px]">
            <h2 class="text-3xl font-bold">{{ role }}</h2>
            <div class="flex gap-8 max-sm:flex-col">
              <skeleton-box class="w-40 md:w-60 h-40 md:h-60 shrink-0 !rounded-full"></skeleton-box>
              <div class="flex-1 flex flex-col gap-6">
                <skeleton-box class="h-[30px]"></skeleton-box>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                  <skeleton-box v-for="index in 10" :key="index" class="h-11"></skeleton-box>
                </div>
              </div>
            </div>
            <div v-for="title in ['Heroes', 'Maps']" :key="title" class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ title }}</h3>
              <div class="flex flex-wrap gap-6">
                <div v-for="group in 3" :key="group" class="flex gap-3">
                  <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                </div>
              </div>
            </div>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0">
            <panel-box :inner="true" title="Party Size Win Rates">
              <skeleton-box v-for="index in 5" :key="index" class="h-10"></skeleton-box>
            </panel-box>
            <panel-box :inner="true" title="Heroes Profile MMR">
              <skeleton-box v-for="index in 6" :key="index" class="h-9"></skeleton-box>
            </panel-box>
            <div class="grid md:grid-cols-2 gap-6">
              <panel-box v-for="title in ['Heroes Played As ' + role, 'Maps Played As ' + role]" :key="title" :inner="true" :title="title">
                <div class="flex flex-wrap gap-2">
                  <skeleton-box v-for="tile in 12" :key="tile" class="w-10 h-10 !rounded-full"></skeleton-box>
                </div>
              </panel-box>
            </div>
          </div>
        </div>
      </panel-box>

      <panel-box title="Win Rate Per Season">
        <skeleton-box class="h-72"></skeleton-box>
      </panel-box>

      <panel-box title="Recent Matches" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-skeleton v-for="index in 7" :key="index"></game-summary-skeleton>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PlayerRoleSingleStats',
    components: {
    },
    props: {
      filters: Object,
      role: String,
      battletag: String,
      blizzid: {
        type: [String, Number]
      },
      playerloadsetting: {
        type: [String, Boolean]
      },
      region: Number,
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
        isLoading: false,
        asyncLoading: false,
        cancelTokenSource: null,
        inputrole: null,
        data: null,
        modifiedgametype: null,
        modifiedseason: null,
        seasonselection: [],
        startdate: null,
        enddate: null,
        modifiedgamemap: null,
        disableFilterInput: null,
      }
    },
    created(){
      this.inputrole = this.hero;
      if(this.gametypedefault && this.gametypedefault.length > 0){
        this.modifiedgametype = [...this.gametypedefault];
      }
    },
    mounted() {
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    },
    computed: {
      overviewStats() {
        return [
          { label: 'Wins', value: this.data.wins.toLocaleString('en-US') },
          { label: 'Losses', value: this.data.losses.toLocaleString('en-US') },
          { label: 'KDR', value: this.data.kdr },
          { label: 'KDA', value: this.data.kda },
          { label: 'Kills', value: this.data.sum_kills.toLocaleString('en-US') },
          { label: 'Deaths', value: this.data.sum_deaths.toLocaleString('en-US') },
          { label: 'Assists', value: this.data.sum_assists.toLocaleString('en-US') },
          { label: 'Takedowns', value: this.data.sum_takedowns.toLocaleString('en-US') },
          { label: 'Games', value: this.data.games_played.toLocaleString('en-US') },
        ];
      },
      topSections() {
        return [
          { title: 'Heroes', allText: 'All Heroes', allPath: '/Hero', groups: [
            { label: 'Most Played', data: this.data.hero_data_top_played.slice(0, 3) },
            { label: 'Highest Win Rate', data: this.data.hero_data_top_win_rate.slice(0, 3) },
            { label: 'Latest Played', data: this.data.hero_data_top_latest_played.slice(0, 3) },
          ] },
          { title: 'Maps', allText: 'All Maps', allPath: '/Map', groups: [
            { label: 'Most Played', data: this.data.map_data_top_played.slice(0, 3) },
            { label: 'Highest Win Rate', data: this.data.map_data_top_win_rate.slice(0, 3) },
            { label: 'Latest Played', data: this.data.map_data_top_latest_played.slice(0, 3) },
          ] },
        ];
      },
      profileUrl() {
        return '/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region;
      },
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
      gameMapWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.game_maps];
        updatedList.unshift(newValue);
        return updatedList;
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
        this.disableFilterInput = true;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/player/role/single", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            start_date: this.startdate,
            end_date: this.enddate,
            game_map: this.modifiedgamemap,
            type: "single",
            page: "role",
            role: this.role,
          },
          {
            cancelToken: this.cancelTokenSource.token,
            onLoadStatus: (status, meta) => {
              if (meta.phase === 'polling') {
                this.asyncLoading = true;
              }
            },
          });

          // Rows show once their teams are in
          await this.$loadMatchDetails(response.data[0]?.latestGames, this.blizzid, this.region);
          this.data = response.data[0];
        }catch(error){
          //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.asyncLoading = false;
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

        this.handleSeasonOrDateChange(eventPayload);

        if(eventPayload.field == "Game Map"){
          if(eventPayload.value == "All"){
            this.modifiedgamemap = null;
          }else{
            this.modifiedgamemap = eventPayload.value;
          }
        }
      },
      // Seasons and the date range are one or the other
      handleSeasonOrDateChange(eventPayload) {
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
    }
  }
</script>