<template>
  <div>
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
        <page-heading :heading="'Profile Data'" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
      </template>
      <div class="w-full">
        <!-- Shown while loading too, so the heading row holds still; the options fill in once the player's seasons load -->
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => handleDropdownClosed()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filterOptions.seasons, defaultValue: modifiedseason },
            { text: 'Divisions', values: filterOptions.divisions, defaultValue: modifieddivision, show: esport == 'NGS' },
            { text: 'Teams', values: teamOptions, defaultValue: modifiedteam },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">

      <!-- Laid out like the profile page, from this league's games only -->
      <panel-box>
        <div class="flex flex-col gap-6 xl:grid xl:grid-cols-[auto_minmax(0,1fr)] xl:grid-rows-[auto_1fr]">

          <!-- Player overview -->
          <panel-box :inner="true" class="!gap-8 xl:!gap-6 xl:row-span-2 xl:grid xl:[grid-template-rows:subgrid]">
            <div class="flex flex-col gap-8">
              <div class="flex items-center gap-3">
                <div class="flex flex-col">
                  <h2 class="text-3xl font-bold">{{ battletag }}</h2>
                  <span class="text-subtext">{{ leagueLabel }}</span>
                </div>
              </div>

              <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
                <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                  <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" :excludehover="true" size="large"></hero-image-wrapper>
                </div>

                <overview-stats class="flex-1 min-w-0 w-full" :win-rate="data.win_rate" :stats="overviewStats"></overview-stats>
              </div>
            </div>
            <!-- On desktop the maps start level with the Heroes card title -->
            <div class="flex flex-col gap-8 xl:pt-6">
              <!-- Maps stay on the player card; the heroes have their own card beside it -->
              <div v-for="section in topSections.filter(section => section.title == 'Maps')" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
                <div class="flex justify-between items-baseline">
                  <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
                  <a class="text-header whitespace-nowrap hover:underline" :href="ngsPageUrl(section.allPath)">{{ $t(section.allText) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a>
                </div>
                <div class="flex flex-wrap gap-6 max-md:justify-center">
                  <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
                </div>
              </div>

              <div class="flex flex-col gap-1 mt-auto">
                <p v-if="data.total_time_played" class="flex flex-wrap gap-2"><span class="text-subtext">{{ $t('Total Time Played:') }}</span><span class="text-header">{{ formatDurationText(data.total_time_played) }}</span></p>
                <p class="flex flex-wrap gap-2"><span class="text-subtext">{{ $t('Total Time Spent Dead:') }}</span><span class="text-header">{{ formatDurationText(shortDuration(data.time_spent_dead)) }}</span></p>
              </div>
            </div>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0 xl:row-span-2 xl:grid xl:[grid-template-rows:subgrid]">
            <div class="grid 2xl:grid-cols-2 gap-6">
              <!-- Win rate by role -->
              <panel-box :inner="true" :title="$t('Win Rate By Role')" link-text="All Roles" :link-href="ngsPageUrl('/Role')">
                <!-- Spread over the card, so it and the card beside it line up -->
                <div class="flex-1 flex flex-col justify-between gap-3">
                  <div v-for="role in roleWinRates" :key="role.role" class="flex flex-col gap-1.5">
                    <span class="leading-[1.2]"><a class="text-header hover:underline" :href="ngsPageUrl('/Role/' + role.role)">{{ $t(role.role) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a> <span class="text-sm text-subtext">{{ $t('· :played games', { played: role.games_played }) }}</span></span>
                    <stat-bar :value="role.win_rate.toFixed(2)" suffix="%" :color="$winRateColor(role.win_rate)"></stat-bar>
                  </div>
                </div>
              </panel-box>

              <!-- The teams they played for -->
              <panel-box v-if="data.teams_played_for && data.teams_played_for.length" :inner="true" :title="$t('Teams Played For')">
                <!-- The latest five; the rest open in a popup, where the card's link would sit -->
                <template v-if="data.teams_played_for.length > 5" #right>
                  <button type="button" class="text-header whitespace-nowrap hover:underline" @click="teamsOpen = true">{{ $t('View More') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></button>
                </template>
                <!-- Spaced as if there were five; fewer leave empty slots at the bottom -->
                <div class="flex flex-col gap-2">
                  <a v-for="team in data.teams_played_for.slice(0, 5)" :key="team.team_name + team.season" :href="team.link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                    <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image || league.loaderImage" :alt="$ngsTeam(team.team_name).name" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = league.loaderImage; } }"/>
                    <div class="flex flex-col min-w-0 flex-1">
                      <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ $ngsTeam(team.team_name).name }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
                      <span class="text-sm text-subtext">{{ [team.season ? $t('Season :season', { season: team.season }) : null, team.division ? $t('Division :division', { division: team.division }) : null].filter(Boolean).join(' · ') }}</span>
                    </div>
                    <div class="flex flex-col items-end shrink-0 text-sm">
                      <span class="font-semibold">{{ team.win_rate.toFixed(2) }}%</span>
                      <span class="text-subtext">{{ $t(':played games', { played: team.games_played }) }}</span>
                    </div>
                  </a>
                  <div v-for="slot in Math.max(0, 5 - data.teams_played_for.length)" :key="'empty' + slot" class="h-14" aria-hidden="true"></div>
                </div>
              </panel-box>
            </div>

            <div class="flex flex-col gap-6">
              <!-- Their heroes, then the heroes on the other side in their wins and losses -->
              <panel-box :inner="true" :title="$t('Heroes')" link-text="All Heroes" :link-href="ngsPageUrl('/Hero')">
                <div class="flex flex-col gap-6">
                  <div class="flex flex-wrap gap-6 justify-evenly">
                    <group-box v-for="group in topSections.find(section => section.title == 'Heroes').groups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
                  </div>
                  <div class="flex flex-wrap gap-6 justify-evenly">
                    <group-box v-if="mostPlayedAgainst.length" class="!m-0" :useinputforhover="true" :text="'Most Played Against'" :data="mostPlayedAgainst"></group-box>
                    <group-box v-if="data.heroes_won_against && data.heroes_won_against.length" class="!m-0" :useinputforhover="true" :text="'Most Won Against'" :data="data.heroes_won_against.slice(0, 3)"></group-box>
                    <group-box v-if="data.heroes_lost_against && data.heroes_lost_against.length" class="!m-0" :useinputforhover="true" :text="'Most Lost Against'" :data="data.heroes_lost_against.slice(0, 3)"></group-box>
                  </div>
                </div>
              </panel-box>
            </div>
          </div>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="data.matches && data.matches.length" :title="$t('Recent Matches')" link-text="Match History" :link-href="getMatchHistoryUrl()" :divider="false">
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

    <!-- The page's layout with grey blocks while it loads, like the profile; the nav logo spins meanwhile -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 space-y-8">
      <!-- The cards start where the loaded ones do; the message sits on a tab of the panel above them -->
      <panel-box class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="league.loaderImage"></loading-component>
        <!-- Built from the loaded cards' own parts (the same labels, bars and boxes, empty), so nothing moves when the data arrives -->
        <div class="flex flex-col gap-6 xl:grid xl:grid-cols-[auto_minmax(0,1fr)] xl:grid-rows-[auto_1fr]">
          <panel-box :inner="true" class="!gap-8 xl:!gap-6 xl:row-span-2 xl:grid xl:[grid-template-rows:subgrid]">
            <div class="flex flex-col gap-8">
              <div class="flex items-center gap-3">
                <div class="flex flex-col">
                  <h2 class="text-3xl font-bold">{{ battletag }}</h2>
                  <span class="text-subtext">{{ leagueLabel }}</span>
                </div>
              </div>
              <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
                <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                  <skeleton-box class="w-40 md:w-[14rem] h-[20.125rem] rounded"></skeleton-box>
                </div>
                <overview-stats class="flex-1 min-w-0 w-full" :loading="true" :count="8"></overview-stats>
              </div>
            </div>
            <!-- On desktop the maps start level with the Heroes card title -->
            <div class="flex flex-col gap-8 xl:pt-6">
              <div v-for="section in [{ title: 'Maps', allText: 'All Maps' }]" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
                <div class="flex justify-between items-baseline">
                  <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
                  <span class="skeleton-text whitespace-nowrap">{{ $t(section.allText) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
                </div>
                <div class="flex flex-wrap gap-6 max-md:justify-center">
                  <div v-for="label in ['Most Played', 'Highest Win Rate', 'Lowest Win Rate']" :key="label" class="w-auto inline-block">
                    <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                    <div class="flex flex-wrap gap-3 max-md:gap-2">
                      <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex flex-col gap-1 mt-auto">
                <p class="flex flex-wrap items-center gap-2"><span class="skeleton-text">{{ $t('Total Time Played:') }}</span><skeleton-box class="h-4 w-36"></skeleton-box></p>
                <p class="flex flex-wrap items-center gap-2"><span class="skeleton-text">{{ $t('Total Time Spent Dead:') }}</span><skeleton-box class="h-4 w-36"></skeleton-box></p>
              </div>
            </div>
          </panel-box>

          <div class="flex flex-col gap-6 min-w-0 xl:row-span-2 xl:grid xl:[grid-template-rows:subgrid]">
            <div class="grid 2xl:grid-cols-2 gap-6">
              <panel-box :inner="true" :title="$t('Win Rate By Role')" link-text="All Roles" :skeleton="true">
                <div class="flex-1 flex flex-col justify-between gap-3">
                  <div v-for="role in ['Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank']" :key="role" class="flex flex-col gap-1.5">
                    <span class="skeleton-text leading-[1.2] w-fit">{{ $t(role) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
                    <stat-bar value="0.00" suffix="%" :hide-value="true"></stat-bar>
                  </div>
                </div>
              </panel-box>
              <panel-box :inner="true" :title="$t('Teams Played For')">
                <div class="flex flex-col gap-2">
                  <skeleton-box v-for="index in 3" :key="index" class="h-14"></skeleton-box>
                  <div v-for="slot in 2" :key="'empty' + slot" class="h-14" aria-hidden="true"></div>
                </div>
              </panel-box>
            </div>
            <div class="flex flex-col gap-6">
              <panel-box :inner="true" :title="$t('Heroes')" link-text="All Heroes" :skeleton="true">
                <div class="flex flex-col gap-6">
                  <div v-for="(row, index) in [['Most Played', 'Highest Win Rate', 'Lowest Win Rate'], ['Most Played Against', 'Most Won Against', 'Most Lost Against']]" :key="index" class="flex flex-wrap gap-6 justify-evenly">
                    <div v-for="label in row" :key="label" class="w-auto inline-block">
                      <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                      <div class="flex flex-wrap gap-3 max-md:gap-2">
                        <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                      </div>
                    </div>
                  </div>
                </div>
              </panel-box>
            </div>
          </div>
        </div>
      </panel-box>

      <panel-box :title="$t('Recent Matches')" link-text="Match History" :skeleton="true" :divider="false">
        <div>
          <game-summary-header :skeleton="true"></game-summary-header>
          <game-summary-skeleton v-for="index in 7" :key="index"></game-summary-skeleton>
        </div>
      </panel-box>
    </div>

    <!-- Every team they played for, from the card's View More -->
    <modal-box v-if="teamsOpen && data" :title="$t('Teams Played For')" width="max-w-[40rem]" @close="teamsOpen = false">
      <div class="flex flex-col gap-2">
        <a v-for="team in data.teams_played_for" :key="team.team_name + team.season" :href="team.link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
          <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image || league.loaderImage" :alt="$ngsTeam(team.team_name).name" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = league.loaderImage; } }"/>
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
      <template #footer>
        <custom-button class="ml-auto" color="teal" text="Close" :ignoreclick="true" @click="teamsOpen = false"></custom-button>
      </template>
    </modal-box>
  </div>
</template>

<script>
import { formatDurationText } from '../../../utils/formatDuration';
export default {
  name: 'EsportsPlayerStats',
  components: {
  },
  props: {
    // Ad-free Patreon supporters, from the session; hides the in-page ads
    patreonUser: Boolean,
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
      modifiedseason: null,
      modifieddivision: null,
      modifiedteam: null,
      // The player's seasons and divisions, kept while the page reloads after a filter change
      filterOptions: { seasons: [], divisions: [] },
      // The popup with every team they played for
      teamsOpen: false,
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
    // Every role, with the ones they haven't played at 0 games
    roleWinRates() {
      const played = this.data.role_win_rates || [];
      return ['Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank']
        .map(role => played.find(row => row.role === role) || { role, games_played: 0, win_rate: 0 });
    },
    // The heroes they faced most, wins and losses together; both lists hold every hero they faced
    mostPlayedAgainst() {
      const counts = {};
      for (const row of [...(this.data.heroes_won_against || []), ...(this.data.heroes_lost_against || [])]) {
        counts[row.hero.id] = counts[row.hero.id] || { hero: row.hero, total: 0 };
        counts[row.hero.id].total += row.total;
      }
      const games = this.data.wins + this.data.losses;
      return Object.values(counts)
        .sort((a, b) => b.total - a.total)
        .slice(0, 3)
        .map(row => ({ ...row, inputhover: this.$t('Played against :hero :count times (:percent% of their games)', { hero: this.$heroName(row.hero), count: row.total, percent: games ? (row.total / games * 100).toFixed(2) : 0 }) }));
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
        this.filterOptions = { seasons: this.data.seasons || [], divisions: this.data.divisions || [] };
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