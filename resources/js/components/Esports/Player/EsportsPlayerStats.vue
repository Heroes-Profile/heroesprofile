<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="heading" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>

    <filters v-if="data"
      :filters="filters"
      :isLoading="isLoading"
      :onFilter="() => handleDropdownClosed()"
      :onChange="handleInputChange"
      :extrafilters="[
        { text: 'Seasons', values: data.seasons, defaultValue: modifiedseason },
        { text: 'Divisions', values: data.divisions, defaultValue: modifieddivision, show: !!(data.divisions) },
        { text: 'Teams', values: teamOptions, defaultValue: modifiedteam, show: !!(teamOptions.length) },
      ]"
      :excludetimeframes="true"
      :excludegametype="true"
      :hideadvancedfilteringbutton="true"
    >
    </filters>
    <div v-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">

      <!-- Laid out like the profile page, from this league's games only -->
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

          <!-- Player overview -->
          <panel-box :inner="true" class="!gap-8">
            <div class="flex flex-col">
              <h2 class="text-3xl font-bold">{{ battletag }}</h2>
              <span class="text-subtext">{{ leagueLabel }}</span>
            </div>
            <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
              <div class="shrink-0">
                <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" :excludehover="true" size="large"></hero-image-wrapper>
              </div>
              <div class="flex-1 min-w-0 w-full flex flex-col gap-6">
                <div class="flex flex-col gap-1">
                  <span class="text-header">{{ $t('Win Rate') }}</span>
                  <stat-bar size="big" :value="data.win_rate.toFixed(2)" suffix="%" :color="$winRateColor(data.win_rate)"></stat-bar>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                  <stat-box v-for="stat in overviewStats" :key="stat.label" class="!m-0 !px-0 !min-w-0" :title="stat.label" :value="stat.value"></stat-box>
                </div>
              </div>
            </div>
            <div v-for="section in topSections" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
              <div class="flex justify-between items-baseline gap-6">
                <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
                <a class="text-header whitespace-nowrap hover:underline" :href="ngsPageUrl(section.allPath)">{{ section.allText }}<i class="fas fa-chevron-right text-sm ml-3"></i></a>
              </div>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
              </div>
            </div>
            <div class="flex flex-col gap-1">
              <p v-if="data.total_time_played" class="flex flex-wrap gap-2"><span class="text-subtext">{{ $t('Total Time Played:') }}</span><span class="text-header">{{ data.total_time_played }}</span></p>
              <p class="flex flex-wrap gap-2"><span class="text-subtext">{{ $t('Total Time Spent Dead:') }}</span><span class="text-header">{{ data.time_spent_dead }}</span></p>
            </div>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0">
            <!-- The teams they played for -->
            <panel-box v-if="data.teams_played_for && data.teams_played_for.length" :inner="true" title="Teams Played For">
              <div class="flex flex-col gap-2">
                <a v-for="team in data.teams_played_for" :key="team.team_name + team.season" :href="team.link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                  <img v-if="team.image" class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image" :alt="$ngsTeam(team.team_name).name"/>
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

            <div class="grid md:grid-cols-2 gap-6">
              <!-- Win rate by role -->
              <panel-box v-if="data.role_win_rates && data.role_win_rates.length" :inner="true" title="Win Rate By Role"  link-text="All Roles" :link-href="ngsPageUrl('/Role')">
                <div class="flex flex-col gap-3">
                  <div v-for="role in data.role_win_rates" :key="role.role" class="flex flex-col gap-1.5">
                    <span class="text-header"><a class="hover:underline" :href="ngsPageUrl('/Role/' + role.role)">{{ role.role }}<i class="fas fa-chevron-right text-sm ml-3"></i></a> <span class="text-sm text-subtext">· {{ role.games_played }} games</span></span>
                    <stat-bar :value="role.win_rate.toFixed(2)" suffix="%" :color="$winRateColor(role.win_rate)"></stat-bar>
                  </div>
                </div>
              </panel-box>

              <!-- Heroes on the other side in their wins and losses -->
              <panel-box v-if="data.heroes_won_against && (data.heroes_won_against.length || data.heroes_lost_against.length)" :inner="true" title="Heroes Played Against">
                <div class="flex flex-col gap-4">
                  <group-box v-if="data.heroes_won_against.length" class="!m-0" :useinputforhover="true" :text="'Most Won Against'" :data="data.heroes_won_against.slice(0, 5)"></group-box>
                  <group-box v-if="data.heroes_lost_against.length" class="!m-0" :useinputforhover="true" :text="'Most Lost Against'" :data="data.heroes_lost_against.slice(0, 5)"></group-box>
                </div>
              </panel-box>
            </div>

            <player-mmr-panel v-if="data.ccl_mmr_data" :data="data" :modes="[{ label: 'CCL', code: 'ccl', data: data.ccl_mmr_data }]" :tiers="false"></player-mmr-panel>
          </div>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="grid xl:grid-cols-2 gap-6">
        <panel-box v-if="data.weekday_data" title="Week Day Win Rates">
          <player-weekday-win-rates :data="data.weekday_data"></player-weekday-win-rates>
        </panel-box>

        <panel-box title="Heroes" :class="{ 'xl:col-span-2': !data.weekday_data }">
          <table class="min-w-0 w-full responsive-table">
            <thead>
              <tr>
                <th @click="sortTable('hero_id')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'hero_id' }">
                  {{ $t('Hero') }}<sort-indicator :column="'hero_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('win_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate' }">
                  {{ $t('Win Rate %') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('games_played')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played' }">
                  {{ $t('Games Played') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr v-for="(row, index) in sortedData" :key="index">
                <td>
                  <a class="group/bt flex items-center justify-center" :href="row.link">
                    <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="hidden md:block font-bold ml-2 group-hover/bt:underline">{{ $heroName(row.hero) }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity hidden md:inline"></i>
                  </a>
                </td>
                <td>{{ row.win_rate }}</td>
                <td>{{ row.games_played }}</td>
              </tr>
            </tbody>
          </table>
        </panel-box>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="data.matches && data.matches.length" title="Recent Matches" link-text="Match History" :link-href="getMatchHistoryUrl()" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box
            v-for="(item, index) in data.matches.slice(0, 7)"
            :key="index"
            :esport="true"
            :esport-league="esport"
            :esport-series="series"
            :tournament="tournament"
            :data="item"
          ></game-summary-box>
        </div>
      </panel-box>
    </div>

    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">
      <loading-component :compact="true" :overrideimage="league.loaderImage"></loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[48.75rem]">
            <skeleton-box class="h-8 w-56"></skeleton-box>
            <div class="flex gap-8 max-sm:flex-col">
              <skeleton-box class="w-40 md:w-72 h-[23em] shrink-0 rounded"></skeleton-box>
              <div class="flex-1 flex flex-col gap-6">
                <skeleton-box class="h-[1.875rem]"></skeleton-box>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                  <skeleton-box v-for="index in 8" :key="index" class="h-11"></skeleton-box>
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
            <panel-box :inner="true" title="Teams Played For">
              <skeleton-box v-for="index in 3" :key="index" class="h-14"></skeleton-box>
            </panel-box>
            <div class="grid md:grid-cols-2 gap-6">
              <panel-box v-for="title in ['Win Rate By Role', 'Heroes Played Against']" :key="title" :inner="true" :title="title">
                <skeleton-box v-for="index in 5" :key="index" class="h-10"></skeleton-box>
              </panel-box>
            </div>
          </div>
        </div>
      </panel-box>
      <div class="grid xl:grid-cols-2 gap-6">
        <panel-box title="Week Day Win Rates">
          <skeleton-box class="h-[22.5rem]"></skeleton-box>
        </panel-box>
        <panel-box title="Heroes">
          <table-skeleton :bare="true" :rows="8" :columns="['avatar', 'stat', 'stat']"></table-skeleton>
        </panel-box>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsPlayerStats',
  components: {
  },
  props: {
  	filters: Object,
  	esport: String,
    series: String,
    division: String, 
    // A team name; NGS and Masters Clash filter by it
    team: String,
    battletag: String,
    blizz_id: {
      type: [Number, String]
    },
    season: {
      type: [Number, String]
    },
    tournament: String,
    seriesimage: String,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedseason: null,
      modifieddivision: null,
      modifiedteam: null,
      // The player's teams, kept once loaded so picking one doesn't shrink the list to it
      teamOptions: [],
      cancelTokenSource: null,
      inputHero: {
          name: "Auto Select",
          short_name: "autoselect3",
          icon: "autoselect3.jpg",
      },
    }
  },
  created(){
    this.modifiedseason = this.season;
    this.modifieddivision = this.division;
    this.modifiedteam = this.team;
  },
  mounted() {
    this.getData();
  },
  computed: {
    // Images, URL root and HI's tournament query for this league
    league() {
      return this.$esportLeague(this.esport, { tournament: this.tournament, series: this.series, seriesimage: this.seriesimage });
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `${this.battletag} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "Masters Clash"){
        return `${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "Other"){
        return `${this.battletag} in series ${this.series}`;
      }
    },
    heading(){
      if(this.esport == "Other"){
        return;
      }
      // The NGS logo already says NGS
      if(this.esport == 'NGS'){
        return '';
      }
      return this.league.name;
    },
    leagueLabel() {
      return this.esport == 'NGS' ? 'Nexus Gaming Series' : this.league.name;
    },
    overviewStats() {
      return [
        { label: 'Wins', value: this.data.wins.toLocaleString('en-US') },
        { label: 'Losses', value: this.data.losses.toLocaleString('en-US') },
        { label: 'KDR', value: this.data.kdr },
        { label: 'KDA', value: this.data.kda },
        { label: 'Takedowns', value: this.data.takedowns.toLocaleString('en-US') },
        { label: 'Kills', value: this.data.kills.toLocaleString('en-US') },
        { label: 'Assists', value: Number(this.data.assists).toLocaleString('en-US') },
        { label: 'Deaths', value: Number(this.data.deaths).toLocaleString('en-US') },
      ];
    },
    topSections() {
      return [
        { title: 'Heroes', allText: 'All Heroes', allPath: '/Hero', groups: [
          { label: 'Most Played', data: this.data.hero_top_three_most_played },
          { label: 'Highest Win Rate', data: this.data.hero_top_three_highest_win_rate },
          { label: 'Lowest Win Rate', data: this.data.hero_top_three_lowest_win_rate },
        ] },
        { title: 'Maps', allText: 'All Maps', allPath: '/Map', groups: [
          { label: 'Most Played', data: this.data.map_top_three_most_played },
          { label: 'Highest Win Rate', data: this.data.map_top_three_highest_win_rate },
          { label: 'Lowest Win Rate', data: this.data.map_top_three_lowest_win_rate },
        ] },
      ];
    },
    sortedData() {
      if (!this.sortKey) return this.data.heroes;
      return this.data.heroes.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
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
        const response = await this.$axios.post("/api/v1/esports/single/player", {
          esport: this.esport,
          series: this.series,
          division: this.modifieddivision,
          battletag: this.battletag,
          blizz_id: this.blizz_id,
          season: this.modifiedseason,
          team: this.modifiedteam,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data;
        if (!this.modifiedteam || !this.teamOptions.length) {
          const names = [...new Set((this.data.teams_played_for || []).map(team => team.team_name))];
          this.teamOptions = [{ code: null, name: 'All' }, ...names.map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
        }
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
    handleInputChange(eventPayload){
        if(eventPayload.field == "Seasons"){
            this.modifiedseason = eventPayload.value;
        }

        if(eventPayload.field == "Divisions"){
          this.modifieddivision = eventPayload.value;
        }

        if(eventPayload.field == "Teams"){
          this.modifiedteam = eventPayload.value;
        }

      // Update the browser's URL without reloading the page
      history.pushState({}, "", this.ngsPageUrl(''));
    },
    handleDropdownClosed(){
      this.data = null;
      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
    // One of the player's other pages in this league, keeping the filters picked here
    ngsPageUrl(path){
      const params = new URLSearchParams(this.league.params);
      if (this.modifiedseason) params.set('season', this.modifiedseason);
      if (this.modifieddivision) params.set('division', this.modifieddivision);
      if (this.modifiedteam) params.set('team', this.modifiedteam);
      const query = params.toString();
      return `${this.league.root}/Player/${this.battletag}/${this.blizz_id}${path}` + (query ? `?${query}` : '');
    },
    getMatchHistoryUrl(){
      return `${this.league.root}/Player/${this.battletag}/${this.blizz_id}/Match/History${this.league.query}`;
    },
  }
}
</script>