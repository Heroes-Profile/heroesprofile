<template>
  <div class="match-page">
    <div v-if="data">
      <!-- The map's art behind the heading and the team cards, fading into the page -->
      <div class="relative">
        <!-- Starts behind the recent-players row above, so the art runs up to the nav -->
        <div class="absolute inset-x-0 bg-cover bg-center opacity-30 [mask-image:linear-gradient(to_bottom,black_40%,transparent)] pointer-events-none" :style="{ top: -navOffset + 'px', height: `calc(40rem + ${navOffset}px)`, backgroundImage: `url('/images/maps/background/header-${data.game_map.sanitized_map_name}.jpg')` }"></div>

        <div class="relative max-w-[120rem] mx-auto px-4 md:px-8 pt-10 pb-6 flex flex-wrap items-end justify-between gap-6">
          <div class="flex flex-col gap-2 min-w-0">
            <h1 class="text-4xl md:text-6xl font-bold">{{ $mapName(data.game_map) }}</h1>
            <p class="text-base md:text-2xl uppercase text-subtext">{{ [esport ? getEsportTitle() : $t(data.game_type), gameLength, formatDate(data.game_date), $t('Match :id', { id: replayid })].filter(Boolean).join(' · ') }}</p>
          </div>
          <div class="flex flex-wrap gap-3">
            <!-- Replay viewer: season games; needs a Battle.net login, like Download Replay -->
            <custom-button v-if="isWatchableGame && replayViewerAllowed" class="font-semibold" color="teal" :href="'/Analytics/Match/Single/' + replayid + '/Replay'" text="Watch Replay" alt="Watch Replay"></custom-button>
            <login-required-hover v-else-if="isWatchableGame" message="You need to be logged in with Battle.net to watch replays."><span class="btn bg-teal font-semibold opacity-60 cursor-pointer">{{ $t('Watch Replay') }}</span></login-required-hover>
            <!-- Download requires login for all (esport and non-esport); blocked battletags get no control -->
            <custom-button v-if="canDownload && user && !data.replay_download_blocked" class="font-semibold max-md:hidden" color="blue" text="Download Replay" alt="Download Replay" :ignoreclick="true" @click="downloadReplay(data, replayid)"></custom-button>
            <login-required-hover v-else-if="canDownload && !user" class="max-md:hidden" message="You need to be logged in to download replays."><span class="btn bg-blue font-semibold opacity-60 cursor-pointer">{{ $t('Download Replay') }}</span></login-required-hover>
          </div>
        </div>

        <!-- The two teams: result, bans, players and their numbers -->
        <div class="relative max-w-[120rem] mx-auto px-4 md:px-8 grid xl:grid-cols-2 gap-6">
          <div v-for="team in [0, 1]" :key="team" :class="['rounded bg-surface border-t-4 p-5 md:p-6 flex flex-col gap-5 min-w-0', data.winner == team ? 'border-win' : 'border-loss']">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <h2 class="text-2xl font-bold truncate">{{ teamTitle(team) }}</h2>
                <span :class="['shrink-0 rounded-md px-2 py-0.5 text-xs font-bold uppercase', data.winner == team ? 'bg-win/20 text-win' : 'bg-loss/20 text-loss']">{{ data.winner == team ? $t('Victory') : $t('Defeat') }}</span>
              </div>
              <div v-if="showBans" class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase text-subtext">{{ $t('Bans') }}</span>
                <template v-for="(item, index) in data.replay_bans[team]" :key="index">
                  <hero-image-wrapper v-if="item.hero !== 0" :hero="item.hero" size="small" class="opacity-70"></hero-image-wrapper>
                  <span v-else class="w-10 h-10 shrink-0 rounded-full border-2 border-dashed border-surface-4" :title="$t('No Ban')"></span>
                </template>
              </div>
            </div>

            <div class="grid grid-cols-5 gap-2">
              <div v-for="(player, index) in partyOrder(team)" :key="index" class="flex flex-col items-center gap-1.5 text-center min-w-0">
                <a :href="getPlayerProfileLink(player, false)" class="group/bt flex flex-col items-center gap-1.5 max-w-full">
                <!-- The player's details on hover, as the old team boxes had -->
                <hero-image-wrapper :hero="player.hero" size="big" :popup-hoverable="true" :award="player.match_award" :winner="data.winner == team" :hpowner="player.hp_owner" :party="player.party" :ispatreon="player.patreon_subscriber" :voideye="!esport && hasVoidEye(player.blizz_id, player.region)">
                  <image-hover-box v-if="player.check" :title="$heroName(player.hero)"></image-hover-box>
                  <image-hover-box v-else-if="esport"
                    :title="$heroName(player.hero)"
                    :paragraph-one="$t('Played by') + ': ' + player.battletag"
                    :paragraph-six="$t('Hero Level') + ': ' + player.hero_level"
                  ></image-hover-box>
                  <image-hover-box v-else
                    :title="$heroName(player.hero)"
                    :played-by="player.battletag"
                    :paragraph-two="$t('Account Level') + ': ' + player.account_level"
                    :paragraph-three="$t('HP Player MMR') + ': ' + player.player_mmr"
                    :paragraph-four="$t('HP Hero MMR') + ': ' + player.hero_mmr"
                    :paragraph-five="$t('HP Role MMR') + ': ' + player.role_mmr"
                    :paragraph-six="$t('Hero Level') + ': ' + player.hero_level"
                  ></image-hover-box>
                </hero-image-wrapper>
                <span class="relative max-w-full text-sm font-semibold"><span class="block truncate group-hover/bt:underline">{{ player.check ? $t('Hidden') : player.battletag }}</span><span v-if="!player.check" class="absolute left-full top-1/2 -translate-y-1/2 pl-1.5 opacity-0 group-hover/bt:opacity-100 transition-opacity"><basil-icon name="arrow-small" class="text-xs"></basil-icon></span></span>
                </a>
                <a :href="getPlayerProfileLink(player, true)" class="group/bt relative max-w-full text-xs text-subtext"><span class="block truncate group-hover/bt:underline">{{ $heroName(player.hero) }}</span><span v-if="!player.check" class="absolute left-full top-1/2 -translate-y-1/2 pl-1.5 opacity-0 group-hover/bt:opacity-100 transition-opacity"><basil-icon name="arrow-small" class="text-xs"></basil-icon></span></a>
              </div>
            </div>

            <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
              <stat-box v-for="stat in teamStats(team)" :key="stat.title" class="!m-0 !min-w-0" :title="stat.title" :value="stat.value"></stat-box>
            </div>

            <div v-if="esport && esport != 'Other' && mapBans(team).length" class="flex items-center gap-3">
              <span class="text-xs font-semibold uppercase text-subtext">{{ $t('Map Bans') }}</span>
              <map-image-wrapper v-for="map in mapBans(team)" :key="map.name" :map="map" size="small">
                <image-hover-box :title="$mapName(map)"></image-hover-box>
              </map-image-wrapper>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 pb-10 flex flex-col gap-6">
        <panel-box v-if="esport && esport != 'Other'" :title="$t('Series Games')" :divider="false">
          <table class="min-w-0 w-full rounded-table">
            <thead>
              <tr>
                <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Match ID') }}</th>
                <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Round') }}</th>
                <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Game') }}</th>
                <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Game Map') }}</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr v-for="(row, index) in data.match_games" :key="index">
                <td width="25%"><a class="group/bt inline-flex items-center" :href="`/Esports/${esport}/Match/Single/${row.replayID}${tournament ? '?tournament=' + tournament : ''}`"><span class="group-hover/bt:underline">{{ row.replayID }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a></td>
                <td width="25%">{{ row.round }}</td>
                <td width="25%">{{ row.game }}</td>
                <td width="25%"><map-image-wrapper size="small" :map="row.game_map"><image-hover-box :title="$mapName(row.game_map)"></image-hover-box></map-image-wrapper></td>
              </tr>
            </tbody>
          </table>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

        <!-- Match Scores: every player, grouped by team until a column is sorted; each column's best value highlighted -->
        <panel-box :title="$t('Match Scores')" :subtitle="$t('Best value in each column is highlighted.')" :divider="false">
          <template #right>
            <!-- Same rules as Watch Replay -->
            <a v-if="isWatchableGame && replayViewerAllowed" class="ml-auto self-center text-sm font-semibold text-teal hover:underline" :href="'/Analytics/Match/Single/' + replayid + '/Replay#analytic-performance'">{{ $t('How each player played') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
            <login-required-hover v-else-if="isWatchableGame" class="ml-auto self-center" message="You need to be logged in with Battle.net to see how each player played."><span class="text-sm opacity-60 cursor-pointer">{{ $t('How each player played') }}</span></login-required-hover>
          </template>
          <scroll-hint-container>
            <table class="w-full min-w-[60rem] rounded-table sticky-first-column">
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm leading-4">{{ $t('Player') }}</th>
                  <th v-for="column in scoreColumns" :key="column.key" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer whitespace-nowrap" :class="[column.width, { 'bg-teal text-white': scoreSort === column.key }]" @click="sortScores(column.key)">
                    {{ $t(column.label) }}<span v-if="column.key === 'total_rank'" class="inline-flex align-middle ml-1" @click.stop><round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true"><slot><p class="text-sm p-2 text-left font-normal whitespace-normal">{{ $t('Heroes Profile Score is a match based analysis ranking showing how a player performed in the match compared to other players in the same match. 100 would be a perfect match with most MVPs hovering between 70-75.') }}</p></slot></round-image></span><sort-indicator :column="column.key" :sort-key="scoreSort || 'total_rank'" :sort-dir="scoreSortDir"></sort-indicator>
                  </th>
                </tr>
              </thead>
              <tbody class="text-center">
                <template v-for="group in scoreGroups" :key="group.key">
                  <tr v-if="group.label">
                    <td :colspan="scoreColumns.length + 1" class="py-2 px-3 text-left text-xs font-semibold uppercase text-subtext"><span class="sticky left-3"><span :class="['inline-block w-1.5 h-1.5 rounded-full mr-2 align-middle', group.won ? 'bg-win' : 'bg-loss']"></span>{{ group.label }}</span></td>
                  </tr>
                  <tr v-for="(player, index) in group.players" :key="index">
                    <td class="py-2 px-3 text-left">
                      <span class="inline-flex items-center gap-2.5 min-w-0">
                        <a :href="getPlayerProfileLink(player, false)" class="shrink-0"><hero-image-wrapper :hero="player.hero" size="small" :excludehover="true"></hero-image-wrapper></a>
                        <span class="flex flex-col items-start min-w-0">
                          <a :href="getPlayerProfileLink(player, false)" class="group/bt inline-flex items-center max-w-full font-semibold"><span class="truncate group-hover/bt:underline">{{ player.check ? $t('Hidden') : player.battletag }}</span><basil-icon v-if="!player.check" name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                          <a :href="getPlayerProfileLink(player, true)" class="group/bt inline-flex items-center max-w-full text-subtext text-xs"><span class="truncate group-hover/bt:underline">{{ $heroName(player.hero) }}</span><basil-icon v-if="!player.check" name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                        </span>
                      </span>
                    </td>
                    <td v-for="column in scoreColumns" :key="column.key" :class="{ 'font-semibold text-teal': isBestScore(column, player) }">{{ formatValue(column.value(player)) }}</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </scroll-hint-container>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

        <!-- Draft Order: each pick and ban in turn, its team's colour along the bottom -->
        <panel-box v-if="data.draft_order && data.draft_order.length > 0" :title="$t('Draft Order')" :subtitle="firstPickTeam !== null ? $t(':team had first pick. Faded heroes were banned.', { team: teamTitle(firstPickTeam) }) : $t('Faded heroes were banned.')" :divider="false">
          <div class="grid grid-cols-4 sm:grid-cols-8 2xl:grid-cols-[repeat(16,minmax(0,1fr))] gap-2">
            <div v-for="(row, index) in draftRows" :key="index" :class="['flex flex-col items-center gap-1.5 rounded bg-surface-1 border-b-4 px-1 pt-2 pb-2.5', row.team === null ? 'border-gray-dark' : data.winner == row.team ? 'border-win' : 'border-loss']">
              <span class="text-xs text-subtext">{{ index + 1 }}</span>
              <hero-image-wrapper v-if="row.hero !== 'No Pick'" :hero="row.hero" size="medium" :class="{ 'opacity-40 grayscale': row.type == 0 }"></hero-image-wrapper>
              <round-image v-else size="medium" :image="'/images/talents/no-image.png'"><h2>{{ $t('No Pick') }}</h2></round-image>
              <span class="text-[0.625rem] font-bold uppercase">{{ row.type == 0 ? $t('Ban') : $t('Pick') }}</span>
            </div>
          </div>
          <p class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-subtext">
            <span v-for="team in [0, 1]" :key="team" class="flex items-center gap-2"><span :class="['w-4 h-1 rounded-full', data.winner == team ? 'bg-win' : 'bg-loss']"></span>{{ teamTitle(team) }}</span>
          </p>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

        <!-- Talents: a row per player, both teams side by side; the build code copies from the row -->
        <panel-box :title="$t('Talents')" :subtitle="$t('Talents picked at levels 1, 4, 7, 10, 13, 16 and 20. Empty slots were not reached.')" :divider="false">
          <div class="grid xl:grid-cols-2 gap-6">
            <div v-for="team in [0, 1]" :key="team" class="flex flex-col gap-2 min-w-0">
              <span class="text-xs font-semibold uppercase text-subtext"><span :class="['inline-block w-1.5 h-1.5 rounded-full mr-2 align-middle', data.winner == team ? 'bg-win' : 'bg-loss']"></span>{{ teamTitle(team) }} · {{ data.winner == team ? $t('Victory') : $t('Defeat') }}</span>
              <div v-for="(player, index) in data.players[team]" :key="index" :class="['group/row flex flex-wrap md:flex-nowrap items-center gap-4 rounded px-4 py-3 transition-colors hover:bg-white/10', index % 2 ? 'bg-surface-2' : 'bg-surface-1']">
                <span class="flex items-center gap-4 min-w-0 flex-1">
                  <a :href="getPlayerProfileLink(player, false)" class="shrink-0"><hero-image-wrapper :hero="player.hero" size="small" :excludehover="true"></hero-image-wrapper></a>
                  <span class="flex flex-col items-start gap-0.5 min-w-0">
                    <a :href="getPlayerProfileLink(player, false)" :class="['group/bt inline-flex items-center max-w-full font-bold', { 'text-subtext': player.check }]"><span class="truncate group-hover/bt:underline">{{ player.check ? $t('Hidden') : player.battletag }}</span><basil-icon v-if="!player.check" name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                    <a :href="getPlayerProfileLink(player, true)" class="group/bt inline-flex items-center max-w-full text-subtext text-[0.8125rem]"><span class="truncate group-hover/bt:underline">{{ $heroName(player.hero) }}</span><basil-icon v-if="!player.check" name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                  </span>
                </span>
                <build-code class="md:opacity-0 md:group-hover/row:opacity-100 md:focus-within:opacity-100 transition-opacity" :build="player.talents" :hero="player.hero" show="code" align="end"></build-code>
                <build-code :build="player.talents" :hero="player.hero" show="talents" align="end" :empty-slots="true"></build-code>
              </div>
            </div>
          </div>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false"></dynamic-banner-ad>

        <!-- HP MMR Change: before → after, and the change, for each player -->
        <panel-box v-if="!esport" :title="$t('HP MMR Change')" :subtitle="$t('Heroes Profile MMR before → after this match.')" :divider="false">
          <scroll-hint-container>
            <table class="w-full min-w-[50rem] rounded-table sticky-first-column">
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm leading-4">{{ $t('Player') }}</th>
                  <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Player MMR') }}</th>
                  <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Hero MMR') }}</th>
                  <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Role MMR') }}</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <template v-for="team in [0, 1]" :key="team">
                  <tr>
                    <td colspan="4" class="py-2 px-3 text-left text-xs font-semibold uppercase text-subtext"><span class="sticky left-3"><span :class="['inline-block w-1.5 h-1.5 rounded-full mr-2 align-middle', data.winner == team ? 'bg-win' : 'bg-loss']"></span>{{ teamTitle(team) }} · {{ data.winner == team ? $t('Victory') : $t('Defeat') }}</span></td>
                  </tr>
                  <tr v-for="(player, index) in data.players[team]" :key="index">
                    <td class="py-2 px-3 text-left">
                      <span class="inline-flex items-center gap-2.5 min-w-0">
                        <a :href="getPlayerProfileLink(player, false)" class="shrink-0"><hero-image-wrapper :hero="player.hero" size="small" :excludehover="true"></hero-image-wrapper></a>
                        <span class="flex flex-col items-start min-w-0">
                          <a :href="getPlayerProfileLink(player, false)" class="group/bt inline-flex items-center max-w-full font-semibold"><span class="truncate group-hover/bt:underline">{{ player.check ? $t('Hidden') : player.battletag }}</span><basil-icon v-if="!player.check" name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                          <a :href="getPlayerProfileLink(player, true)" class="group/bt inline-flex items-center max-w-full text-subtext text-xs"><span class="truncate group-hover/bt:underline">{{ $heroName(player.hero) }}</span><basil-icon v-if="!player.check" name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                        </span>
                      </span>
                    </td>
                    <td v-for="kind in ['player', 'hero', 'role']" :key="kind">
                      <span v-if="player.check" class="text-subtext">{{ $t('Hidden') }}</span>
                      <span v-else class="inline-flex items-center gap-2 whitespace-nowrap">
                        <span class="text-subtext">{{ Math.round(player[kind + '_mmr'] - player[kind + '_change']) }}</span>
                        <span class="text-subtext">→</span>
                        <span class="font-semibold">{{ player[kind + '_mmr'] }}</span>
                        <span :class="['text-xs', player[kind + '_change'] >= 0 ? 'text-win' : 'text-loss']">{{ (player[kind + '_change'] > 0 ? '+' : '') + player[kind + '_change'] }}</span>
                      </span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </scroll-hint-container>
        </panel-box>

        <panel-box v-if="data.experience_breakdown" :title="$t('Experience')" :subtitle="$t('Team experience over the course of the match.')" :divider="false" class="overflow-x-clip">
          <dual-line-chart :data="data.experience_breakdown" :winner="data.winner"></dual-line-chart>
        </panel-box>

        <dynamic-banner-ad :patreon-user="patreonUser" :index="5" :mobile-override="false"></dynamic-banner-ad>

        <!-- Advanced Stats: one team at a time, a category at a time; the search looks through every category -->
        <panel-box :title="$t('Advanced Stats')" :subtitle="$t('Detailed stats per player, grouped by category.')" :divider="false">
          <template #right>
            <tab-button class="ml-auto self-start" :small="true" :tab1text="teamTitle(0)" :tab2text="teamTitle(1)" :ignoreclick="true" @tab-click="side => advancedTeam = side === 'left' ? 0 : 1"></tab-button>
          </template>
          <div class="flex flex-wrap items-center justify-between gap-3">
            <tab-button :small="true" :dark="true" :tabs="advancedSections.map(section => ({ key: section.title, text: section.title }))" :overridedefaultside="advancedCategory" :ignoreclick="true" :class="{ 'opacity-50': statSearch.trim() }" @tab-click="key => { advancedCategory = key; statSearch = ''; }"></tab-button>
            <search-input v-model="statSearch" :placeholder="$t('Search stats')" class="w-full sm:w-[18.125rem]"></search-input>
          </div>
          <scroll-hint-container>
            <table class="w-full min-w-[50rem] rounded-table sticky-first-column">
              <thead>
                <tr>
                  <th class="py-2 px-3 text-left text-sm leading-4">{{ $t('Stat') }}</th>
                  <th v-for="(player, index) in data.players[advancedTeam]" :key="index" class="py-2 px-3 text-center text-sm leading-4">
                    <span class="inline-flex items-center gap-2"><hero-image-wrapper :hero="player.hero" size="small" :excludehover="true"></hero-image-wrapper>{{ player.check ? $t('Hidden') : player.battletag }}</span>
                  </th>
                </tr>
              </thead>
              <tbody class="text-center">
                <tr v-for="row in shownAdvancedRows" :key="row.key">
                  <td class="py-2 px-3 text-left font-semibold">{{ $t(row.label) }}<span v-if="statSearch.trim()" class="ml-2 text-xs font-normal text-subtext">{{ $t(row.category) }}</span></td>
                  <td v-for="(player, index) in data.players[advancedTeam]" :key="index">{{ row.key === 'time_spent_dead' ? formatSeconds(player.score[row.key]) : formatValue(player.score[row.key]) }}</td>
                </tr>
                <tr v-if="!shownAdvancedRows.length">
                  <td :colspan="data.players[advancedTeam].length + 1" class="py-4 text-subtext">{{ $t('No stats match ":search".', { search: statSearch }) }}</td>
                </tr>
                <!-- Blank rows up to the longest category, so the box keeps its height between tabs -->
                <tr v-for="n in advancedFillerRows" :key="'filler' + n" class="invisible" aria-hidden="true">
                  <td class="py-2 px-3">&nbsp;</td>
                </tr>
              </tbody>
            </table>
          </scroll-hint-container>
        </panel-box>
      </div>
    </div>
    <!-- The page's layout with grey blocks while it loads -->
    <match-skeleton v-else-if="isLoading" :overrideimage="esport ? getLoadingImage() : undefined"></match-skeleton>
  </div>
</template>

<script>
  import { formatGameDate } from '../utils/formatGameDate';
  import { hasVoidEye } from '../voidEye';
  import moment from 'moment-timezone';

  export default {
    name: 'SingleMatch',
    components: {
    },
    props: {
      esport: String,
      series: String,
      replayid: Number,
      user: Object,
      patreonUser: Boolean,
      tournament: String,
      // Replay viewer: any Battle.net login, season games only
      replayViewerAllowed: { type: Boolean, default: false },
      analyticsCutover: { type: String, default: null },
    },
    data(){
      return {
        windowWidth: window.innerWidth,
        // The recent-players row's height, so the map art can start behind it
        navOffset: 0,
        cancelTokenSource: null,
        isLoading: false,
        userTimezone: moment.tz.guess(),
        data: null,
        // Match Scores: grouped by team until a column is clicked
        scoreSort: null,
        scoreSortDir: 'desc',
        // Advanced Stats: the team shown, its category, and a search across every category
        advancedTeam: 0,
        advancedCategory: 'Combat',
        statSearch: '',
        // The design's six categories; a stat sits in one
        sections: [
        {
          title: 'Combat',
          rows: [
            { label: 'Kills', key: 'kills' },
            { label: 'Assists', key: 'assists' },
            { label: 'Takedowns', key: 'takedowns' },
            { label: 'Deaths', key: 'deaths' },
            { label: 'Hero Damage', key: 'hero_damage' },
            { label: 'Physical Damage Done', key: 'physical_damage' },
            { label: 'Spell Damage Done', key: 'spell_damage' },
            { label: 'Damage Taken', key: 'damage_taken' },
            { label: 'Time Spent Dead', key: 'time_spent_dead' },
            { label: 'Escapes', key: 'escapes' },
            { label: 'Vengeances', key: 'vengeance' },
            { label: 'Outnumbered Deaths', key: 'outnumbered_deaths' },
            ],
        },
        {
          title: 'Siege',
          rows: [
            { label: 'Siege Damage', key: 'siege_damage' },
            { label: 'Structure Damage', key: 'structure_damage' },
            { label: 'Minion Damage', key: 'minion_damage' },
            { label: 'Lane Merc. Damage', key: 'creep_damage' },
            { label: 'Summon Damage', key: 'summon_damage' },
            ],
        },
        {
          title: 'Macro',
          rows: [
            { label: 'Experience Contribution', key: 'experience_contribution' },
            { label: 'Merc. Camp Captures', key: 'merc_camp_captures' },
            { label: 'Watch Tower Captures', key: 'watch_tower_captures' },
            { label: 'Team Exp.', key: 'meta_experience' },
            ],
        },
        {
          title: 'Team Fight',
          rows: [
            { label: 'Teamfight Damage Taken', key: 'teamfight_damage_taken' },
            { label: 'Teamfight Hero Damage', key: 'teamfight_hero_damage' },
            { label: 'Teamfight Escapes', key: 'teamfight_escapes' },
            { label: 'Teamfight Healing', key: 'teamfight_healing' },
            ],
        },
        {
          title: 'Defense/Healing',
          rows: [
            { label: 'Healing', key: 'healing' },
            { label: 'Self Healing', key: 'self_healing' },
            { label: 'Clutch Heals', key: 'clutch_heals' },
            { label: 'Ally Protection', key: 'protection_allies' },
            { label: 'Crowd Control Enemies', key: 'time_cc_enemy_heroes' },
            { label: 'Enemy Silence Duration', key: 'silencing_enemies' },
            { label: 'Enemy Rooted Duration', key: 'rooting_enemies' },
            { label: 'Enemy Stunned Duration', key: 'stunning_enemies' },
            ],
        },
        {
          title: 'Other',
          rows: [
            { label: 'Regeneration Globes', key: 'regen_globes' },
            { label: 'Town Kills', key: 'town_kills' },
            { label: 'Kill Streak', key: 'highest_kill_streak' },
            { label: 'Multikills', key: 'multikill' },
            ],
        },
        ],
      }
    },
    created(){
      this.getData();
    },
    mounted() {
      this.measureNav();
      window.addEventListener('resize', this.measureNav);
    },
    beforeUnmount() {
      window.removeEventListener('resize', this.measureNav);
    },
    computed: {
      advancedSections() {
        return this.sections;
      },
      // The category's stats; while searching, matching stats from every category, each named with its category
      shownAdvancedRows() {
        const search = this.statSearch.trim().toLowerCase();
        if (search) {
          return this.sections.flatMap(section => section.rows
            .filter(row => row.label.toLowerCase().includes(search) || this.$t(row.label).toLowerCase().includes(search))
            .map(row => ({ ...row, category: section.title })));
        }
        return this.sections.find(section => section.title === this.advancedCategory)?.rows ?? [];
      },
      advancedFillerRows() {
        const longest = Math.max(...this.sections.map(section => section.rows.length));
        return Math.max(0, longest - Math.max(this.shownAdvancedRows.length, 1));
      },
      canDownload() {
        return this.data.downloadable || (this.esport && (this.esport == 'CCL' || this.esport == 'Other'));
      },
      // "21 minutes 5 seconds" as "21:05"
      gameLength() {
        const text = String(this.data.game_length ?? '');
        const minutes = text.match(/(\d+)\s*minute/);
        const seconds = text.match(/(\d+)\s*second/);
        if (!minutes && !seconds) {
          return text;
        }
        return (minutes ? minutes[1] : '0') + ':' + String(seconds ? seconds[1] : 0).padStart(2, '0');
      },
      // Match Scores' columns; deaths are best low
      scoreColumns() {
        return [
          { key: 'total_rank', label: this.$t('HP Rating'), value: player => player.total_rank, width: 'w-[150px]' },
          { key: 'kills', label: this.$t('Kills'), value: player => player.score.kills, width: 'w-[110px]' },
          { key: 'takedowns', label: this.$t('Takedowns'), value: player => player.score.takedowns, width: 'w-[130px]' },
          { key: 'deaths', label: this.$t('Deaths'), value: player => player.score.deaths, lowest: true, width: 'w-[110px]' },
          { key: 'siege_damage', label: this.$t('Siege Dmg'), value: player => player.score.siege_damage, width: 'w-[160px]' },
          { key: 'hero_damage', label: this.$t('Hero Dmg'), value: player => player.score.hero_damage, width: 'w-[160px]' },
          { key: 'total_healing', label: this.$t('Healing'), value: player => player.score.total_healing, width: 'w-[160px]' },
          { key: 'damage_taken', label: this.$t('Dmg Taken'), value: player => player.score.damage_taken || 0, width: 'w-[160px]' },
          { key: 'experience_contribution', label: this.$t('XP Contrib.'), value: player => player.score.experience_contribution, width: 'w-[160px]' },
        ];
      },
      // Each column's best value over all ten players
      bestScores() {
        const players = [...this.data.players[0], ...this.data.players[1]];
        return Object.fromEntries(this.scoreColumns.map(column => {
          const values = players.map(player => Number(column.value(player)) || 0);
          return [column.key, column.lowest ? Math.min(...values) : Math.max(...values)];
        }));
      },
      scoreGroups() {
        // Unsorted: by team, each team by HP Rating
        if (!this.scoreSort) {
          return [0, 1].map(team => ({ key: team, label: this.teamTitle(team) + ' · ' + (this.data.winner == team ? this.$t('Victory') : this.$t('Defeat')), won: this.data.winner == team, players: [...this.data.players[team]].sort((a, b) => (Number(b.total_rank) || 0) - (Number(a.total_rank) || 0)) }));
        }
        const column = this.scoreColumns.find(item => item.key === this.scoreSort);
        const players = [...this.data.players[0], ...this.data.players[1]]
          .sort((a, b) => (Number(column.value(b)) || 0) - (Number(column.value(a)) || 0));
        return [{ key: 'sorted', label: null, players: this.scoreSortDir === 'desc' ? players : players.reverse() }];
      },
      // Each pick or ban with its team: a pick's from the team that played the hero, a ban's from that team's bans
      draftRows() {
        return (this.data.draft_order || []).map(row => {
          if (row.hero === 'No Pick') {
            return { ...row, team: null };
          }
          const heroId = row.hero.id;
          let team = null;
          if (row.type == 0) {
            team = [0, 1].find(side => (this.data.replay_bans?.[side] || []).some(ban => ban.hero && ban.hero.id === heroId)) ?? null;
          } else {
            team = [0, 1].find(side => this.data.players[side].some(player => player.hero && player.hero.id === heroId)) ?? null;
          }
          return { ...row, team };
        });
      },
      firstPickTeam() {
        return this.draftRows.find(row => row.type != 0 && row.team !== null)?.team ?? null;
      },
      // A non-esport game from the season start on: the ones the replay viewer can show
      isWatchableGame() {
        return !this.esport && !!this.analyticsCutover && !!this.data
          && this.data.game_date >= this.analyticsCutover;
      },
      // Non-draft modes still have replay_bans rows, all hero 0
      showBans(){
        if(!this.data.replay_bans || this.data.replay_bans.length == 0){
          return false;
        }
        const hasDraft = this.data.draft_order && this.data.draft_order.length > 0;
        const hasRealBan = Object.values(this.data.replay_bans).some(team => team.some(ban => ban.hero !== 0));
        return hasDraft || hasRealBan;
      },
    },
    watch: {
    },
    methods: {
      hasVoidEye,
      measureNav() {
        this.navOffset = document.getElementById('alt-account-nav')?.offsetHeight ?? 0;
      },
      // A party's players side by side, in the order the first of them appears
      partyOrder(team) {
        const players = this.data.players[team];
        const order = [];
        players.forEach(player => {
          if (order.includes(player)) {
            return;
          }
          order.push(...(player.party ? players.filter(other => other.party === player.party) : [player]));
        });
        return order;
      },
      teamTitle(team) {
        return this.esport ? this.getEsportTeamName(team) : this.$t(team == 0 ? 'Team 1' : 'Team 2');
      },
      // The numbers under a team's players
      teamStats(team) {
        const players = this.data.players[team];
        if (this.esport) {
          return [
            { title: this.$t('Team Level'), value: players[0].score.level },
            { title: this.$t('Takedowns'), value: this.getTakedownsValue(players) },
            { title: this.$t('Avg. Hero Level'), value: this.getAverageValue('avg_hero_level', players) },
          ];
        }
        return [
          { title: this.$t('Team Level'), value: players[0].score.level },
          { title: this.$t('Takedowns'), value: this.getTakedownsValue(players) },
          { title: this.$t('Avg. HP MMR'), value: this.getAverageValue('player_mmr', players) },
          { title: this.$t('Avg. HP Hero MMR'), value: this.getAverageValue('hero_mmr', players) },
          { title: this.$t('Avg. HP Role MMR'), value: this.getAverageValue('role_mmr', players) },
          { title: this.$t('Avg. Account Level'), value: this.getAverageValue('account_level', players) },
        ];
      },
      mapBans(team) {
        const bans = team == 0 ? this.data.map_bans?.team_zero_ban_data : this.data.map_bans?.team_one_ban_data;
        return bans ? [bans.map_ban_one, bans.map_ban_two].filter(Boolean) : [];
      },
      sortScores(key) {
        if (this.scoreSort === key) {
          // Second click flips it; a third goes back to the teams
          if (this.scoreSortDir === 'desc') {
            this.scoreSortDir = 'asc';
          } else {
            this.scoreSort = null;
            this.scoreSortDir = 'desc';
          }
          return;
        }
        this.scoreSort = key;
        this.scoreSortDir = 'desc';
      },
      isBestScore(column, player) {
        return (Number(column.value(player)) || 0) === this.bestScores[column.key];
      },
      formatSeconds(value) {
        return (value ? Number(value).toLocaleString('en-US') : 0) + 's';
      },
      resizeTables(){
        if(this.$el && this.$el.querySelectorAll('table')){
          const tables = this.$el.querySelectorAll('table');
          tables.forEach(table => {
            var tablewrapper = table.closest('.table-container');
            if(tablewrapper){
              // On mobile we prefer native horizontal scroll for readability.
              // Fixed-column tables scroll too, so every section keeps the same column widths.
              if (this.windowWidth < 1024 || table.classList.contains('fixed-column-table')) {
                table.style.transformOrigin = '';
                table.style.transform = '';
                tablewrapper.style.height = '';
                return;
              }

              const wrapperWidth = tablewrapper.clientWidth || this.windowWidth;
              const scale = Math.min(1, wrapperWidth / table.clientWidth);

              if (scale < 1) {
                table.style.transformOrigin = 'top left';
                table.style.transform = `scale(${scale})`;
                tablewrapper.style.height = (table.clientHeight * scale) + 'px';
              } else {
                // Never scale up; upscaling causes clipping on narrower layouts.
                table.style.transformOrigin = '';
                table.style.transform = '';
                tablewrapper.style.height = '';
              }
            }
          })
        }
      },
     async getData(){
      this.isLoading = true;
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$axios.post("/api/v1/match/single", {
          esport: this.esport,
          replayID: this.replayid,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if (response.data?.status === 'incomplete' || !response.data?.game_map) {
          window.location.href = '/';
          return;
        }

        this.data = response.data;
      }catch(error){
        if (error.response?.data?.status === 'incomplete') {
          window.location.href = '/';
          return;
        }
      }finally {
        this.cancelTokenSource = null; // Reset cancel token source
        this.isLoading = false;
        this.$nextTick(() => {
          if(this.windowWidth < 1500){
            this.resizeTables();
          }
        
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    getAverageValue(type, data) {
      if (!data || data.length === 0) {
        return 0;
      }

      const filteredData = data.filter(obj => obj.blizz_id !== null);

      let sum;
      if (type === "prev_player_mmr") {
        sum = filteredData.reduce((acc, curr) => acc + (curr.player_mmr - curr.player_change || 0), 0);
      } else if (type === "prev_hero_mmr") {
        sum = filteredData.reduce((acc, curr) => acc + (curr.hero_mmr - curr.hero_change || 0), 0);
      } else if (type === "prev_role_mmr") {
        sum = filteredData.reduce((acc, curr) => acc + (curr.role_mmr - curr.role_change || 0), 0);
      } else {
        sum = filteredData.reduce((acc, curr) => acc + (curr[type] || 0), 0);
      }

      const length = filteredData.length;
      
      const average = sum / length;
      
      return average.toFixed(0);
    },

    getEsportTeamName(team){
      if(!this.esport){
        return null;
      }
      let teamName = "";
        if(team == 0){
          teamName = this.data.team_names.team_one.team_name;
        }else{
          teamName = this.data.team_names.team_two.team_name;
        }
      return teamName;
    },

    getWinnerLoser(team, winner){


      if(this.esport){
        let first_pick = this.data.first_pick;

        if(winner == 0 && team == 0){
          if(first_pick == team){
            return "First Pick - Winner";
          }else{
            return "Map Pick - Winner";
          }
        }else if(winner == 1 && team == 0){
          if(first_pick == team){
            return "First Pick - Loser";
          }else{
            return "Map Pick - Loser";
          }
        }else if(winner == 0 && team == 1){
          if(first_pick == team){
            return "First Pick - Loser";
          }else{
            return "Map Pick - Loser";
          }
        }else if(winner == 1 && team == 1){
          if(first_pick == team){
            return "First Pick - Winner";
          }else{
            return "Map Pick - Winner";
          }
        }
      }


      if(winner == 0 && team == 0){
        return "Team 1 Winner";
      }else if(winner == 1 && team == 0){
        return "Team 1 Loser";
      }else if(winner == 1 && team == 1){
        return "Team 2 Winner";
      }else if(winner == 0 && team == 1){
        return "Team 2 Loser";
      }


    },
    formatDate(dateString) {
      return formatGameDate(dateString);
    },
    formatValue(value){
      return value ? value.toLocaleString('en-US') : 0;
    },
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }
    },
    downloadReplay(data, replayID){
      if(!this.user || (data && data.replay_download_blocked)){
        return;
      }
      // Identity comes from the session now, not the query string — this is a
      // route on this site rather than a call out to the API host.
      const params = new URLSearchParams();
      if(this.esport == "CCL"){
        params.set('esport', 'CCL');
      }else if(this.esport == "Other"){
        params.set('esport', 'Other');
      }
      const query = params.toString();
      window.location = `/Match/Single/${replayID}/Download${query ? '?' + query : ''}`;
    },
    getTakedownsValue(data){
      let totalKills = 0;

      for (let i = 0; i < data.length; i++) {
          totalKills += data[i].score.kills;
      }

      return totalKills;
    },

    getPlayerProfileLink(item, heroPage){
      if(item.check){
        return 'javascript:void(0)';
      }
      var url = "";
      if(this.esport){
        if(this.esport == "Other"){
          url = '/Esports/' + this.esport + '/' + this.series + '/Player/' + item.battletag + '/' + item.blizz_id;

          if(heroPage){
            url = url + '/Hero/' + item.hero.name;
          }
          return url;
    
        }else{
          url = '/Esports/' + this.esport + '/Player/' + item.battletag + '/' + item.blizz_id;

          if(heroPage){
            url = url + '/Hero/' + item.hero.name;
          }

          if(this.esport == "HeroesInternational"){
            url = url + '?tournament=' + this.tournament;
          }
          return url;
        }
      }
      url = '/Player/' + item.battletag + '/' + item.blizz_id + '/' + this.data.region;
      if(heroPage){
        url = url + '/Hero/' + item.hero.name;
      }
      return url;
    },
    getEsportTitle(){
      if(this.series){
        return this.series;
      }else if(this.esport == "HeroesInternational"){
        if(this.tournament == "main"){
          return "Heroes International";
        }
        if(this.tournament == "nationscup"){
          return "Heroes International Nations Cup";
        }
      }

      return this.esport;
    },
  }
}
</script>