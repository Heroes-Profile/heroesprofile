<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :battletag="team" :esport="esport" :heading="heading" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="filterSeasons" class="max-w-[1920px] mx-auto px-4 md:px-8">
      <panel-box :filters="true">
        <div class="filter-row justify-center">
          <single-select-filter :values="filterSeasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="modifiedseason"></single-select-filter>
          <single-select-filter v-if="esport == 'NGS'" :values="filterDivisions" :text="'Divisions'" @input-changed="handleInputChange" :defaultValue="modifieddivision"></single-select-filter>
          <button :disabled="isLoading" @click="handleDropdownClosed" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div>
      </panel-box>
    </div>

    <!-- Laid out like the player profile -->
    <div v-if="data" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

          <!-- Team overview -->
          <panel-box :inner="true" class="!gap-8">
            <div class="flex flex-col">
              <h2 class="text-3xl font-bold">{{ teamName(team) }}</h2>
              <span v-if="subtitle" class="text-subtext">{{ subtitle }}</span>
            </div>
            <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
              <div class="shrink-0 w-40 md:w-60 aspect-square flex items-center justify-center">
                <img class="max-w-full max-h-full object-contain text-[0px]" :src="data.icon_url || isLoadingImageUrl" :alt="teamName(team)"/>
              </div>
              <div class="flex-1 min-w-0 w-full grid grid-cols-2 gap-x-6 gap-y-1 content-start">
                <stat-box v-for="stat in overviewStats" :key="stat.label" class="!m-0 !px-0 !min-w-0" :title="stat.label" :value="stat.value"></stat-box>
              </div>
            </div>
            <div v-for="section in topSections" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
              <h3 class="text-2xl font-bold">{{ section.title }} <span class="text-sm font-normal text-subtext">5+ games</span></h3>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :text="group.label" :data="group.data"></group-box>
              </div>
            </div>
            <p class="flex flex-wrap gap-2"><span class="text-subtext">Total Time Spent Dead:</span><span class="text-header">{{ data.time_spent_dead }}</span></p>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0">
            <!-- The roster, with each player's most played hero -->
            <panel-box v-if="playerList.length" :inner="true" title="Players">
              <div class="flex flex-col gap-2">
                <div v-for="(row, index) in playerList" :key="index" class="flex items-center gap-3 rounded bg-surface-2 px-3 py-2">
                  <a v-if="row.most_played_hero" class="shrink-0" :href="row.herolink">
                    <hero-image-wrapper :hero="row.most_played_hero">
                      <image-hover-box :title="row.most_played_hero.name" :paragraph-one="'Win Rate on Hero: ' + row.win_rate_on_hero + '%'"></image-hover-box>
                    </hero-image-wrapper>
                  </a>
                  <div class="flex flex-col min-w-0 flex-1">
                    <a class="group/bt font-semibold truncate" :href="row.playerlink"><span class="group-hover/bt:underline">{{ row.battletag }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                    <span v-if="row.most_played_role" class="text-sm text-subtext">{{ row.most_played_role }}</span>
                  </div>
                  <span class="shrink-0 text-sm text-subtext">{{ row.games_played }} games</span>
                </div>
              </div>
            </panel-box>

            <panel-box v-if="data.enemy_teams && data.enemy_teams.length" :inner="true" title="Teams Lost Against">
              <div class="flex flex-col gap-2">
                <a v-for="(enemy, index) in data.enemy_teams" :key="index" :href="enemy.enemy_link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                  <img v-if="enemy.icon_url" class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0px]" :src="enemy.icon_url" :alt="teamName(enemy.team_name || enemy.team)"/>
                  <div class="flex flex-col min-w-0 flex-1">
                    <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ teamName(enemy.team_name || enemy.team) }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></span>
                    <span class="text-sm text-subtext">{{ enemy.inputhover }}</span>
                  </div>
                </a>
              </div>
            </panel-box>

            <panel-box v-if="(data.heroes_won_against || []).length || (data.heroes_lost_against || []).length" :inner="true" title="Heroes Played Against">
              <div class="flex flex-col gap-4">
                <group-box v-if="data.heroes_won_against.length" class="!m-0" :useinputforhover="true" :text="'Most Won Against'" :data="data.heroes_won_against.slice(0, 5)"></group-box>
                <group-box v-if="data.heroes_lost_against.length" class="!m-0" :useinputforhover="true" :text="'Most Lost Against'" :data="data.heroes_lost_against.slice(0, 5)"></group-box>
              </div>
            </panel-box>
          </div>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="grid xl:grid-cols-2 gap-6 items-start">
        <panel-box title="Heroes Played">
          <div class="flex flex-wrap gap-2">
            <hero-image-wrapper v-for="(item, index) in data.heroes" :key="index" size="big" :hero="item.hero">
              <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
            </hero-image-wrapper>
          </div>
        </panel-box>
        <panel-box title="Maps Played">
          <div class="flex flex-wrap gap-2">
            <map-image-wrapper v-for="(item, index) in data.maps" :key="index" size="big" :map="item.game_map">
              <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
            </map-image-wrapper>
          </div>
        </panel-box>
      </div>

      <div class="grid xl:grid-cols-2 gap-6 items-start">
        <panel-box :title="'Heroes Banned by ' + teamName(team)">
          <div class="flex flex-wrap gap-2">
            <hero-image-wrapper v-for="(item, index) in data.team_ban_date" :key="index" size="big" :hero="item.hero">
              <image-hover-box :title="item.name" :paragraph-one="item.inputhover"></image-hover-box>
            </hero-image-wrapper>
          </div>
        </panel-box>
        <panel-box :title="'Heroes Banned Against ' + teamName(team)">
          <div class="flex flex-wrap gap-2">
            <hero-image-wrapper v-for="(item, index) in data.enemy_ban_date" :key="index" size="big" :hero="item.hero">
              <image-hover-box :title="item.name" :paragraph-one="item.inputhover"></image-hover-box>
            </hero-image-wrapper>
          </div>
        </panel-box>
      </div>

      <div class="grid xl:grid-cols-2 gap-6 items-start">
        <panel-box title="Heroes">
          <table class="min-w-0 w-full responsive-table max-md:text-xs">
            <thead>
              <tr>
                <th @click="sortTable('hero_id')" class="py-2 px-3 text-left text-sm cursor-pointer">Hero<sort-indicator :column="'hero_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
                <th @click="sortTable('win_rate')" class="py-2 px-3 text-left text-sm cursor-pointer">Win Rate %<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
                <th @click="sortTable('games_played')" class="py-2 px-3 text-left text-sm cursor-pointer">Games Played<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in sortedData" :key="index">
                <td class="py-2 px-3"><div class="flex items-center gap-2"><hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span></div></td>
                <td class="py-2 px-3">{{ row.win_rate }}</td>
                <td class="py-2 px-3">{{ row.games_played }}</td>
              </tr>
            </tbody>
          </table>
        </panel-box>
        <panel-box v-if="esport != 'Other'" :title="'Maps Banned by ' + teamName(team)">
          <div class="flex flex-wrap gap-2">
            <map-image-wrapper v-for="(item, index) in data.maps_banned" :key="index" size="big" :map="item.game_map">
              <image-hover-box :title="item.game_map.name" :paragraph-one="item.inputhover"></image-hover-box>
            </map-image-wrapper>
          </div>
        </panel-box>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <div class="flex justify-between items-baseline gap-6">
        <h2 class="text-2xl font-bold">Recent Matches</h2>
        <a class="text-header whitespace-nowrap hover:underline" :href="matchHistoryUrl">Match History<i class="fas fa-chevron-right text-sm ml-3"></i></a>
      </div>
    </div>
    <!-- The league pages' own recent match rows -->
    <div v-if="data" class="mt-4 pb-8">
      <esports-recent-matches v-if="recentMatches.length" :data="recentMatches" :esport="recentMatchesEsport" :series="series" :tournament="tournament" :always-expanded="true"></esports-recent-matches>
      <p v-else class="max-w-[1920px] mx-auto px-4 md:px-8 text-subtext">No recent games.</p>
    </div>

    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">
      <loading-component :compact="true" :overrideimage="isLoadingImageUrl"></loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[780px]">
            <div class="flex flex-col gap-2">
              <h2 class="text-3xl font-bold">{{ teamName(team) }}</h2>
              <skeleton-box class="h-4 w-40"></skeleton-box>
            </div>
            <div class="flex gap-8 max-sm:flex-col">
              <skeleton-box class="w-40 md:w-60 aspect-square shrink-0 rounded"></skeleton-box>
              <div class="flex-1 grid grid-cols-2 gap-x-6 gap-y-4 content-start">
                <skeleton-box v-for="index in 10" :key="index" class="h-11"></skeleton-box>
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
            <panel-box :inner="true" title="Players">
              <skeleton-box v-for="index in 6" :key="index" class="h-14"></skeleton-box>
            </panel-box>
            <panel-box :inner="true" title="Teams Lost Against">
              <skeleton-box v-for="index in 3" :key="index" class="h-14"></skeleton-box>
            </panel-box>
          </div>
        </div>
      </panel-box>
      <div v-for="pair in [['Heroes Played', 'Maps Played'], ['Heroes Banned', 'Heroes Banned Against']]" :key="pair[0]" class="grid xl:grid-cols-2 gap-6">
        <panel-box v-for="title in pair" :key="title" :title="title">
          <div class="flex flex-wrap gap-2">
            <skeleton-box v-for="tile in 16" :key="tile" class="w-16 h-16 !rounded-full"></skeleton-box>
          </div>
        </panel-box>
      </div>
      <h2 class="text-2xl font-bold">Recent Matches</h2>
      <div class="flex flex-col gap-2 pb-8">
        <skeleton-box v-for="row in 6" :key="row" class="h-16"></skeleton-box>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'EsportsSingleTeam',
  components: {
  },
  props: {
    esport: String,
    division: String, 
    team: String,
    season: {
      type: [Number, String]
    },
    tournament: String,
    image: String,
    series: String,
    seriesimage: String,
    patreonUser: Boolean,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedseason: null,
      modifieddivision: null,
      cancelTokenSource: null,
      filterSeasons: null,
      filterDivisions: null,
      recentMatches: [],
    }
  },
  created(){
    this.modifiedseason = this.season;
    this.modifieddivision = this.division;
   
  },
  mounted() {
    this.getData();
  },
  computed: {
    headingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/ngs_large.png";
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png";
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png";
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
    headingImageUrl(){
      if(this.esport == "NGS"){
        return "/Esports/NGS"
      }else if(this.esport == "CCL"){
        return "/Esports/CCL"
      }else if(this.esport == "MastersClash"){
        return "/Esports/MastersClash"
      }else if(this.esport == "HeroesInternational"){
        return "/Esports/HeroesInternational"
      }else if(this.esport == "Other"){
        return "/Esports/Other/" + this.series; 
      }
    },
    isLoadingImageUrl(){
     if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `${this.team} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `${this.team} during season ${this.modifiedseason}`;
      }else if(this.esport == "MastersClash"){
        return `${this.team} during season ${this.modifiedseason}`;
      }else if(this.esport == "Other"){
        return `${this.team} in series ${this.series}`;
      }
    },
    heading(){
      if(this.esport == 'HeroesInternational'){
        return "Heroes International";
      }else if(this.esport == "Other"){
        return;
      }

      // The NGS logo already says NGS
      if(this.esport == 'NGS'){
        return '';
      }
      return this.esport;
    },
    subtitle() {
      return [
        this.modifiedseason ? 'Season ' + this.modifiedseason : null,
        this.esport == 'NGS' && this.modifieddivision ? 'Division ' + this.modifieddivision : null,
        this.series,
      ].filter(Boolean).join(' · ');
    },
    // The API keys players by battletag
    playerList() {
      return Object.values(this.data.players || {}).sort((a, b) => b.games_played - a.games_played);
    },
    overviewStats() {
      const number = value => Number(value).toLocaleString('en-US');
      return [
        { label: 'Wins', value: number(this.data.wins) },
        { label: 'Losses', value: number(this.data.losses) },
        { label: 'Win Rate', value: Number(this.data.win_rate).toFixed(2) + '%' },
        { label: 'Games', value: number(this.data.total_games) },
        { label: 'KDR', value: this.data.kdr },
        { label: 'KDA', value: this.data.kda },
        { label: 'Takedowns', value: number(this.data.takedowns) },
        { label: 'Kills', value: number(this.data.kills) },
        { label: 'Assists', value: number(this.data.assists) },
        { label: 'Deaths', value: number(this.data.deaths) },
      ];
    },
    topSections() {
      return [
        { title: 'Heroes', groups: [
          { label: 'Most Played', data: this.data.hero_top_three_most_played },
          { label: 'Highest Win Rate', data: this.data.hero_top_three_highest_win_rate },
          { label: 'Lowest Win Rate', data: this.data.hero_top_three_lowest_win_rate },
        ] },
        { title: 'Maps', groups: [
          { label: 'Most Played', data: this.data.map_top_three_most_played },
          { label: 'Highest Win Rate', data: this.data.map_top_three_highest_win_rate },
          { label: 'Lowest Win Rate', data: this.data.map_top_three_lowest_win_rate },
        ] },
      ];
    },
    // The recent match rows' league key; HI splits main and Nations Cup
    recentMatchesEsport() {
      if (this.esport == 'HeroesInternational') {
        return this.tournament == 'nationscup' ? 'hi_nc' : 'hi';
      }
      return this.esport;
    },
    matchHistoryUrl() {
      const params = new URLSearchParams();
      if (this.modifiedseason) params.set('season', this.modifiedseason);
      if (this.esport === 'NGS' && this.modifieddivision) params.set('division', this.modifieddivision);
      if (this.esport === 'HeroesInternational' && this.tournament) params.set('tournament', this.tournament);
      const query = params.toString();
      return `/Esports/${this.esport}${this.esport === 'Other' ? `/${this.series}` : ''}/Team/${this.team}/Match/History${query ? '?' + query : ''}`;
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

      var url = "/api/v1/esports/single/team";
      
      if(this.series){
        url = "/api/v1/esports/other/single/team";
      }
      try{
        // Other series come from their own controller
        const teamGames = this.esport == 'Other' ? this.$axios.post("/api/v1/esports/other/team/match/history", {
          esport: this.esport,
          series: this.series,
          season: this.modifiedseason,
          team: this.team,
          pagination_page: 1,
        }, { cancelToken: this.cancelTokenSource.token }) : this.$axios.post("/api/v1/esports/single/team/matches", {
          esport: this.esport,
          tournament: this.tournament,
          season: this.modifiedseason,
          division: this.esport == 'NGS' ? this.modifieddivision : null,
          team: ['NGS', 'MastersClash'].includes(this.esport) ? this.team : null,
          team_name: ['NGS', 'MastersClash'].includes(this.esport) ? null : this.team,
          pagination_page: 1,
        }, { cancelToken: this.cancelTokenSource.token });
        const [response, matches] = await Promise.all([
          this.$axios.post(url, {
            esport: this.esport,
            series: this.series,
            division: this.modifieddivision,
            team: this.team,
            season: this.modifiedseason,
            tournament: this.tournament,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          }),
          teamGames,
        ]);
        this.recentMatches = ((matches && matches.data.data) || []).slice(0, 7);
        this.data = response.data;
        this.filterSeasons = response.data.seasons;
        this.filterDivisions = response.data.divisions;
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

      let newURL = `/Esports/${this.esport}/`;
      if(this.esport != "Other"){
        newURL += `Team/${this.team}`;
      }else{
        newURL += `${this.series}/Team/${this.team}`;
      }

      if (this.modifiedseason && this.modifieddivision) {
        newURL += `?season=${this.modifiedseason}&division=${this.modifieddivision}`;
      } else if (this.modifiedseason) {
        newURL += `?season=${this.modifiedseason}`;
      } else if (this.modifieddivision) {
        newURL += `?division=${this.modifieddivision}`;
      }

      // Update the browser's URL without reisLoading the page
      history.pushState({}, "", newURL);
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
    teamName(name) {
      return this.esport == 'NGS' ? this.$ngsTeam(name).name : name;
    },
  }
}
</script>