<template>
  <div>
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'Role data for ' + battletag + ' on ' + role" -->
        <page-heading v-if="esport" :heading="$t('Role Stats') + ': ' + $t(role)" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
        <page-heading v-else :heading="$t('Role Stats') + ': ' + $t(role)" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
    <div class="w-full">
      <filters v-if="esport && ngsFilterOptions"
        :filters="filters"
        :isLoading="isLoading"
        :onFilter="() => applyFilter()"
        :onChange="ngsFilterChange"
        :extrafilters="[
          { text: 'Seasons', values: ngsFilterOptions.seasons, defaultValue: ngsSeason },
          { text: 'Divisions', values: ngsFilterOptions.divisions, defaultValue: ngsDivision, show: !!(ngsFilterOptions.divisions) },
          { text: 'Teams', values: ngsTeamOptions, defaultValue: ngsTeam },
        ]"
        :excludetimeframes="true"
        :excludegametype="true"
        :hideadvancedfilteringbutton="true"
      >
      </filters>
      <filters v-else-if="!esport"
        :onFilter="filterData"
        :filters="filters"
        :isLoading="isLoading"
        :gametypedefault="gametypedefault"
        :defaultseasons="seasonselection"
        :includegametypefull="true"
        :includemultiseason="true"
        :includegamedaterange="true"
        :includesinglegamemap="true"
        :hideadvancedfilteringbutton="true"
      >
      </filters>
    </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    
    <div v-if="data && esport && !data.games_played" class="flex items-center justify-center mt-8">
      {{ $t('No :role games in :league for these filters.', { role: role, league: league.name }) }}
    </div>
    <div v-else-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">

      <panel-box>
      <div class="flex flex-col xl:flex-row gap-6">

        <!-- Role overview, laid out like the profile's player card -->
        <panel-box :inner="true" class="!gap-8 xl:shrink-0">
          <h2 class="text-3xl font-bold">
            <hero-heading-select type="role" :heroes="filters.role" :value="role" textclass="" @hero-changed="changeRole($event)"></hero-heading-select>
          </h2>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="flex flex-col items-center justify-between gap-1 shrink-0 max-sm:self-center [&_img]:!object-contain">
              <round-image class="my-auto" :image="`/images/roles/${role.toLowerCase()}.PNG`" :excludehover="true" size="large"></round-image>
              <div class="text-xl font-semibold leading-[1.2] text-subtext">{{ $t('Games:') }} <span class="text-header">{{ data.games_played.toLocaleString('en-US') }}</span></div>
            </div>

            <overview-stats class="flex-1 min-w-0 w-full" :win-rate="data.win_rate" :stats="overviewStats"></overview-stats>
          </div>

          <!-- Each group followed by every hero (or map) played in the role, as tiles like the hero page's "Maps played on";
               the lists have zero width so they wrap to the card instead of widening it -->
          <template v-for="section in topSections" :key="section.title">
            <div class="flex flex-col gap-4 w-fit max-w-full">
              <div class="flex justify-between items-baseline gap-6">
                <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
                <a class="text-header whitespace-nowrap hover:underline" :href="esport ? ngsPlayerUrl + section.allPath + ngsQuery : profileUrl + section.allPath + '?role=' + encodeURIComponent(role)">{{ $t(section.allText) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a>
              </div>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
              </div>
            </div>
            <div v-if="section.title === 'Heroes'" class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ $t('Heroes Played As :role', { role: $t(role) }) }}</h3>
              <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                <a :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" v-for="(item, index) in data.hero_data_all_heroes" :key="index">
                  <hero-image-wrapper :hero="item.hero" size="tile" :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" link-text="View Hero">
                    <image-hover-box :title="$heroName(item.hero)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
                  </hero-image-wrapper>
                </a>
              </div>
            </div>

            <div v-else class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ $t('Maps Played As :role', { role: $t(role) }) }}</h3>
              <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                <a :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" v-for="(item, index) in data.map_data" :key="index">
                  <map-image-wrapper :map="item.game_map" size="tile" :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" link-text="View Map">
                    <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
                  </map-image-wrapper>
                </a>
              </div>
            </div>
          </template>

        </panel-box>

        <div class="flex-1 flex flex-col gap-6 min-w-0">
          <!-- Esports: the teams they played the role for -->
          <panel-box v-if="esport && data.teams_played_for && data.teams_played_for.length" :inner="true" :title="$t('Teams Played For')">
            <div class="flex flex-col gap-2">
              <a v-for="team in data.teams_played_for" :key="team.team_name + team.season" :href="team.link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                <img v-if="team.image" class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image" :alt="$ngsTeam(team.team_name).name"/>
                <div class="flex flex-col min-w-0 flex-1">
                  <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
                  <span class="text-sm text-subtext">{{ [team.season ? $t('Season :season', { season: team.season }) : null, team.division ? $t('Division :division', { division: team.division }) : null].filter(Boolean).join(' · ') }}</span>
                </div>
                <div class="flex flex-col items-end shrink-0 text-sm">
                  <span class="font-semibold">{{ team.win_rate.toFixed(2) }}%</span>
                  <span class="text-subtext">{{ $t(':played games', { played: team.games_played }) }}</span>
                </div>
              </a>
            </div>
          </panel-box>

          <player-party-size-panel v-if="!esport" :data="data" prefix="stack_size_"></player-party-size-panel>

          <player-mmr-panel v-if="!esport" :data="data" :link-href="profileUrl + '/MMR'" :link-query="{ role: role }"></player-mmr-panel>

          <player-awards-panel v-if="!esport" class="flex-1" :link-query="{ role: role }" :battletag="battletag" :blizzid="blizzid" :region="region" :params="{ game_type: modifiedgametype, role: role, season: modifiedseason, game_map: modifiedgamemap, start_date: startdate, end_date: enddate }"></player-awards-panel>
        </div>
      </div>
      </panel-box>

      <player-season-win-rate-panel :data="data" :title="`${battletag} Win Rate Data Per Season with ${role}`"></player-season-win-rate-panel>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="esport && data.latestGames && data.latestGames.length" :title="$t('Recent Matches')" link-text="Match History" :link-href="ngsPlayerUrl + '/Match/History' + league.query" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :esport="true" :esport-league="esport" :esport-series="esportfilters?.series" :tournament="esportfilters?.tournament" :data="item"></game-summary-box>
        </div>
      </panel-box>
      <panel-box v-else-if="!esport && data.latestGames" :title="$t('Recent Matches')" link-text="Match History" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Match/History?role=' + encodeURIComponent(role)" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :data="item" :replay-viewer-allowed="replayViewerAllowed" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
        </div>
      </panel-box>
    </div>
    <div v-else-if="typeof data === 'undefined'"  class="flex items-center justify-center">
      {{ $t('No data') }}
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading || asyncLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">
      <!-- The cards start where the loaded ones do; the message sits on a tab of the panel above them -->
      <panel-box class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true" :overrideimage="league?.loaderImage">{{ asyncLoading ? $t('This is taking longer than expected. A background task is computing your results. Please be patient.') : $t('Loading data...') }}</loading-component>
        <div class="flex flex-col xl:flex-row gap-6">
          <!-- Built from the loaded card's own parts (the same headings, links, icon and boxes, empty), so nothing moves when the data arrives -->
          <panel-box :inner="true" class="!gap-8 xl:shrink-0">
            <h2 class="text-3xl font-bold">
              <hero-heading-select type="role" :heroes="filters.role" :value="role" textclass="" @hero-changed="changeRole($event)"></hero-heading-select>
            </h2>
            <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
              <div class="flex flex-col items-center justify-between gap-1 shrink-0 max-sm:self-center [&_img]:!object-contain">
                <!-- The icon's box, the same size as the hero pages' picture -->
                <skeleton-box class="my-auto w-40 md:w-[14rem] h-[20.125rem] rounded"></skeleton-box>
                <skeleton-box class="h-[1.5rem] w-32"></skeleton-box>
              </div>
              <overview-stats class="flex-1 min-w-0 w-full" :loading="true" :count="8"></overview-stats>
            </div>
            <template v-for="section in [{ title: 'Heroes', allText: 'All Heroes', played: 'Heroes Played As :role', tiles: 18 }, { title: 'Maps', allText: 'All Maps', played: 'Maps Played As :role', tiles: 19 }]" :key="section.title">
              <div class="flex flex-col gap-4 w-fit max-w-full">
                <div class="flex justify-between items-baseline gap-6">
                  <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
                  <span class="skeleton-text whitespace-nowrap">{{ $t(section.allText) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
                </div>
                <div class="flex flex-wrap gap-6 max-md:justify-center">
                  <div v-for="label in ['Most Played', 'Highest Win Rate', esport ? 'Lowest Win Rate' : 'Latest Played']" :key="label" class="w-auto inline-block">
                    <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                    <div class="flex flex-wrap gap-3 max-md:gap-2">
                      <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex flex-col gap-4">
                <h3 class="text-2xl font-bold">{{ $t(section.played, { role: $t(role) }) }}</h3>
                <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                  <skeleton-box v-for="tile in section.tiles" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                </div>
              </div>
            </template>
          </panel-box>

          <div class="flex-1 flex flex-col gap-6 min-w-0">
            <panel-box v-if="esport" :inner="true" :title="$t('Teams Played For')">
              <skeleton-box v-for="index in 2" :key="index" class="h-14"></skeleton-box>
            </panel-box>
            <player-party-size-panel v-if="!esport" :data="emptyPartyData" prefix="stack_size_" :skeleton="true"></player-party-size-panel>
            <player-mmr-panel v-if="!esport" :data="{}" :skeleton="true"></player-mmr-panel>
            <player-awards-panel v-if="!esport" class="flex-1" :placeholder="true" :battletag="battletag" :blizzid="blizzid" :region="region"></player-awards-panel>
          </div>
        </div>
      </panel-box>

      <panel-box :title="$t('Win Rate Per Season')">
        <!-- The season chart's shape: as wide as the panel, 3.1 times wider than tall -->
        <skeleton-box class="w-full aspect-[3.1]"></skeleton-box>
      </panel-box>

      <panel-box :title="$t('Recent Matches')" link-text="Match History" :skeleton="true" :divider="false">
        <div>
          <game-summary-header :skeleton="true"></game-summary-header>
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
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
    esport: String,
    esportfilters: Object,
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
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
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
        ];
      },
      // The party card with no games, for the skeleton: its labels and empty bars
      emptyPartyData() {
        const data = {};
        ['one', 'two', 'three', 'four', 'five'].forEach(key => { data[`stack_size_${key}_wins`] = 0; data[`stack_size_${key}_losses`] = 0; data[`stack_size_${key}_win_rate`] = 0; });
        return data;
      },
      topSections() {
        // Esports has no latest-played lists; its profile shows the lowest win rates there
        const third = this.esport ? ['Lowest Win Rate', 'lowest_win_rate'] : ['Latest Played', 'latest_played'];
        return [
          { title: 'Heroes', allText: 'All Heroes', allPath: '/Hero', groups: [
            { label: 'Most Played', data: this.data.hero_data_top_played.slice(0, 3) },
            { label: 'Highest Win Rate', data: this.data.hero_data_top_win_rate.slice(0, 3) },
            { label: third[0], data: this.data['hero_data_top_' + third[1]].slice(0, 3) },
          ] },
          { title: 'Maps', allText: 'All Maps', allPath: '/Map', groups: [
            { label: 'Most Played', data: this.data.map_data_top_played.slice(0, 3) },
            { label: 'Highest Win Rate', data: this.data.map_data_top_win_rate.slice(0, 3) },
            { label: third[0], data: this.data['map_data_top_' + third[1]].slice(0, 3) },
          ] },
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
      changeRole(role) {
        window.location.href = this.esport
          ? `${this.ngsPlayerUrl}/Role/${role}${window.location.search}`
          : `/Player/${this.battletag}/${this.blizzid}/${this.region}/Role/${role}`;
      },
      ngsParams(extra = {}) {
        return { ...this.league.params, battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam, ...extra };
      },
      ngsFilterChange(eventPayload) {
        const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
        if (field) {
          this[field] = eventPayload.value;
        }
        const params = new URLSearchParams(this.league.params);
        if (this.ngsSeason) params.set('season', this.ngsSeason);
        if (this.ngsDivision) params.set('division', this.ngsDivision);
        if (this.ngsTeam) params.set('team', this.ngsTeam);
        const query = params.toString();
        history.pushState({}, '', window.location.pathname + (query ? `?${query}` : ''));
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
            const response = await this.$axios.post(`${this.league.api}/role`, this.ngsParams({ role: this.role }), { cancelToken: this.cancelTokenSource.token });
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
      filterData(filteredData){
        this.modifiedgametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.modifiedseason = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
        this.startdate = filteredData.single["From Date"] || null;
        this.enddate = filteredData.single["To Date"] || null;
        this.modifiedgamemap = filteredData.single.Map || null;
        this.applyFilter();
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