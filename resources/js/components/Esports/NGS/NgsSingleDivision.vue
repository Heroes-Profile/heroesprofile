<template>
  <div>
    <page-heading :heading="'Division ' + division" esport="NGS" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>

    <filters v-if="seasons"
      :filters="filters"
      :isLoading="isLoading"
      :onFilter="() => handleDropdownClosed()"
      :onChange="handleInputChange"
      :extrafilters="[
        { text: 'Seasons', values: seasons, defaultValue: modifiedseason },
      ]"
      :excludetimeframes="true"
      :excludegametype="true"
      :hideadvancedfilteringbutton="true"
    >
    </filters>

    <!-- Laid out like the player profile -->
    <div v-if="data" class="mt-8 space-y-8">
      <div class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">
        <panel-box>
          <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

            <!-- Division overview -->
            <panel-box :inner="true" class="!gap-8">
              <div class="flex flex-col">
                <h2 class="text-3xl font-bold">Division {{ division }}</h2>
                <span class="text-subtext">Season {{ modifiedseason }}</span>
              </div>
              <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
                <div class="shrink-0 w-40 md:w-60 aspect-square flex items-center justify-center">
                  <img class="max-w-full max-h-full object-contain text-[0rem]" :src="divisionImage" :alt="'Division ' + division" @error="divisionImageFailed = true"/>
                </div>
                <div class="flex-1 min-w-0 w-full grid grid-cols-2 gap-x-6 gap-y-1 content-start">
                  <stat-box v-for="stat in overviewStats" :key="stat.label" class="!m-0 !px-0 !min-w-0" :title="stat.label" :value="stat.value"></stat-box>
                </div>
              </div>
              <div v-for="section in topSections" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
                <h3 class="text-2xl font-bold">{{ $t(section.title) }} <span class="text-sm font-normal text-subtext">5+ games</span></h3>
                <div class="flex flex-wrap gap-6 max-md:justify-center">
                  <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :text="group.label" :data="group.data"></group-box>
                </div>
              </div>
              <p class="flex flex-wrap gap-2"><span class="text-subtext">{{ $t('Total Time Spent Dead:') }}</span><span class="text-header">{{ data.time_spent_dead }}</span></p>
            </panel-box>

            <!-- The division's teams, best record first -->
            <div class="flex flex-col gap-6 min-w-0">
              <panel-box :inner="true" :title="'Teams in Division ' + division">
                <div class="flex flex-col gap-2">
                  <a v-for="team in sortedTeams" :key="team.team_name" :href="teamUrl(team)" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                    <img v-if="team.image" class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image" :alt="$ngsTeam(team.team_name).name"/>
                    <div class="flex flex-col min-w-0 flex-1">
                      <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></span>
                      <span v-if="$ngsTeam(team.team_name).withdrawnSeason" class="text-sm text-subtext">Withdrew Season {{ $ngsTeam(team.team_name).withdrawnSeason }}</span>
                    </div>
                    <div class="flex flex-col items-end shrink-0 text-sm">
                      <span class="font-semibold">{{ team.win_rate.toFixed(2) }}%</span>
                      <span class="text-subtext">{{ team.wins + team.losses }} games</span>
                    </div>
                  </a>
                </div>
              </panel-box>
            </div>
          </div>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

        <div class="grid xl:grid-cols-2 gap-6 items-start">
          <panel-box title="Heroes Played">
            <div class="flex flex-wrap gap-2">
              <hero-image-wrapper v-for="item in data.heroes" :key="item.hero_id" size="big" :hero="item.hero">
                <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
              </hero-image-wrapper>
            </div>
          </panel-box>
          <panel-box title="Maps Played">
            <div class="flex flex-wrap gap-2">
              <map-image-wrapper v-for="item in data.maps" :key="item.map_id" size="big" :map="item.map">
                <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
              </map-image-wrapper>
            </div>
          </panel-box>
        </div>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

        <div class="flex justify-between items-baseline gap-6">
          <h2 class="text-2xl font-bold">{{ $t('Recent Matches') }}</h2>
          <a class="text-header whitespace-nowrap hover:underline" :href="`/Esports/NGS/Division/${division}/Match/History?season=${modifiedseason}`">{{ $t('Match History') }}<i class="fas fa-chevron-right text-sm ml-3"></i></a>
        </div>
      </div>

      <!-- The NGS page's own recent match rows -->
      <esports-recent-matches v-if="recentMatches.length" :data="recentMatches" :esport="'NGS'" :always-expanded="true"></esports-recent-matches>
      <div v-else class="max-w-[120rem] mx-auto px-4 md:px-8">
        <p class="text-subtext">{{ $t('No games in this division yet.') }}</p>
      </div>
    </div>

    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 space-y-8">
      <loading-component :compact="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[48.75rem]">
            <div class="flex flex-col gap-2">
              <h2 class="text-3xl font-bold">Division {{ division }}</h2>
              <skeleton-box class="h-4 w-24"></skeleton-box>
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
            <panel-box :inner="true" :title="'Teams in Division ' + division">
              <skeleton-box v-for="index in 8" :key="index" class="h-14"></skeleton-box>
            </panel-box>
          </div>
        </div>
      </panel-box>
      <div class="grid xl:grid-cols-2 gap-6">
        <panel-box v-for="title in ['Heroes Played', 'Maps Played']" :key="title" :title="title">
          <div class="flex flex-wrap gap-2">
            <skeleton-box v-for="tile in 24" :key="tile" class="w-16 h-16 !rounded-full"></skeleton-box>
          </div>
        </panel-box>
      </div>
      <h2 class="text-2xl font-bold">{{ $t('Recent Matches') }}</h2>
      <!-- The NGS page's recent match rows, in grey -->
        <panel-box>
          <div v-for="row in 7" :key="row" class="flex flex-wrap 2xl:flex-nowrap items-center gap-6 rounded bg-surface-1 px-6 py-4">
            <div class="flex flex-col gap-2 w-[18.75rem] shrink-0">
              <skeleton-box class="h-4 w-40"></skeleton-box>
              <skeleton-box class="h-4 w-32"></skeleton-box>
              <skeleton-box class="h-3 w-36"></skeleton-box>
              <skeleton-box class="h-3 w-28"></skeleton-box>
            </div>
            <div class="flex-1 grid grid-cols-[1fr_auto_1fr] items-center gap-6">
              <div class="flex flex-row-reverse items-center gap-3">
                <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
                <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
              </div>
              <div class="flex flex-col items-center gap-2">
                <skeleton-box class="h-8 w-20"></skeleton-box>
                <skeleton-box class="h-3 w-28"></skeleton-box>
              </div>
              <div class="flex items-center gap-3">
                <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
                <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
              </div>
            </div>
            <div class="flex gap-2 w-[21.25rem] max-w-full shrink-0">
              <div v-for="team in 2" :key="team" class="flex flex-col gap-1.5 w-[9.375rem]">
                <div v-for="player in 5" :key="player" class="flex items-center gap-1">
                  <skeleton-box class="w-4 h-4 shrink-0 !rounded-full"></skeleton-box>
                  <skeleton-box class="h-3 w-24"></skeleton-box>
                </div>
              </div>
            </div>
          </div>
        </panel-box>
    </div>
  </div>
</template>

<script>
  import moment from 'moment-timezone';

export default {
  name: 'NgsSingleDivision',
  components: {
  },
  props: {
    division: String,
    defaultseason: Number,
    season: [String, Number],
    filters: Object,
    patreonUser: Boolean,
  },
  data(){
    return {
      userTimezone: moment.tz.guess(),
      preloadedImage: new Image(),
      isLoading: false,
      data: null,
      infoText1: "Heroes of the Storm statistics and comparison for the Nexus Gaming Series",
      modifiedseason: null,
      cancelTokenSource: null,
      // Kept apart from data so the filter stays while a season loads
      seasons: null,
      recentMatches: [],
      divisionImageFailed: false,
    }
  },
  created(){
    this.preloadedImage.src = '/images/NGS/no-image-clipped.png';
    this.modifiedseason = this.season ?? this.defaultseason;
  },
  mounted() {
    this.getData();
  },
  computed: {
    // Storm and Nexus files are named "Storm Division.png"; the rest "Division A East.png". Heroic has none yet.
    divisionImage() {
      if (this.divisionImageFailed) {
        return '/images/NGS/no-image-clipped.png';
      }
      const name = ['Storm', 'Nexus'].includes(this.division) ? `${this.division} Division` : `Division ${this.division}`;
      return `/images/NGS/Divisions/${name}.png`;
    },
    overviewStats() {
      const minutes = Math.floor(this.data.avg_game_length / 60);
      const seconds = String(this.data.avg_game_length % 60).padStart(2, '0');
      return [
        { label: 'Games', value: this.data.total_games.toLocaleString('en-US') },
        { label: 'Avg. Game Length', value: `${minutes}:${seconds}` },
        { label: 'Kills', value: this.data.kills.toLocaleString('en-US') },
        { label: 'Assists', value: Number(this.data.assists).toLocaleString('en-US') },
        { label: 'Takedowns', value: this.data.takedowns.toLocaleString('en-US') },
        { label: 'Vengeances', value: this.data.vengeances.toLocaleString('en-US') },
        { label: 'Escapes', value: this.data.escapes.toLocaleString('en-US') },
        { label: 'Avg. Healing', value: Number(this.data.healing).toLocaleString('en-US') },
        { label: 'Avg. Hero Damage', value: Number(this.data.hero_damage).toLocaleString('en-US') },
        { label: 'Avg. Siege Damage', value: Number(this.data.siege_damage).toLocaleString('en-US') },
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
    sortedTeams() {
      return this.data.teams.slice().sort((a, b) => b.win_rate - a.win_rate || (b.wins + b.losses) - (a.wins + a.losses));
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
        // The division's numbers and its latest games together, so the page fills in one go
        const [response, matches] = await Promise.all([
          this.$axios.post("/api/v1/esports/ngs/division/single", {
            season: this.modifiedseason,
            division: this.division,
          }, { cancelToken: this.cancelTokenSource.token }),
          this.$axios.post("/api/v1/esports/ngs/matches", {
            season: this.modifiedseason,
            division: this.division,
            pagination_page: 1,
            esport: "NGS",
          }, { cancelToken: this.cancelTokenSource.token }),
        ]);
        this.seasons = response.data.seasons;
        this.recentMatches = (matches.data.data || []).slice(0, 7);
        this.data = response.data;
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
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
    handleInputChange(eventPayload){
        if(eventPayload.field == "Seasons"){
            this.modifiedseason = eventPayload.value;
        }

      let newURL = `/Esports/NGS/Division/${this.division}`;
      if (this.modifiedseason) {
        newURL += `?season=${this.modifiedseason}`;
      }
      history.pushState({}, "", newURL);
    },
    teamUrl(team) {
      return `/Esports/NGS/Team/${encodeURIComponent(this.$ngsTeam(team.team_name).name)}?season=${this.modifiedseason}&division=${this.division}`;
    },
    handleDropdownClosed(){
      this.data = null;
      this.getData();
    },
  }
}
</script>