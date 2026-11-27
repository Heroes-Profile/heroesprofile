<template>
  <div>
    <!-- The league's heading with the player's filters beside it, as on the league pages -->
    <player-page-top>
      <template #heading>
        <page-heading :heading="leagueTitle" :subheading="leagueSubheading">
          <a :href="league.headingImageUrl" class="flex items-center justify-center w-28 h-28"><img :src="league.headingImage" :alt="league.name" class="w-full h-full object-contain"></a>
        </page-heading>
      </template>
      <div class="w-full">
        <!-- Shown while loading too, so the heading row holds still; the options fill in once the player's seasons load -->
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => handleDropdownClosed()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Season', values: filterOptions.seasons, defaultValue: modifiedseason },
            { text: 'Division', values: filterOptions.divisions, defaultValue: modifieddivision, show: esport == 'NGS' },
            { text: 'Team', values: teamOptions, defaultValue: modifiedteam },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>

    <!-- The league's sections, as on its own page; each opens that section there, the player's section marked -->
    <div v-if="sectionTabs.length" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6">
      <tab-button :tabs="sectionTabs" :stretch="true" :no-default="!currentSection" :overridedefaultside="currentSection" :ignoreclick="true" @tab-click="openSection"></tab-button>
    </div>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 flex flex-col gap-6">
      <!-- The player across the top: name, win rate and their latest team, then the eight numbers -->
      <panel-box :divider="false" class="!gap-6">
        <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,1fr)] gap-6 items-center">
          <div class="flex items-center gap-4 min-w-0">
            <img v-if="mostPlayedHero" class="w-20 h-20 shrink-0 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${mostPlayedHero.short_name}.png`" :alt="$heroName(mostPlayedHero)"/>
            <div class="flex flex-col min-w-0">
              <h2 class="text-3xl font-bold truncate">{{ battletag }}</h2>
              <span class="text-sm text-subtext truncate">{{ [leagueLabel, latestTeam].filter(Boolean).join(' · ') }}</span>
            </div>
          </div>
          <div class="flex flex-col gap-2 rounded bg-surface-1 px-4 py-3">
            <div class="flex justify-between gap-4 text-sm text-subtext"><span>{{ $t('Win Rate') }}</span><span>{{ $t(':wins W · :losses L', { wins: data.wins, losses: data.losses }) }}</span></div>
            <div class="flex items-center gap-4">
              <span class="text-3xl font-bold">{{ Number(data.win_rate).toFixed(2) }}%</span>
              <stat-bar class="flex-1" :value="Number(data.win_rate).toFixed(2)" display-text="" color="teal"></stat-bar>
            </div>
          </div>
          <!-- Their latest team; every team they played for in the popup -->
          <div v-if="latestTeamRow" class="flex flex-col gap-2 min-w-0">
            <div class="flex items-center justify-between gap-4 text-sm text-subtext">
              <span>{{ $t('Teams played for') }}</span>
              <button v-if="data.teams_played_for.length > 1" type="button" class="text-sm font-semibold text-teal link-more" @click="teamsOpen = true">{{ $t('View More') }}</button>
            </div>
            <a :href="latestTeamRow.link" class="flex items-center gap-3 bg-surface-1 px-3 py-2 link-tile">
              <img v-if="latestTeamRow.image" class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="latestTeamRow.image" :alt="$ngsTeam(latestTeamRow.team_name).name" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = league.loaderImage; } }"/>
              <span v-else class="w-10 h-10 shrink-0 rounded-full bg-surface-3 flex items-center justify-center text-sm font-bold text-subtext">{{ initials($ngsTeam(latestTeamRow.team_name).name) }}</span>
              <div class="flex flex-col min-w-0 flex-1">
                <span class="font-semibold truncate"><span class="link-tile-title">{{ $ngsTeam(latestTeamRow.team_name).name }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
                <span class="text-xs text-subtext">{{ [latestTeamRow.season ? $t('Season :season', { season: latestTeamRow.season }) : null, latestTeamRow.division ? $t('Division :division', { division: latestTeamRow.division }) : null].filter(Boolean).join(' · ') }}</span>
              </div>
              <div class="flex flex-col items-end shrink-0 text-sm">
                <span class="font-semibold">{{ latestTeamRow.win_rate.toFixed(2) }}%</span>
                <span class="text-xs text-subtext">{{ $t(':played games', { played: latestTeamRow.games_played }) }}</span>
              </div>
            </a>
          </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-2">
          <div v-for="stat in overviewStats" :key="stat.label" class="flex flex-col rounded bg-surface-1 px-3 py-2.5 min-w-0">
            <span class="text-xs text-subtext">{{ $t(stat.label) }}</span>
            <span class="text-lg font-bold truncate">{{ stat.value }}</span>
          </div>
        </div>
      </panel-box>

      <div class="grid xl:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] gap-6">
        <!-- The roles they played, most played first -->
        <panel-box :title="$t('Win rate by role')" link-text="All roles" :link-href="ngsPageUrl('/Role')" :divider="false">
          <div class="flex flex-col gap-3">
            <div v-for="role in rolesPlayed" :key="role.role" class="flex flex-col gap-1.5">
              <div class="flex justify-between gap-4 text-sm">
                <span><a class="font-semibold link-row" :href="ngsPageUrl('/Role/' + role.role)">{{ $t(role.role) }}</a> <span class="text-subtext">{{ $t('· :played games', { played: role.games_played }) }}</span></span>
                <span :class="['font-semibold', role.win_rate >= 50 ? 'text-teal' : '']">{{ role.win_rate.toFixed(2) }}%</span>
              </div>
              <stat-bar :value="role.win_rate.toFixed(2)" display-text="" :color="$winRateColor(role.win_rate)"></stat-bar>
            </div>
          </div>
        </panel-box>

        <!-- The heroes on the other side in their wins and in their losses, each with how many of those games -->
        <panel-box :title="$t('Heroes played against')" :divider="false">
          <div class="flex flex-col gap-4">
            <div v-for="group in againstGroups" :key="group.label" class="flex flex-col gap-2">
              <h4 class="text-sm text-subtext">{{ $t(group.label) }}</h4>
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                <div v-for="item in group.items" :key="item.hero.id" class="flex items-center gap-2 rounded bg-surface-1 px-2 py-2 min-w-0">
                  <hero-image-wrapper size="small" :hero="item.hero">
                    <image-hover-box :title="$heroName(item.hero)" :paragraph-one="item.inputhover"></image-hover-box>
                  </hero-image-wrapper>
                  <div class="flex flex-col min-w-0">
                    <span class="text-sm font-semibold truncate">{{ $heroName(item.hero) }}</span>
                    <span :class="['text-xs', group.wins ? 'text-win' : 'text-loss']">{{ $t(group.wins ? ':count W' : ':count L', { count: item.total }) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </panel-box>
      </div>

      <!-- Their heroes, most played first -->
      <panel-box :title="$t('Heroes')" link-text="All Heroes" :link-href="ngsPageUrl('/Hero')" :divider="false">
        <table class="w-full rounded-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm leading-4">{{ $t('Hero') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Win Rate') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Games') }}</th>
              <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Record') }}</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <tr v-for="row in heroRows" :key="row.hero_id">
              <td class="py-2 px-3 text-left">
                <a :href="row.link" class="inline-flex items-center gap-2 min-w-0 link-row">
                  <img class="w-7 h-7 shrink-0 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${row.hero.short_name}.png`" :alt="$heroName(row.hero)"/>
                  <span class="font-semibold truncate">{{ $heroName(row.hero) }}</span>
                </a>
              </td>
              <td :class="['font-semibold', row.win_rate >= 50 ? 'text-teal' : '']">{{ Number(row.win_rate).toFixed(2) }}%</td>
              <td>{{ row.games_played }}</td>
              <td class="text-subtext">{{ $t(':wins W · :losses L', heroRecord(row)) }}</td>
            </tr>
          </tbody>
        </table>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="data.matches && data.matches.length" :title="$t('Recent matches')" link-text="Match History" :link-href="getMatchHistoryUrl()" :divider="false">
        <div>
          <game-summary-box
            v-for="(item, index) in data.matches.slice(0, 7)"
            :key="index"
            :esport="true"
            :esport-league="esport"
            :esport-series="series"
            :tournament="tournament"
            :hide-players="true"
            :data="item"
          ></game-summary-box>
        </div>
      </panel-box>
    </div>

    <!-- The load failed: the error page's card in place of the data -->
    <div v-else-if="loadError" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
    <!-- The page's cards in grey while it loads; the message sits on a tab of the player card -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 flex flex-col gap-6">
      <panel-box :divider="false" class="!gap-6 relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :overrideimage="league.loaderImage"></loading-component>
        <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,1fr)] gap-6 items-center">
          <div class="flex items-center gap-4">
            <skeleton-box class="w-20 h-20 shrink-0 !rounded-full"></skeleton-box>
            <div class="flex flex-col gap-2 min-w-0">
              <h2 class="text-3xl font-bold truncate">{{ battletag }}</h2>
              <skeleton-box class="h-4 w-48"></skeleton-box>
            </div>
          </div>
          <skeleton-box class="h-[5.25rem] rounded-control"></skeleton-box>
          <skeleton-box class="h-[5.25rem] rounded-control"></skeleton-box>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-2">
          <skeleton-box v-for="stat in 8" :key="stat" class="h-[4.25rem] rounded-control"></skeleton-box>
        </div>
      </panel-box>
      <div class="grid xl:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] gap-6">
        <panel-box :title="$t('Win rate by role')" link-text="All roles" :skeleton="true" :divider="false">
          <div v-for="role in 3" :key="role" class="flex flex-col gap-1.5">
            <skeleton-box class="h-4 w-40"></skeleton-box>
            <stat-bar value="0.00" display-text=""></stat-bar>
          </div>
        </panel-box>
        <panel-box :title="$t('Heroes played against')" :skeleton="true" :divider="false">
          <div v-for="label in ['Most wins against', 'Most losses against']" :key="label" class="flex flex-col gap-2">
            <h4 class="text-sm"><span class="skeleton-text">{{ $t(label) }}</span></h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
              <skeleton-box v-for="tile in 5" :key="tile" class="h-14 rounded"></skeleton-box>
            </div>
          </div>
        </panel-box>
      </div>
      <panel-box :title="$t('Heroes')" link-text="All Heroes" :skeleton="true" :divider="false">
        <table-skeleton :bare="true" :rows="6" :columns="['hero', 'stat', 'stat', 'stat']"></table-skeleton>
      </panel-box>

      <panel-box :title="$t('Recent matches')" link-text="Match History" :skeleton="true" :divider="false">
        <div>
          <game-summary-skeleton v-for="index in 7" :key="index"></game-summary-skeleton>
        </div>
      </panel-box>
    </div>

    <!-- Every team they played for, from the card's View More -->
    <modal-box v-if="teamsOpen && data" :title="$t('Teams Played For')" width="max-w-[40rem]" @close="teamsOpen = false">
      <div class="flex flex-col gap-2">
        <a v-for="team in data.teams_played_for" :key="team.team_name + team.season" :href="team.link" class="flex items-center gap-3 bg-surface-2 px-3 py-2 link-tile">
          <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="team.image || league.loaderImage" :alt="$ngsTeam(team.team_name).name" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = league.loaderImage; } }"/>
          <div class="flex flex-col min-w-0 flex-1">
            <span class="font-semibold truncate"><span class="link-tile-title">{{ $ngsTeam(team.team_name).name }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
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
      loadError: null,
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
    leagueTitle() {
      return this.esport == 'NGS' ? 'Nexus Gaming Series' : this.league.name;
    },
    leagueSubheading() {
      if (this.esport == 'NGS') {
        return this.modifiedseason ? this.$t('NGS · Community league · Season :season', { season: this.modifiedseason }) : this.$t('NGS · Community league');
      }
      return this.$t('Player stats');
    },
    // The league's own section tabs, and which of them is the player's (NGS Player Search, a series' Players)
    sectionTabs() {
      const tabs = {
        NGS: ['standings', 'divisions', 'teams', 'playerSearch', 'recentMatches', 'overallHeroStats', 'overallTalentStats'],
        CCL: ['organizations', 'recentMatches', 'overallHeroStats', 'overallTalentStats'],
        MastersClash: ['teams', 'recentMatches', 'overallHeroStats', 'overallTalentStats'],
        HeroesInternational: ['teams', 'recentMatches', 'overallHeroStats', 'overallTalentStats'],
        Other: ['teams', 'recentMatches', 'players', 'overallHeroStats', 'overallTalentStats'],
      }[this.esport] || [];
      const labels = { standings: 'Standings', divisions: 'Divisions', teams: 'Teams', playerSearch: 'Player Search', organizations: 'Organizations', players: 'Players', recentMatches: 'Recent Matches', overallHeroStats: 'Overall Hero Stats', overallTalentStats: 'Overall Talent Stats' };
      return tabs.map(key => ({ key, text: labels[key] }));
    },
    currentSection() {
      return this.sectionTabs.find(tab => ['playerSearch', 'players'].includes(tab.key))?.key ?? null;
    },
    latestTeamRow() {
      return this.data.teams_played_for?.[0] ?? null;
    },
    againstGroups() {
      return [
        { label: 'Most wins against', wins: true, items: (this.data.heroes_won_against || []).slice(0, 5) },
        { label: 'Most losses against', wins: false, items: (this.data.heroes_lost_against || []).slice(0, 5) },
      ].filter(group => group.items.length);
    },
    mostPlayedHero() {
      return this.data.hero_top_three_most_played?.[0]?.hero ?? null;
    },
    latestTeam() {
      const team = this.data.teams_played_for?.[0];
      return team ? this.$ngsTeam(team.team_name).name : null;
    },
    // The roles they played, most played first
    rolesPlayed() {
      return (this.data.role_win_rates || []).filter(role => role.games_played).slice().sort((a, b) => b.games_played - a.games_played);
    },
    // Their heroes, most played first
    heroRows() {
      return (this.data.heroes || []).slice().sort((a, b) => b.games_played - a.games_played).slice(0, 8);
    },
    overviewStats() {
      return [
        { label: 'KDR', value: this.data.kdr },
        { label: 'KDA', value: this.data.kda },
        { label: 'Takedowns', value: this.data.takedowns.toLocaleString('en-US') },
        { label: 'Kills', value: this.data.kills.toLocaleString('en-US') },
        { label: 'Assists', value: Number(this.data.assists).toLocaleString('en-US') },
        { label: 'Deaths', value: Number(this.data.deaths).toLocaleString('en-US') },
        { label: 'Time played', value: this.data.total_time_played ? formatDurationText(this.data.total_time_played) : '—' },
        { label: 'Time spent dead', value: formatDurationText(this.shortDuration(this.data.time_spent_dead)) },
      ];
    },
  },
  watch: {
  },
  methods: {
    formatDurationText,
    initials(name) {
      return String(name).split(/\s+/).filter(Boolean).slice(0, 2).map(word => word.charAt(0).toUpperCase()).join('');
    },
    // The server's "1 hours, 26 minutes, 40 seconds" as "1h, 26m, 40s", which formatDurationText reads
    shortDuration(text) {
      return String(text ?? '').replace(/ (day|hour|minute|second)s/g, (_, unit) => unit[0]);
    },
    async getData(){
      this.loadError = null;
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
        // Kept once loaded like the teams, so picking a team doesn't drop the chosen season from the list
        if (!this.filterOptions.seasons.length) {
          this.filterOptions = { seasons: this.data.seasons || [], divisions: this.data.divisions || [] };
        }
        if (!this.modifiedteam || !this.teamOptions.length) {
          const names = [...new Set((this.data.teams_played_for || []).map(team => team.team_name))];
          this.teamOptions = [{ code: null, name: 'All' }, ...names.map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
        }
      }catch(error){
      this.loadError = this.$loadError(error, () => this.getData());
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
        if(eventPayload.field == "Season"){
            this.modifiedseason = eventPayload.value;
        }

        if(eventPayload.field == "Division"){
          this.modifieddivision = eventPayload.value;
        }

        if(eventPayload.field == "Team"){
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
    // A section of the league's own page, for this season (and division in NGS)
    openSection(key) {
      const params = new URLSearchParams(this.league.params);
      params.set('tab', key);
      if (this.modifiedseason) params.set('season', this.modifiedseason);
      if (this.esport === 'NGS' && this.modifieddivision) params.set('division', this.modifieddivision);
      window.location.href = `${this.league.root}?${params.toString()}`;
    },
    // A hero row's wins and losses, from its games and win rate when the row has no counts
    heroRecord(row) {
      const wins = row.wins ?? Math.round(row.games_played * Number(row.win_rate) / 100);
      return { wins, losses: row.losses ?? row.games_played - wins };
    },
    getMatchHistoryUrl(){
      return this.ngsPageUrl('/Match/History');
    },
  }
}
</script>