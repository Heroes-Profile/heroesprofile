<template>
  <div>
    <global-async-debug-banner page-label="Global Hero" />

    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <!-- As Global Talents: the heading with the filters beside it -->
    <player-page-top>
      <template #heading>
        <page-heading scope="Global" heading="Hero Stats" subheading="Tier List and Win Rates">
          <template v-if="isSLRank" #headingInfo>
            <round-image :popup-hoverable="true" class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
              <p class="font-normal">
                {{ $t("In-game rank uses Storm League ranks read off loading screens by the uploader's opt-in rank reader. Only games with a rank read for a player count, so sample sizes are much smaller than HP rank.") }}
                <a href="/Upload/Uploader#rank-reading" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">How rank reading works</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
              </p>
            </round-image>
          </template>
        </page-heading>
      </template>
      <filters 
        :isLoading="isLoading"
        :onFilter="filterData" 
        :filters="filters" 
        :timeframetypeinput="timeframetype"
        :timeframeinput="timeframe"
        :gametypeinput="gametype"
        :regioninput="region"
        :statfilterinput="statfilter"
        :herolevelinput="herolevel"
        :heroinput="getHeroID()"
        :roleinput="role"
        :gamemapinput="gamemap"
        :playerrankinput="playerrank"
        :herorankinput="herorank"
        :rolerankinput="rolerank"
        :talentbuildtypeinput="talentbuildtype"
        :mirrormatchinput="mirrormatch"
        :ranksourceinput="ranksource"
        :slrankinput="slrank"

        :gametypedefault="gametypedefault"
        :includetimeframe="true"
        :includeregion="true"
        :includestatfilter="true"
        :includeherolevel="true"
        :includerole="true"
        :includehero="true"
        :includegametype="true"
        :includegamemap="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
        :includemirror="true"
        :includegroupsize="true"
        :groupsizeadvanced="true"
        :groupsizemulti="true"
        :groupSizeDefaultValue="groupsize"
        :includetalentbuildtype="true"
        :includeranksource="true"
        :advancedfiltering="advancedfiltering"
        :buildtypedefault="defaultbuildtype"
  
        >
      </filters>
    </player-page-top>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="this.data.data" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <!-- Tier List and Patch Notes bars side by side; the open one's card shows full width below, and opening one closes the other -->
      <div :class="['grid gap-6', { 'md:grid-cols-2': patchNotesUrl }]">
        <panel-box title="Tier List" :divider="false" class="relative md:!py-4 cursor-pointer" @click="toggleTierList">
          <template #right>
            <button type="button" class="absolute top-4 right-4 md:right-6 h-8 flex items-center gap-2 text-sm text-subtext hover:text-white transition-colors" :aria-expanded="!tierListCollapsed" @click.stop="toggleTierList">
              {{ tierListCollapsed ? $t('Show') : $t('Hide') }}
              <basil-icon name="arrow-small" :class="['text-xs transition-transform', tierListCollapsed ? 'rotate-90' : '-rotate-90']"></basil-icon>
            </button>
          </template>
        </panel-box>
        <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]" body-target="#hero-stats-open-card" :expanded="patchNotesOpen" @toggle="patchNotesToggled"></patch-notes-panel>
      </div>
      <!-- display: contents, so it adds no gap while empty -->
      <div id="hero-stats-open-card" class="contents"></div>

      <transition name="drop">
      <panel-box v-if="!tierListCollapsed">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <p class="text-sm text-subtext">{{ $t('Heroes grouped by :stat', { stat: $t(tierStatLabel).toLowerCase() }) }}</p>
          <div class="flex flex-wrap items-center gap-4">
            <tab-button :small="true" :tabs="tierRoleTabs" :ignoreclick="true" :overridedefaultside="tierRole" :key="'role' + tierRoleTabs.length" @tab-click="tierRole = $event"></tab-button>
            <tab-button :small="true" :tabs="tierStatTabs" :ignoreclick="true" :overridedefaultside="tierStat" :key="tierStatTabs.length" @tab-click="tierStat = $event"></tab-button>
          </div>
        </div>
        <div class="flex flex-col gap-2">
          <div v-for="tier in tiers" :key="tier.letter" class="flex rounded-control bg-surface-1 overflow-hidden min-w-0">
            <div class="w-[8.5rem] md:w-[10.625rem] shrink-0 bg-surface-2 px-4 md:px-5 py-3 flex flex-col justify-center">
              <span :class="['text-4xl font-bold leading-none', tier.class]">{{ tier.letter }}</span>
              <span class="text-xs text-subtext mt-2">{{ tier.range }}</span>
              <span class="text-xs text-subtext">{{ $t(':count heroes', { count: tier.heroes.length }) }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 px-4 md:px-5 py-4 min-w-0">
              <a v-for="row in tier.heroes" :key="row.name" :href="getGlobalTalentsURL(row)">
                <hero-image-wrapper :hero="row" size="tier"></hero-image-wrapper>
              </a>
            </div>
          </div>
        </div>
      </panel-box>
      </transition>

      <dynamic-banner-ad v-if="!tierListCollapsed" :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <panel-box title="Hero Win Rates" subtitle="Popularity = pick rate + ban rate. Change is compared to the previous patch.">
        <template #right>
          <tab-button :small="true" tab1text="Table" tab2text="Chart" :ignoreclick="true" :overridedefaultside="togglechart ? 'right' : 'left'" @tab-click="togglechart = $event === 'right'"></tab-button>
        </template>

        <bubble-chart v-if="togglechart" :heroData="this.data.data"></bubble-chart>
        <div v-else id="table-container" ref="tablecontainer" class="overflow-clip">
          <table id="responsive-table" class="min-w-0 w-full rounded-table relative" ref="responsivetable">
            <thead>
              <tr>
                <!-- Wide enough for the "#" box beside the rank, so sorting by another column doesn't widen it, as on the leaderboard -->
                <th @click="sortTable('rank')" class="w-[calc(7.25rem+1.5px)] py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'rank' }">
                  <span v-if="rankChanged" class="inline-grid grid-cols-[2.75rem_2.5rem] gap-2 items-center"><span class="text-center">#</span><span class="text-left whitespace-nowrap">{{ $t('Rank') }}<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span></span>
                  <template v-else>#<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></template>
                </th>
                <th @click="sortTable('name')" class="py-2 px-3 text-left text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'name' }">
                  {{ $t('Hero') }}<sort-indicator :column="'name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('win_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate' }">
                  <!-- The ± beside each win rate is its 95% confidence interval, named and explained here -->
                  <div class="inline-flex items-center">
                    {{ $t('Win Rate') }}<span class="text-xs text-subtext ml-1.5">{{ $t('± conf.') }}</span>
                    <round-image class="hidden md:block" size="small" icon="fas fa-info" title="info" popupsize="large">
                      <slot>
                        <div>
                          <p>{{ $t('The ± figure is the 95% confidence range of the win rate: the hero’s true win rate is very likely within that many points of the one shown. Fewer games mean a wider range.') }}</p>
                        </div>
                      </slot>
                    </round-image>
                  </div><sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th v-if="showWinRateChange" @click="sortTable('win_rate_change')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate_change' }">
                  {{ $t('Change') }}<sort-indicator :column="'win_rate_change'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('popularity')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'popularity' }">
                  {{ $t('Popularity') }}<sort-indicator :column="'popularity'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('pick_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'pick_rate' }">
                  {{ $t('Pick Rate') }}<sort-indicator :column="'pick_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th v-if="this.gametype.includes('sl') && !this.groupSizeActive" @click="sortTable('ban_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'ban_rate' }">
                  {{ $t('Ban Rate') }}<sort-indicator :column="'ban_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('influence')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'influence' }">
                  <div class="inline-flex items-center">
                    {{ $t('Influence') }}
                    <round-image class="hidden md:block" size="small" icon="fas fa-info" title="info" popupsize="large">
                      <slot>
                        <div>
                          <p>{{ $t('Influence is an integer scaled from -5000 to 5000 that combines Win Rate, Pick Rate, and Ban Rate to determine the impact a hero will have on a particular team.') }}</p>
                        </div>
                      </slot>
                    </round-image>
                  </div><sort-indicator :column="'influence'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th @click="sortTable('games_played')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played' }">
                  {{ $t('Games') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th v-if="this.showStatTypeColumn" @click="sortTable('total_filter_type')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'total_filter_type' }">
                  {{ this.statfiltername }}<sort-indicator :column="'total_filter_type'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                </th>
                <th v-if="!this.groupSizeActive" class="py-2 px-3 text-center text-sm leading-4 w-px whitespace-nowrap">
                  {{ $t('Builds') }}
                </th>
              </tr>
            </thead>
            <tbody class="text-center">
              <!-- Every hero's average for the filters, set apart from the heroes by a heavier line (on the cells: the
                   rounded table's separate borders ignore a border on the row) -->
              <tr class="font-medium [&>td]:border-b-2 [&>td]:border-b-teal/60">
                <td class="py-3 px-3 text-subtext">—</td>
                <td class="py-3 px-3 text-left">{{ $t('Average (all heroes)') }}</td>
                <td class="py-3 px-3 whitespace-nowrap">{{ getValueFixed(data.average_win_rate) }}%<span class="text-xs text-subtext ml-2">&#177;{{ getValueFixed(data.average_confidence_interval) }}</span></td>
                <td v-if="showWinRateChange" class="py-3 px-3 text-subtext whitespace-nowrap">{{ getValueFixed(data.average_positive_win_rate_change) }} / {{ getValueFixed(data.average_negative_win_rate_change) }}</td>
                <td class="py-3 px-3">{{ getValueFixed(data.average_popularity) }}%</td>
                <td class="py-3 px-3">{{ getValueFixed(data.average_pick_rate) }}%</td>
                <td v-if="this.gametype.includes('sl') && !this.groupSizeActive" class="py-3 px-3">{{ getValueFixed(data.average_ban_rate) }}%</td>
                <td class="py-3 px-3 whitespace-nowrap">{{ getValueLocal(data.average_positive_influence) }} / {{ getValueLocal(data.average_negative_influence) }}</td>
                <td class="py-3 px-3">{{ getValueLocal(data.average_games_played) }}</td>
                <td v-if="this.showStatTypeColumn" class="py-3 px-3">{{ getValueLocal(getValueFixed(data.average_total_filter_type)) }}</td>
                <td v-if="!groupSizeActive"></td>
              </tr>
              <template v-for="(row, index) in sortedData" :key="row.name">
                <tr>
                  <td class="py-2 px-3"><div v-if="rankChanged" class="inline-grid grid-cols-[2.75rem_2.5rem] gap-2 items-center"><div class="bg-blue text-white py-1 rounded-lg text-center tabular-nums">{{ sortDir == 'desc' ? index + 1 : sortedData.length - index }}</div><span class="text-left tabular-nums">{{ row.rank }}</span></div><span v-else class="p-1">{{ row.rank }}</span></td>
                  <td class="py-2 px-3 max-md:w-[9.375rem]">
                    <a class="group/bt flex w-full items-center justify-start" :href="getGlobalTalentsURL(row)">
                      <hero-image-wrapper class="mr-3" mobileClick="true" :hero="row" :includehover="false"></hero-image-wrapper><span class="hidden md:block font-medium group-hover/bt:underline">{{ $heroName(row.name) }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity hidden md:inline"></basil-icon>
                    </a>
                  </td>
                  <td class="py-2 px-3 whitespace-nowrap">{{ getValueFixed(row.win_rate) }}%<span class="text-xs text-subtext ml-2">&#177;{{ getValueFixed(row.confidence_interval) }}</span></td>
                  <td v-if="showWinRateChange" :class="['py-2 px-3 whitespace-nowrap', row.win_rate_change > 0 ? 'text-win' : row.win_rate_change < 0 ? 'text-loss' : 'text-subtext']">
                    <template v-if="row.win_rate_change > 0">&#9650; +{{ getValueFixed(row.win_rate_change) }}</template>
                    <template v-else-if="row.win_rate_change < 0">&#9660; {{ getValueFixed(row.win_rate_change) }}</template>
                    <template v-else>0.00</template>
                  </td>
                  <td class="py-2 px-3">{{ getValueFixed(row.popularity) }}%</td>
                  <td class="py-2 px-3">{{ getValueFixed(row.pick_rate) }}%</td>
                  <td v-if="this.gametype.includes('sl') && !this.groupSizeActive" class="py-2 px-3">{{ getValueFixed(row.ban_rate) }}%</td>
                  <td class="py-2 px-3">{{ getValueLocal(row.influence) }}</td>
                  <td class="py-2 px-3">{{ getValueLocal(row.games_played) }}</td>
                  <td v-if="this.showStatTypeColumn" class="py-2 px-3">{{ getValueLocal(getValueFixed(row.total_filter_type)) }}</td>
                  <td v-if="!groupSizeActive" class="py-2 px-3">
                    <!-- Like Friend Foe's Matches column: a chevron that opens this hero's builds below the row -->
                    <button type="button" @click="viewtalentbuilds(row.name, index)" :aria-expanded="!!toggletalentbuilds[row.name]" :aria-label="$t(':hero talent builds', { hero: $heroName(row.name) })" :title="$t('Talent Builds')" class="w-full flex items-center justify-center min-h-[2rem] hover:text-lteal transition-colors">
                      <img v-if="loadingStates[row.name]" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="Loading"/>
                      <basil-icon v-else name="arrow-small" :class="['transition-transform', { 'rotate-90': toggletalentbuilds[row.name] }]"></basil-icon>
                    </button>
                  </td>
                </tr>
                <tr v-if="toggletalentbuilds[row.name] && talentbuilddata[row.name]">
                  <td colspan="12" class="p-3">
                    <global-talent-builds-section v-if="toggletalentbuilds[row.name] && talentbuilddata[row.name]" :talentbuilddata="talentbuilddata[row.name]" :buildtype="selectedbuildtype" :hero-win-rate="Number(row.win_rate)"></global-talent-builds-section>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </panel-box>
    </div>
    <!-- The same layout in grey while the stats load, the message on a tab of the first panel -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <!-- The two bars, as loaded -->
      <div :class="['grid gap-6', { 'md:grid-cols-2': patchNotesUrl }]">
        <panel-box title="Tier List" :skeleton="true" :divider="false" class="relative md:!py-4">
          <template #right>
            <span class="absolute top-4 right-4 md:right-6 h-8 flex items-center"><span class="text-sm skeleton-text">{{ tierListCollapsed ? $t('Show') : $t('Hide') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span></span>
          </template>
        </panel-box>
        <panel-box v-if="patchNotesUrl" title="Patch Notes" :skeleton="true" :divider="false" class="md:!py-4">
          <template #right>
            <span class="text-sm skeleton-text">{{ $t('Show') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
          </template>
        </panel-box>
      </div>
      <panel-box v-if="!tierListCollapsed" :skeleton="true">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <p class="text-sm"><span class="skeleton-text">Heroes grouped by win rate</span></p>
          <div class="flex flex-wrap items-center gap-4">
            <div class="relative">
              <tab-button class="invisible" :small="true" :tabs="skeletonRoleTabs" aria-hidden="true"></tab-button>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
            <div class="relative">
              <tab-button class="invisible" :small="true" :tabs="tierStatTabs" aria-hidden="true"></tab-button>
              <skeleton-box class="absolute inset-0"></skeleton-box>
            </div>
          </div>
        </div>
        <div class="flex flex-col gap-2">
          <div v-for="(count, tier) in [8, 22, 26, 22, 9]" :key="tier" class="flex rounded-control bg-surface-1 overflow-hidden min-w-0">
            <div class="w-[8.5rem] md:w-[10.625rem] shrink-0 bg-surface-2 px-4 md:px-5 py-3 flex flex-col justify-center items-start">
              <span class="text-4xl font-bold leading-none skeleton-text">S</span>
              <span class="text-xs mt-2 skeleton-text">51 – 53%</span>
              <span class="text-xs skeleton-text">00 heroes</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 px-4 md:px-5 py-4 min-w-0">
              <skeleton-box v-for="hero in count" :key="hero" class="w-10 h-10 md:w-[3.25rem] md:h-[3.25rem] !rounded-full"></skeleton-box>
            </div>
          </div>
        </div>
      </panel-box>

      <!-- The table is what's loading, so the message sits on its tab -->
      <panel-box title="Hero Win Rates" subtitle="Popularity = pick rate + ban rate. Change is compared to the previous patch." :skeleton="true" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true">{{ loadingMessage }}</loading-component>
        <template #right>
          <div class="relative">
            <tab-button class="invisible" :small="true" tab1text="Table" tab2text="Chart" aria-hidden="true"></tab-button>
            <skeleton-box class="absolute inset-0"></skeleton-box>
          </div>
        </template>
        <table-skeleton :bare="true" :rows="15" :columns="skeletonColumns"></table-skeleton>
      </panel-box>
    </div>
    <div v-else-if="dataError" class="flex flex-col items-center justify-center">
      {{ $t('Error: Reload page/filter') }}
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';
import { scaleTableToFit } from '../../../utils/scaleTable';
export default {
  name: 'GlobalHeroStats',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaulttimeframetype: String,
    defaulttimeframe: Array,
    advancedfiltering: Boolean,
    patreonUser: Boolean,
    defaultbuildtype: String,
    heroes: Object,
    urlparameters: Object,
  },
  data(){
    return {
      dataError: false,
      windowWidth: window.innerWidth,
      isLoading: false,
    	infoText: "Hero win rates based on differing increments, stat types, game type, or rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'desc',
      data: [],
      togglechart: false,
      // The column the tier list groups by
      tierStat: 'win_rate',
      // The tier list's role: 'All' or one role, picked separately from the filters' Role
      tierRole: 'All',
      skeletonRoleTabs: ['All', 'Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank'].map(role => ({ key: role, text: role })),
      // Collapsed unless it was opened before
      tierListCollapsed: Cookies.get('heroTierListCollapsed') !== 'false',
      patchNotesOpen: false,
      toggletalentbuilds: {},
      talentbuilddata: {},
      selectedbuildtype: null,
      cancelTokenSource: null,
      statfiltername: null,

      //Sending to filter
      timeframetype: null,
      timeframe: null,
      region: null,
      statfilter: "win_rate",
      herolevel: null,
      role: null,
      hero: null,
      gametype: null,
      gamemap: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
      mirrormatch: 0,
      groupsize: [],
      ranksource: 'hp',
      slrank: null,
      talentbuildtype: null,
      loadingStates: {},
      tablewidth: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;
    this.talentbuildtype = this.defaultbuildtype;


    if(this.urlparameters){
      this.setURLParameters();
    }

  	this.getData();
  },
  computed: {
    isSLRank(){
      return this.ranksource === 'sl';
    },
    showStatTypeColumn(){
      if(this.statfilter && this.statfilter != "win_rate" && !this.isLoading){
        return true;
      }
      return false;
    },
    groupSizeActive(){
      // `All`, or every size picked, is the unfiltered table rather than a stack size.
      const selected = Array.isArray(this.groupsize) ? this.groupsize : (this.groupsize ? [this.groupsize] : []);
      const allSizes = ["Solo", "Duo", "3 Players", "4 Players", "5 Players"];
      return selected.length > 0 && !selected.includes("All") && !allSizes.every(size => selected.includes(size));
    },
    patchNotesUrl() {
      if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
        return null;
      }
      const selectedVersion = this.timeframe[0];
      const match = this.filters.timeframes.find(t => t.code === selectedVersion);
      return match && match.patch_notes_url ? match.patch_notes_url : null;
    },
    showWinRateChange(){
      return (
        this.timeframetype !== 'major' &&
        this.timeframetype !== 'major_grouped' &&
        this.timeframe.length === 1 &&
        !this.region &&
        this.gametype.length === 1 &&
        this.gametype[0] !== 'br' &&
        this.gametype[0] !== 'cu' &&
        this.gametype[0] !== 'ud' &&
        !this.gamemap &&
        !this.playerrank &&
        !this.herorank &&
        !this.rolerank &&
        !this.herolevel &&
        this.statfilter === 'win_rate' &&
        this.mirrormatch == 0 &&
        !this.groupSizeActive &&
        !this.isSLRank
      );

    },
    loadingMessage() {
      if (this.determineIfVeryLargeData()) {
        return this.$t('Very large amount of data. Compiling can take 5-10 minutes. Please be patient.');
      }
      if (this.determineIfLargeData()) {
        return this.$t('Large amount of data. Please be patient.');
      }
      return this.$t('Loading hero stats...');
    },
    tierStatTabs() {
      const tabs = [
        { key: 'win_rate', text: 'Win Rate' },
        { key: 'popularity', text: 'Popularity' },
        { key: 'pick_rate', text: 'Pick Rate' },
      ];
      if (this.gametype.includes('sl') && !this.groupSizeActive) {
        tabs.push({ key: 'ban_rate', text: 'Ban Rate' });
      }
      tabs.push({ key: 'influence', text: 'Influence' });
      return tabs;
    },
    // All, then each role among the loaded heroes
    tierRoleTabs() {
      const rows = Array.isArray(this.data?.data) ? this.data.data.filter(row => row) : [];
      const roles = [...new Set(rows.map(row => row.role).filter(role => role))].sort();
      return [{ key: 'All', text: 'All' }, ...roles.map(role => ({ key: role, text: role }))];
    },
    tierStatLabel() {
      return (this.tierStatTabs.find(tab => tab.key === this.tierStat) || this.tierStatTabs[0]).text;
    },
    // Win rate uses fixed 2-point bands; the other columns split the heroes by percentile
    tiers() {
      const stat = this.tierStatTabs.some(tab => tab.key === this.tierStat) ? this.tierStat : 'win_rate';
      const role = this.tierRoleTabs.some(tab => tab.key === this.tierRole) ? this.tierRole : 'All';
      const rows = (Array.isArray(this.data?.data) ? this.data.data.filter(row => row && (role === 'All' || row.role === role)) : []).sort((a, b) => b[stat] - a[stat]);
      const letters = [
        { letter: 'S', class: 'text-[#f5c542]' },
        { letter: 'A', class: 'text-teal' },
        { letter: 'B', class: 'text-win' },
        { letter: 'C', class: 'text-subtext' },
        { letter: 'D', class: 'text-loss' },
      ];
      if (stat === 'win_rate') {
        const bands = [
          { min: 53, range: this.$t('53% and higher') },
          { min: 51, range: '51 – 53%' },
          { min: 49, range: '49 – 51%' },
          { min: 47, range: '47 – 49%' },
          { min: -Infinity, range: this.$t('Below 47%') },
        ];
        return letters.map((tier, i) => ({
          ...tier,
          range: bands[i].range,
          heroes: rows.filter(row => row.win_rate >= bands[i].min && (i === 0 || row.win_rate < bands[i - 1].min)),
        }));
      }
      const cuts = [0.1, 0.3, 0.7, 0.9, 1];
      const suffix = stat === 'influence' ? '' : '%';
      const format = value => (stat === 'influence' ? this.getValueLocal(value) : this.getValueFixed(value)) + suffix;
      return letters.map((tier, i) => {
        const heroes = rows.slice(Math.round((i ? cuts[i - 1] : 0) * rows.length), Math.round(cuts[i] * rows.length));
        return {
          ...tier,
          range: heroes.length ? format(heroes[heroes.length - 1][stat]) + ' – ' + format(heroes[0][stat]) : '',
          heroes,
        };
      });
    },
    // Sorting by anything but the default order gives each row a new position
    // The loading table's columns, matching what the loaded table will show for these filters
    skeletonColumns() {
      return [
        'rank', 'hero', 'stat',
        ...(this.showWinRateChange ? ['stat'] : []),
        'stat', 'stat',
        ...(this.gametype.includes('sl') && !this.groupSizeActive ? ['stat'] : []),
        'stat', 'stat',
        ...(!this.groupSizeActive ? ['rank'] : []),
      ];
    },
    rankChanged() {
      return !!this.sortKey && this.sortKey !== 'rank' && this.sortKey !== (this.statfilter === 'win_rate' ? 'win_rate' : 'total_filter_type');
    },
    sortedData() {
      const rows = Array.isArray(this.data?.data) ? this.data.data.filter(row => row) : [];
      if (!this.sortKey) return rows;
      return rows.sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    queryString(){      
      let queryString  = `?timeframe_type=${this.timeframetype}`;
      queryString += `&timeframe=${this.timeframe}`;

      queryString += `&game_type=${this.gametype}`;

      if(this.region){
        queryString += `&region=${this.region}`;
      }

      if(this.herolevel){
        queryString += `&hero_level=${this.herolevel}`;
      }

      if(this.gamemap){
        queryString += `&game_map=${this.gamemap}`;
      }

      if(this.hero){
        queryString += `&hero=${this.hero}`;
      }

      if(this.role){
        queryString += `&role=${this.role}`;
      }

    
      if(this.playerrank){
        queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
      }

      if(this.herorank){
        queryString += `&hero_league_tier=${this.convertRankIDtoName(this.herorank)}`;
      }

      if(this.rolerank){
        queryString += `&role_league_tier=${this.convertRankIDtoName(this.rolerank)}`;
      }

      if(this.groupSizeActive){
        queryString += `&group_size=${this.groupsize.join(',')}`;
      }

      if(this.isSLRank){
        queryString += `&rank_source=sl`;

        if(this.slrank){
          queryString += `&sl_rank=${this.slrank}`;
        }
      }

      queryString += `&statfilter=${this.statfilter}`;
      queryString += `&build_type=${this.talentbuildtype}`;
      queryString += `&mirror=${this.mirrormatch}`;

      return queryString;
    },
  },
  watch: {
    // Back from the chart, the table is drawn afresh, so it's shrunk to fit the screen again
    togglechart(showChart) {
      if (!showChart) {
        this.$nextTick(() => {
          const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            this.tablewidth = scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
  },
  methods: {
  	async getData(){
      this.dataError = false;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        this.data = [];

        const response = await this.$globalAsyncPost("/api/v1/global/hero", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          role: this.role,
          hero: this.hero,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirror: this.mirrormatch,
          groupsize: this.groupsize,
          rank_source: this.isSLRank ? 'sl' : null,
          sl_rank: this.isSLRank ? this.slrank : null,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        // The API returns heroes in rank order
        let rank = 0;
        (response.data?.data || []).forEach(row => { if (row) row.rank = ++rank; });
        this.data = response.data;
        this.loadingStates = this.sortedData.map(() => false);
      }catch(error){
        this.dataError = true;
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            this.tablewidth = scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
    async getTalentBuildData(hero, index){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        this.loadingStates[hero] = true;
        const response = await this.$globalAsyncPost("/api/v1/global/talents/build", {
          hero: hero,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirror: this.mirrormatch,
          talentbuildtype: this.talentbuildtype,
          rank_source: this.isSLRank ? 'sl' : null,
          sl_rank: this.isSLRank ? this.slrank : null,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.sortedData[index].talentbuilddata = response.data;
        this.talentbuilddata[hero] = response.data;
        this.loadingStates[hero] = false;

      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            this.tablewidth = scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : this.statfilter;
      this.statfiltername = this.statfilter ? this.filters.stat_filter.find(s => s.code === this.statfilter).name : null;

      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
      this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
      this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.groupsize = filteredData.multi["Group Size"] ? Array.from(filteredData.multi["Group Size"]) : [];
      this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.talentbuildtype;
      this.ranksource = filteredData.single["Rank Source"] ? filteredData.single["Rank Source"] : 'hp';
      this.slrank = this.isSLRank && filteredData.multi["SL Rank"] ? Array.from(filteredData.multi["SL Rank"]) : null;

      if(this.groupSizeActive && this.statfilter != 'win_rate'){
        this.statfilter = 'win_rate';
        this.statfiltername = this.filters.stat_filter.find(s => s.code === this.statfilter).name;
      }
      
      this.talentbuilddata = {};
      this.toggletalentbuilds = {};
      this.loadingStates = {};
      this.sortKey = '';
      this.sortDir = 'desc';

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
      this.sortKey = key;
    },
    toggleTierList() {
      // A click that closes an open filter menu only closes the menu
      if (document.querySelector('.expandable-dropdown')) return;
      this.tierListCollapsed = !this.tierListCollapsed;
      if (!this.tierListCollapsed) {
        this.patchNotesOpen = false;
      }
      Cookies.set('heroTierListCollapsed', this.tierListCollapsed ? 'true' : 'false', { expires: 365, path: '/' });
    },
    // Opening the notes closes the tier list for now; the saved choice stays as it was
    patchNotesToggled(open) {
      this.patchNotesOpen = open;
      if (open) {
        this.tierListCollapsed = true;
      }
    },
    toggleChartValue() {
      this.togglechart = !this.togglechart;
    },
    viewtalentbuilds(hero, index){
      if (!this.talentbuilddata[hero]) {
        this.loadingStates[hero] = true;
        this.getTalentBuildData(hero, index);
      }
      this.toggletalentbuilds[hero] = !this.toggletalentbuilds[hero];
    },
    determineIfLargeData(){
      if(this.timeframetype == "major" || this.timeframetype == "major_grouped" || this.timeframe.length >= 3 || this.statfilter != "win_rate"){
        return  true;
      }
      return false;
    },
    determineIfVeryLargeData(){
      if((this.timeframetype == "major" || this.timeframetype == "major_grouped") && this.timeframe && this.timeframe.length > 1){
        return true;
      }
      return false;
    },
    getGlobalTalentsURL(hero){
      var url = "";
      if(hero){
        if(this.queryString){
          url = '/Global/Talents/' + hero.name + this.queryString.replace(/&group_size=[^&]*/, '');
        }else{
          url = '/Global/Talents/' + hero.name;
        }
      }
      return url;
    },
    getValueFixed(value){
      return value != null && value !== "" && !isNaN(value) ? Number(value).toFixed(2) : "";
    },
    getValueLocal(value){
      return value != null && value !== "" ? value.toLocaleString('en-US') : "";
    },
    getHeroID(){
      if(this.hero){
        return this.heroes.find(hero => hero.name === this.hero).id
      }
      return null;
    },
    convertRankIDtoName(rankIDs) {
      return rankIDs.map(rankID => this.filters.rank_tiers.find(tier => tier.code == rankID).name);
    },
    setURLParameters(){
      if(this.urlparameters["timeframe_type"]){
        this.timeframetype = this.urlparameters["timeframe_type"];
      }
      
      if(this.urlparameters["timeframe"]){
        this.timeframe = this.urlparameters["timeframe"].split(',');
      }

      if(this.urlparameters["game_type"]){
        this.gametype = this.urlparameters["game_type"].split(',');
      }

      if(this.urlparameters["region"]){
        this.region = this.urlparameters["region"].split(',');
      }

      if(this.urlparameters["statfilter"]){
        this.statfilter = this.urlparameters["statfilter"];
        this.statfiltername = this.statfilter ? this.filters.stat_filter.find(s => s.code === this.statfilter).name : null;
      }
      
      if(this.urlparameters["hero_level"]){
        this.herolevel = this.urlparameters["hero_level"].split(',');
      }

      if(this.urlparameters["hero"]){
        this.hero = this.urlparameters["hero"];
      }

      if(this.urlparameters["role"]){
        this.role = this.urlparameters["role"];
      }

      if(this.urlparameters["game_map"]){
        this.gamemap = this.urlparameters["game_map"].split(',');
      }

      if (this.urlparameters["league_tier"]) {
        this.playerrank = this.urlparameters["league_tier"]
          .split(',')
          .map(tierName => {
              const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
              const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
              return tier?.code;
          });
      }

      if (this.urlparameters["hero_league_tier"]) {
        this.herorank = this.urlparameters["hero_league_tier"]
        .split(',')
        .map(tierName => {
            const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
            const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
            return tier?.code;
        });
      }

      if (this.urlparameters["role_league_tier"]) {
        this.rolerank = this.urlparameters["role_league_tier"]
        .split(',')
        .map(tierName => {
            const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
            const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
            return tier?.code;
        });
      }


      if (this.urlparameters["build_type"]) {
        this.talentbuildtype = this.urlparameters["build_type"];
      }

      if (this.urlparameters["mirror"]) {
        this.mirrormatch = this.urlparameters["mirror"];
      }

      if (this.urlparameters["rank_source"]) {
        this.ranksource = this.urlparameters["rank_source"];
      }

      if (this.isSLRank) {
        this.gametype = ['sl'];
      }

      if (this.urlparameters["sl_rank"] && this.isSLRank) {
        this.slrank = this.urlparameters["sl_rank"].split(',');
      }

      if (this.urlparameters["group_size"]) {
        this.groupsize = this.urlparameters["group_size"].split(',');

        if(this.groupSizeActive && this.statfilter != 'win_rate'){
          this.statfilter = 'win_rate';
          this.statfiltername = this.filters.stat_filter.find(s => s.code === this.statfilter).name;
        }
      }
    },
  }
}
</script>