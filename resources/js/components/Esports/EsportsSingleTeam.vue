<template>
  <div>
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
        <page-heading :battletag="team" :esport="esport" :heading="heading" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>
      </template>
      <div class="w-full">
        <!-- Shown while loading too, so the heading row holds still; the options fill in once the team's seasons load -->
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => handleDropdownClosed()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: filterSeasons || [], defaultValue: modifiedseason },
            { text: 'Divisions', values: filterDivisions || [], defaultValue: modifieddivision, show: !!(esport == 'NGS') },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <!-- Laid out like the esports player profile -->
    <div v-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">
      <panel-box>
        <!-- Two rows on desktop: the team card beside Players and Teams Lost Against, then Maps beside Heroes -->
        <div class="grid xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

          <!-- Team overview -->
          <panel-box :inner="true" class="!gap-8">
            <div class="flex flex-col gap-8">
              <div class="flex flex-col">
                <h2 class="text-3xl font-bold">{{ teamName(team) }}</h2>
                <span v-if="subtitle" class="text-subtext">{{ subtitle }}</span>
              </div>
              <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
                <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                  <div class="w-40 md:w-[14rem] aspect-square flex items-center justify-center">
                    <img class="max-w-full max-h-full object-contain text-[0rem]" :src="data.icon_url || isLoadingImageUrl" :alt="teamName(team)" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = isLoadingImageUrl; } }"/>
                  </div>
                  <div class="text-xl font-semibold leading-[1.2] text-subtext">{{ $t('Games:') }} <span class="text-header">{{ Number(data.total_games).toLocaleString('en-US') }}</span></div>
                </div>
                <overview-stats class="flex-1 min-w-0 w-full" :win-rate="data.win_rate" :stats="overviewStats"></overview-stats>
              </div>
            </div>
          </panel-box>

          <div class="grid 2xl:grid-cols-2 gap-6 min-w-0">
            <!-- The roster, with each player's most played hero -->
            <panel-box v-if="playerList.length" :inner="true" :title="$t('Players')">
              <!-- The five who played most; the rest open in a popup, where the card's link would sit -->
              <template v-if="playerList.length > 5" #right>
                <button type="button" class="text-header whitespace-nowrap hover:underline" @click="popup = 'players'">{{ $t('View More') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></button>
              </template>
              <div class="flex flex-col gap-2">
                <div v-for="(row, index) in playerList.slice(0, 5)" :key="index" class="flex items-center gap-3 rounded bg-surface-2 px-3 py-2">
                  <a v-if="row.most_played_hero" class="shrink-0" :href="row.herolink">
                    <hero-image-wrapper :hero="row.most_played_hero">
                      <image-hover-box :title="$heroName(row.most_played_hero)" :paragraph-one="$t('Win Rate on Hero') + ': ' + row.win_rate_on_hero + '%'"></image-hover-box>
                    </hero-image-wrapper>
                  </a>
                  <div class="flex flex-col min-w-0 flex-1">
                    <a class="group/bt font-semibold truncate" :href="row.playerlink"><span class="group-hover/bt:underline">{{ row.battletag }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                    <span v-if="row.most_played_role" class="text-sm text-subtext">{{ row.most_played_role }}</span>
                  </div>
                  <span class="shrink-0 text-sm text-subtext">{{ $t(':played games', { played: row.games_played }) }}</span>
                </div>
              </div>
              <p class="flex flex-wrap gap-2 mt-auto"><span class="text-subtext">{{ $t('Total Time Spent Dead:') }}</span><span class="text-header">{{ formatDurationText(shortDuration(data.time_spent_dead)) }}</span></p>
            </panel-box>

            <panel-box v-if="enemyTeams.length" :inner="true" :title="$t('Teams Lost Against')">
              <!-- The four they lost to most; the rest open in a popup -->
              <template v-if="enemyTeams.length > 4" #right>
                <button type="button" class="text-header whitespace-nowrap hover:underline" @click="popup = 'teams'">{{ $t('View More') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></button>
              </template>
              <div class="flex flex-col gap-2">
                <a v-for="(enemy, index) in enemyTeams.slice(0, 4)" :key="index" :href="enemy.enemy_link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
                  <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="enemy.icon_url || isLoadingImageUrl" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = isLoadingImageUrl; } }" :alt="teamName(enemy.team_name || enemy.team)"/>
                  <div class="flex flex-col min-w-0 flex-1">
                    <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ teamName(enemy.team_name || enemy.team) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
                    <span class="text-sm text-subtext">{{ enemy.inputhover }}</span>
                  </div>
                </a>
              </div>
            </panel-box>
          </div>

          <!-- The team's maps, its own card under the team card, opposite Heroes -->
          <panel-box :inner="true" :title="$t('Maps')">
          <div class="flex flex-col gap-4 w-fit max-w-full">
            <div class="flex flex-wrap gap-6 max-md:justify-center">
              <group-box v-for="group in topSections.find(section => section.title == 'Maps').groups" :key="group.label" class="!m-0" :text="group.label" :data="group.data" :min-tiles="3"></group-box>
            </div>
          </div>
          <div class="flex flex-col gap-4">
            <h3 class="text-2xl font-bold">{{ $t('Maps Played') }}</h3>
            <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
              <map-image-wrapper v-for="(item, index) in data.maps" :key="index" size="tile" :map="item.game_map">
                <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + (item.wins + item.losses)"></image-hover-box>
              </map-image-wrapper>
            </div>
          </div>
            <div v-if="esport != 'Other' && (data.maps_banned || []).length" class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ $t('Maps Banned by :team', { team: teamName(team) }) }}</h3>
              <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                <map-image-wrapper v-for="(item, index) in data.maps_banned" :key="index" size="tile" :map="item.game_map">
                  <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="item.inputhover"></image-hover-box>
                </map-image-wrapper>
              </div>
            </div>
          </panel-box>

          <panel-box :inner="true" class="flex-1" :title="$t('Heroes')">
            <div class="flex flex-col gap-6">
              <div class="flex flex-wrap gap-6 justify-evenly">
                <group-box v-for="group in topSections.find(section => section.title == 'Heroes').groups" :key="group.label" class="!m-0" :text="group.label" :data="group.data" :min-tiles="3" empty-note="Only heroes with 5+ games count here."></group-box>
              </div>
              <div class="flex flex-wrap gap-6 justify-evenly">
                <group-box class="!m-0" :useinputforhover="true" :text="'Most Played Against'" :data="mostPlayedAgainst" :min-tiles="3" empty-note="No games yet."></group-box>
                <group-box class="!m-0" :useinputforhover="true" :text="'Most Won Against'" :data="(data.heroes_won_against || []).slice(0, 3)" :min-tiles="3" empty-note="No wins yet."></group-box>
                <group-box class="!m-0" :useinputforhover="true" :text="'Most Lost Against'" :data="(data.heroes_lost_against || []).slice(0, 3)" :min-tiles="3" empty-note="No losses yet."></group-box>
              </div>
              <div class="flex flex-col gap-4">
                <h3 class="text-2xl font-bold">{{ $t('Heroes Played') }}</h3>
                <div class="flex flex-wrap gap-3 max-md:gap-2 max-md:justify-center">
                  <hero-image-wrapper v-for="(item, index) in data.heroes" :key="index" size="tile" :hero="item.hero">
                    <image-hover-box :title="item.name" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + (item.wins + item.losses)"></image-hover-box>
                  </hero-image-wrapper>
                </div>
              </div>
            </div>
          </panel-box>
        </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="grid xl:grid-cols-2 gap-6 items-start">
        <panel-box :title="'Heroes Banned by ' + teamName(team)">
          <group-box v-if="(data.team_ban_date || []).length" class="!m-0 self-center" :useinputforhover="true" :text="'Most Banned'" :data="data.team_ban_date.slice(0, 3)"></group-box>
          <div>
            <h3 class="text-sm text-subtext mb-2">{{ $t('All Bans') }}</h3>
            <div class="flex flex-wrap gap-3 max-md:gap-2">
              <hero-image-wrapper v-for="(item, index) in data.team_ban_date" :key="index" size="tile" :hero="item.hero">
                <image-hover-box :title="item.name" :paragraph-one="item.inputhover"></image-hover-box>
              </hero-image-wrapper>
            </div>
          </div>
        </panel-box>
        <panel-box :title="'Heroes Banned Against ' + teamName(team)">
          <group-box v-if="(data.enemy_ban_date || []).length" class="!m-0 self-center" :useinputforhover="true" :text="'Most Banned'" :data="data.enemy_ban_date.slice(0, 3)"></group-box>
          <div>
            <h3 class="text-sm text-subtext mb-2">{{ $t('All Bans') }}</h3>
            <div class="flex flex-wrap gap-3 max-md:gap-2">
              <hero-image-wrapper v-for="(item, index) in data.enemy_ban_date" :key="index" size="tile" :hero="item.hero">
                <image-hover-box :title="item.name" :paragraph-one="item.inputhover"></image-hover-box>
              </hero-image-wrapper>
            </div>
          </div>
        </panel-box>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <div class="flex justify-between items-baseline gap-6">
        <h2 class="text-2xl font-bold">{{ $t('Recent Matches') }}</h2>
        <a class="text-header whitespace-nowrap hover:underline" :href="matchHistoryUrl">{{ $t('Match History') }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></a>
      </div>
    </div>
    <!-- The league pages' own recent match rows -->
    <div v-if="data" class="mt-4 pb-8">
      <esports-recent-matches v-if="recentMatches.length" :data="recentMatches" :esport="recentMatchesEsport" :series="series" :tournament="tournament" :always-expanded="true"></esports-recent-matches>
      <p v-else class="max-w-[120rem] mx-auto px-4 md:px-8 text-subtext">{{ $t('No recent games.') }}</p>
    </div>

    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="relative max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 flex flex-col gap-8 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
      <!-- The message sits on a tab of the first card, as on the global pages: lined up with the card past the side padding, the card's top-left corner squared to meet it -->
      <loading-component :compact="true" :tab="true" :overrideimage="isLoadingImageUrl"></loading-component>
      <panel-box>
        <!-- Two rows on desktop: the team card beside Players and Teams Lost Against, then Maps beside Heroes -->
        <div class="grid xl:grid-cols-[auto_minmax(0,1fr)] gap-6">

          <!-- Team overview -->
          <panel-box :inner="true" class="!gap-8">
            <div class="flex flex-col gap-8">
              <div class="flex flex-col">
                <h2 class="text-3xl font-bold">{{ teamName(team) }}</h2>
                <span v-if="subtitle" class="text-subtext">{{ subtitle }}</span>
              </div>
              <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
                <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                  <skeleton-box class="w-40 md:w-[14rem] aspect-square rounded"></skeleton-box>
                  <skeleton-box class="h-[1.5rem] w-28"></skeleton-box>
                </div>
                <overview-stats class="flex-1 min-w-0 w-full" :loading="true" :count="8"></overview-stats>
              </div>
            </div>
          </panel-box>

          <div class="grid 2xl:grid-cols-2 gap-6 min-w-0">
            <panel-box :inner="true" :title="$t('Players')">
              <div class="flex flex-col gap-2">
                <skeleton-box v-for="index in 5" :key="index" class="h-14"></skeleton-box>
              </div>
              <p class="flex flex-wrap items-center gap-2 mt-auto"><span class="skeleton-text">{{ $t('Total Time Spent Dead:') }}</span><skeleton-box class="h-4 w-36"></skeleton-box></p>
            </panel-box>
            <panel-box :inner="true" :title="$t('Teams Lost Against')">
              <div class="flex flex-col gap-2">
                <skeleton-box v-for="index in 4" :key="index" class="h-14"></skeleton-box>
              </div>
            </panel-box>
          </div>

          <!-- The team's maps, its own card under the team card, opposite Heroes -->
          <panel-box :inner="true" :title="$t('Maps')">
          <div class="flex flex-col gap-4 w-fit max-w-full">
            <div class="flex flex-wrap gap-6 max-md:justify-center">
              <div v-for="label in ['Most Played', 'Highest Win Rate', 'Lowest Win Rate']" :key="label" class="w-auto inline-block">
                <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                <div class="flex flex-wrap gap-3 max-md:gap-2">
                  <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                </div>
              </div>
            </div>
          </div>
          <div class="flex flex-col gap-4">
            <h3 class="text-2xl font-bold">{{ $t('Maps Played') }}</h3>
            <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2">
              <skeleton-box v-for="tile in 14" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
            </div>
          </div>
            <div v-if="esport != 'Other'" class="flex flex-col gap-4">
              <h3 class="text-2xl font-bold">{{ $t('Maps Banned by :team', { team: teamName(team) }) }}</h3>
              <div class="w-0 min-w-full flex flex-wrap gap-3 max-md:gap-2">
                <skeleton-box v-for="tile in 5" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
              </div>
            </div>
          </panel-box>

          <panel-box :inner="true" class="flex-1" :title="$t('Heroes')">
            <div class="flex flex-col gap-6">
              <div v-for="(row, index) in [['Most Played', 'Highest Win Rate', 'Lowest Win Rate'], ['Most Played Against', 'Most Won Against', 'Most Lost Against']]" :key="index" class="flex flex-wrap gap-6 justify-evenly">
                <div v-for="label in row" :key="label" class="w-auto inline-block">
                  <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                  <div class="flex flex-wrap gap-3 max-md:gap-2">
                    <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                  </div>
                </div>
              </div>
              <div class="flex flex-col gap-4">
                <h3 class="text-2xl font-bold">{{ $t('Heroes Played') }}</h3>
                <div class="flex flex-wrap gap-3 max-md:gap-2">
                  <skeleton-box v-for="tile in 20" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                </div>
              </div>
            </div>
          </panel-box>
        </div>
      </panel-box>
      <!-- The ban cards: their top three, then every banned hero -->
      <div class="grid xl:grid-cols-2 gap-6">
        <panel-box v-for="title in ['Heroes Banned', 'Heroes Banned Against']" :key="title" :title="title">
          <div class="self-center">
            <h3 class="text-sm mb-2"><span class="skeleton-text">{{ $t('Most Banned') }}</span></h3>
            <div class="flex gap-3"><skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box></div>
          </div>
          <div>
            <h3 class="text-sm mb-2"><span class="skeleton-text">{{ $t('All Bans') }}</span></h3>
            <div class="flex flex-wrap gap-3 max-md:gap-2"><skeleton-box v-for="tile in 24" :key="tile" class="w-16 h-16 rounded"></skeleton-box></div>
          </div>
        </panel-box>
      </div>
      <h2 class="text-2xl font-bold">{{ $t('Recent Matches') }}</h2>
      <div class="flex flex-col gap-2 pb-8">
        <skeleton-box v-for="row in 6" :key="row" class="h-16"></skeleton-box>
      </div>
    </div>


    <!-- Every player or every team they lost to, from the cards' View More -->
    <modal-box v-if="popup && data" :title="popup === 'players' ? $t('Players') : $t('Teams Lost Against')" width="max-w-[40rem]" @close="popup = null">
      <div v-if="popup === 'players'" class="flex flex-col gap-2">
        <div v-for="(row, index) in playerList" :key="index" class="flex items-center gap-3 rounded bg-surface-2 px-3 py-2">
          <a v-if="row.most_played_hero" class="shrink-0" :href="row.herolink">
            <hero-image-wrapper :hero="row.most_played_hero">
              <image-hover-box :title="$heroName(row.most_played_hero)" :paragraph-one="$t('Win Rate on Hero') + ': ' + row.win_rate_on_hero + '%'"></image-hover-box>
            </hero-image-wrapper>
          </a>
          <div class="flex flex-col min-w-0 flex-1">
            <a class="group/bt font-semibold truncate" :href="row.playerlink"><span class="group-hover/bt:underline">{{ row.battletag }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
            <span v-if="row.most_played_role" class="text-sm text-subtext">{{ row.most_played_role }}</span>
          </div>
          <span class="shrink-0 text-sm text-subtext">{{ $t(':played games', { played: row.games_played }) }}</span>
        </div>
      </div>
      <div v-else class="flex flex-col gap-2">
        <a v-for="(enemy, index) in enemyTeams" :key="index" :href="enemy.enemy_link" class="group/bt flex items-center gap-3 rounded bg-surface-2 hover:bg-surface-3 px-3 py-2">
          <img class="w-10 h-10 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="enemy.icon_url || isLoadingImageUrl" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = isLoadingImageUrl; } }" :alt="teamName(enemy.team_name || enemy.team)"/>
          <div class="flex flex-col min-w-0 flex-1">
            <span class="font-semibold truncate"><span class="group-hover/bt:underline">{{ teamName(enemy.team_name || enemy.team) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
            <span class="text-sm text-subtext">{{ enemy.inputhover }}</span>
          </div>
        </a>
      </div>
      <template #footer>
        <custom-button class="ml-auto" color="teal" text="Close" :ignoreclick="true" @click="popup = null"></custom-button>
      </template>
    </modal-box>
  </div>
</template>

<script>
import { formatDurationText } from '../../utils/formatDuration';
export default {
  name: 'EsportsSingleTeam',
  components: {
  },
  props: {
    filters: Object,
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
      modifiedseason: null,
      modifieddivision: null,
      cancelTokenSource: null,
      filterSeasons: null,
      // The View More popup open: 'players' or 'teams'
      popup: null,
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
        return this.tournament == 'nationscup' ? 'Heroes International Nations Cup' : 'Heroes International';
      }else if(this.esport == "Other"){
        return;
      }

      // The NGS logo already says NGS
      if(this.esport == 'NGS'){
        return '';
      }
      // The league's name, not its URL key
      return { MastersClash: 'Masters Clash', NutCup: 'Nut Cup' }[this.esport] ?? this.esport;
    },
    subtitle() {
      return [
        this.modifiedseason ? this.$t('Season :season', { season: this.modifiedseason }) : null,
        this.esport == 'NGS' && this.modifieddivision ? 'Division ' + this.modifieddivision : null,
        this.series,
      ].filter(Boolean).join(' · ');
    },
    // The heroes they faced most, wins and losses together; both lists hold every hero they faced
    mostPlayedAgainst() {
      const counts = {};
      for (const row of [...(this.data.heroes_won_against || []), ...(this.data.heroes_lost_against || [])]) {
        counts[row.hero.id] = counts[row.hero.id] || { hero: row.hero, total: 0 };
        counts[row.hero.id].total += row.total;
      }
      const games = Number(this.data.total_games) || 0;
      return Object.values(counts)
        .sort((a, b) => b.total - a.total)
        .slice(0, 3)
        .map(row => ({ ...row, inputhover: this.$t('Played against :hero :count times (:percent% of their games)', { hero: this.$heroName(row.hero), count: row.total, percent: games ? (row.total / games * 100).toFixed(2) : 0 }) }));
    },
    // The teams they lost to, most games first
    enemyTeams() {
      return [...(this.data.enemy_teams || [])].sort((a, b) => (b.total || 0) - (a.total || 0));
    },
    // The API keys players by battletag
    playerList() {
      return Object.values(this.data.players || {}).sort((a, b) => b.games_played - a.games_played);
    },
    overviewStats() {
      const number = value => Number(value).toLocaleString('en-US');
      // As on the profile; the win rate has its own bar and the games sit under the logo
      return [
        { label: 'Wins', value: number(this.data.wins) },
        { label: 'Losses', value: number(this.data.losses) },
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
    teamName(name) {
      return this.esport == 'NGS' ? this.$ngsTeam(name).name : name;
    },
  }
}
</script>