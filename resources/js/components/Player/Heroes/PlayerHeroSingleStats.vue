<template>
  <div>
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="hero + ' stats for ' + battletag" -->
        <page-heading v-if="esport" heading="Hero Stats" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
        <page-heading v-else heading="Hero Stats" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
    <div class="w-full">
      <filters v-if="esport"
        :filters="filters"
        :isLoading="isLoading"
        :onFilter="() => applyFilter()"
        :onChange="ngsFilterChange"
        :extrafilters="[
          { text: 'Seasons', values: ngsFilterOptions?.seasons || [], defaultValue: ngsSeason },
          { text: 'Divisions', values: ngsFilterOptions?.divisions || [], defaultValue: ngsDivision, show: esport == 'NGS' },
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
      {{ $t('No :heroobject games in :league for these filters.', { heroobject: $heroName(heroobject), league: league.name }) }}
    </div>
    <div v-else-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">

      <panel-box>
      <div class="flex flex-col xl:flex-row gap-6">

        <!-- Hero overview, laid out like the profile's player card -->
        <panel-box :inner="true" class="!gap-8 xl:shrink-0">
          <h2 class="text-3xl font-bold">
            <hero-heading-select :heroes="heroes" :value="heroobject.id" textclass="" @hero-changed="changeHero($event)"></hero-heading-select>
          </h2>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="flex flex-col items-center justify-between gap-1 shrink-0">
              <hero-image-wrapper :rectangle="true" :hero="heroobject" :title="$heroName(heroobject)" size="large"></hero-image-wrapper>
              <div class="text-xl font-semibold leading-[1.2] text-subtext">{{ $t('Games:') }} <span class="text-header">{{ data.games_played.toLocaleString('en-US') }}</span></div>
            </div>

            <overview-stats class="flex-1 min-w-0 w-full" :win-rate="data.win_rate" :stats="overviewStats"></overview-stats>
          </div>

          <a href="#advanced-stats" class="text-header w-fit hover:underline" @click.prevent="scrollToAdvancedStats">{{ $t('See advanced stats below') }}<basil-icon name="arrow" class="rotate-90 text-sm ml-3"></basil-icon></a>

          <div class="flex flex-col gap-4 w-fit max-w-full">
            <div class="flex justify-between items-baseline gap-6">
              <h3 class="text-2xl font-bold">{{ $t('Maps') }}</h3>
              <a v-if="!esport" class="text-header whitespace-nowrap hover:underline" :href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Map?hero=' + encodeURIComponent(heroobject.name)">{{ $t('All Maps') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a>
            </div>
            <div class="flex flex-wrap gap-6 max-md:justify-center">
              <group-box v-for="group in mapGroups" :key="group.label" class="!m-0" :esport="esport" :playerlink="true" :text="group.label" :data="group.data"></group-box>
            </div>
          </div>

          <div class="flex flex-col gap-4">
            <h3 class="text-2xl font-bold">{{ $t('Maps played on :heroobject', { heroobject: $heroName(heroobject) }) }}</h3>
            <!-- Zero width so the list wraps to the card instead of widening it; tiles as in Maps above -->
            <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
              <a :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" v-for="(item, index) in data.map_data" :key="index">
                <map-image-wrapper :map="item.game_map" size="tile" :href="esport ? item.link : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" link-text="View Map">
                  <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
                </map-image-wrapper>
              </a>
            </div>
          </div>

          <div class="flex flex-col gap-4">
            <div class="flex justify-between items-baseline gap-3">
              <h3 class="text-2xl font-bold">{{ $t('Most Played Build') }}</h3>
              <a class="text-header whitespace-nowrap hover:underline" :href="getTalentPageUrl()">{{ $t('Talent Data') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a>
            </div>
            <tab-button v-if="!esport" class="self-start max-md:self-center" tab1text="All Seasons" tab2text="This Season" :ignoreclick="true" @tab-click="buildThisSeason = $event === 'right'" :overridedefaultside="buildThisSeason ? 'right' : 'left'"></tab-button>
            <div v-if="topBuild" class="flex flex-wrap items-center gap-x-6 gap-y-3 max-md:justify-center">
              <div class="flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                <talent-image-wrapper v-for="level in talentLevels" :key="level" :talent="topBuild[level]" size="tile"></talent-image-wrapper>
              </div>
              <div class="flex gap-6 max-md:justify-center max-md:text-center">
                <stat-box class="!m-0 !px-0 !min-w-0" :title="$t('Games')" :value="topBuild.games_played.toLocaleString('en-US')"></stat-box>
                <stat-box class="!m-0 !px-0 !min-w-0" :title="$t('Win Rate')" :value="topBuild.win_rate.toFixed(2) + '%'"></stat-box>
              </div>
            </div>
            <!-- Results cached before builds were added have no top_build at all -->
            <p v-else-if="topBuild === null" class="text-subtext">{{ $t('No complete talent builds :filters.', { filters: buildThisSeason ? $t('this season') : $t('for these filters') }) }}</p>
          </div>

        </panel-box>

        <div class="flex-1 flex flex-col gap-6 min-w-0">
          <!-- Esports: the teams they played it for, and the heroes they beat and lost to on it -->
          <panel-box v-if="esport && data.teams_played_for && data.teams_played_for.length" :inner="true" :title="$t('Teams Played For')">
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
          </panel-box>

          <panel-box v-if="esport && data.heroes_won_against && (data.heroes_won_against.length || data.heroes_lost_against.length)" :inner="true" :title="$t('Heroes Played Against')">
            <div class="flex flex-col gap-4">
              <group-box v-if="data.heroes_won_against.length" class="!m-0" :useinputforhover="true" :text="'Most Won Against'" :data="data.heroes_won_against.slice(0, 5)"></group-box>
              <group-box v-if="data.heroes_lost_against.length" class="!m-0" :useinputforhover="true" :text="'Most Lost Against'" :data="data.heroes_lost_against.slice(0, 5)"></group-box>
            </div>
          </panel-box>

          <player-party-size-panel v-if="!esport" :data="data" prefix="stack_size_" link-text="Party Stats" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Party?hero=' + heroobject.name"></player-party-size-panel>

          <player-mmr-panel v-if="!esport" :data="data" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/MMR'" :link-query="{ hero: heroobject.name }"></player-mmr-panel>

          <player-awards-panel v-if="!esport" class="flex-1" :link-query="{ hero: heroobject.name }" :battletag="battletag" :blizzid="blizzid" :region="region" :params="{ game_type: modifiedgametype, hero: heroobject.id, season: modifiedseason, game_map: modifiedgamemap, start_date: startdate, end_date: enddate }"></player-awards-panel>
        </div>
      </div>
      </panel-box>

      <player-season-win-rate-panel :data="data" :title="`${battletag} Win Rate Data Per Season with ${heroobject.name}`"></player-season-win-rate-panel>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="esport && data.latestGames && data.latestGames.length" :title="$t('Recent Matches')" link-text="Match History" :link-href="ngsPlayerUrl + '/Match/History' + ngsQuery" :divider="false">
        <div>
          <game-summary-header :esport="true"></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :esport="true" :esport-league="esport" :esport-series="esportfilters?.series" :tournament="esportfilters?.tournament" :data="item"></game-summary-box>
        </div>
      </panel-box>
      <panel-box v-else-if="!esport && data.latestGames" :title="$t('Recent Matches')" link-text="Match History" :link-href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Match/History?hero=' + encodeURIComponent(heroobject.name)" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="(item, index) in data.latestGames" :key="index" :data="item" :replay-viewer-allowed="replayViewerAllowed" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <panel-box id="advanced-stats" class="scroll-mt-4" :title="$t('Advanced Stats')">
        <div class="grid xl:grid-cols-2 gap-6 items-start">
          <div v-for="(column, columnIndex) in sectionColumns" :key="columnIndex" class="flex flex-col gap-6 min-w-0">
            <!-- Short tables side by side: their headers don't stick -->
            <table v-for="section in visibleSections(column)" :key="section.title" class="min-w-0 w-full responsive-table rounded-table max-md:text-xs [&_thead]:static">
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm w-[31%]">{{ $t(section.title) }}</th>
                  <th class="py-2 px-3 text-left text-sm w-[23%]">{{ $t('Total') }}</th>
                  <th class="py-2 px-3 text-left text-sm w-[23%]">{{ $t('Average') }}</th>
                  <th class="py-2 px-3 text-left text-sm w-[23%]">{{ $t('Maximum') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in section.visibleRows" :key="row.key">
                  <td class="py-2 px-3">{{ $t(row.label) }}</td>
                  <td class="py-2 px-3">{{ formatValue(row.key, data["sum_" + row.key]) }}</td>
                  <td class="py-2 px-3">{{ formatValue(row.key, data["avg_" + row.key], true) }}</td>
                  <td class="py-2 px-3">
                    <span v-if="data['max_' + row.key] && !esport" class="group/bt inline-flex items-center text-header whitespace-nowrap cursor-pointer" :title="$t('Navigate to this match')" @click="navigateToMaxStatMatch(row.key, data['max_' + row.key])"><span class="group-hover/bt:underline">{{ formatValue(row.key, data["max_" + row.key]) }}</span><basil-icon name="arrow-small" class="text-sm ml-3 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
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
      {{ $t('No data') }}
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading || asyncLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">
      <!-- The cards start where the loaded ones do; the message sits on a tab of the panel above them -->
      <panel-box class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true" :overrideimage="league?.loaderImage">{{ asyncLoading ? $t('This is taking longer than expected. A background task is computing your results. Please be patient.') : $t('Loading data...') }}</loading-component>
        <div class="flex flex-col xl:flex-row gap-6">
          <!-- Built from the loaded card's own parts (the same headings, links and boxes, empty), so nothing moves when the data arrives -->
          <panel-box :inner="true" class="!gap-8 xl:shrink-0">
            <h2 class="text-3xl font-bold">
              <hero-heading-select :heroes="heroes" :value="heroobject.id" textclass="" @hero-changed="changeHero($event)"></hero-heading-select>
            </h2>
            <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
              <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                <skeleton-box class="w-40 md:w-[14rem] h-[20.125rem] rounded"></skeleton-box>
                <skeleton-box class="h-[1.5rem] w-32"></skeleton-box>
              </div>
              <overview-stats class="flex-1 min-w-0 w-full" :loading="true" :count="8"></overview-stats>
            </div>
            <span class="skeleton-text w-fit">{{ $t('See advanced stats below') }}<basil-icon name="arrow" class="rotate-90 text-sm ml-3"></basil-icon></span>
            <div class="flex flex-col gap-4 w-fit max-w-full">
              <div class="flex justify-between items-baseline gap-6">
                <h3 class="text-2xl font-bold">{{ $t('Maps') }}</h3>
                <span v-if="!esport" class="skeleton-text whitespace-nowrap">{{ $t('All Maps') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
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
              <h3 class="text-2xl font-bold">{{ $t('Maps played on :heroobject', { heroobject: $heroName(heroobject) }) }}</h3>
              <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                <skeleton-box v-for="tile in 14" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
              </div>
            </div>
            <div class="flex flex-col gap-4">
              <div class="flex justify-between items-baseline gap-3">
                <h3 class="text-2xl font-bold">{{ $t('Most Played Build') }}</h3>
                <span class="skeleton-text whitespace-nowrap">{{ $t('Talent Data') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
              </div>
              <!-- The season toggle's size as a grey block -->
              <div v-if="!esport" class="relative self-start max-md:self-center">
                <tab-button class="invisible" tab1text="All Seasons" tab2text="This Season" :ignoreclick="true" aria-hidden="true"></tab-button>
                <skeleton-box class="absolute inset-0"></skeleton-box>
              </div>
              <div class="flex flex-wrap items-center gap-x-6 gap-y-3 max-md:justify-center">
                <div class="flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                  <skeleton-box v-for="level in 7" :key="level" class="w-16 h-16 rounded"></skeleton-box>
                </div>
                <div class="flex gap-6 max-md:justify-center max-md:text-center">
                  <div v-for="stat in [{ title: 'Games', value: '00' }, { title: 'Win Rate', value: '00.00%' }]" :key="stat.title" class="relative">
                    <stat-box class="!m-0 !px-0 !min-w-0 invisible" :title="$t(stat.title)" :value="stat.value" aria-hidden="true"></stat-box>
                    <skeleton-box class="absolute inset-0"></skeleton-box>
                  </div>
                </div>
              </div>
            </div>
          </panel-box>

          <div v-if="esport" class="flex-1 flex flex-col gap-6 min-w-0">
            <panel-box :inner="true" :title="$t('Teams Played For')">
              <skeleton-box v-for="index in 2" :key="index" class="h-14"></skeleton-box>
            </panel-box>
            <panel-box :inner="true" :title="$t('Heroes Played Against')">
              <div v-for="group in 2" :key="group" class="flex gap-3">
                <skeleton-box v-for="tile in 5" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
              </div>
            </panel-box>
          </div>
          <div v-else class="flex-1 flex flex-col gap-6 min-w-0">
            <player-party-size-panel :data="emptyPartyData" prefix="stack_size_" link-text="Party Stats" :skeleton="true"></player-party-size-panel>
            <player-mmr-panel :data="{}" :skeleton="true"></player-mmr-panel>
            <player-awards-panel class="flex-1" :placeholder="true" :battletag="battletag" :blizzid="blizzid" :region="region"></player-awards-panel>
          </div>
        </div>
      </panel-box>

      <panel-box :title="$t('Win Rate Per Season')">
        <!-- The season chart's shape: as wide as the panel, 3.1 times wider than tall -->
        <skeleton-box class="w-full aspect-[3.1]"></skeleton-box>
      </panel-box>

      <panel-box :title="$t('Recent Matches')" :link-text="esport ? null : 'Match History'" :skeleton="true" :divider="false">
        <div>
          <game-summary-header :esport="!!esport" :skeleton="true"></game-summary-header>
          <game-summary-skeleton v-for="index in 7" :key="index"></game-summary-skeleton>
        </div>
      </panel-box>

      <!-- The real tables with their titles and labels; grey blocks where the numbers load. Hero-specific rows only show once the data says they apply. -->
      <panel-box :title="$t('Advanced Stats')">
        <div class="grid xl:grid-cols-2 gap-6 items-start">
          <div v-for="(column, columnIndex) in sectionColumns" :key="columnIndex" class="flex flex-col gap-6 min-w-0">
            <table v-for="section in column.filter(section => section.rows.some(row => !row.heroSpecific))" :key="section.title" class="min-w-0 w-full responsive-table rounded-table max-md:text-xs [&_thead]:static">
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm w-[31%]"><span class="skeleton-text">{{ $t(section.title) }}</span></th>
                  <th v-for="heading in ['Total', 'Average', 'Maximum']" :key="heading" class="py-2 px-3 text-left text-sm w-[23%]"><span class="skeleton-text">{{ $t(heading) }}</span></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in section.rows.filter(row => !row.heroSpecific)" :key="row.key">
                  <td class="py-2 px-3"><span class="skeleton-text">{{ $t(row.label) }}</span></td>
                  <td v-for="cell in 3" :key="cell" class="py-2 px-3"><skeleton-box class="inline-block align-middle h-4 w-16"></skeleton-box></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </panel-box>
    </div>

    <match-lookup v-if="matchIsLoading" :text="matchLookupText"></match-lookup>
    <div v-if="matchNotFound" class="fixed bottom-4 max-md:bottom-24 left-1/2 -translate-x-1/2 bg-red text-white px-4 py-2 rounded z-50">
      {{ $t("Couldn't find the match for that value.") }}
    </div>
  </div>
</template>

<script>
  import { formatDuration } from '../../../utils/formatDuration';
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
      // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
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
      matchLookupText: '',
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
      // The party card with no games, for the skeleton: its labels and empty bars
      emptyPartyData() {
        const data = {};
        ['one', 'two', 'three', 'four', 'five'].forEach(key => { data[`stack_size_${key}_wins`] = 0; data[`stack_size_${key}_losses`] = 0; data[`stack_size_${key}_win_rate`] = 0; });
        return data;
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
        ];
      },
      mapGroups() {
        // Esports has no latest-played lists; its profile shows the lowest win rates there
        return [
          { label: 'Most Played', data: this.data.map_data_top_played.slice(0, 3) },
          { label: 'Highest Win Rate', data: this.data.map_data_top_win_rate.slice(0, 3) },
          this.esport
            ? { label: 'Lowest Win Rate', data: this.data.map_data_top_lowest_win_rate.slice(0, 3) }
            : { label: 'Latest Played', data: this.data.map_data_top_latest_played.slice(0, 3) },
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
    scrollToAdvancedStats() {
      document.getElementById('advanced-stats')?.scrollIntoView({ behavior: 'smooth' });
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
            const response = await this.$axios.post(`${this.league.api}/hero`, {
              ...this.league.params,
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
      async navigateToMaxStatMatch(stat, value){
        // Opened during the click so popup blockers allow it; pointed at the match once found
        const tab = window.open('', '_blank');
        this.matchLookupText = `Finding replay with max ${stat.replace(/_/g, ' ')} for hero ${this.hero}`;
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
          return;
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
          return formatDuration(value);
        }

        if (average && value < 1000) {
          return value.toLocaleString('en-US', { maximumFractionDigits: 2 });
        }
        return Math.round(value).toLocaleString('en-US');
      },
    }
  }
</script>