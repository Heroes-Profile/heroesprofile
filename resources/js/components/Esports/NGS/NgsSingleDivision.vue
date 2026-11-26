<template>
  <div>
    <player-page-top>
      <template #heading>
        <page-heading :battletag="$t('Division :division', { division: division })" esport="NGS" heading="" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>
      </template>
      <div class="w-full">
        <!-- Shown while loading too, so the heading row holds still; the seasons fill in once they load -->
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => handleDropdownClosed()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: seasons || [], defaultValue: modifiedseason },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <!-- Laid out like the esports team page -->
    <div v-if="data" class="space-y-8">
      <div class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">
        <panel-box>
          <!-- The division card, maps included, beside its teams; the heroes take the full row below -->
          <div class="grid xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
            <!-- Division overview; a division has no win rate of its own, so just its stats -->
            <panel-box :inner="true" class="!gap-8">
              <div class="flex flex-col">
                <h2 class="text-3xl font-bold">{{ $t('Division :division', { division: division }) }}</h2>
                <span class="text-subtext">{{ $t('Season :modifiedseason', { modifiedseason: modifiedseason }) }}</span>
              </div>
              <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
                <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                  <div class="w-40 md:w-[14rem] aspect-square flex items-center justify-center">
                    <img class="max-w-full max-h-full object-contain text-[0rem]" :src="divisionImage" :alt="'Division ' + division" @error="divisionImageFailed = true"/>
                  </div>
                  <div class="text-xl font-semibold leading-[1.2] text-subtext">{{ $t('Games:') }} <span class="text-header">{{ data.total_games.toLocaleString('en-US') }}</span></div>
                </div>
                <overview-stats class="flex-1 min-w-0 w-full" :no-win-rate="true" :stats="overviewStats"></overview-stats>
              </div>
              <!-- Maps sit under the stats in the division card -->
              <div class="flex flex-col gap-4">
                <h3 class="text-2xl font-bold">{{ $t('Maps') }}</h3>
                <div class="flex flex-wrap gap-6 max-md:justify-center">
                  <group-box v-for="group in topSections.find(section => section.title == 'Maps').groups" :key="group.label" class="!m-0" :text="group.label" :data="group.data" :min-tiles="3"></group-box>
                </div>
                <div class="flex flex-col gap-4">
                  <h3 class="text-2xl font-bold">{{ $t('Maps Played') }}</h3>
                  <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                    <map-image-wrapper v-for="item in data.maps" :key="item.map_id" size="tile" :map="item.map">
                      <image-hover-box :title="item.name" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + (item.wins + item.losses)"></image-hover-box>
                    </map-image-wrapper>
                  </div>
                </div>
              </div>
            </panel-box>

            <!-- The division's teams, best record first -->
            <panel-box :inner="true" class="min-w-0" :title="$t('Teams')">
              <div class="flex flex-col gap-2">
                <a v-for="team in sortedTeams" :key="team.team_name" :href="teamUrl(team)" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                  <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image || '/images/NGS/no-image-clipped.png'" :alt="$ngsTeam(team.team_name).name" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = '/images/NGS/no-image-clipped.png'; } }"/>
                  <div class="flex flex-col min-w-0 flex-1">
                    <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
                    <span v-if="$ngsTeam(team.team_name).withdrawnSeason" class="text-sm text-subtext">{{ $t('Withdrew Season :withdrawnseason', { withdrawnseason: $ngsTeam(team.team_name).withdrawnSeason }) }}</span>
                  </div>
                  <div class="flex flex-col items-end shrink-0 text-sm">
                    <span class="font-semibold">{{ team.win_rate.toFixed(2) }}%</span>
                    <span class="text-subtext">{{ $t(':losses games', { losses: team.wins + team.losses }) }}</span>
                  </div>
                </a>
              </div>
              <p class="flex flex-wrap gap-2 mt-auto"><span class="text-subtext">{{ $t('Total Time Spent Dead:') }}</span><span class="text-header">{{ formatDurationText(shortDuration(data.time_spent_dead)) }}</span></p>
            </panel-box>

            <!-- The division's heroes: the groups, then every hero played -->
            <panel-box :inner="true" class="min-w-0 xl:col-span-2" :title="$t('Heroes')">
              <div class="flex flex-wrap gap-6 justify-evenly">
                <group-box v-for="group in topSections.find(section => section.title == 'Heroes').groups" :key="group.label" class="!m-0" :text="group.label" :data="group.data" :min-tiles="3" empty-note="Only heroes with 5+ games count here."></group-box>
              </div>
              <div class="flex flex-col gap-4">
                <h3 class="text-2xl font-bold">{{ $t('Heroes Played') }}</h3>
                <div class="flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                  <hero-image-wrapper v-for="item in data.heroes" :key="item.hero_id" size="tile" :hero="item.hero">
                    <image-hover-box :title="item.name" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + (item.wins + item.losses)"></image-hover-box>
                  </hero-image-wrapper>
                </div>
              </div>
            </panel-box>
          </div>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

        <div class="flex justify-between items-baseline gap-6">
          <h2 class="text-2xl font-bold">{{ $t('Recent Matches') }}</h2>
          <a class="text-header whitespace-nowrap hover:underline" :href="`/Esports/NGS/Division/${division}/Match/History?season=${modifiedseason}`">{{ $t('Match History') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a>
        </div>
      </div>

      <!-- The NGS page's own recent match rows -->
      <esports-recent-matches v-if="recentMatches.length" :data="recentMatches" :esport="'NGS'" :always-expanded="true"></esports-recent-matches>
      <div v-else class="max-w-[120rem] mx-auto px-4 md:px-8">
        <p class="text-subtext">{{ $t('No games in this division yet.') }}</p>
      </div>
    </div>

    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="relative max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 flex flex-col gap-8 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
      <!-- The message sits on a tab of the first card, as on the global pages: lined up with the card past the side padding, the card's top-left corner squared to meet it -->
      <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
      <panel-box>
        <div class="grid xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8">
            <div class="flex flex-col">
              <h2 class="text-3xl font-bold">{{ $t('Division :division', { division: division }) }}</h2>
              <span class="text-subtext">{{ $t('Season :modifiedseason', { modifiedseason: modifiedseason }) }}</span>
            </div>
            <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
              <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                <skeleton-box class="w-40 md:w-[14rem] aspect-square rounded"></skeleton-box>
                <skeleton-box class="h-[1.5rem] w-28"></skeleton-box>
              </div>
              <overview-stats class="flex-1 min-w-0 w-full" :loading="true" :no-win-rate="true" :count="9"></overview-stats>
            </div>
            <!-- Maps sit under the stats in the division card -->
            <div class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ $t('Maps') }}</h3>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <div v-for="label in ['Most Played', 'Highest Win Rate', 'Lowest Win Rate']" :key="label" class="w-auto inline-block">
                  <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                  <div class="flex flex-wrap gap-3 max-md:gap-2"><skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box></div>
                </div>
              </div>
              <div class="flex flex-col gap-4">
                <h3 class="text-2xl font-bold">{{ $t('Maps Played') }}</h3>
                <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2"><skeleton-box v-for="tile in 14" :key="tile" class="w-16 h-16 rounded"></skeleton-box></div>
              </div>
            </div>
          </panel-box>

          <panel-box :inner="true" class="min-w-0" :title="$t('Teams')">
            <div class="flex flex-col gap-2">
              <skeleton-box v-for="index in 8" :key="index" class="h-14 rounded-lg"></skeleton-box>
            </div>
            <p class="flex flex-wrap items-center gap-2 mt-auto"><span class="skeleton-text">{{ $t('Total Time Spent Dead:') }}</span><skeleton-box class="h-4 w-36"></skeleton-box></p>
          </panel-box>

          <panel-box :inner="true" class="min-w-0 xl:col-span-2" :title="$t('Heroes')">
            <div class="flex flex-wrap gap-6 justify-evenly">
              <div v-for="label in ['Most Played', 'Highest Win Rate', 'Lowest Win Rate']" :key="label" class="w-auto inline-block">
                <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                <div class="flex flex-wrap gap-3 max-md:gap-2"><skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box></div>
              </div>
            </div>
            <div class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ $t('Heroes Played') }}</h3>
              <div class="flex flex-wrap gap-3 max-md:gap-2"><skeleton-box v-for="tile in 30" :key="tile" class="w-16 h-16 rounded"></skeleton-box></div>
            </div>
          </panel-box>
        </div>
      </panel-box>
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
                <skeleton-box class="h-8 w-20 rounded"></skeleton-box>
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
  import { formatGameDate } from '../../../utils/formatGameDate';
  import moment from 'moment-timezone';
  import { formatDurationText } from '../../../utils/formatDuration';

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
    formatDurationText,
    // The server's "1 hours, 26 minutes, 40 seconds" as "1h, 26m, 40s", which formatDurationText reads
    shortDuration(text) {
      return String(text ?? '').replace(/ (day|hour|minute|second)s/g, (_, unit) => unit[0]);
    },
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
      return formatGameDate(dateString);
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