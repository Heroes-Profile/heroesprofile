<template>
  <div>
    <div v-if="showPopup">
      <leaderboard-popup @popupclosed="showPopup = false"></leaderboard-popup>
    </div>

    <!-- As the other global pages: the heading and the filters beside it -->
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
        <page-heading scope="Global" :heading="'Leaderboard'" :subheading="'Top players by season'">
          <template #headingInfo>
            <round-image :popup-hoverable="true" class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
              <!-- SL Rank is read off loading screens, not worked out from uploaded games like the other boards -->
          <p v-if="leaderboardtype == 'SL Rank'" class="font-normal">
            {{ $t("The SL Rank leaderboard shows players' real in-game Storm League ranks, read off loading screens by the uploader's opt-in rank reader. Each player is placed by their latest rank read this season: by league from Master (Grandmasters by ladder position) down to Bronze, then by division. Within a division, players who meet the games requirement come first, by win rate.") }}
            <a href="/Upload/Uploader#rank-reading" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ $t('How rank reading works') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
          </p>
          <p v-else-if="leaderboardtype == 'Match Prediction'" class="font-normal">
                <template v-for="(part, index) in $t('Match Prediction leaderboard data is based on users making predictions about match outcomes through :link.').split(':link')" :key="index">{{ part }}<template v-if="index === 0"><a href="/Match/Prediction/Game" target="_blank" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ $t('Match Prediction') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a></template></template>
              </p>
              <p v-else class="font-normal">
                {{ $t('Only users who upload replays through an approved automatic uploader will be able to rank on the leaderboards.') }}
                <template v-for="(part, index) in $t('The :link works on Windows, macOS and Linux.').split(':link')" :key="index">{{ part }}<template v-if="index === 0"><a href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases" target="_blank" rel="noopener noreferrer" class="underline">{{ $t('Heroes Profile Uploader') }}</a></template></template>
                {{ $t('For any questions, please contact :email.', { email: 'zemill@heroesprofile.com' }) }}
              </p>
            </round-image>
          </template>
        </page-heading>
      </template>
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypeinput="[gametype]"
        :regioninput="region"
        :gametypedefault="gametypedefault"
        :defaultSeason="leaderboardtype == 'Match Prediction' ? defaultseason : season"
        :defaultpredictionseason="leaderboardtype == 'Match Prediction' ? season : defaultpredictionseason"
        :leaderboardfiltertype="true"
        :leaderboardtypeinput="leaderboardtype"
        :includehero="false"
        :heroinput="hero"
        :roleinput="role"
        :mapinput="map"
        :groupSizeDefaultValue="groupsize"
        :includegroupsize="true"
        :includesinglegametypeleaderboard="true"
        :includeseason="true"
        :includesingleleaguetier="true"
        :includesingleregion="true"
        :minimumseason="13"
        :hideadvancedfilteringbutton="true"
        :advancedfiltering="advancedfiltering"
        :excludetimeframes="true"
        :includetier="true"
        :tierrankinput="tierrank"
        :leagueinput="league"
        :disabledeselectfilters="true"
      >
      </filters>
    </player-page-top>
      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
      <div v-if="data">
        <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
          <!-- One table per group, each under its own heading; the rating sits beside the first heading -->
          <template v-for="(group, groupIndex) in groupedData" :key="group.group">
          <div v-if="showGroupHeadings || (groupIndex == 0 && (showRating || leaderboardtype != 'SL Rank'))" class="relative flex flex-wrap items-center gap-4">
            <h2 v-if="showGroupHeadings" class="text-2xl font-bold">
              {{ group.name ?? groupName(group.group) }}<span v-if="group.minGamesRequired !== null"> - {{ $t('At least :count games played', { count: group.minGamesRequired.toLocaleString('en-US') }) }}</span>
            </h2>
            <!-- On the right beside Your HP Rating, its panel opening under it over the table. On phones it follows the group
                 heading and opens in place, pushing Your HP Rating down -->
            <div v-if="groupIndex == 0 && leaderboardtype != 'SL Rank'" class="max-md:w-full relative ml-auto z-40">
              <button type="button" class="bg-surface rounded px-4 py-3 text-sm font-bold flex items-center gap-3 hover:bg-surface-1 transition-colors" :aria-expanded="showLeaderboardRequirements" @click="showLeaderboardRequirements = !showLeaderboardRequirements">
                {{ $t('Leaderboard Requirements') }}<basil-icon name="arrow" :class="['text-xs transition-transform', showLeaderboardRequirements ? '-rotate-90' : 'rotate-90']"></basil-icon>
              </button>
              <transition name="drop">
              <div v-if="showLeaderboardRequirements" class="md:absolute z-40 md:right-0 md:top-full mt-2 w-full md:w-[40rem] md:max-w-[calc(100vw-2rem)] md:max-h-[75vh] md:overflow-y-auto bg-surface rounded p-6 shadow-lg flex flex-col gap-4 text-sm">
                <h3 class="font-bold text-base">{{ leaderboardtype == 'Match Prediction' ? $t('To be eligible for Match Prediction leaderboards, the following conditions must be met:') : $t('To be eligible for leaderboards, the following conditions must be met:') }}</h3>
                <ol v-if="leaderboardtype != 'Match Prediction'" class="list-disc ml-5 space-y-1">
                  <li>{{ $t('Account level must be greater than or equal to 250.') }}</li>
                  <li>{{ $t('Must have played at least :weekssincestartscalar times the number of weeks since the season started. (Currently that is :weekssincestartscalar2 games).', { weekssincestartscalar: weeksSinceStartScalar, weekssincestartscalar2: weekssincestart * weeksSinceStartScalar }) }}</li>
                  <li>{{ $t('Must have a win rate greater than or equal to 50%.') }}</li>
                </ol>
                <ol v-else class="list-disc ml-5">
                  <!--<li> {{ $t('Must have predicted at least :matchpredictionweekssincestartscalar times the number of weeks since the season started. (Currently that is :matchpredictionweekssincestartscalar2 games)', { matchpredictionweekssincestartscalar: matchPredictionWeeksSinceStartScalar, matchpredictionweekssincestartscalar2: weekssincestart * matchPredictionWeeksSinceStartScalar }) }}</li>-->
                  <li>{{ $t('Must have predicted 20 games in the game type and season') }}</li>
                </ol>
                <div class="flex flex-col gap-2">
                  <h4 class="font-bold">{{ $t('Leaderboard groups') }}</h4>
                  <p>{{ leaderboardtype == 'Match Prediction' ? $t('If fewer than 50 players meet the predictions requirement, it is lowered by one game per week at a time until at least 50 players qualify, down to a minimum of 5 games. Each step down is a new group: Group A met the full requirement, Group B met the next step down, and so on. Each group shows the games it required.') : $t('If fewer than 50 players meet the games played requirement, it is lowered by one game per week at a time until at least 50 players qualify, down to a minimum of 5 games. Each step down is a new group: Group A met the full requirement, Group B met the next step down, and so on. Each group shows the games it required.') }}</p>
                  <p>{{ leaderboardtype == 'Match Prediction' ? $t('Everyone in Group A ranks above everyone in Group B, even with a lower prediction rate, so players who met the full requirement are never passed by players who have played fewer games. Within a group, players are ranked by prediction rate.') : $t('Everyone in Group A ranks above everyone in Group B, even with a lower Heroes Profile Rating, so players who met the full requirement are never passed by players who have played fewer games. Within a group, players are ranked by Heroes Profile Rating.') }}</p>
                </div>
                <template v-if="leaderboardtype != 'Match Prediction'">
                  <h4 class="font-bold">{{ $t('Heroes Profile Rating formula') }}</h4>
                  <!-- Black on white; inverted and screened so only the white text shows on the panel. The page language's own
                       version (leaderboard_rating_calculation.ko.png), or the English one if that language has none yet. -->
                  <img class="w-full invert mix-blend-screen" :src="formulaImage" :alt="$t('Heroes Profile Rating formula')" @error="formulaImageFailed = true"/>
                </template>
              </div>
              </transition>
            </div>
            <!-- Your HP Rating: a small card with the rating, games and how far from Group A; before that, the button.
                 Starts collapsed to its title; Show opens the rest over the table, like Leaderboard Requirements, so the
                 page doesn't move -->
            <div v-if="groupIndex == 0 && showRating" :class="['relative w-full sm:w-[22rem]', { 'ml-auto': leaderboardtype == 'SL Rank' }]">
              <div :class="['bg-surface px-5 py-3', ratingCardOpen ? 'rounded-t shadow-lg' : 'rounded']">
              <div class="flex items-center justify-between gap-3">
                <span class="font-bold">{{ $t('Your HP Rating') }}</span>
                <span class="flex items-center gap-4">
                  <button v-if="ratingCardOpen && user && playerRating !== null" type="button" class="inline-flex items-center gap-1.5 text-sm font-medium text-teal hover:text-lteal" @click="calculateHPRating(user)">{{ $t('Recalculate') }}<basil-icon name="arrow-small" class="text-xs"></basil-icon></button>
                  <button type="button" class="flex items-center gap-1.5 text-sm font-medium text-subtext hover:text-white transition-colors" :aria-expanded="ratingCardOpen" @click="ratingCardOpen = !ratingCardOpen">
                    {{ ratingCardOpen ? $t('Hide') : $t('Show') }}
                    <basil-icon name="arrow-small" :class="['text-xs transition-transform', ratingCardOpen ? '-rotate-90' : 'rotate-90']"></basil-icon>
                  </button>
                </span>
              </div>
              </div>
              <transition name="drop">
              <div v-if="ratingCardOpen" class="absolute z-40 left-0 right-0 top-full bg-surface rounded-b px-5 pb-4 pt-1 flex flex-col gap-3 shadow-lg">
              <template v-if="playerRating">
                <div class="flex items-baseline gap-3">
                  <span class="text-3xl font-bold tabular-nums">{{ playerRating }}</span>
                  <span class="text-sm text-subtext">{{ $t(':count games', { count: Number(playerRatingGamesPlayed).toLocaleString('en-US') }) }}</span>
                </div>
                <template v-if="playerRatingGamesPlayed < groupARequirement">
                  <div class="flex items-center gap-3">
                    <stat-bar class="flex-1" :value="Math.min(100, playerRatingGamesPlayed / groupARequirement * 100)" :hide-value="true" color="teal"></stat-bar>
                    <span class="text-xs text-subtext tabular-nums whitespace-nowrap">{{ playerRatingGamesPlayed }} / {{ groupARequirement }}</span>
                  </div>
                  <span class="text-xs text-subtext">{{ $t(':count more games to reach Group A', { count: groupARequirement - playerRatingGamesPlayed }) }}</span>
                </template>
                <span v-else class="text-sm font-semibold text-win">{{ $t('In Group A') }}</span>
              </template>
              <p v-else-if="playerRating === 0" class="text-sm text-subtext">{{ $t('No data for the selected filters') }}</p>
              <template v-else>
                <p class="text-sm text-subtext">{{ $t("See where you'd place on this leaderboard.") }}</p>
                <custom-button v-if="user" class="h-10 font-semibold" color="teal" @click="calculateHPRating(user)" text="Calculate" alt="Calculate My HP Rating" :ignoreclick="true" :loading="ratingLoading"></custom-button>
                <login-required-hover v-else message="You need to be logged in with Battle.net to calculate your rating."><span class="btn bg-teal font-semibold opacity-60 cursor-pointer w-full">{{ $t('Calculate') }}</span></login-required-hover>
              </template>
              </div>
              </transition>
            </div>
          </div>

          <panel-box>
          <!-- overflow-clip, not overflow-hidden: it clips the scaled-down table the same way but, unlike hidden, lets the
               headers below stick to the top of the screen -->
          <div class="overflow-clip">
            <!-- Shrinks to fit the screen, wrapping cell text first -->
            <div ref="responsivetable" class="grid min-w-full" style="width: fit-content">
            <table class="min-w-0 w-full rounded-table relative">
              <thead class="w-full">
                <tr class="">
                  <!-- Wide enough for the "#" box beside the rank, so sorting by another column doesn't widen it; the 1.5px is the cells' right border -->
                  <th @click="sortTable('rank')" class="w-[calc(7.25rem+1.5px)] py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'rank' }">
                    <!-- Sorted by another column: "#" over each row's position in the sort, "Rank" over the leaderboard rank,
                         on the same grid as the cells so both line up -->
                    <span v-if="rankchange" class="inline-grid grid-cols-[2.75rem_2.5rem] gap-2 items-center"><span class="text-center">#</span><span class="text-left whitespace-nowrap">{{ $t('Rank') }}<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span></span>
                    <template v-else>{{ $t('Rank') }}<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></template>
                  </th>
                  <th :class="['py-2 px-3 text-center text-sm leading-4',{ 'bg-teal text-white': sortKey === 'battletag'}]">
                    <!-- "Player" is centred on its own and the glass sits after it; the open search box centres on the column -->
                    <div class="relative grid grid-cols-[1fr_auto_1fr] items-center gap-[1.125rem]">
                      <span></span>
                      <span class="cursor-pointer" @click="sortTable('battletag')">{{ $t('Player') }}<sort-indicator :column="'battletag'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                      <span class="justify-self-start flex">
                        <search-input v-model="searchTerm" :placeholder="$t('Search player')" :collapsible="true" align="center" class="w-40"></search-input>
                      </span>
                    </div>
                  </th>            
                  <th @click="sortTable('region_id')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'region_id'}]">
                    {{ $t('Region') }}<sort-indicator :column="'region_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th v-if="leaderboardtype == 'SL Rank'" class="py-2 px-3  text-center text-sm leading-4">
                    <span class="inline-flex items-center gap-2">{{ $t('Storm League Rank') }}
                      <round-image :popup-hoverable="true" class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                        <p class="font-normal normal-case">
                          {{ $t("Real in-game Storm League ranks, read off loading screens by the uploader's opt-in rank reader. Each player is listed by the latest rank read for them this season, grouped by league: Grand Masters by ladder position, then Master, then each league by division. Within a division, players with at least :games Storm League games this season (the Player leaderboard's games requirement) come first, by Storm League win rate; players under it follow, by games played. Ties go to the rank read in more games. Shows the top 500 in each league.", { games: data.length ? data[0].min_games : 0 }) }}
                          <a href="/Upload/Uploader#rank-reading" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ $t('How rank reading works') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
                        </p>
                      </round-image>
                    </span>
                  </th>
                  <th v-if="leaderboardtype != 'Match Prediction'" @click="sortTable('win_rate')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'win_rate'}]">
                    {{ $t('Win Rate') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th v-if="leaderboardtype == 'Match Prediction'" @click="sortTable('win_rate')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'win_rate'}]">
                    {{ $t('Prediction Rate') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" @click="sortTable('rating')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'rating'}]">
                    <span class="inline-flex items-center gap-2">{{ $t('HP Rating') }}<sort-indicator :column="'rating'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                      <round-image :popup-hoverable="true" class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" @click.stop>
                        <p class="font-normal normal-case">
                          {{ $t("Heroes Profile Rating ranks players by combining their MMR, win rate and games played this season, each measured against the other players on this leaderboard. Games played is capped relative to the top players, so playing lots of games alone can't carry someone to the top.") }}
                          <template v-for="(part, index) in $t('The exact formula is under Leaderboard Requirements, and the :link explains why it is used instead of MMR.').split(':link')" :key="index">{{ part }}<template v-if="index === 0"><a href="/FAQ" class="underline">{{ $t('FAQ') }}</a></template></template>
                        </p>
                      </round-image>
                    </span>
                  </th>      
                  <th v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" @click="sortTable('mmr')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'mmr'}]">
                    {{ $t('HP :leaderboardtype MMR', { leaderboardtype: $t(leaderboardtype) }) }}<sort-indicator :column="'mmr'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th> 
                  <th v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" @click="sortTable('mmr')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'mmr'}]">
                    {{ $t('HP Tier') }}<sort-indicator :column="'mmr'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>    
                  <th @click="sortTable('games_played')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'games_played'}]">
                    {{ $t('Games Played') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>     


                  <th v-if="leaderboardtype == 'Player' || leaderboardtype == 'Role' || leaderboardtype == 'Map'" class="py-2 px-3  text-left text-sm leading-4">
                    {{ $t('Most Played Hero') }}
                  </th>    

                  <template v-else-if="leaderboardtype == 'Hero'">
                    <th class="py-2 px-3  text-center text-sm leading-4">
                      {{ $t('Most Played Build') }}
                    </th>  
                    <th class="py-2 px-3  text-center text-sm leading-4">
                      {{ $t('Copy Build to Game') }}
                    </th>  
                    <th @click="sortTable('hero_build_games_played')" class="cursor-pointer py-2 px-3  text-center text-sm leading-4" :class="{ 'bg-teal text-white': sortKey === 'hero_build_games_played' }">
                      {{ $t('Played') }}<sort-indicator :column="'hero_build_games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                    </th>    
                  </template>
                </tr>
              </thead>
              <tbody class="text-center">
                <tr v-if="group.noMatch">
                  <td colspan="10" class="py-6 text-subtext">{{ $t('No players match ":playersearch".', { playersearch: searchTerm }) }}</td>
                </tr>
                <template v-for="(row, index) in group.rows">
                  <tr v-if="!patreonUser && group.offset + index != 0 && (group.offset + index) % 50 === 0">
                    <td colspan="10">
                      <dynamic-banner-ad :patreon-user="patreonUser" :index="group.offset + index + 3" :mobile-override="true"></dynamic-banner-ad>
                    </td>
                  </tr>
                  <tr>
                    <td><div v-if="rankchange" class="inline-grid grid-cols-[2.75rem_2.5rem] gap-2 items-center"><div class="bg-blue text-white py-1 rounded-lg text-center tabular-nums"><span v-if="sortDir == 'desc'">{{  index+1 }}</span><span v-if="sortDir == 'asc'">{{  group.rows.length - index }}</span></div><span class="text-left tabular-nums">{{ row.rank }}</span></div><span v-else class="p-1">{{ row.rank }}</span></td>
                    <td>
                      <player-name :flair-outside="true" :battletag="row.split_battletag" :blizz-id="row.blizz_id" :region="row.region_id" :redirect="true" :hp-owner="!!row.hp_owner" :patreon="!!row.patreon"></player-name>
                    </td>
                    <td>{{ row.region }}</td>
                    <td v-if="leaderboardtype == 'SL Rank'"><real-rank-badge :rank="row" :width="36" label="right"></real-rank-badge></td>
                    <td>{{ row.win_rate === null ? '-' : row.win_rate.toFixed(2) + '%' }}</td>
                    <td v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'">{{ row.rating.toFixed(2) }}</td>
                    <td v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'">{{ row.mmr.toLocaleString('en-US') }}</td>
                    <td v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" class="px-6"><rank-tier-pill :tier="row.tier"></rank-tier-pill></td>
                    <td>{{ row.games_played.toLocaleString('en-US') }}</td>

                    <td class="py-2 px-3" v-if="(leaderboardtype === 'Player' || leaderboardtype === 'Role' || leaderboardtype === 'Map')">
                      <!-- Holds a portrait's height when there's no hero, so every row is the same height -->
                      <div class="flex items-center min-h-10">
                        <template v-if="row.most_played_hero">
                        <hero-image-wrapper :hero="row.most_played_hero">
                          <image-hover-box :title="$heroName(row.most_played_hero)" :paragraph-one="$t('Games Played') + ': ' + row.hero_build_games_played"></image-hover-box>
                        </hero-image-wrapper><a :href="`/Player/${row.split_battletag}/${row.blizz_id}/${row.region_id}/Hero/${encodeURIComponent(row.most_played_hero.name)}`" class="group/bt hidden md:inline-flex items-center ml-2"><span class="group-hover/bt:underline">{{ $heroName(row.most_played_hero) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                        </template>
                      </div>
                    </td>


                    <template v-else-if="leaderboardtype == 'Hero'">
                      <td class="py-2 px-3 ">
                        <build-code v-if="row.level_one" :build="row" :hero="row.hero" show="talents" :popup-hoverable="false"></build-code>
                      </td>
                      <td class="py-2 px-3">
                        <build-code v-if="row.level_one" :build="row" :hero="row.hero" show="code"></build-code>
                     
                      </td>
                      <td>
                        <div v-if="row.level_one">
                          {{ row.hero_build_games_played.toLocaleString('en-US') }}
                        </div>
                      </td>
                    </template>
                  </tr>
                </template>
              </tbody>
            </table>
            </div>
          </div>
          </panel-box>
          </template>
        </div>
    </div>
    <!-- The load failed: the error page's card in place of the data -->
    <div v-else-if="loadError" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <!-- The first group's heading, with the rating button where it loads, so nothing moves when the data arrives -->
      <div class="relative flex flex-wrap items-center gap-4">
        <h2 class="text-2xl font-bold skeleton-text">{{ leaderboardtype == 'SL Rank' ? 'Grand Master' : 'Group A - At least 35 games played' }}</h2>
        <!-- The requirements button's place -->
        <span v-if="leaderboardtype != 'SL Rank'" class="ml-auto rounded px-4 py-3 text-sm font-bold flex items-center gap-3 skeleton-text">{{ $t('Leaderboard Requirements') }}<basil-icon name="arrow" class="text-xs rotate-90"></basil-icon></span>
        <skeleton-box v-if="showRating" :class="['h-12 w-full sm:w-[22rem]', { 'ml-auto': leaderboardtype == 'SL Rank' }]" class="rounded"></skeleton-box>
      </div>
      <!-- Shaped like the table for this leaderboard type; the message sits on a tab of its panel, as on the profile -->
      <div class="relative">
        <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading leaderboard...') }}</loading-component>
        <table-skeleton class="md:!rounded-tl-none" :rows="12" :columns="skeletonColumns"></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
import { siteLocale } from '../../../utils/translate';
export default {
  name: 'GlobalLeaderboard',
  components: {
  },
  props: {
    user: Object,
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaultseason: String,
    defaultpredictionseason: String,
    advancedfiltering: Boolean,
    weekssincestart: Number,
    matchpredictionweekssincestart: Number,
    urlparameters: Object,
  },
  data(){
    return {
      loadError: null,
      windowWidth: window.innerWidth,
      isLoading: false,
      infoText1: "Leaderboards are a reflection of user uploaded data. Due to replay file corruption or other issues, the data is not always reflective of real player stats. Please keep that in mind when reviewing leaderboards.",
      columns: [
        { text: 'Rank', value: 'rank', sortable: true },
        { text: 'Battletag', value: 'battletag', sortable: true },
        { text: 'Region', value: 'region', sortable: true },
        { text: 'Win Rate', value: 'win_rate', sortable: true },
        { text: 'Heroes Profile Rating', value: 'rating', sortable: true },
        { text: 'Player MMR', value: 'conservative_rating', sortable: true }, 
        { text: 'Tier', value: 'tier', sortable: true }, 
        { text: 'Games Played', value: 'games_played', sortable: true }, 
        { text: 'Most Played Hero', value: 'most_played_hero', sortable: false }, 
      ],
      leaderboardtype: "Player",
      groupsize: "Solo",
      gametype: null,
      season: null,
      matchpredictionseason: null,
      region: null,
      data: null,
      hero: 1,
      role: "Bruiser",
      // By name, like the Map dropdown's codes
      map: null,
      sortKey: '',
      sortDir: 'desc',
      showLeaderboardRequirements: false,
      // The page language has no formula image of its own: use the English one
      formulaImageFailed: false,
      searchTerm: '',
      playerRating: null,
      ratingLoading: false,
      // The HP Rating card starts collapsed
      ratingCardOpen: false,
      playerRatingGamesPlayed: null,
      tierrank: null,
      league: null,
      rankchange: false,
      isfiltered: false,
      showPopup: true,
    }
  },
  created(){   
    this.gametype = this.gametypedefault[0];
    this.season = this.defaultseason;
    // A number, like the season dropdown's codes and ?season=
    this.matchpredictionseason = this.defaultpredictionseason ? Number(this.defaultpredictionseason) : null;

    if(this.region != null || this.tierrank != null){
      this.rankchange = true;
      this.isfiltered = true;
    }

    if(this.urlparameters){
      this.setURLParameters();
    }

    // Same first map the Map dropdown opens on
    if(this.leaderboardtype == 'Map' && !this.map){
      this.map = this.filters.game_maps[0]?.code ?? null;
    }

    if (typeof localStorage !== 'undefined' && localStorage !== null) {
        if (localStorage.getItem('leaderboardpopup')) {
          this.showPopup = false;
        }
      } else {
        this.showPopup = false;
      }

    this.getData();
  },
  mounted() {
  },
  computed: {
    // leaderboard_rating_calculation.png in English; leaderboard_rating_calculation.ko.png and so on in other languages
    formulaImage() {
      const base = '/images/miscellaneous/leaderboard_rating_calculation';
      const lang = siteLocale();
      return lang && !this.formulaImageFailed ? `${base}.${lang}.png` : `${base}.png`;
    },
    skeletonColumns() {
      const lead = ['stat', 'stat', 'stat'];
      switch (this.leaderboardtype) {
        case 'Hero':
          return [...lead, 'stat', 'stat', 'stat', 'pill', 'stat', 'talentrow', 'code', 'stat'];
        case 'Match Prediction':
          return [...lead, 'stat', 'stat'];
        case 'SL Rank':
          return [...lead, 'badge', 'stat', 'stat'];
        default:
          return [...lead, 'stat', 'stat', 'stat', 'pill', 'stat', 'hero'];
      }
    },
    sortedData() {
      if (!this.sortKey) return this.data;
      return this.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    weeksSinceStartScalar(){
      if(this.leaderboardtype != "Player"){
        return 2;
      }
      return 5;
    },
    matchPredictionWeeksSinceStartScalar(){
      return 10;
    },
    filteredData() {
      const searchTerm = this.searchTerm.toLowerCase();
      return this.sortedData.filter(row => row.battletag.toLowerCase().includes(searchTerm));
    },
    // Splits rows into leaderboard groups (0 = Group A), keeping the current sort and search within each group.
    // Match Prediction and boards calculated before groups existed come back as a single Group A.
    groupedData() {
      if (!this.data) return [];
      // Every group keeps its table while searching, so the search box being typed in never disappears
      const groups = new Map();
      this.sortedData.forEach(row => {
        const key = row.leaderboard_group ?? 0;
        if (!groups.has(key)) {
          groups.set(key, { group: key, name: row.group_name ?? null, minGamesRequired: row.min_games_required ?? null, rows: [] });
        }
      });
      this.filteredData.forEach(row => groups.get(row.leaderboard_group ?? 0).rows.push(row));

      const sorted = [...groups.values()].sort((a, b) => a.group - b.group);
      let offset = 0;
      sorted.forEach(group => {
        group.offset = offset;
        group.noMatch = !!this.searchTerm && !group.rows.length;
        offset += group.rows.length;
      });
      return sorted;
    },
    // Games needed for Group A: the same weekly requirement the leaderboard's groups use
    groupARequirement() {
      return this.weekssincestart * this.weeksSinceStartScalar;
    },
    showRating() {
      return this.season == this.defaultseason && this.leaderboardtype != 'SL Rank';
    },
    showGroupHeadings() {
      return this.groupedData.length > 1 || this.groupedData.some(group => group.minGamesRequired !== null);
    },
    patreonUser(){
      if(this.user && this.user.patreon == 1){
        return true;
      }
      return false;
    },
    queryString(){      
      let queryString = `?type=${this.leaderboardtype}`;
      if(this.leaderboardtype != 'SL Rank' && this.leaderboardtype != 'Match Prediction'){
        queryString += `&group_size=${this.groupsize}`;
      }

      if(this.hero){
        queryString += `&hero=${this.hero}`;
      }
      if(this.role){
        queryString += `&role=${this.role}`;
      }
      if(this.map){
        queryString += `&map=${encodeURIComponent(this.map)}`;
      }


      queryString += `&game_type=${this.gametype}`;
      if(this.season){
        queryString += `&season=${this.season}`;
      }

      if(this.region){
        queryString += `&region=${this.region}`;
      }

      if(this.league && this.leaderboardtype == 'SL Rank'){
        queryString += `&league=${this.league}`;
      }

    
      if(this.tierrank){
        queryString += `&tier_rank=${this.convertRankIDtoName(this.tierrank)}`;
      }

      return queryString;
    },
  },
  watch: {
    // A group's table can be new after a filter change
    groupedData() {
      this.$nextTick(this.fitTables);
    },
  },
  methods: {
    fitTables() {
      [].concat(this.$refs.responsivetable || []).forEach(table => scaleTableToFit(table, table.parentElement));
    },
    async getData(){
      this.loadError = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/global/leaderboard", {

          season: this.season, 
          game_type: this.gametype,
          type: this.leaderboardtype.toLowerCase(),
          groupsize: this.groupsize,
          hero: this.hero,
          role: this.role,
          map: this.map,
          region: this.region,
          tierrank: this.tierrank,
          league: this.leaderboardtype == 'SL Rank' ? this.league : null,
        }, 
        {
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        
        this.data = response.data;
        
      }catch(error){
        this.loadError = this.$loadError(error, () => this.getData());
      }finally {
        this.isLoading = false;
        this.$nextTick(this.fitTables);
      }
    },
    filterData(filteredData){
      const previoustype = this.leaderboardtype;
      this.leaderboardtype = filteredData.single["Leaderboard Type"] ? filteredData.single["Leaderboard Type"] : this.leaderboardtype;


      this.groupsize = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : this.groupsize;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.map = this.leaderboardtype == "Map" && filteredData.single["Map"] ? filteredData.single["Map"] : null;
      
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;

      if(this.leaderboardtype == "Match Prediction"){
        this.season = filteredData.single["Match Prediction Season"] ? filteredData.single["Match Prediction Season"] : this.matchpredictionseason;
      }else{
        // Coming from Match Prediction, this.season is a prediction season id, not a game season
        const fallbackseason = previoustype == "Match Prediction" ? this.defaultseason : this.season;
        this.season = filteredData.single["Season"] ? filteredData.single["Season"] : fallbackseason;
      }

      this.region = filteredData.single["Region"] ? filteredData.single["Region"] : null;
      this.tierrank = filteredData.single["HP Rank"] ? filteredData.single["HP Rank"]  : null;
      this.league = filteredData.single["League"] ? filteredData.single["League"] : null;
      this.sortKey = '';
      this.sortDir = 'desc';
      this.playerRating = null;
      this.playerRatingGamesPlayed = null;

      const currentUrl = window.location.href;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}${this.queryString}`);

      
      this.data = null;
      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }

      this.rankchange = true;
      if((key == 'rank' || key == 'rating') && !this.isfiltered){
        this.rankchange = false;
      }
      this.sortKey = key;
    },
    groupName(group){
      return this.$t('Group :letter', { letter: String.fromCharCode(65 + group) });
    },
    async calculateHPRating(user){
      if(user){
        this.playerRating = null;
        this.ratingLoading = true;
        
        try{
          const response = await this.$axios.post("/api/v1/global/leaderboard/calculate/rating", {
            season: this.season, 
            game_type: this.gametype,
            type: this.leaderboardtype.toLowerCase(),
            groupsize: this.groupsize,
            hero: this.hero,
            role: this.role,
            map: this.map,
          }, 
          {
          });

          this.playerRating = response.data.rating;
          this.playerRatingGamesPlayed = response.data.games_played;
        }catch(error){
          // A failed load leaves it empty
        }finally {
          this.ratingLoading = false;
        }
      }

    },
    convertRankIDtoName(rankID) {
      const tier = this.filters.rank_tiers.find(tier => tier.code === rankID);
      return tier ? tier.name : null;
    },
    setURLParameters(){
      if(this.urlparameters["type"]){
        this.leaderboardtype = this.urlparameters["type"];
      }
      if(this.urlparameters["group_size"]){
        this.groupsize = this.urlparameters["group_size"];
      }

      if(this.urlparameters["season"]){
        this.season = Number(this.urlparameters["season"]);
      }else if(this.leaderboardtype == "Match Prediction"){
        this.season = this.matchpredictionseason;
      }

      if(this.urlparameters["game_type"]){
        this.gametype = this.urlparameters["game_type"];
      }

      if(this.urlparameters["region"]){
        this.region = this.urlparameters["region"];
      }

      if(this.urlparameters["league"]){
        this.league = this.urlparameters["league"];
      }

      if(this.urlparameters["role"]){
        this.role = this.urlparameters["role"];
      }
      
      if(this.urlparameters["hero"]){
        this.hero = Number(this.urlparameters["hero"]);
      }

      if(this.urlparameters["map"]){
        this.map = this.urlparameters["map"];
      }


      if (this.urlparameters["tier_rank"]) {
        const tierName = this.urlparameters["tier_rank"];
        const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
        const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
        this.tierrank = tier ? tier.code : null;
      }

    },
  }
}
</script>