<template>
  <div>
    <div v-if="showPopup" class="modal">
      <leaderboard-popup @popupclosed="showPopup = false"></leaderboard-popup>
    </div>

    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Leaderboard'">
      <template #headingInfo>
        <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <p class="font-normal">
            Only users who upload replays through an approved automatic uploader will be able to rank on the leaderboards.
            The main uploader can be found at
            <a class="link" href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases" target="_blank" rel="noopener noreferrer">Heroes Profile Uploader</a>
            while the secondary uploader that works on platforms other than Windows can be found at
            <a class="link" href="https://github.com/Heroes-Profile/heroesprofile-electron-uploader/releases" target="_blank" rel="noopener noreferrer">Heroes Profile Electron Uploader</a>.
            For any questions, please contact zemill@heroesprofile.com.
          </p>
        </round-image>
      </template>
    </page-heading>
    <!-- Opens over the filters and table -->
    <div v-if="leaderboardtype != 'SL Rank'" class="relative max-w-[1920px] mx-auto px-4 md:px-8 -mt-2 mb-6">
      <button type="button" class="bg-surface-3 rounded px-4 py-2 text-sm flex items-center gap-3" :aria-expanded="showLeaderboardRequirements" @click="showLeaderboardRequirements = !showLeaderboardRequirements">
        Leaderboard Requirements<i :class="['fas fa-chevron-down text-xs transition-transform', { 'rotate-180': showLeaderboardRequirements }]"></i>
      </button>
      <div v-if="showLeaderboardRequirements" class="absolute z-40 left-4 md:left-8 top-full mt-2 w-[640px] max-w-[calc(100vw-2rem)] max-h-[75vh] overflow-y-auto bg-surface-3 rounded p-6 shadow-lg flex flex-col gap-4 text-sm">
        <h3 class="font-bold text-base">To be eligible for <span v-if="leaderboardtype == 'Match Prediction'">Match Prediction</span> leaderboards, the following conditions must be met:</h3>
        <ol v-if="leaderboardtype != 'Match Prediction'" class="list-disc ml-5 space-y-1">
          <li>Account level must be greater than or equal to 250.</li>
          <li>Must have played at least {{ weeksSinceStartScalar }} times the number of weeks since the season started. (Currently that is {{ weekssincestart * weeksSinceStartScalar }} games).</li>
          <li>Must have a win rate greater than or equal to 50%.</li>
        </ol>
        <ol v-else class="list-disc ml-5">
          <!--<li> Must have predicted at least {{ matchPredictionWeeksSinceStartScalar }} times the number of weeks since the season started. (Currently that is {{ weekssincestart * matchPredictionWeeksSinceStartScalar }} games)</li>-->
          <li>Must have predicted 20 games in the game type and season</li>
        </ol>
        <div class="flex flex-col gap-2">
          <h4 class="font-bold">Leaderboard groups</h4>
          <p>If fewer than 50 players meet the {{ leaderboardtype == 'Match Prediction' ? 'predictions' : 'games played' }} requirement, it is lowered by one game per week at a time until at least 50 players qualify, down to a minimum of 5 games. Each step down is a new group: Group A met the full requirement, Group B met the next step down, and so on. Each group shows the games it required.</p>
          <p>Everyone in Group A ranks above everyone in Group B, even with a lower {{ leaderboardtype == 'Match Prediction' ? 'prediction rate' : 'Heroes Profile Rating' }}, so players who met the full requirement are never passed by players who have played fewer games. Within a group, players are ranked by {{ leaderboardtype == 'Match Prediction' ? 'prediction rate' : 'Heroes Profile Rating' }}.</p>
        </div>
        <template v-if="leaderboardtype != 'Match Prediction'">
          <h4 class="font-bold">Heroes Profile Rating formula</h4>
          <!-- Black on white; inverted and screened so only the white text shows on the panel -->
          <img class="w-full invert mix-blend-screen" :src="'/images/miscellaneous/leaderboard_rating_calculation.png'" alt="Heroes Profile Rating formula"/>
        </template>
      </div>
    </div>
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
      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
      <div v-if="data">
        <div v-if="leaderboardtype == 'Match Prediction'" class="my-5 bg-teal py-5 px-2">
          <infobox class="max-w-[1500px] mx-auto">
            Match Prediction leaderboard data is based on users making predictions about match outcomes through <a class="link" href="/Match/Prediction/Game" target="_blank">Match Prediction</a>
          </infobox>
        </div>
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
          <div class="flex flex-wrap items-center gap-4">
            <div class="relative w-full sm:w-72">
              <label for="search" class="sr-only">Search Battletag:</label>
              <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-teal text-sm"></i>
              <input
                v-model="searchTerm"
                @input="performSearch"
                id="search"
                type="text"
                placeholder="Search battletag"
                class="w-full bg-field text-white rounded-control pl-9 pr-3 py-2 text-sm"
              />
            </div>

            <div v-if="season == defaultseason && leaderboardtype != 'SL Rank'" class="flex flex-wrap items-center justify-end gap-4 ml-auto">
              <span v-if="playerRating" class="text-sm">
                {{ ratingText(playerRating, playerRatingGamesPlayed) }}
              </span>
              <span v-else-if="playerRating == 0" class="text-sm">
                No data for the selected filters
              </span>
              <custom-button class="h-10" @click="calculateHPRating(user)" text="Calculate My HP Rating" alt="Calculate My HP Rating" :ignoreclick="true" :disabled="!user" :title="user ? null : 'Log in with Battle.net to use'" :loading="ratingLoading"></custom-button>
            </div>
          </div>

          <panel-box>
          <div id="table-container" ref="tablecontainer" class="overflow-hidden">
            <!-- Shrinks to fit the screen like a single table would (wrapping cell text first), and every group
                 table stretches to the same width so headings line up and the small-screen scaling covers them all -->
            <div ref="responsivetable" class="grid min-w-full" style="width: fit-content">
            <template v-for="group in groupedData" :key="group.group">
            <h3 v-if="showGroupHeadings" class="stack-header !mx-0 mt-6 first:mt-0 mb-2">
              {{ group.name ?? groupName(group.group) }}<span v-if="group.minGamesRequired !== null">&nbsp;- At least {{ group.minGamesRequired.toLocaleString('en-US') }} games played</span>
            </h3>
            <table class="min-w-0 w-full responsive-table relative">
              <thead class=" top-0 w-full  z-40">
                <tr class="">
                  <th @click="sortTable('rank')" class="py-2 px-3  text-center text-sm leading-4 cursor-pointer">
                    {{ getHeaderRankText("rank") }}<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th @click="sortTable('battletag')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'battletag'}]">
                    Battletag<sort-indicator :column="'battletag'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>            
                  <th @click="sortTable('region_id')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'region_id'}]">
                    Region<sort-indicator :column="'region_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th v-if="leaderboardtype == 'SL Rank'" class="py-2 px-3  text-center text-sm leading-4">
                    <span class="inline-flex items-center gap-2">Storm League Rank
                      <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                        <p class="font-normal normal-case">
                          Real in-game Storm League ranks, read off loading screens by the uploader's opt-in rank reader. Each player is listed by the latest rank read for them this season, grouped by league: Grandmasters by ladder position, then Master, then each league by division. Within a division, players with at least {{ data.length ? data[0].min_games : 0 }} Storm League games this season (the Player leaderboard's games requirement) come first, by Storm League win rate; players under it follow, by games played. Ties go to the rank read in more games. Shows the top 500 in each league.
                          <a class="link" href="/Upload/Uploader#rank-reading">How rank reading works</a>
                        </p>
                      </round-image>
                    </span>
                  </th>
                  <th v-if="leaderboardtype != 'Match Prediction'" @click="sortTable('win_rate')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'win_rate'}]">
                    Win Rate&nbsp;%<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th v-if="leaderboardtype == 'Match Prediction'" @click="sortTable('win_rate')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'win_rate'}]">
                    Prediction Rate&nbsp;%<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                  <th v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" @click="sortTable('rating')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'rating'}]">
                    <span class="inline-flex items-center gap-2">HP Rating<sort-indicator :column="'rating'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                      <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000" @click.stop>
                        <p class="font-normal normal-case">
                          Heroes Profile Rating ranks players by combining their MMR, win rate and games played this season, each measured against the other players on this leaderboard. Games played is capped relative to the top players, so playing lots of games alone can't carry someone to the top.
                          The exact formula is under Leaderboard Requirements, and the <a class="link" href="/FAQ">FAQ</a> explains why it is used instead of MMR.
                        </p>
                      </round-image>
                    </span>
                  </th>      
                  <th v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" @click="sortTable('mmr')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'mmr'}]">
                    HP {{ leaderboardtype }} MMR<sort-indicator :column="'mmr'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th> 
                  <th v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'" @click="sortTable('tier_id')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'tier_id'}]">
                    Tier<sort-indicator :column="'tier_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>    
                  <th @click="sortTable('games_played')" :class="['py-2 px-3  text-center text-sm leading-4 cursor-pointer',{ 'bg-teal text-white': sortKey === 'games_played'}]">
                    Games Played<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>     


                  <th v-if="leaderboardtype == 'Player' || leaderboardtype == 'Role' " class="py-2 px-3  text-center text-sm leading-4">
                    Most Played Hero
                  </th>    

                  <template v-else-if="leaderboardtype == 'Hero'">
                    <th class="py-2 px-3  text-center text-sm leading-4">
                      Most Played Build
                    </th>  
                    <th class="py-2 px-3  text-center text-sm leading-4">
                      Copy Build to Game
                    </th>  
                    <th @click="sortTable('hero_build_games_played')" class="cursor-pointer py-2 px-3  text-center text-sm leading-4">
                      Played<sort-indicator :column="'hero_build_games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                    </th>    
                  </template>
                </tr>
              </thead>
              <tbody class="text-center">
                <template v-for="(row, index) in group.rows">
                  <tr v-if="!patreonUser && group.offset + index != 0 && (group.offset + index) % 50 === 0">
                    <td colspan="10" class="align-content-center">
                      <dynamic-banner-ad :patreon-user="patreonUser" :index="group.offset + index + 3" :mobile-override="true"></dynamic-banner-ad>
                    </td>
                  </tr>
                  <tr>
                    <td><div class="flex justify-center gap-1"><div v-if="rankchange" class="bg-blue text-white min-w-[2em] p-1 rounded-control text-center"><span v-if="sortDir == 'desc'">{{  index+1 }}</span><span v-if="sortDir == 'asc'">{{  group.rows.length - index }}</span></div><span class="p-1">{{ row.rank }}</span></div></td>
                    <td>
                      <div class="flex items-center justify-center">
                        <a class="group/bt inline-flex items-center mr-2" @click="this.$redirectToProfile(row.split_battletag, row.blizz_id, row.region_id, false)" :href="`/Player/${row.split_battletag}/${row.blizz_id}/${row.region_id}`" >{{ row.split_battletag }}<i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                        <div class="" v-if="row.hp_owner">
                          <icon-with-hover class="mt-3"  size="small"    icon="fas fa-crown"   title="info"  popupsize="small" style="color:rgba(216, 184, 0, 0.719);">
                            <slot>
                              <div>
                                <p class="max-sm:text-xs">Site Owner</p>
                              </div>
                            </slot>
                          </icon-with-hover>
         
                        </div>
                        <div class="" v-else-if="row.patreon">
                          <icon-with-hover class="mt-3"  size="small"    icon="fas fa-star"   title="info"  popupsize="small" style="color:rgba(216, 184, 0, 0.719);">
                              <slot>
                                <div>
                                  <p class="max-sm:text-xs">Patreon Subscriber</p>
                                </div>
                              </slot>
                        </icon-with-hover>
                        </div>
                        <void-eye-flair class="mt-3 -ml-1 -translate-y-[0.45em]" :blizz-id="row.blizz_id" :region="row.region_id"></void-eye-flair>
                      </div>
                    </td>
                    <td>{{ row.region }}</td>
                    <td v-if="leaderboardtype == 'SL Rank'"><real-rank-badge :rank="row" :width="36" label="right"></real-rank-badge></td>
                    <td>{{ row.win_rate === null ? '-' : row.win_rate.toFixed(2) }}</td>
                    <td v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'">{{ row.rating.toFixed(2) }}</td>
                    <td v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'">{{ row.mmr.toLocaleString('en-US') }}</td>
                    <td v-if="leaderboardtype != 'Match Prediction' && leaderboardtype != 'SL Rank'"><rank-tier-pill :tier="row.tier"></rank-tier-pill></td>
                    <td>{{ row.games_played.toLocaleString('en-US') }}</td>

                    <td class="py-2 px-3 flex items-center gap-1" v-if="(leaderboardtype === 'Player' || leaderboardtype === 'Role')">
                      <template v-if="row.most_played_hero">
                        <hero-image-wrapper :hero="row.most_played_hero">
                          <image-hover-box :title="row.most_played_hero.name" :paragraph-one="'Games Played:' + row.hero_build_games_played"></image-hover-box>
                        </hero-image-wrapper>
                       <span class="max-md:hidden font-bold"> {{ row.most_played_hero.name }}</span>
                      </template>
                    </td>


                    <template v-else-if="leaderboardtype == 'Hero'">
                      <td class="py-2 px-3 ">
                        <div class="flex flex-nowrap justify-center gap-1.5 [&_img]:!w-[42px] [&_img]:!h-[42px] [&_img]:!min-w-0 [&_img]:!rounded">
                          <talent-image-wrapper :talent="row.level_one" :size="'medium'"></talent-image-wrapper>
                          <talent-image-wrapper :talent="row.level_four" :size="'medium'"></talent-image-wrapper>
                          <talent-image-wrapper :talent="row.level_seven" :size="'medium'"></talent-image-wrapper>
                          <talent-image-wrapper :talent="row.level_ten" :size="'medium'"></talent-image-wrapper>
                          <talent-image-wrapper :talent="row.level_thirteen" :size="'medium'"></talent-image-wrapper>
                          <talent-image-wrapper :talent="row.level_sixteen" :size="'medium'"></talent-image-wrapper>
                          <talent-image-wrapper :talent="row.level_twenty" :size="'medium'"></talent-image-wrapper>
                        </div>
                      </td>
                      <td class="py-2 px-3">
                        <div v-if="row.level_one">
                          {{ this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero) }}
                          <custom-button @click="copyToClipboard(row)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true"></custom-button>
                        </div>
                     
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
            </template>
            </div>
          </div>
          </panel-box>
        </div>
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <loading-component :compact="true" :textoverride="true">Loading leaderboard...</loading-component>
      <div class="flex flex-wrap items-center gap-4">
        <skeleton-box class="h-10 w-full sm:w-72"></skeleton-box>
        <skeleton-box v-if="leaderboardtype != 'SL Rank'" class="h-10 w-56 ml-auto"></skeleton-box>
      </div>
      <panel-box>
        <skeleton-box class="h-8 w-80 max-w-full"></skeleton-box>
        <div class="flex flex-col gap-1">
          <skeleton-box class="h-8"></skeleton-box>
          <skeleton-box v-for="index in 15" :key="index" class="h-10"></skeleton-box>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
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
      windowWidth: window.innerWidth,
      isLoading: false,
      infoText1: "Leaderboards are a reflection of user uploaded data. Due to replay file corruption or other issues, the data is not always reflective of real player stats. Please keep that in mind when reviewing leaderboards.",
      columns: [
        { text: 'Rank', value: 'rank', sortable: true },
        { text: 'Battletag', value: 'battletag', sortable: true },
        { text: 'Region', value: 'region', sortable: true },
        { text: 'Win Rate %', value: 'win_rate', sortable: true },
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
      sortKey: '',
      sortDir: 'desc',
      showLeaderboardRequirements: false,
      searchTerm: '',
      playerRating: null,
      ratingLoading: false,
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
    this.matchpredictionseason = this.defaultpredictionseason;

    if(this.region != null || this.tierrank != null){
      this.rankchange = true;
      this.isfiltered = true;
    }

    if(this.urlparameters){
      this.setURLParameters();
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
      const groups = new Map();
      this.filteredData.forEach(row => {
        const key = row.leaderboard_group ?? 0;
        if (!groups.has(key)) {
          groups.set(key, { group: key, name: row.group_name ?? null, minGamesRequired: row.min_games_required ?? null, rows: [] });
        }
        groups.get(key).rows.push(row);
      });

      const sorted = [...groups.values()].sort((a, b) => a.group - b.group);
      let offset = 0;
      sorted.forEach(group => {
        group.offset = offset;
        offset += group.rows.length;
      });
      return sorted;
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
  },
  methods: {
    async getData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/global/leaderboard", {

          season: this.season, 
          game_type: this.gametype,
          type: this.leaderboardtype.toLowerCase(),
          groupsize: this.groupsize,
          hero: this.hero,
          role: this.role,
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
        //Do something here
      }finally {
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
    filterData(filteredData){
      const previoustype = this.leaderboardtype;
      this.leaderboardtype = filteredData.single["Leaderboard Type"] ? filteredData.single["Leaderboard Type"] : this.leaderboardtype;


      this.groupsize = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : this.groupsize;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;

      if(this.leaderboardtype == "Match Prediction"){
        this.season = filteredData.single["Match Prediction Season"] ? filteredData.single["Match Prediction Season"] : this.matchpredictionseason;
      }else{
        // Coming from Match Prediction, this.season is a prediction season id, not a game season
        const fallbackseason = previoustype == "Match Prediction" ? this.defaultseason : this.season;
        this.season = filteredData.single["Season"] ? filteredData.single["Season"] : fallbackseason;
      }

      this.region = filteredData.single["Regions"] ? filteredData.single["Regions"] : null;
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
    getCopyBuildToGame(level_one, level_four, level_seven, level_ten, level_thirteen, level_sixteen, level_twenty, hero) {
      return "[T" + 
        (level_one ? level_one.sort : '0') + 
        (level_four ? level_four.sort : '0') + 
        (level_seven ? level_seven.sort : '0') + 
        (level_ten ? level_ten.sort : '0') + 
        (level_thirteen ? level_thirteen.sort : '0') + 
        (level_sixteen ? level_sixteen.sort : '0') + 
        (level_twenty ? level_twenty.sort : '0') +
        "," + hero.build_copy_name + "]"
    },
    copyToClipboard(row) {
      const textToCopy = this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero);
      navigator.clipboard.writeText(textToCopy).then(function() {
      }).catch(function(err) {
      });
    },
    groupName(group){
      return `Group ${String.fromCharCode(65 + group)}`;
    },
    ratingText(playerRating, playerRatingGamesPlayed){
      let scalar = this.weekssincestart * this.weeksSinceStartScalar;
      if(playerRatingGamesPlayed < scalar){
        let gameDifference = scalar - playerRatingGamesPlayed;

        return `Rating of ${playerRating} over ${playerRatingGamesPlayed} games.  ${gameDifference} more games to reach Group A.`;
      }
      return `Rating of ${playerRating} over ${playerRatingGamesPlayed} games`;
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
          }, 
          {
          });

          this.playerRating = response.data.rating;
          this.playerRatingGamesPlayed = response.data.games_played;
        }catch(error){
          //Do something here
        }finally {
          this.ratingLoading = false;
        }
      }

    },
    getHeaderRankText(){
      if(this.rankchange){
        return "Sorted Rank|(Rank)"; 
      }
      return "Rank"
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