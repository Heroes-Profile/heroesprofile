<template>
  <div>
    <div class="flex max-md:flex-col max-w-[1920px] mx-auto">
      <div class="">
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="hero + ' stats for ' + battletag" -->
        <page-heading v-if="esport" heading="Hero Stats" :battletag="battletag" :esport="esport" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>
        <page-heading v-else heading="Hero Stats" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </div>
    <div class="max-w-[1920px] mx-auto px-4 md:px-8 w-full">
      <panel-box v-if="esport && ngsFilterOptions">
        <div class="filter-row justify-center">
          <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" @dropdown-closed="applyFilter" :trackclosure="true" :defaultValue="ngsSeason"></single-select-filter>
          <single-select-filter :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" @dropdown-closed="applyFilter" :trackclosure="true" :defaultValue="ngsDivision"></single-select-filter>
          <single-select-filter :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" @dropdown-closed="applyFilter" :trackclosure="true" :defaultValue="ngsTeam"></single-select-filter>
        </div>
      </panel-box>
      <panel-box v-else-if="!esport">
        <div class="filter-row">
          <multi-select-filter :values="filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="gametypedefault"></multi-select-filter>
          <multi-select-filter :values="filters.seasons" :text="'Season'" @input-changed="handleInputChange" :defaultValue="seasonselection"></multi-select-filter>
          <date-range-filter :startDate="startdate" :endDate="enddate" @input-changed="handleInputChange"></date-range-filter>
          <single-select-filter :values="gameMapWithAll" :text="'Game Map'" @input-changed="handleInputChange" :trackclosure="true" :defaultValue="'All'" :disabled="disableFilterInput"></single-select-filter>
          <custom-button class="font-semibold text-center h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :text="'Filter'" :alt="'Filter'" :color="'blue'" :disabled="disableFilterInput" :ignoreclick="true" @click="applyFilter"></custom-button>
        </div>
      </panel-box>
    </div>
    </div>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    
    <div v-if="data && esport && !data.games_played" class="flex items-center justify-center mt-8">
      No {{ heroobject.name }} games in NGS for these filters.
    </div>
    <div v-else-if="data" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">

      <panel-box>
      <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

        <!-- Hero overview, laid out like the profile's player card -->
        <panel-box :inner="true" class="!gap-8">
          <h2 class="text-3xl font-bold">
            <hero-heading-select :heroes="heroes" :value="heroobject.id" textclass="" @hero-changed="changeHero($event)"></hero-heading-select>
          </h2>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="shrink-0">
              <hero-image-wrapper :rectangle="true" :hero="heroobject" :title="heroobject.name" size="large"></hero-image-wrapper>
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
            <h3 class="text-2xl font-bold">Maps</h3>
            <div class="flex flex-wrap gap-6 max-md:justify-center">
              <group-box v-for="group in mapGroups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
            </div>
          </div>

          <details class="bg-surface-3 rounded text-sm w-fit max-w-full group open:w-full">
            <summary class="px-4 py-3 flex gap-4 justify-between items-center cursor-pointer list-none">
              <h3 class="text-lg">Maps played on {{ heroobject.name }}</h3>
              <i class="fas fa-chevron-down transition-transform group-open:rotate-180"></i>
            </summary>
            <!-- Zero width so the list wraps to the card instead of widening it -->
            <div class="w-0 min-w-full flex flex-wrap gap-2 max-md:justify-center px-4 pb-4">
              <a :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" v-for="(item, index) in data.map_data" :key="index">
                <map-image-wrapper :map="item.game_map" size="big" :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" link-text="View Map">
                  <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate + '%'" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
                </map-image-wrapper>
              </a>
            </div>
          </details>

          <div class="flex flex-col gap-4">
            <div class="flex justify-between items-baseline gap-3">
              <h3 class="text-2xl font-bold">Most Played Build</h3>
              <a class="text-header whitespace-nowrap hover:underline" :href="getTalentPageUrl()">Talent Data<i class="fas fa-chevron-right text-sm ml-3"></i></a>
            </div>
            <tab-button v-if="!esport" class="self-start" tab1text="All Seasons" tab2text="This Season" :ignoreclick="true" @tab-click="buildThisSeason = $event === 'right'" :overridedefaultside="buildThisSeason ? 'right' : 'left'"></tab-button>
            <div v-if="topBuild" class="flex flex-wrap items-center gap-x-6 gap-y-3">
              <div class="flex flex-wrap gap-1.5">
                <talent-image-wrapper v-for="level in talentLevels" :key="level" :talent="topBuild[level]" size="talent"></talent-image-wrapper>
              </div>
              <div class="flex gap-6">
                <stat-box class="!m-0 !px-0 !min-w-0" title="Games" :value="topBuild.games_played.toLocaleString('en-US')"></stat-box>
                <stat-box class="!m-0 !px-0 !min-w-0" title="Win Rate" :value="topBuild.win_rate.toFixed(2) + '%'"></stat-box>
              </div>
            </div>
            <!-- Results cached before builds were added have no top_build at all -->
            <p v-else-if="topBuild === null" class="text-subtext">No complete talent builds {{ buildThisSeason ? 'this season' : 'for these filters' }}.</p>
          </div>

        </panel-box>

        <div class="flex flex-col gap-6 min-w-0">
          <!-- NGS: the teams they played it for, and the heroes they beat and lost to on it -->
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

          <panel-box v-if="esport && data.heroes_won_against && (data.heroes_won_against.length || data.heroes_lost_against.length)" :inner="true" title="Heroes Played Against">
            <div class="flex flex-col gap-4">
              <group-box v-if="data.heroes_won_against.length" class="!m-0" :useinputforhover="true" :text="'Most Won Against'" :data="data.heroes_won_against.slice(0, 5)"></group-box>
              <group-box v-if="data.heroes_lost_against.length" class="!m-0" :useinputforhover="true" :text="'Most Lost Against'" :data="data.heroes_lost_against.slice(0, 5)"></group-box>
            </div>
          </panel-box>

          <player-party-size-panel v-if="!esport" :data="data" prefix="stack_size_" link-text="Party Stats" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Party?hero=' + heroobject.name"></player-party-size-panel>

          <player-mmr-panel v-if="!esport" :data="data" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/MMR'" :link-query="{ hero: heroobject.name }"></player-mmr-panel>
        </div>
      </div>
      </panel-box>

      <player-season-win-rate-panel :data="data" :title="`${battletag} Win Rate Data Per Season with ${heroobject.name}`"></player-season-win-rate-panel>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="esport && data.latestGames && data.latestGames.length" title="Recent Matches" link-text="Match History" :link-href="ngsPlayerUrl + '/Match/History' + ngsQuery" :divider="false">
        <div>
          <game-summary-header :esport="true"></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :esport="true" :esport-league="esport" :data="item"></game-summary-box>
        </div>
      </panel-box>
      <panel-box v-else-if="!esport && data.latestGames" title="Recent Matches" link-text="Match History" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Match/History/?hero=' + heroobject.name" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :data="item" :replay-viewer-allowed="replayViewerAllowed" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <panel-box title="Advanced Stats">
        <div class="grid xl:grid-cols-2 gap-6 items-start">
          <div v-for="(column, columnIndex) in sectionColumns" :key="columnIndex" class="flex flex-col gap-6 min-w-0">
            <table v-for="section in visibleSections(column)" :key="section.title" class="min-w-0 w-full responsive-table max-md:text-xs">
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm w-[31%]">{{ section.title }}</th>
                  <th class="py-2 px-3 text-left text-sm w-[23%]">Total</th>
                  <th class="py-2 px-3 text-left text-sm w-[23%]">Average</th>
                  <th class="py-2 px-3 text-left text-sm w-[23%]">Maximum</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in section.visibleRows" :key="row.key">
                  <td class="py-2 px-3">{{ row.label }}</td>
                  <td class="py-2 px-3">{{ formatValue(row.key, data["sum_" + row.key]) }}</td>
                  <td class="py-2 px-3">{{ formatValue(row.key, data["avg_" + row.key], true) }}</td>
                  <td class="py-2 px-3">
                    <span v-if="data['max_' + row.key] && !esport" class="text-header whitespace-nowrap cursor-pointer hover:underline" title="Navigate to this match" @click="navigateToMaxStatMatch(row.key, data['max_' + row.key])">{{ formatValue(row.key, data["max_" + row.key]) }}<i class="fas fa-chevron-right text-sm ml-3"></i></span>
                    <template v-else>{{ formatValue(row.key, data["max_" + row.key]) }}</template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </panel-box>
    </div>
    <div v-else-if="typeof data === 'undefined'"  class="flex items-center justify-center">
      No data
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading || asyncLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 mt-8 space-y-8">
      <loading-component :compact="true" :textoverride="true" :overrideimage="esport ? '/images/NGS/no-image-clipped.png' : undefined">{{ asyncLoading ? 'This is taking longer than expected. A background task is computing your results. Please be patient.' : 'Loading data...' }}</loading-component>
      <panel-box>
        <div class="grid 2xl:grid-cols-[auto_minmax(0,1fr)] gap-6">
          <panel-box :inner="true" class="!gap-8 2xl:w-[780px]">
            <h2 class="text-3xl font-bold">{{ heroobject.name }}</h2>
            <div class="flex gap-8 max-sm:flex-col">
              <skeleton-box class="w-40 md:w-72 h-[23em] shrink-0 rounded"></skeleton-box>
              <div class="flex-1 flex flex-col gap-6">
                <skeleton-box class="h-[30px]"></skeleton-box>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                  <skeleton-box v-for="index in 10" :key="index" class="h-11"></skeleton-box>
                </div>
              </div>
            </div>
            <div class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">Maps</h3>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <div v-for="group in 3" :key="group" class="flex gap-3">
                  <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                </div>
              </div>
            </div>
            <div class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">Most Played Build</h3>
              <div class="flex flex-wrap gap-1.5">
                <skeleton-box v-for="level in 7" :key="level" class="w-[42px] h-[42px] !rounded"></skeleton-box>
              </div>
            </div>
          </panel-box>

          <div v-if="esport" class="flex flex-col gap-6 min-w-0">
            <panel-box :inner="true" title="Teams Played For">
              <skeleton-box v-for="index in 2" :key="index" class="h-14"></skeleton-box>
            </panel-box>
            <panel-box :inner="true" title="Heroes Played Against">
              <div v-for="group in 2" :key="group" class="flex gap-3">
                <skeleton-box v-for="tile in 5" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
              </div>
            </panel-box>
          </div>
          <div v-else class="flex flex-col gap-6 min-w-0">
            <panel-box :inner="true" title="Party Size Win Rates">
              <skeleton-box v-for="index in 5" :key="index" class="h-10"></skeleton-box>
            </panel-box>
            <panel-box :inner="true" title="Heroes Profile MMR">
              <skeleton-box v-for="index in 6" :key="index" class="h-9"></skeleton-box>
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

      <panel-box title="Advanced Stats">
        <div class="grid xl:grid-cols-2 gap-6 items-start">
          <div v-for="column in 2" :key="column" class="flex flex-col gap-6 min-w-0">
            <table-skeleton v-for="table in 2" :key="table" :bare="true" :rows="5" :columns="['text', 'text', 'text', 'text']"></table-skeleton>
          </div>
        </div>
      </panel-box>
    </div>

    <div v-if="matchIsLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
    <div v-if="matchNotFound" class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-red text-white px-4 py-2 rounded z-50">
      Couldn't find the match for that value.
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
      playerloadsetting: {
        type: [String, Boolean]
      },
      hero: String,
      heroobject: Object,
      heroes: Array,
      battletag: String,
      blizzid: {
        type: [String, Number]
      },
      region: Number,
      regionsmap: Object,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
      replayViewerAllowed: Boolean,
      analyticsCutover: String,
      replayDownloadBlocked: Boolean,
      // 'NGS' on the NGS player pages, with the season, division and team from the URL
      esport: String,
      esportfilters: Object,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        asyncLoading: false,
        modifiedgametype: null,
        modifiedseason: null,
        seasonselection: [],
        startdate: null,
        enddate: null,
        modifiedgamemap: null,
        data: null,
        disableFilterInput: null,
        matchIsLoading: false,
        matchNotFound: false,
        ngsSeason: this.esportfilters?.season ?? null,
        ngsDivision: this.esportfilters?.division ?? null,
        ngsTeam: this.esportfilters?.team ?? null,
        ngsFilterOptions: null,
// Most Played Build from this season's games only
        buildThisSeason: false,
        talentLevels: ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'],
        // Left and right columns on wide screens; heroSpecific rows hide when no game has them
        sectionColumns: [
          [
            {
              title: 'Damage',
              rows: [
                { label: 'Siege', key: 'siege_damage' },
                { label: 'Structure', key: 'structure_damage' },
                { label: 'Minion', key: 'minion_damage' },
                { label: 'Lane Merc. Damage', key: 'creep_damage' },
                { label: 'Summon', key: 'summon_damage', heroSpecific: true },
              ],
            },
            {
              title: 'Combat',
              rows: [
                { label: 'Kills', key: 'kills' },
                { label: 'Assists', key: 'assists' },
                { label: 'Takedowns', key: 'takedowns' },
                { label: 'Deaths', key: 'deaths' },
              ],
            },
            {
              title: 'Team Fight',
              rows: [
                { label: 'Teamfight Damage Taken', key: 'teamfight_damage_taken' },
                { label: 'Teamfight Hero Damage', key: 'teamfight_hero_damage' },
                { label: 'Teamfight Escapes', key: 'teamfight_escapes' },
                { label: 'Teamfight Healing', key: 'teamfight_healing', heroSpecific: true },
              ],
            },
            {
              title: 'Defense / Healing',
              rows: [
                { label: 'Healing', key: 'healing', heroSpecific: true },
                { label: 'Self Healing', key: 'self_healing', heroSpecific: true },
                { label: 'Clutch Heals', key: 'clutch_heals', heroSpecific: true },
                { label: 'Ally Protection', key: 'protection_allies', heroSpecific: true },
                { label: 'Crowd Control Enemies', key: 'time_cc_enemy_heroes', heroSpecific: true },
              ],
            },
          ],
          [
            {
              title: 'Player',
              rows: [
                { label: 'Regeneration Globes', key: 'regen_globes' },
                { label: 'Hero Damage', key: 'hero_damage' },
                { label: 'Physical Damage Done', key: 'physical_damage', heroSpecific: true },
                { label: 'Spell Damage Done', key: 'spell_damage', heroSpecific: true },
                { label: 'Damage Taken', key: 'damage_taken' },
                { label: 'Time Spent Dead', key: 'time_spent_dead' },
                { label: 'Enemy Silence Duration', key: 'silencing_enemies', heroSpecific: true },
                { label: 'Enemy Rooted Duration', key: 'rooting_enemies', heroSpecific: true },
                { label: 'Enemy Stunned Duration', key: 'stunning_enemies', heroSpecific: true },
                { label: 'Escapes', key: 'escapes' },
                { label: 'Vengeances', key: 'vengeance' },
                { label: 'Outnumbered Deaths', key: 'outnumbered_deaths' },
              ],
            },
            {
              title: 'Macro',
              rows: [
                { label: 'Experience Contribution', key: 'experience_contribution' },
                { label: 'Merc. Camp Captures', key: 'merc_camp_captures' },
                { label: 'Watch Tower Captures', key: 'watch_tower_captures' },
                { label: 'Team Exp.', key: 'meta_experience' },
                { label: 'Game Length', key: 'game_length' },
              ],
            },
            {
              title: 'Other',
              rows: [
                { label: 'Town Kills', key: 'town_kills' },
                { label: 'Kill Streak', key: 'highest_kill_streak' },
                { label: 'Multikills', key: 'multikill' },
              ],
            },
          ],
        ],

      }
    },
    created(){
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
      topBuild() {
        return this.buildThisSeason ? this.data?.top_build_season : this.data?.top_build;
      },
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
      mapGroups() {
        // NGS has no latest-played lists; its profile shows the lowest win rates there
        return [
          { label: 'Most Played', data: this.data.map_data_top_played.slice(0, 3) },
          { label: 'Highest Win Rate', data: this.data.map_data_top_win_rate.slice(0, 3) },
          this.esport
            ? { label: 'Lowest Win Rate', data: this.data.map_data_top_lowest_win_rate.slice(0, 3) }
            : { label: 'Latest Played', data: this.data.map_data_top_latest_played.slice(0, 3) },
        ];
      },
      ngsTeamOptions() {
        return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
      },
      ngsPlayerUrl() {
        return `/Esports/NGS/Player/${this.battletag}/${this.blizzid}`;
      },
      ngsQuery() {
        const params = new URLSearchParams();
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

        if (this.esport) {
          try{
            const response = await this.$axios.post("/api/v1/esports/ngs/player/hero", {
              battletag: this.battletag,
              blizz_id: this.blizzid,
              season: this.ngsSeason,
              division: this.ngsDivision,
              team: this.ngsTeam,
              hero: this.hero,
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
          const response = await this.$globalAsyncPost("/api/v1/player/heroes/single", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            start_date: this.startdate,
            end_date: this.enddate,
            game_map: this.modifiedgamemap,
            hero: this.hero,
            type: "single",
            page: "hero",
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
      changeHero(heroId) {
        const hero = this.heroes.find(h => h.id === heroId);
        window.location.href = this.esport
          ? `${this.ngsPlayerUrl}/Hero/${hero.name}${this.ngsQuery}`
          : `/Player/${this.battletag}/${this.blizzid}/${this.region}/Hero/${hero.name}`;
      },
      ngsFilterChange(eventPayload) {
        const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
        if (field) {
          this[field] = eventPayload.value;
        }
        history.pushState({}, '', window.location.pathname + this.ngsQuery);
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
      async navigateToMaxStatMatch(stat, value){
        // Opened during the click so popup blockers allow it; pointed at the match once found
        const tab = window.open('', '_blank');
        this.matchIsLoading = true;
        try{
          const response = await this.$axios.post("/api/v1/player/find/max/stat/match", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            start_date: this.startdate,
            end_date: this.enddate,
            game_map: this.modifiedgamemap,
            hero: this.hero,
            page: "hero",
            stat: 'max_' + stat,
            value: Math.round(value),
            type: "all",
          });
          // A replay id, or null (or a failure body) when no match holds that value.
          const replayID = Number(response.data);

          if (Number.isInteger(replayID) && replayID > 0 && tab) {
            tab.location.href = `/Match/Single/${replayID}`;
          } else if (Number.isInteger(replayID) && replayID > 0) {
            window.location.href = `/Match/Single/${replayID}`;
          } else {
            tab?.close();
            this.showMatchNotFound();
          }
        }catch(error){
          tab?.close();
          this.showMatchNotFound();
        }
        this.matchIsLoading = false;
      },
      showMatchNotFound() {
        this.matchNotFound = true;
        setTimeout(() => {
          this.matchNotFound = false;
        }, 3000);
      },
      getTalentPageUrl(){
        if (this.esport) {
          return `${this.ngsPlayerUrl}/Talents/${this.hero}${this.ngsQuery}`;
        }
        return "/Player/" + this.battletag + "/" + this.blizzid + "/" + this.region  + "/Talents/" + this.hero;
      },
      visibleSections(column){
        return column
          .map(section => ({ ...section, visibleRows: section.rows.filter(row => !row.heroSpecific || this.data['max_' + row.key] > 0) }))
          .filter(section => section.visibleRows.length);
      },
      // Whole numbers, except averages under 1,000 which keep up to 2 decimals
      formatValue(key, value, average = false){
        value = value || 0;

        if (key == "game_length") {
          let seconds = Math.floor(value);
          const parts = [];
          [[86400, 'd'], [3600, 'h'], [60, 'm']].forEach(([size, unit]) => {
            const count = Math.floor(seconds / size);
            seconds %= size;
            if (count || parts.length) {
              parts.push(count + unit);
            }
          });
          return [...parts, seconds + 's'].join(', ');
        }

        if (average && value < 1000) {
          return value.toLocaleString('en-US', { maximumFractionDigits: 2 });
        }
        return Math.round(value).toLocaleString('en-US');
      },
    }
  }
</script>