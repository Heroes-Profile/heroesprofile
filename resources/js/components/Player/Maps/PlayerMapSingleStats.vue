<template>
  <div>
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'Map data for ' + battletag + ' on ' + map" -->
        <page-heading v-if="esport" :heading="'Map Stats: ' + map" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
        <page-heading v-else :heading="'Map Stats: '+  map" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
    <div class="max-w-[1920px] mx-auto px-4 md:px-8 w-full">
      <panel-box :filters="true" v-if="esport && ngsFilterOptions">
        <div class="filter-row justify-center">
          <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" :defaultValue="ngsSeason"></single-select-filter>
          <single-select-filter v-if="ngsFilterOptions.divisions" :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" :defaultValue="ngsDivision"></single-select-filter>
          <single-select-filter :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" :defaultValue="ngsTeam"></single-select-filter>
          <button :disabled="isLoading" @click="applyFilter" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div>
      </panel-box>
      <panel-box :filters="true" v-else-if="!esport">
        <div class="filter-row">
          <multi-select-filter :values="filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="gametypedefault"></multi-select-filter>
          <multi-select-filter :values="filters.seasons" :text="'Season'" @input-changed="handleInputChange" :defaultValue="seasonselection"></multi-select-filter>
          <date-range-filter :startDate="startdate" :endDate="enddate" @input-changed="handleInputChange"></date-range-filter>
          <custom-button data-filter-apply class="font-semibold text-center h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :text="'Filter'" :alt="'Filter'" :color="'blue'" :disabled="disableFilterInput" :ignoreclick="true" @click="applyFilter"></custom-button>
        </div>
      </panel-box>
    </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data && esport && !data.games_played" class="flex items-center justify-center mt-8">
      No {{ map }} games in {{ league.name }} for these filters.
    </div>
    <div v-else-if="data" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">

      <panel-box>
      <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

        <!-- Map overview, laid out like the profile's player card -->
        <panel-box :inner="true" class="!gap-8">
          <h2 class="text-3xl font-bold">
            <hero-heading-select type="map" :heroes="filters.game_maps" :value="map" textclass="" @hero-changed="changeMap($event)"></hero-heading-select>
          </h2>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="shrink-0 w-40 md:w-60 aspect-[3/4] overflow-hidden rounded">
              <round-image class="!w-full !h-full" :image="`/images/maps/icon/${formatMapName(map)}`" :excludehover="true" size="large"></round-image>
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

          <div class="flex flex-col gap-4 w-fit max-w-full">
            <h3 class="text-2xl font-bold">Heroes</h3>
            <div class="flex flex-wrap gap-6 max-md:justify-center">
              <group-box v-for="group in heroGroups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
            </div>
          </div>
        </panel-box>

        <div class="flex flex-col gap-6 min-w-0">
          <!-- Esports: the teams they played the map for -->
          <panel-box v-if="esport && data.teams_played_for && data.teams_played_for.length" :inner="true" title="Teams Played For">
            <div class="flex flex-col gap-2">
              <a v-for="team in data.teams_played_for" :key="team.team_name + team.season" :href="team.link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                <img v-if="team.image" class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0px]" :src="team.image" :alt="$ngsTeam(team.team_name).name"/>
                <div class="flex flex-col min-w-0 flex-1">
                  <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></span>
                  <span class="text-sm text-subtext">{{ [team.season ? 'Season ' + team.season : null, team.division ? 'Division ' + team.division : null].filter(Boolean).join(' · ') }}</span>
                </div>
                <div class="flex flex-col items-end shrink-0 text-sm">
                  <span class="font-semibold">{{ team.win_rate.toFixed(2) }}%</span>
                  <span class="text-subtext">{{ team.games_played }} games</span>
                </div>
              </a>
            </div>
          </panel-box>
          <player-party-size-panel v-if="!esport" :data="data" prefix="stack_size_"></player-party-size-panel>
        </div>
      </div>
      </panel-box>

      <player-season-win-rate-panel :data="data" :title="`${battletag} Win Rate Data Per Season on ${map}`"></player-season-win-rate-panel>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box :title="'Heroes Played On ' + map">
        <div class="flex flex-wrap gap-2">
          <a :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" v-for="(item, index) in data.hero_data_all_heroes" :key="index">
            <hero-image-wrapper :hero="item.hero" size="big" :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" link-text="View Hero">
              <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
            </hero-image-wrapper>
          </a>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="esport && data.latestGames && data.latestGames.length" title="Recent Matches" link-text="Match History" :link-href="ngsPlayerUrl + '/Match/History' + ngsQuery" :divider="false">
        <div>
          <game-summary-header :esport="true"></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :esport="true" :esport-league="esport" :esport-series="esportfilters?.series" :tournament="esportfilters?.tournament" :data="item"></game-summary-box>
        </div>
      </panel-box>
      <panel-box v-else-if="!esport && data.latestGames" title="Recent Matches" link-text="Match History" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Match/History/?game_map=' + map" :divider="false">
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
      <loading-component :compact="true" :textoverride="true" :overrideimage="league?.loaderImage">{{ asyncLoading ? 'This is taking longer than expected. A background task is computing your results. Please be patient.' : 'Loading data...' }}</loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[780px]">
            <h2 class="text-3xl font-bold">{{ map }}</h2>
            <div class="flex gap-8 max-sm:flex-col">
              <skeleton-box class="w-40 md:w-60 aspect-[3/4] shrink-0 rounded"></skeleton-box>
              <div class="flex-1 flex flex-col gap-6">
                <skeleton-box class="h-[30px]"></skeleton-box>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                  <skeleton-box v-for="index in 10" :key="index" class="h-11"></skeleton-box>
                </div>
              </div>
            </div>
            <div class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">Heroes</h3>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <div v-for="group in 3" :key="group" class="flex gap-3">
                  <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                </div>
              </div>
            </div>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0">
            <panel-box v-if="esport" :inner="true" title="Teams Played For">
              <skeleton-box v-for="index in 2" :key="index" class="h-14"></skeleton-box>
            </panel-box>
            <panel-box v-else :inner="true" title="Party Size Win Rates">
              <skeleton-box v-for="index in 5" :key="index" class="h-10"></skeleton-box>
            </panel-box>
          </div>
        </div>
      </panel-box>

      <panel-box title="Win Rate Per Season">
        <skeleton-box class="h-72"></skeleton-box>
      </panel-box>

      <panel-box title="Recent Matches" :divider="false">
        <div>
          <game-summary-header :esport="!!esport"></game-summary-header>
          <game-summary-skeleton v-for="index in 7" :key="index"></game-summary-skeleton>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerHeroSingleStats',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    playerloadsetting: {
      type: [String, Boolean]
    },
    region: Number,
    regions: Object,
    map: String,
    regionsmap: Object,
    mapobject: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    gametypedefault: Array,
    replayViewerAllowed: Boolean,
    analyticsCutover: String,
    replayDownloadBlocked: Boolean,
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
    esport: String,
    esportfilters: Object,
  },
  data(){
    return {
      isLoading: false,
      asyncLoading: false,
      cancelTokenSource: null,
      inputmap: null,
      modifiedgametype: null,
      modifiedseason: null,
      seasonselection: [],
      startdate: null,
      enddate: null,
      data: null,
      disableFilterInput: null,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
    }
  },
  created(){
    this.inputmap = this.map;
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
    heroGroups() {
      // Esports has no latest-played lists; its profile shows the lowest win rates there
      return [
        { label: 'Most Played', data: this.data.hero_data_top_played.slice(0, 3) },
        { label: 'Highest Win Rate', data: this.data.hero_data_top_win_rate.slice(0, 3) },
        this.esport
          ? { label: 'Lowest Win Rate', data: this.data.hero_data_top_lowest_win_rate.slice(0, 3) }
          : { label: 'Latest Played', data: this.data.hero_data_top_latest_played.slice(0, 3) },
      ];
    },
    league() {
      return this.esport ? this.$esportLeague(this.esport, this.esportfilters) : null;
    },
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    ngsPlayerUrl() {
      return `${this.league.root}/Player/${this.battletag}/${this.blizzid}`;
    },
    ngsQuery() {
      const params = new URLSearchParams(this.league.params);
      if (this.ngsSeason) params.set('season', this.ngsSeason);
      if (this.ngsDivision) params.set('division', this.ngsDivision);
      if (this.ngsTeam) params.set('team', this.ngsTeam);
      const query = params.toString();
      return query ? `?${query}` : '';
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
    changeMap(map) {
      window.location.href = this.esport
        ? `${this.ngsPlayerUrl}/Map/${map}${this.ngsQuery}`
        : `/Player/${this.battletag}/${this.blizzid}/${this.region}/Map/${map}`;
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field) {
        this[field] = eventPayload.value;
      }
      history.pushState({}, '', window.location.pathname + this.ngsQuery);
    },
    async getData(){
      this.disableFilterInput = true;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      if (this.esport) {
        try{
          const response = await this.$axios.post(`${this.league.api}/map`, {
            ...this.league.params,
            battletag: this.battletag,
            blizz_id: this.blizzid,
            season: this.ngsSeason,
            division: this.ngsDivision,
            team: this.ngsTeam,
            game_map: this.map,
          }, { cancelToken: this.cancelTokenSource.token });
          this.ngsFilterOptions = response.data.filters;
          this.data = response.data;
        }catch(error){
          //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.disableFilterInput = false;
        }
        return;
      }

      try{
        const response = await this.$globalAsyncPost("/api/v1/player/maps/single", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          game_type: this.modifiedgametype,
          season: this.modifiedseason,
          start_date: this.startdate,
          end_date: this.enddate,
          type: "single",
          page: "map",
          game_map: this.map,
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
    getRegionName(regionID){
      return this.regions[regionID];
    },
    handleInputChange(eventPayload) {
      if(eventPayload.field == "Game Type"){
        this.modifiedgametype = eventPayload.value.length ? [...eventPayload.value] : null;
      }

      this.handleSeasonOrDateChange(eventPayload);
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
    formatMapName(map) {
      return `bg_${map.toLowerCase().replace(/\s+/g, '_')}.jpg`;
    },
  }
}
</script>