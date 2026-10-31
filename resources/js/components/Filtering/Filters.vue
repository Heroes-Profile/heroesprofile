<template>
  <div :class="compact ? 'px-4' : 'max-w-[1920px] mx-auto md:px-8 mb-8'">
    <button v-if="!compact" class="md:hidden p-2 bg-blue w-full" @click="showMobile">Show Filters</button>
    <div v-show="compact || showNav" :class="compact ? 'bg-lighten rounded p-3' : 'bg-surface md:rounded-lg p-4 md:p-6 max-md:fixed max-md:inset-0 max-md:z-50 max-md:h-[100dvh] max-md:overflow-y-auto max-md:flex max-md:flex-col'">
      <button v-if="!compact" class="md:hidden ml-auto text-2xl min-w-[44px] min-h-[44px]" aria-label="Close filters" @click="showMobile">x</button>
      <div :class="compact ? 'flex flex-wrap items-end gap-3' : 'filter-row'">
        <div :class="compact ? 'grid grid-cols-[repeat(auto-fit,minmax(min(100%,160px),1fr))] flex-1 basis-[800px] gap-2 min-w-0' : 'contents'">
          <!--Hero or Role -->
          <single-select-filter :compact="compact" v-if="includeherorole"
            :values="filters.hero_role" 
            :text="'Hero or Role'" 
            @input-changed="handleInputChange"
            :defaultValue="'Hero'"
          ></single-select-filter>

          <!-- Type -->
          <single-select-filter :compact="compact" v-if="playerheroroletype"
            :values="filters.type" 
            :text="'Type'"
            @input-changed="handleInputChange" 
            :defaultValue="'Player'"
          ></single-select-filter>

          <!-- Leaderboard -->
          <single-select-filter :compact="compact" v-if="leaderboardfiltertype"
            :values="filters.leaderboard_type" 
            :text="'Leaderboard Type'"
            @input-changed="handleInputChange" 
            :defaultValue="leaderboardtypeinput || 'Player'"
            :disabledeselectfilters="disabledeselectfilters"
          ></single-select-filter>

          <!-- Heroes Swap -->
          <single-select-filter :compact="compact" v-if="modifiedincludeheroes && swapHeroesFilter"
            :values="filters.heroes" 
            :text="'Heroes'" 
            :defaultValue="heroinput"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Role Swap -->
          <single-select-filter :compact="compact" v-if="modifiedincluderole && swapRolesFilter"
            :values="filters.role" 
            :text="'Role'" 
            :defaultValue="role"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!--<single-select-filter v-if="includecharttype" :values="filters.chart_type" :text="'Chart Type'" @input-changed="handleInputChange" :defaultValue="'Account Level'"></single-select-filter>-->

          <!-- Timeframe Type -->
          <single-select-filter :compact="compact" v-if="includetimeframetype"
            :values="filters.timeframe_type" 
            :text="'Timeframe Type'" 
            :defaultValue="timeframetype" 
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Timeframe Type -->
          <single-select-filter :compact="compact" v-if="includetimeframetypewithlastupdate"
            :values="timeframeTypeWithLastUpdate" 
            :text="'Timeframe Type'" 
            :defaultValue="timeframetype" 
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Timeframes -->
          <!-- Remounted on a rank source switch so a dropped pre-in-game timeframe isn't left selected -->
          <single-select-filter :compact="compact" v-if="includetimeframemodified && timeframetype == 'major' && !combinableMajorPatches.length"
            :key="rankSourceSL ? 'timeframe-sl' : 'timeframe-hp'"
            :values="timeframes"
            :text="'Timeframes'"
            :defaultValue="timeframe[0]"
            :disabledeselectfilters="true"
            @input-changed="handleInputChange"
          ></single-select-filter>
          <multi-select-filter :compact="compact" v-else-if="includetimeframemodified"
            :key="rankSourceSL ? 'timeframes-sl' : 'timeframes-hp'"
            :groupby="timeframeGroupOf"
            :values="timeframes"
            :text="'Timeframes'"
            :defaultValue="timeframe"
            @input-changed="handleInputChange"
            infolink="/FAQ#patch-history"
          ></multi-select-filter>

          <!-- Current Game Type Multiselect-->
          <multi-select-filter :compact="compact" v-if="modifiedincludegametype"
            :values="filters.game_types" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></multi-select-filter>

          <!-- All Game Types Multiselect -->
          <multi-select-filter :compact="compact" v-if="includegametypefull"
            :values="filters.game_types_full" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></multi-select-filter>

          <!-- All Game Types Multiselect -->
          <multi-select-filter :compact="compact" v-if="includegametypefullcustom"
            :values="filters.game_types_full_add_custom" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></multi-select-filter>

          

          <!-- Group Size -->
          <single-select-filter :compact="compact" v-if="modifiedincludegroupsize && !groupsizeadvanced && !groupsizemulti"
            :values="filters.group_size"
            :text="'Group Size'"
            @input-changed="handleInputChange"
            :defaultValue="modifiedGroupSizeDefaultValue"
            :disabledeselectfilters="disabledeselectfilters"
          ></single-select-filter>

          <multi-select-filter :compact="compact" v-if="modifiedincludegroupsize && !groupsizeadvanced && groupsizemulti"
            :values="groupSizeOptions"
            :text="'Group Size'"
            @input-changed="handleInputChange"
            :defaultValue="modifiedGroupSizeDefaultValue"
          ></multi-select-filter>

          <!-- Leaderboard Game Type Excluding UD -->
          <single-select-filter :compact="compact" v-if="includesinglegametypeleaderboard && selectedSingleFilters['Leaderboard Type'] != 'SL Rank'"
            :values="leaderboardGameTypes" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype[0]"
            :disabledeselectfilters="disabledeselectfilters"
          ></single-select-filter>

          <!-- Current Game Type Single -->
          <single-select-filter :compact="compact" v-if="includesinglegametype"
            :values="filters.game_types" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype[0]"
          ></single-select-filter>


          <!-- All Game Type Single -->
          <single-select-filter :compact="compact" v-if="includesinglegametypefull"
            :values="filters.game_types_full" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype[0]"
          ></single-select-filter>
          
          <!-- Regions Multiselect-->
          <multi-select-filter :compact="compact" v-if="includeregion"
            :values="filters.regions" 
            :text="'Regions'" 
            :defaultValue="region"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Regions Single-->
          <single-select-filter :compact="compact" v-if="includesingleregion"
            :values="filters.regions" 
            :text="'Regions'"
            :defaultValue="region"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- SL Rank League -->
          <single-select-filter :compact="compact" v-if="leaderboardfiltertype && selectedSingleFilters['Leaderboard Type'] == 'SL Rank' && filters.sl_leagues"
            :values="filters.sl_leagues"
            :text="'League'"
            :defaultValue="leagueinput"
            @input-changed="handleInputChange"
          ></single-select-filter>


          <!-- Stat Type Filter -->
          <single-select-filter :compact="compact" v-if="showStatTypeFilter"
            :values="filters.stat_filter"
            :text="'Stat Filter'"
            :defaultValue="statfilter"
            :disabled="statfilterdisabled"
            :key="'statfilter-' + statfilterdisabled"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Hero Level -->
          <multi-select-filter :compact="compact" v-if="includeherolevel && toggleExtraFilters && !rankSourceSL"
            :values="filters.hero_level" 
            :text="'Hero Level'" 
            :defaultValue="herolevel"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Heroes -->
          <single-select-filter :compact="compact" v-if="modifiedincludeheroes && !swapHeroesFilter"
            :values="filters.heroes" 
            :text="'Heroes'" 
            :defaultValue="heroinput"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Role -->
          <single-select-filter :compact="compact" v-if="modifiedincluderole && !swapRolesFilter"
            :values="filters.role" 
            :text="'Role'" 
            :defaultValue="role"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Season -->
          <single-select-filter :compact="compact" v-if="modifiedincludeseason"
            :values="seasons" 
            :text="'Season'" 
            @input-changed="handleInputChange" 
            :defaultValue="defaultSeason"
            :disabledeselectfilters="disabledeselectfilters"
          ></single-select-filter>

          <!-- Match Prediction Seasons -->
          <single-select-filter :compact="compact" v-if="modifiedincludematchpredictionseason"
            :values="this.filters.match_prediction_seasons" 
            :text="'Match Prediction Season'" 
            @input-changed="handleInputChange" 
            :defaultValue="defaultpredictionseason"
          ></single-select-filter>


          <!-- All Seasons -->
          <single-select-filter :compact="compact" v-if="includeseasonwithall"
            :values="seasonsWithAll" 
            :text="'Season'" 
            @input-changed="handleInputChange" 
            :defaultValue="defaultSeason"
          ></single-select-filter>

          <!-- Seasons Multiselect -->
          <multi-select-filter :compact="compact" v-if="includemultiseason"
            :values="seasons"
            :text="'Season'"
            :defaultValue="multiSeasonValue"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Heroes Multiselect -->
          <multi-select-filter :compact="compact" v-if="includemultihero"
            :values="filters.heroes"
            :text="'Heroes'"
            @input-changed="handleInputChange"
          ></multi-select-filter>
          <div v-if="includemultihero && selectedMultiFilters['Heroes'] && selectedMultiFilters['Heroes'].length > 1" class="flex flex-col text-sm font-medium p-2">
            <span>Heroes Match</span>
            <tab-button tab1text="OR" tab2text="AND" :ignoreclick="true"
              @tab-click="(side) => handleInputChange({ field: 'Heroes Match', value: side === 'right' ? 'all' : 'any', type: 'single' })"
              :overridedefaultside="selectedSingleFilters['Heroes Match'] === 'all' ? 'right' : 'left'"
            ></tab-button>
          </div>

          <!-- HP MMR ranges and rank tiers (advanced) -->
          <template v-if="includemmrranges && toggleExtraFilters">
            <number-range-filter v-for="mmrType in ['HP Player MMR', 'HP Hero MMR', 'HP Role MMR']" :key="mmrType"
              :text="mmrType"
              @input-changed="handleInputChange"
            ></number-range-filter>
            <multi-select-filter :compact="compact"
              :values="rankTiersByMmr"
              :text="'HP Player Rank'"
              :showrankinfo="true"
              @input-changed="handleInputChange"
            ></multi-select-filter>
            <!-- Hero tiers are per hero, so this needs heroes to look up -->
            <multi-select-filter :compact="compact" v-if="selectedMultiFilters['Heroes'] && selectedMultiFilters['Heroes'].length"
              :values="rankTiersByMmr"
              :text="'HP Hero Rank'"
              :showrankinfo="true"
              @input-changed="handleInputChange"
            ></multi-select-filter>
            <multi-select-filter :compact="compact"
              :values="rankTiersByMmr"
              :text="'HP Role Rank'"
              :showrankinfo="true"
              @input-changed="handleInputChange"
            ></multi-select-filter>
          </template>

          <!-- Players -->
          <player-picker-filter v-if="includeplayerpicker"
            :text="'Players'"
            @input-changed="handleInputChange"
            @searching="(searching) => onFfSearching && onFfSearching(searching)"
          ></player-picker-filter>

          <!-- Game Version Multiselect -->
          <multi-select-filter :compact="compact" v-if="includegameversion"
            :values="filters.timeframes"
            :text="'Game Version'"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Game Map Multiselect -->
          <multi-select-filter :compact="compact" v-if="includegamemap"
            :values="filters.game_maps" 
            :text="'Map'" 
            :defaultValue="gamemap"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Game Map Single -->
          <single-select-filter :compact="compact" v-if="includesinglegamemap"
            :values="filters.game_maps" 
            :text="'Map'" 
            :defaultValue="gamemap[0]"
            @input-changed="handleInputChange"
          ></single-select-filter>


          <!-- Tier Single -->
          <single-select-filter :compact="compact" v-if="modifiedincludetier"
            :values="filters.rank_tiers" 
            :text="'HP Rank'" 
            :showrankinfo="true"
            :defaultValue="tierrank"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Player Rank; with includeranksource, an HP | In-Game switch picks HP tiers or in-game SL ranks -->
          <multi-select-filter :compact="compact" v-if="includeplayerrank"
            :values="rankSourceSL ? filters.sl_leagues : filters.rank_tiers"
            :text="includeranksource ? 'Player Rank' : 'HP Player Rank'"
            :field="rankSourceSL ? 'SL Rank' : 'HP Player Rank'"
            :showrankinfo="true"
            :toggletabs="includeranksource ? ['HP', 'In-Game'] : null"
            :toggleside="rankSourceSL ? 'right' : 'left'"
            :defaultValue="playerRankDefault"
            @input-changed="handleInputChange"
            @toggle-click="(side) => handleInputChange({ field: 'Rank Source', value: side === 'right' ? 'sl' : 'hp', type: 'single' })"
          >
            <!-- Replaces the HP rank explanation while In-Game is on -->
            <div v-if="rankSourceSL">
              <p class="max-sm:text-xs">In-game rank is the Storm League rank Blizzard shows on the loading screen, read by players using the Heroes Profile uploader's opt-in rank reader. Each player counts in the rank they had going into that game. Only players whose rank was read count, so sample sizes are smaller than HP rank.</p>
            </div>
          </multi-select-filter>

          <!-- Hero Rank -->
          <multi-select-filter :compact="compact" v-if="includeherorank && toggleExtraFilters && !rankSourceSL"
            :values="filters.rank_tiers" 
            :text="'HP Hero Rank'" 
            :showrankinfo="true"
            :defaultValue="herorank"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Role Rank -->
          <multi-select-filter :compact="compact" v-if="includerolerank && toggleExtraFilters && !rankSourceSL"
            :values="filters.rank_tiers" 
            :text="'HP Role Rank'" 
            :showrankinfo="true"
            :defaultValue="rolerank"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Talent built Type -->
          <single-select-filter :compact="compact" v-if="includetalentbuildtype"
            :values="filters.talent_build_types" 
            :text="'Talent Build Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="talentbuildtype"
          ></single-select-filter>

          <!-- Minimum Games -->
          <single-select-filter :compact="compact" v-if="includeminimumgames"
            :values="filters.minimum_games" 
            :text="'Minimum Games'" 
            @input-changed="handleInputChange" 
            :defaultValue="modifiedminimumgamedefault"
          ></single-select-filter>

          <!-- Team One Party -->
          <single-select-filter :compact="compact" v-if="includeteamoneparty"
            :values="filters.party_combinations" 
            :text="'Team One Party'" 
            :defaultValue="teamonepartyinput"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Team Two Party -->
          <single-select-filter :compact="compact" v-if="includeteamtwoparty"
            :values="filters.party_combinations" 
            :text="'Team Two Party'" 
            :defaultValue="teamtwopartyinput"
            @input-changed="handleInputChange"
          ></single-select-filter>


          <!--<single-select-filter v-if="modifiedincludeminimumaccountlevel" :values="filters.minimum_account_level" :text="'Min. Account Level'" @input-changed="handleInputChange" :defaultValue="'100'"></single-select-filter>-->
          <!--<single-select-filter v-if="modifiedincludexaxisincrements" :values="filters.x_axis_increments" :text="'X Axis Increments'" @input-changed="handleInputChange" :defaultValue="'25'"></single-select-filter>-->

          <!-- Mirror -->
          <single-select-filter :compact="compact" v-if="includemirror && toggleExtraFilters"
            :values="filters.mirror" 
            :text="'Mirror Matches'" 
            @input-changed="handleInputChange" 
            :defaultValue="mirrormatch"
          ></single-select-filter>

          <!-- Game Date Range -->
          <date-range-filter v-if="includegamedaterange"
            :startDate="selectedStartDate"
            :endDate="selectedEndDate"
            @input-changed="handleDateRangeChange"
          ></date-range-filter>

          <!-- Friend/Foe Battletag Search -->
          <div v-if="matchhistorybattletagsearch" id="filter-label" class="filter-fill relative max-md:w-full md:w-[200px]">
            <div class="flex flex-col gap-1.5 text-sm font-medium text-subtext p-2">
              <span>Friend or Foe Player</span>
              <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-subtext pointer-events-none"></i>
                <input
                  type="text"
                  v-model="ffBattletag"
                  placeholder="Enter battletag"
                  class="w-full h-[40px] rounded !bg-surface-3 pl-9 pr-2 text-white placeholder:text-subtext focus:outline-none"
                  @input="ffBlizzid = null; ffRegion = null; ffSearchResults = []; ffSearchError = null"
                />
                <div v-if="ffSearchResults.length > 1" class="absolute left-0 top-full z-50 bg-gray-dark border border-white/20 rounded shadow-lg" style="min-width:280px; max-height:340px; overflow-y:auto;">
                  <div
                    v-for="result in ffSearchResults"
                    :key="result.blizz_id + '-' + result.region"
                    class="bg-blue hover:bg-lblue p-3 mb-1 rounded flex flex-col items-center cursor-pointer text-sm"
                    @click="selectFfResult(result)"
                  >
                    <div>{{ result.battletagShort }} ({{ result.regionName }})</div>
                    <div class="text-xs text-gray-400">{{ result.latest_game }}</div>
                    <div>Games Played: {{ result.totalGamesPlayed }}</div>
                    <div v-if="result.latestMap">{{ result.latestMap.name }}</div>
                    <div v-if="result.latestHero">
                      <hero-image-wrapper :hero="result.latestHero"></hero-image-wrapper>
                    </div>
                  </div>
                </div>
                <p v-if="ffSearchError" class="text-red text-xs mt-1">{{ ffSearchError }}</p>
              </div>
            </div>
          </div>

          <!-- Game Date -->
          <div v-if="includegamedate" id="filter-label" class="relative">
            <div v-if="includegamedate" class="flex items-end  text-sm font-medium text-gray-700 cursor-pointer p-2  transition-colors">
              <div class="flex flex-col"><span>From Date  </span>
              <input type="date" 
                v-model="selectedGameDate" 
                @input="handleGameDateChange"
                @blur="handleDateInputBlur"
                class="w-[200px] h-[40px] overflow-hidden hover:bg-teal border-solid border-[1px] border-white bg-blue p-2 text-white"
              ></div>
              <button class="h-[40px] bg-blue md:m-t-auto  p-2 border-r-[1px] border-t-[1px] border-b-[1px] hover:bg-teal" @click="resetGameDate()">X</button>
            </div>
          </div>

          <!-- Group Size (advanced, global pages) -->
          <single-select-filter :compact="compact" v-if="modifiedincludegroupsize && groupsizeadvanced && toggleExtraFilters && !groupsizemulti"
            :values="filters.group_size"
            :text="'Group Size'"
            @input-changed="handleInputChange"
            :defaultValue="modifiedGroupSizeDefaultValue"
            :disabledeselectfilters="disabledeselectfilters"
          ></single-select-filter>

          <multi-select-filter :compact="compact" v-if="modifiedincludegroupsize && groupsizeadvanced && toggleExtraFilters && groupsizemulti"
            :values="groupSizeOptions"
            :text="'Group Size'"
            @input-changed="handleInputChange"
            :defaultValue="modifiedGroupSizeDefaultValue"
          ></multi-select-filter>
        </div>
        <button :disabled="disabledFilter" @click="applyFilter" :class="compact ? 'bg-blue rounded text-white px-5 py-2 h-10 disabled:opacity-50 hover:bg-lblue' : ['rounded text-white font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]', disabledFilter ? 'bg-gray-md' : 'bg-blue hover:bg-lblue']">
          Filter
        </button>

        
      </div>
      <div class="flex max-md:mb-auto" :class="compact && $slots['compact-footer'] ? 'justify-between items-start' : 'justify-end'">
        <div v-if="compact && $slots['compact-footer']" class="mt-2"><slot name="compact-footer"></slot></div>
        <button class="m-l-auto underline" :class="{ 'text-sm mt-2': compact }" v-if="!hideadvancedfilteringbutton" :aria-expanded="!!toggleExtraFilters" @click="toggleExtraFilters = !toggleExtraFilters" >{{toggleButtonText}}</button>
      </div>
    </div>
  </div>
</template>

<script>
  import { earliestPatchDate } from '../../utils/patchAge';

  export default {
    name: 'Filters',
    components: {
    },
    props: {
      compact: { type: Boolean, default: false },
      isLoading: Boolean,
      timeframetypeinput: String,
      timeframeinput: Array,
      gametypeinput: Array,
      regioninput: Array,
      statfilterinput: String,
      herolevelinput: Array,
      heroinput: Number,
      roleinput: String,
      gamemapinput: Array,
      playerrankinput: Array,
      herorankinput: Array,
      rolerankinput: Array,
      talentbuildtypeinput: String,
      mirrormatchinput: {
        type: [String, Number]
      },
      teamonepartyinput: String,
      teamtwopartyinput: String,
      includesingleregion: Boolean,
      includeherorole: Boolean,
      playerheroroletype: Boolean,
      leaderboardfiltertype: Boolean,
      includegroupsize: Boolean,
      groupsizeadvanced: Boolean,
      includecharttype: Boolean,
      includetimeframetype: Boolean,
      includetimeframe: Boolean,
      includeseason: Boolean,
      includeregion: Boolean,
      includestatfilter: Boolean,
      includeherolevel: Boolean,
      includerole: Boolean,
      includehero: Boolean,
      includegametype: Boolean,
      includegametypefull: Boolean,
      includegametypefullcustom: Boolean,
      includesinglegametype: Boolean,
      includesinglegametypeleaderboard: Boolean,
      includegamemap: Boolean,
      includesinglegamemap: Boolean,
      includeplayerrank: Boolean,
      includeherorank: Boolean,
      includerolerank: Boolean,
      includemirror: Boolean,
      includetalentbuildtype: Boolean,
      includeminimumgames: Boolean,
      includeteamoneparty: Boolean,
      includeteamtwoparty: Boolean,
      includeminimumaccountlevel: Boolean,
      includexaxisincrements: Boolean,
      includesinglegametypefull: Boolean,
      minimumseason: Number,
      includegamedate: Boolean,
      hideadvancedfilteringbutton: Boolean,
      includeseasonwithall: Boolean,
      includemultiseason: Boolean,
      includegamedaterange: Boolean,
      includemultihero: Boolean,
      includemmrranges: Boolean,
      includeplayerpicker: Boolean,
      includegameversion: Boolean,
      defaultseasons: Array,
      overrideGroupSizeRemoval: Boolean,
      includetimeframetypewithlastupdate: Boolean,
      includetier: Boolean,
      includeranksource: Boolean,
      filters: {
        type: Object,
        required: true
      },
      onFilter: {
        type: Function,
        required: true,
      },
      onFfSearching: {
        type: Function,
        default: null,
      },
      gametypedefault: Array,
      minimumgamesdefault: {
        tpye: [String, Number]
      },
      defaultSeason: [String, Number],
      defaultpredictionseason: [String, Number],
      defaultPredictionSeason: String,
      advancedfiltering: Boolean,
      groupSizeDefaultValue: [String, Array],
      groupsizemulti: Boolean,
      leaderboardtypeinput: String,
      leagueinput: String,
      ranksourceinput: String,
      slrankinput: Array,
      tierrankinput: [String, Number],
      rolerequired: Boolean,
      excludetimeframes: Boolean,
      disablefilter: Boolean,
      disabledeselectfilters: Boolean,
      matchhistorybattletagsearch: Boolean,
      ffbattletaginput: String,
      ffblizzidinput: [String, Number],
      ffregioninput: [String, Number],
    },
    data(){
      return {
        timeframetype: String,
        timeframe: Array,
        gametype: Array,
        region: Array,
        herolevel: Array,
        hero: Number,
        role: String,
        gamemap: Array,
        playerrank: Array,
        // Set once: the Player Rank switch clears its own selection
        playerRankDefault: null,
        tierrank: String,
        herorank: Array,
        rolerank: Array,
        talentbuildtype: String,
        mirrormatch: {
          type: [String, Number]
        },

        selectedSingleFilters: {},
        selectedMultiFilters: {},
        defaultGameType: [],

        modifiedincluderegion: null,
        modifiedincludeminimumaccountlevel: null,
        modifiedincludexaxisincrements: null,
        modifiedincludegametype: null,
        modifiedminimumgamedefault: null,
        modifiedincludeheroes: null,
        selectedGameDate: null,
        selectedStartDate: null,
        selectedEndDate: null,
        multiSeasonValue: [],
        toggleExtraFilters: null,
        modifiedincluderole: null,
        modifiedincludegroupsize: null,
        showNav: true,
        desktopView: true,
        defaultRoleModified: null,
        modifiedGroupSizeDefaultValue: null,
        statfilterdisabled: false,
        swapHeroesFilter: false,
        swapRolesFilter: false,
        includetimeframemodified: null,
        seasonvalue: null,
        modifiedincludetier: null,
        modifiedincludeseason: null,
        rankSourceSL: false,
        ffBattletag: null,
        ffBlizzid: null,
        ffRegion: null,
        ffSearchResults: [],
        ffSearchError: null,
        ffSearchLoading: false,
      }
    },
    created(){
      this.timeframetype = this.timeframetypeinput ? this.timeframetypeinput : this.defaultTimeFrameType;

      if(this.timeframetype == "last_update"){
        this.includetimeframemodified = false;
      }else{
        this.includetimeframemodified = this.includetimeframe;
      }

      this.timeframe = this.timeframeinput ? this.timeframeinput : this.getDefaultMinorBasedOnTimeframeType();
      this.gametype = this.gametypeinput ? this.gametypeinput : this.gametypedefault;
      this.region = this.regioninput ? this.regioninput : null;
      this.statfilter = this.statfilterinput ? this.statfilterinput : this.defaultStatType;
      this.herolevel = this.herolevelinput ? this.herolevelinput : null;
      this.hero = this.heroinput ? this.heroinput : null;
      this.role = this.roleinput ? this.roleinput : null;
      this.gamemap = this.gamemapinput ? this.gamemapinput : null;
      this.playerrank = this.playerrankinput ? this.playerrankinput : null;
      this.herorank = this.herorankinput ? this.herorankinput : null;
      this.rolerank = this.rolerankinput ? this.rolerankinput : null;
      this.talentbuildtype = this.talentbuildtypeinput ? this.talentbuildtypeinput : "Popular";
      this.mirrormatch = this.mirrormatchinput ? this.mirrormatchinput : this.filters.mirror[0].code;

      this.selectedSingleFilters = {
      };

      this.selectedMultiFilters = {
      };

      this.modifiedincludeminimumaccountlevel = this.includeminimumaccountlevel;
      this.modifiedincludexaxisincrements = this.includexaxisincrements;
      this.modifiedincludegametype = this.includegametype;
      this.modifiedincludeheroes = this.includehero;

      this.modifiedminimumgamedefault = this.minimumgamesdefault ? this.minimumgamesdefault : 0;

      this.selectedSingleFilters["Timeframe Type"] = this.timeframetype;
      this.selectedMultiFilters["Game Type"] = this.gametype;
      this.selectedMultiFilters["Timeframes"] = this.getDefaultMinorBasedOnTimeframeType();
      // The single select does not emit its starting value
      if(this.timeframetype == "major"){
        this.selectedMultiFilters["Timeframes"] = this.timeframe;
      }
      this.selectedSingleFilters["Stat Filter"] = this.defaultStatType;

      this.toggleExtraFilters = this.advancedfiltering;

      this.modifiedincluderole = this.includerole
      this.modifiedincludegroupsize = this.includegroupsize;
      this.modifiedincludetier = this.includetier;
      this.modifiedincludeseason = this.includeseason;

      if(this.includemultiseason && this.defaultseasons){
        this.multiSeasonValue = [...this.defaultseasons];
        this.selectedMultiFilters["Season"] = [...this.defaultseasons];
      }

      if(this.groupSizeDefaultValue){
        this.modifiedGroupSizeDefaultValue = this.groupSizeDefaultValue;
      } else {
        this.modifiedGroupSizeDefaultValue = this.groupsizemulti ? [] : "Solo";
      }

      if(this.groupsizeadvanced && this.groupSizeFilters(this.groupSizeDefaultValue)){
        this.statfilter = 'win_rate';
        this.statfilterdisabled = true;
      }

      if(this.tierrankinput != null && this.tierrankinput !== ''){
        this.tierrank = this.tierrankinput;
      }

      if(this.leaderboardfiltertype && this.leaderboardtypeinput){
        this.initializeLeaderboardFilters();
      }

      if(this.includeranksource){
        this.selectedSingleFilters['Rank Source'] = this.ranksourceinput || 'hp';
        if(this.ranksourceinput === 'sl'){
          this.setRankSource('sl');
          if(this.slrankinput){
            this.selectedMultiFilters['SL Rank'] = this.slrankinput;
          }
        }
      }
      this.playerRankDefault = this.rankSourceSL ? this.slrankinput : this.playerrank;

      this.ffBattletag = this.ffbattletaginput || null;
      this.ffBlizzid = this.ffblizzidinput || null;
      this.ffRegion = this.ffregioninput || null;

      // Hidden on mobile, shown on desktop. Media query, not innerWidth, so a page wider than the phone can't open it
      this.desktopView = !this.isMobileView();
      this.showNav = this.desktopView;

      window.addEventListener('resize', this.checkScreenWidth);
    },
    mounted() {

      if(this.playerrankinput && !this.rankSourceSL){
        this.selectedMultiFilters["HP Player Rank"] = this.playerrankinput;
      }

      if(this.herorankinput){
        this.selectedMultiFilters["HP Hero Rank"] = this.herorankinput;
      }

      if(this.rolerankinput){
        this.selectedMultiFilters["HP Role Rank"] = this.rolerankinput;
      }

      if(this.regioninput){
        this.selectedMultiFilters["Regions"] = this.regioninput;
      }

      if(this.gamemapinput){
        this.selectedMultiFilters["Map"] = this.gamemapinput;
      }

      if(this.herolevelinput){
        this.selectedMultiFilters["Hero Level"] = this.herolevelinput;
      }
    },
    computed: {
      // Select All would otherwise tick `All` alongside every real size. Nothing
      // selected already means the unfiltered table, so the option is redundant.
      groupSizeOptions() {
        return (this.filters.group_size || []).filter(option => option.code !== 'All');
      },
      showStatTypeFilter(){

        if(this.includestatfilter && this.toggleExtraFilters){
          if(this.selectedMultiFilters.hasOwnProperty('Timeframes')){
            if(this.selectedSingleFilters['Timeframe Type'] == 'major' || this.selectedSingleFilters['Timeframe Type'] == 'major_grouped' || this.selectedMultiFilters['Timeframes'].length > 5){
              return false;
            }
          }
          return true;
        }
         


        return false;
      },
      timeframeTypeWithLastUpdate(){
        const updatedTimeframeTypes = [...this.filters.timeframe_type];
        //updatedTimeframeTypes.push({ code: 'last_update', name: 'Last Update' });
        return updatedTimeframeTypes;
      },
      combinableMajorPatches(){
        return this.filters.combinable_major_patches || [];
      },
      defaultTimeFrameType(){
        return this.filters.timeframe_type[1].code;
      },
      disabledFilter(){
        if(this.isLoading){
          return true;
        }

        if(!this.selectedMultiFilters.hasOwnProperty('Game Type')){
          return true;
        }

        if(!this.excludetimeframes){
          (3);

          if(!this.selectedMultiFilters.hasOwnProperty('Timeframes') && this.selectedSingleFilters["Timeframe Type"] != "last_update"){
            return true;
          }
        }

        if(this.disablefilter == true){
          return true;
        }
        return false;
      },
      defaultStatType(){
        return this.filters.stat_filter[0].code;
      },
      timeframes(){
        let timeframes;
        if(this.timeframetype == "minor" || this.timeframetype == "last_update"){
          timeframes = this.filters.timeframes;
        }else if(this.timeframetype == "major"){
          timeframes = this.withPatchDates(this.filters.timeframes_grouped);
        }else if(this.timeframetype == "major_grouped"){
          timeframes = this.withPatchDates(this.filters.timeframes_sub_grouped);
        }

        // In-game rank data starts at its own patch
        if(timeframes && this.rankSourceSL && this.filters.real_rank_minimum_patch){
          return timeframes.filter(timeframe => this.atOrAfterPatch(timeframe.code, this.filters.real_rank_minimum_patch));
        }
        return timeframes;
      },
      seasons(){
        if(this.minimumseason){
          return this.filters.seasons.filter(season => season.code >= this.minimumseason);
        }
        return this.filters.seasons;
      },
      toggleButtonText(){
        if(this.toggleExtraFilters){
          return "Hide Advanced Filters";
        }
        return "Show Advanced Filters";
      },
      // Wood is decided by games played, not MMR
      rankTiersByMmr() {
        return this.filters.rank_tiers.filter(tier => tier.name !== 'Wood');
      },
      seasonsWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.seasons];
        updatedList.unshift(newValue);
        return updatedList;
      },
      leaderboardGameTypes(){
        if(!this.seasonvalue || this.seasonvalue >= 26){
          return this.filters.game_types.filter(gameType => gameType.code !== 'ud');
        }
        return this.filters.game_types;
      }
    },
    watch: {
      // The mobile panel covers the screen, so the page behind it shouldn't scroll
      showNav(open){
        if(!this.compact && this.isMobileView()){
          document.body.style.overflow = open ? 'hidden' : '';
        }
      },
      toggleExtraFilters(value){
        if(!value){
          // Only filters that disappear with the advanced section; Map stays visible.
          delete this.selectedMultiFilters['Stat Filter'];
          delete this.selectedMultiFilters['Hero Level'];
          delete this.selectedMultiFilters['HP Hero Rank'];
          delete this.selectedMultiFilters['HP Role Rank'];
          delete this.selectedSingleFilters['Mirror Matches'];
          if(this.groupsizeadvanced){
            delete this.selectedSingleFilters['Group Size'];
            delete this.selectedMultiFilters['Group Size'];
          }

          if(this.includemmrranges){
            ['HP Player MMR', 'HP Hero MMR', 'HP Role MMR'].forEach(mmrType => {
              delete this.selectedSingleFilters[mmrType + ' Min'];
              delete this.selectedSingleFilters[mmrType + ' Max'];
            });
            delete this.selectedMultiFilters['HP Player Rank'];
            delete this.selectedMultiFilters['HP Hero Rank'];
            delete this.selectedMultiFilters['HP Role Rank'];
          }
        }
      },
    },
    unmounted(){
      window.removeEventListener('resize', this.checkScreenWidth);
      if(!this.compact && this.showNav && this.isMobileView()){
        document.body.style.overflow = '';
      }
    },
    methods: {
      // Patch groups carry no date of their own; they date from their earliest build.
      withPatchDates(groups){
        return (groups || []).map(group => ({
          ...group,
          date_added: earliestPatchDate(group.code, this.filters.timeframes),
        }));
      },
      // Whether a Group Size selection actually narrows anything. `All` means the
      // unfiltered table rather than a stack size, so it counts as no filter even
      // when picked alongside real sizes. Takes the string the single select emits
      // or the array the multi one does.
      groupSizeFilters(value) {
        const selected = Array.isArray(value) ? value : (value ? [value] : []);
        return selected.length > 0 && !selected.includes('All');
      },
      // SL Rank reads the real-rank tables: Storm League only, with no HP ranks, hero level or group size
      setRankSource(source) {
        this.rankSourceSL = (source === 'sl');
        this.modifiedincludegametype = this.rankSourceSL ? false : this.includegametype;
        this.modifiedincludegroupsize = this.rankSourceSL ? false : this.includegroupsize;

        if(this.rankSourceSL){
          this.selectedMultiFilters['Game Type'] = ['sl'];
          delete this.selectedMultiFilters['HP Player Rank'];
          delete this.selectedMultiFilters['HP Hero Rank'];
          delete this.selectedMultiFilters['HP Role Rank'];
          delete this.selectedMultiFilters['Hero Level'];
          delete this.selectedSingleFilters['Group Size'];
          delete this.selectedMultiFilters['Group Size'];
          this.statfilterdisabled = false;

          // Drop timeframes from before in-game rank data; the newest one if nothing is left
          const minimum = this.filters.real_rank_minimum_patch;
          const selectedTimeframes = this.selectedMultiFilters['Timeframes'] || this.timeframe || [];
          if(minimum && Array.isArray(selectedTimeframes)){
            const kept = selectedTimeframes.filter(timeframe => this.atOrAfterPatch(timeframe, minimum));
            if(kept.length !== selectedTimeframes.length){
              this.timeframe = kept.length ? kept : this.getDefaultMinorBasedOnTimeframeType();
              this.selectedMultiFilters['Timeframes'] = this.timeframe;
            }
          }
        }else{
          delete this.selectedMultiFilters['SL Rank'];

          // The HP dropdowns remount showing their inputs; Game Type sends its own. Player Rank stays mounted and clears itself.
          if(this.herorankinput){
            this.selectedMultiFilters['HP Hero Rank'] = this.herorankinput;
          }
          if(this.rolerankinput){
            this.selectedMultiFilters['HP Role Rank'] = this.rolerankinput;
          }
          if(this.herolevelinput){
            this.selectedMultiFilters['Hero Level'] = this.herolevelinput;
          }
        }
      },
      initializeLeaderboardFilters() {
        const type = this.leaderboardtypeinput;
        this.selectedSingleFilters['Leaderboard Type'] = type;
        this.modifiedincludeseason = true;
        this.modifiedincludematchpredictionseason = false;
        this.modifiedincludegroupsize = true;
        this.modifiedincludetier = true;

        if(type === 'Player'){
          this.modifiedincludeheroes = false;
          this.modifiedincluderole = false;
          this.swapHeroesFilter = false;
          this.swapRolesFilter = false;
        }else if(type === 'Hero'){
          this.modifiedincludeheroes = true;
          this.modifiedincluderole = false;
          this.swapHeroesFilter = true;
          this.swapRolesFilter = false;
          if(this.heroinput){
            this.hero = this.heroinput;
            this.selectedSingleFilters['Heroes'] = this.heroinput;
          }
        }else if(type === 'Role'){
          this.modifiedincludeheroes = false;
          this.modifiedincluderole = true;
          this.swapRolesFilter = true;
          this.swapHeroesFilter = false;
          if(this.roleinput){
            this.role = this.roleinput;
            this.selectedSingleFilters['Role'] = this.roleinput;
          }
        }else if(type === 'Match Prediction'){
          this.modifiedincludegroupsize = false;
          this.modifiedincludetier = false;
          this.modifiedincludeseason = false;
          this.modifiedincludematchpredictionseason = true;
        }else if(type === 'SL Rank'){
          this.modifiedincludegroupsize = false;
          this.modifiedincludetier = false;
          this.modifiedincludeheroes = false;
          this.modifiedincluderole = false;
        }

        if(this.modifiedGroupSizeDefaultValue){
          if(this.groupsizemulti){
            this.selectedMultiFilters['Group Size'] = this.modifiedGroupSizeDefaultValue;
          } else {
            this.selectedSingleFilters['Group Size'] = this.modifiedGroupSizeDefaultValue;
          }
        }

        if(this.defaultSeason){
          this.seasonvalue = this.defaultSeason;
          this.selectedSingleFilters['Season'] = this.defaultSeason;
        }

        if(this.defaultpredictionseason && type === 'Match Prediction'){
          this.selectedSingleFilters['Match Prediction Season'] = this.defaultpredictionseason;
        }

        if(this.gametype && this.gametype[0]){
          this.selectedSingleFilters['Game Type'] = this.gametype[0];
        }

        if(this.region){
          this.selectedSingleFilters['Regions'] = this.region;
        }

        if(this.tierrank){
          this.selectedSingleFilters['HP Rank'] = this.tierrank;
        }

        if(this.leagueinput && type === 'SL Rank'){
          this.selectedSingleFilters['League'] = this.leagueinput;
        }
      },
      handleInputChange(eventPayload) {
        if(eventPayload.field == "Timeframe Type"){
          if(eventPayload.value == 'last_update'){
            this.includetimeframemodified = false;
            delete this.selectedMultiFilters["Timeframes"];
          }else{
            this.timeframetype = eventPayload.value;
            this.timeframe = this.getDefaultMinorBasedOnTimeframeType();
            this.selectedMultiFilters["Timeframes"] = this.timeframe;
            this.includetimeframemodified = true;
          }
        }

        // A major patch is picked one at a time, but pages read Timeframes as a list
        if(eventPayload.field == "Timeframes" && eventPayload.type === 'single'){
          eventPayload = { field: "Timeframes", value: eventPayload.value === '' ? [] : [eventPayload.value], type: 'multi' };
        }

        if(eventPayload.type === 'single') {
          if (eventPayload.value === '') {
            delete this.selectedSingleFilters[eventPayload.field];
          } else {
            this.selectedSingleFilters[eventPayload.field] = eventPayload.value;
          }
        } else if(eventPayload.type === 'multi') {
          if(eventPayload.value.length == 0){
            delete this.selectedMultiFilters[eventPayload.field];
          }else{
            this.selectedMultiFilters[eventPayload.field] = eventPayload.value;
          }
        }


        if(eventPayload.field == "Rank Source"){
          this.setRankSource(eventPayload.value);
        }

        if(eventPayload.field == "Chart Type" && eventPayload.value == "Hero Level"){
          this.modifiedincludeminimumaccountlevel = false;
          this.modifiedincludexaxisincrements = false;
          this.modifiedincludegametype = true;
          this.modifiedincludeheroes = true;
          

        }else if(eventPayload.field == "Chart Type" && eventPayload.value == "Account Level"){
          this.modifiedincludeminimumaccountlevel = true;
          this.modifiedincludexaxisincrements = true;
          this.modifiedincludegametype = false;
          this.modifiedincludeheroes = false;
        }


        if(eventPayload.field == "Type" || eventPayload.field == "Leaderboard Type"){
          this.modifiedincludeheroes = (eventPayload.value == "Hero");
          this.modifiedincluderole = (eventPayload.value == "Role");

          if(eventPayload.field == "Leaderboard Type"){
            this.modifiedincludeseason = true;
            this.modifiedincludematchpredictionseason = false;
            this.modifiedincludegroupsize = true;
            this.modifiedincludetier = true;

            if(eventPayload.value != "SL Rank"){
              delete this.selectedSingleFilters['League'];
            }
          }
   

          if(eventPayload.value == "Player"){
            delete this.selectedSingleFilters['Heroes'];
            delete this.selectedSingleFilters['Role'];
            this.swapHeroesFilter = false;
            this.swapRolesFilter = false;
          }else if(eventPayload.value == "Hero"){
            delete this.selectedSingleFilters['Role'];
            this.hero = 1;
            this.selectedSingleFilters.Heroes = 1;
            this.swapHeroesFilter = true;
            this.swapRolesFilter = false;
          }else if(eventPayload.value == "Role"){
            delete this.selectedSingleFilters['Heroes'];
            this.role = "Support";
            this.selectedSingleFilters["Role"] = "Support";
            this.swapRolesFilter = true;
            this.swapHeroesFilter = false;
          }else if(eventPayload.value == "Match Prediction"){
            this.modifiedincludegroupsize = false;
            this.modifiedincludetier = false;
            this.modifiedincludeseason = false;
            this.modifiedincludematchpredictionseason = true;

            delete this.selectedSingleFilters['Group Size'];
            delete this.selectedMultiFilters['Group Size'];
            delete this.selectedSingleFilters['Rank'];
            delete this.selectedSingleFilters['Season'];
            delete this.selectedSingleFilters['Heroes'];
            delete this.selectedSingleFilters['Role'];

          }else if(eventPayload.value == "SL Rank"){
            this.modifiedincludegroupsize = false;
            this.modifiedincludetier = false;

            delete this.selectedSingleFilters['Group Size'];
            delete this.selectedMultiFilters['Group Size'];
            delete this.selectedSingleFilters['HP Rank'];
            delete this.selectedSingleFilters['Heroes'];
            delete this.selectedSingleFilters['Role'];

            // The League dropdown remounts showing leagueinput
            if(this.leagueinput){
              this.selectedSingleFilters['League'] = this.leagueinput;
            }
          }
        }
        if(eventPayload.field == "Group Size" && this.groupsizeadvanced){
          if(this.groupSizeFilters(eventPayload.value)){
            this.statfilter = 'win_rate';
            this.selectedSingleFilters['Stat Filter'] = 'win_rate';
            this.statfilterdisabled = true;
          } else {
            this.statfilterdisabled = false;
          }
        }

        if(eventPayload.field == "Season" && eventPayload.type === 'single' && this.includegroupsize){
          if(!this.overrideGroupSizeRemoval){
            this.modifiedincludegroupsize = (eventPayload.value >= 20);
          }
        }

        if(eventPayload.field == "Season" && eventPayload.type === 'single'){
          this.seasonvalue = eventPayload.value;
        }

        // Seasons and the date range are one or the other
        if(eventPayload.field == "Season" && eventPayload.type === 'multi' && eventPayload.value.length > 0 && this.includegamedaterange){
          this.selectedStartDate = null;
          this.selectedEndDate = null;
          delete this.selectedSingleFilters["From Date"];
          delete this.selectedSingleFilters["To Date"];
        }


      },
      handleGameDateChange() {
        this.selectedSingleFilters["From Date"] = this.selectedGameDate;
      },
      handleDateInputBlur() {
        if(this.selectedGameDate == ""){
          this.selectedGameDate = null;
        }
        this.selectedSingleFilters["From Date"] = this.selectedGameDate;
      },
      handleDateRangeChange({ field, value }) {
        if(field == "From Date"){
          this.selectedStartDate = value;
        }else{
          this.selectedEndDate = value;
        }

        if(value){
          this.selectedSingleFilters[field] = value;

          if(this.includemultiseason && this.selectedMultiFilters["Season"]){
            // New array so the season dropdown's watcher clears its selection
            this.multiSeasonValue = [];
            delete this.selectedMultiFilters["Season"];
          }
        }else{
          delete this.selectedSingleFilters[field];
        }
      },
      resetGameDate(){
        this.selectedGameDate = null;
        delete this.selectedSingleFilters["From Date"];
      },
      // Timeframes can only be picked together within one major patch. While a major
      // patch is new, it and the one before it count as one group.
      timeframeGroupOf(timeframe) {
        const major = String(timeframe).split('.').slice(0, 2).join('.');

        return this.combinableMajorPatches.includes(major) ? this.combinableMajorPatches.join('+') : major;
      },
      // Whether a timeframe (build, sub patch or major patch) is on or after a minimum build: 2.57 counts for 2.57.0.98348
      atOrAfterPatch(timeframe, minimum) {
        const parts = String(timeframe).split('.').map(Number);
        const floor = String(minimum).split('.').map(Number).slice(0, parts.length);
        for(let i = 0; i < parts.length; i++){
          if(parts[i] !== floor[i]){
            return parts[i] > floor[i];
          }
        }
        return true;
      },
      getDefaultMinorBasedOnTimeframeType() {
        if(this.timeframetype == "minor"){
          return [this.filters.timeframes[0]?.code || ''];
        } else if(this.timeframetype == "major"){
          return [this.filters.timeframes_grouped[0]?.code || ''];
        }else if(this.timeframetype == "major_grouped"){
          return [this.filters.timeframes_sub_grouped[0]?.code || ''];
        }
        return '';
      },
      async applyFilter() {
        if ((this.selectedMultiFilters.hasOwnProperty('Timeframes') || this.selectedSingleFilters["Timeframe Type"] == "last_update") && this.selectedMultiFilters.hasOwnProperty('Game Type')) {
          if (this.matchhistorybattletagsearch && this.ffBattletag && !this.ffBlizzid) {
            await this.resolveFfPlayer();
            if (!this.ffBlizzid) return;
          }

          if(this.isMobileView()){
            this.showNav = false;
          }
          const allSelectedFilters = {
            single: this.selectedSingleFilters,
            multi: this.selectedMultiFilters,
            ffBattletag: this.ffBattletag || null,
            ffBlizzid: this.ffBlizzid || null,
            ffRegion: this.ffRegion || null,
          };
          this.onFilter(allSelectedFilters);
        }
      },
      async resolveFfPlayer() {
        this.ffSearchResults = [];
        this.ffSearchError = null;
        this.ffSearchLoading = true;
        if (this.onFfSearching) this.onFfSearching(true);
        try {
          const response = await this.$axios.post('/api/v1/player/friendfoe/search', {
            userinput: this.ffBattletag,
            region: this.ffregioninput,
          });
          const results = Object.values(response.data);
          if (results.length === 0) {
            this.ffSearchError = 'No player found.';
          } else if (results.length === 1) {
            this.selectFfResult(results[0]);
          } else {
            this.ffSearchResults = results;
          }
        } catch {
          this.ffSearchError = 'Search failed. Please try again.';
        } finally {
          this.ffSearchLoading = false;
          if (this.onFfSearching) this.onFfSearching(false);
        }
      },
      selectFfResult(result) {
        this.ffBattletag = result.battletagShort;
        this.ffBlizzid = result.blizz_id;
        this.ffRegion = result.region;
        this.ffSearchResults = [];
        this.applyFilter();
      },
      showMobile(){
        this.showNav = !this.showNav;
      },
      isMobileView() {
        // Same breakpoint as Tailwind's md
        return !window.matchMedia('(min-width: 1024px)').matches;
      },
      checkScreenWidth() {
        // Show on desktop; close it when going back to mobile (e.g. rotating a phone) so it doesn't cover the page
        const desktop = !this.isMobileView();
        if (desktop) {
          this.showNav = true;
        } else if (this.desktopView) {
          this.showNav = false;
        }
        this.desktopView = desktop;
      },
    }
  };
</script>
