<template>
  <div>
    <player-page-top>
      <template #heading>
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading :heading="'Heroes Profile MMR'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </template>
      <div class="w-full">
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypedefault="startingGameType"
        :playertypeinput="type"
        :heroinput="hero"
        :roleinput="role"
        :mapinput="map"
        :includesinglegametypefull="true"
        :playerheroroletype="true"
        :hideadvancedfilteringbutton="true"
        :rolerequired="true"
        :herorequired="true"
        :includemultiseason="true"
        :includegamedaterange="true"
        >
      </filters>
      </div>
    </player-page-top>
    
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <!-- Storm League MMR is being rebuilt; remove once the rebuild is done -->
    <div v-if="gametype === 'sl'" class="max-w-[120rem] mx-auto px-4 md:px-8 mb-6">
      <div class="flex items-center gap-3 rounded-control bg-surface-1 border-l-4 border-yellow px-5 py-3 text-sm">
        <basil-icon name="info" class="text-yellow text-xl shrink-0"></basil-icon>
        <span class="flex-1 min-w-0">{{ $t("We're rebuilding Storm League MMR. Until it's finished, the graph and table below may look wrong. Your MMR still goes up and down with each game you play, but the rebuilt values won't show until the rebuild is complete.") }}</span>
      </div>
    </div>

    <p v-if="data === ''" class="text-center text-subtext mt-10">{{ $t('No data for this player and filters') }}</p>
    <div v-else-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-8">

      <div class="grid xl:grid-cols-[1288fr_600fr] gap-8 items-stretch">
        <panel-box :title="$t('MMR over time') + ' · ' + selectionLabel" :subtitle="$t('HP :type MMR after every :gametype game.', { type: $t(type), gametype: gameTypeName })" :divider="false">
          <!-- Dots on the games once few enough are in view to tell apart -->
          <line-chart :data="zoomedData" :dataAttribute="'mmr'" :gridded="true" :aspect-ratio="2.4" :point-radius="zoomedData.length <= 120 ? 2 : 0" :point-hover-radius="5"
            :title="`${battletag} HP MMR Graph for ${gametype}`" :tooltip-label="`HP ${gameTypeName} MMR`"></line-chart>
          <!-- Drag either handle to zoom into a stretch of games -->
          <div v-if="reversedData.length > 1" class="flex flex-col gap-2">
            <range-slider v-model="zoom" :min="0" :max="reversedData.length - 1"></range-slider>
            <div class="flex items-center justify-between gap-4 text-sm text-subtext">
              <span>{{ zoomDate(zoom[0]) }}</span>
              <button v-if="isZoomed" type="button" class="text-white hover:underline" @click="resetZoom">{{ $t('Reset zoom') }}</button>
              <span>{{ zoomDate(zoom[1]) }}</span>
            </div>
          </div>
        </panel-box>

        <panel-box :title="$t('Current MMR') + ' · ' + selectionLabel" :divider="false">
          <div v-if="currentRow" class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-control text-sm font-medium" :style="tierStyle(currentTier)">{{ $t(currentTier) }}</span>
            <span class="text-4xl font-bold tabular-nums leading-none">{{ currentRow.mmr.toLocaleString('en-US') }}</span>
          </div>
          <p v-if="currentRow" class="text-sm text-subtext -mt-2">{{ $t(':tier · :gametype league tier', { tier: $t(currentTier), gametype: gameTypeName }) }}</p>

          <!-- One row per league, best first; yours names your division -->
          <div class="flex flex-col gap-1.5">
            <span class="text-xs font-bold uppercase text-subtext">{{ $t('League tier breakdown') }}</span>
            <template v-for="league in leagues" :key="league.tier">
              <button type="button" :disabled="!league.divisions.length" :aria-expanded="!!openLeagues[league.tier]" @click="openLeagues[league.tier] = !openLeagues[league.tier]"
                :class="['flex items-center justify-between gap-3 rounded-control px-2.5 py-1.5 text-left transition-colors', league.current ? 'bg-surface-2 font-bold' : 'bg-surface-1', league.divisions.length ? 'hover:bg-surface-3 cursor-pointer' : 'cursor-default']">
                <span class="px-2.5 py-0.5 rounded-control text-xs font-medium" :style="tierStyle(league.label)">{{ $t(league.label) }}</span>
                <span class="flex items-center gap-2 text-sm tabular-nums">
                  {{ league.range }}
                  <basil-icon v-if="league.divisions.length" name="arrow-small" :class="['text-xs transition-transform', openLeagues[league.tier] ? '-rotate-90' : 'rotate-90']"></basil-icon>
                  <span v-else class="w-3"></span>
                </span>
              </button>
              <div v-if="openLeagues[league.tier]" class="flex flex-col gap-1 pl-4">
                <div v-for="division in league.divisions" :key="division.label" :class="['flex items-center justify-between gap-3 rounded-control px-2.5 py-1 text-sm', division.current ? 'bg-surface-2 font-bold' : 'text-subtext']">
                  <span>{{ $t(division.label) }}</span>
                  <span class="tabular-nums pr-5">{{ division.range }}</span>
                </div>
              </div>
            </template>
          </div>

          <!-- Across the games the filters return -->
          <div class="flex flex-col gap-1.5">
            <span class="text-xs font-bold uppercase text-subtext">{{ $t('In these games') }}</span>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="tile in statTiles" :key="tile.label" class="bg-surface-1 rounded-control p-3 flex flex-col gap-0.5 min-w-0">
                <span class="text-xs text-subtext">{{ $t(tile.label) }}</span>
                <span :class="['text-xl font-bold tabular-nums', tile.class]">{{ tile.value }}</span>
                <span class="text-xs text-subtext">{{ tile.caption }}</span>
              </div>
            </div>
          </div>
        </panel-box>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <!-- Styled like the match history's compact rows -->
      <div ref="tablecontainer" class="scroll-mt-4">
        <panel-box :title="$t('Match History') + ' · ' + selectionLabel" subtitle="HP MMR after each game. Parsed date is when the replay was processed." :divider="false">
          <template #right>
            <pagination-bar v-if="lastPage > 1" :current="currentPage" :last="lastPage" @page="goToPage"></pagination-bar>
          </template>
          <!-- Wide rows scroll sideways on narrow screens -->
          <div class="overflow-x-auto">
            <div class="md:min-w-max w-full">
              <div class="max-md:hidden flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext">
                <span class="w-[21.25rem] shrink-0 pl-5 cursor-pointer" @click="sortTable('game_date')">{{ $t('Match') }}<sort-indicator column="game_date" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[8.75rem] shrink-0 px-4 cursor-pointer" @click="sortTable('replayID')">{{ $t('Match ID') }}<sort-indicator column="replayID" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[16.25rem] shrink-0 px-4 cursor-pointer" @click="sortTable('mmr_date_parsed')">{{ $t('Parsed') }}<sort-indicator column="mmr_date_parsed" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[8.75rem] shrink-0 px-4 text-center cursor-pointer" @click="sortTable('mmr')">{{ $t('HP MMR') }}<sort-indicator column="mmr" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[11.25rem] shrink-0 px-4 text-center cursor-pointer" @click="sortTable('mmr_change')">{{ $t('Change') }}<sort-indicator column="mmr_change" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
                <span class="w-[7.5rem] shrink-0 px-4 text-center cursor-pointer" @click="sortTable('winner')">{{ $t('Result') }}<sort-indicator column="winner" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
              </div>
              <template v-for="(row, index) in pagedData" :key="row.replayID">
                <div v-if="!patreonUser && index != 0 && index % 50 === 0" class="my-2">
                  <dynamic-banner-ad :patreon-user="patreonUser" :index="index + 3" :mobile-override="false"></dynamic-banner-ad>
                </div>
                <match-row-mobile class="md:hidden" :bg="index % 2 ? 'bg-surface-2' : 'bg-surface-1'" :href="'/Match/Single/' + row.replayID" :hero="row.hero" :title="$heroName(row.hero)" :date="formatRowDate(row.game_date)" :winner="row.winner === 'True'" :result="row.winner === 'True' ? 'Win' : 'Loss'">
                  <template v-if="notCalculated(row)">{{ $t('HP MMR not calculated yet') }}</template>
                  <template v-else>HP MMR <span class="text-white font-medium">{{ row.mmr.toLocaleString('en-US') }}</span> <span :class="row.mmr_change >= 0 ? 'text-win' : 'text-red'">({{ row.mmr_change > 0 ? '+' : '' }}{{ row.mmr_change.toFixed(2) }})</span></template>
                </match-row-mobile>
                <div :class="['max-md:hidden group/row relative flex items-center justify-between rounded overflow-hidden mb-1', index % 2 ? 'bg-surface-2' : 'bg-surface-1']">
                  <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
                  <a :href="'/Match/Single/' + row.replayID" class="absolute inset-0 z-[1]" :aria-label="$t('Open match')"></a>
                  <div class="relative w-[21.25rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
                    <div class="absolute inset-0 [mask-image:linear-gradient(to_right,black_60%,transparent)]" :class="row.winner === 'True' ? 'bg-[rgba(0,139,139,0.35)]' : 'bg-[rgba(179,54,22,0.13)]'"></div>
                    <div class="absolute inset-y-0 left-0 w-1.5" :class="row.winner === 'True' ? 'bg-win' : 'bg-red'"></div>
                    <div class="relative flex items-center gap-4">
                      <hero-image-wrapper size="row" :hero="row.hero" :excludehover="true"></hero-image-wrapper>
                      <div class="flex flex-col whitespace-nowrap">
                        <span class="font-bold">{{ $heroName(row.hero) }}</span>
                        <span class="text-xs">{{ formatRowDate(row.game_date) }}</span>
                      </div>
                    </div>
                  </div>
                  <span class="relative z-[2] w-[8.75rem] shrink-0 px-4"><a class="group/bt inline-flex items-center" :href="'/Match/Single/' + row.replayID"><span class="group-hover/bt:underline">{{ row.replayID }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a></span>
                  <!-- 1800 is the starting value before the game's MMR has been calculated -->
                  <template v-if="notCalculated(row)">
                  <span class="relative w-[16.25rem] shrink-0 px-4 text-sm text-subtext italic">{{ $t('Pending') }}</span>
                  <span class="relative w-[8.75rem] shrink-0 px-4 text-center text-sm text-subtext italic">{{ $t('Not calculated yet') }}</span>
                  <span class="relative w-[11.25rem] shrink-0 px-4 text-center text-subtext">-</span>
                  </template>
                  <template v-else>
                  <span class="relative w-[16.25rem] shrink-0 px-4 text-sm text-subtext whitespace-nowrap">{{ formatRowDate(row.mmr_date_parsed) }}</span>
                  <span class="relative w-[8.75rem] shrink-0 px-4 text-center font-bold tabular-nums">{{ row.mmr.toLocaleString('en-US') }}</span>
                  <div class="relative z-[2] w-[11.25rem] shrink-0 px-4 flex items-center justify-center gap-2">
                    <span class="font-medium tabular-nums whitespace-nowrap" :class="row.mmr_change >= 0 ? 'text-win' : 'text-red'">{{ row.mmr_change >= 0 ? '▲' : '▼' }} {{ row.mmr_change > 0 ? '+' : '' }}{{ row.mmr_change.toFixed(2) }}</span>
                    <!-- Down after a win or up after a loss: the FAQ explains why -->
                    <round-image :popup-hoverable="true" v-if="isUnexpectedChange(row)" class="text-xs" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                      <p class="font-normal">{{ $t('HP MMR measures your skill against everyone else, so it can :players. The system is finding the bracket you belong in, not only rewarding wins.', { players: row.winner === 'True' ? $t('drop after a win against much lower rated players') : $t('rise after a loss against much higher rated players') }) }}</p>
                      <a href="/FAQ#mmr-change-direction" class="group/bt inline-flex items-center mt-2 font-normal"><span class="group-hover/bt:underline">Read more in the FAQ</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
                    </round-image>
                  </div>
                  </template>
                  <span class="relative w-[7.5rem] shrink-0 px-4 text-center"><span :class="['inline-block px-2 py-0.5 rounded-[0.25rem] text-xs font-bold uppercase', row.winner === 'True' ? 'bg-win text-black' : 'bg-red text-white']">{{ row.winner === 'True' ? $t('Win') : $t('Loss') }}</span></span>
                </div>
              </template>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-4">
            <span class="text-sm text-subtext">{{ $t('Showing :from–:to of :total games', { from: (currentPage - 1) * pageSize + 1, to: (currentPage - 1) * pageSize + pagedData.length, total: data.length }) }}</span>
            <pagination-bar v-if="lastPage > 1" :current="currentPage" :last="lastPage" @page="goToPage($event, true)"></pagination-bar>
          </div>
        </panel-box>
      </div>
    </div>
    <!-- The same cards in grey while it loads, the message on a tab of the first -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-8">
      <div class="grid xl:grid-cols-[1288fr_600fr] gap-8 items-stretch">
        <panel-box title="MMR over time" subtitle="HP Player MMR after every Storm League game." :divider="false" :skeleton="true" class="relative md:!rounded-tl-none">
          <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading HP MMR...') }}</loading-component>
          <skeleton-box class="w-full aspect-[2.4]"></skeleton-box>
          <div class="flex flex-col gap-2">
            <div class="h-6 flex items-center"><skeleton-box class="h-1.5 w-full"></skeleton-box></div>
            <div class="flex items-center justify-between gap-4 text-sm">
              <span class="skeleton-text">21 Sep 2015</span>
              <span class="skeleton-text">7 Nov 2026</span>
            </div>
          </div>
        </panel-box>
        <panel-box title="Current MMR" :divider="false" :skeleton="true">
          <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-control text-sm font-medium skeleton-text">Diamond 3</span>
            <span class="text-4xl font-bold leading-none skeleton-text">0,000</span>
          </div>
          <p class="text-sm -mt-2"><span class="skeleton-text">Diamond 3 · Storm League league tier</span></p>
          <div class="flex flex-col gap-1.5">
            <span class="text-xs font-bold uppercase skeleton-text w-fit">{{ $t('League tier breakdown') }}</span>
            <div v-for="row in 6" :key="row" class="flex items-center justify-between gap-3 rounded-control px-2.5 py-1.5 bg-surface-1">
              <span class="px-2.5 py-0.5 rounded-control text-xs font-medium skeleton-text">Platinum</span>
              <span class="text-sm skeleton-text">0,000 – 0,000</span>
            </div>
          </div>
          <div class="flex flex-col gap-1.5">
            <span class="text-xs font-bold uppercase skeleton-text w-fit">{{ $t('In these games') }}</span>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="tile in 4" :key="tile" class="bg-surface-1 rounded-control p-3 flex flex-col gap-0.5 min-w-0 items-start">
                <span class="text-xs skeleton-text">Peak MMR</span>
                <span class="text-xl font-bold skeleton-text">0,000</span>
                <span class="text-xs skeleton-text">00 Nov 2026</span>
              </div>
            </div>
          </div>
        </panel-box>
      </div>

      <!-- Where the loaded page has its ad slot, so the gap above Match History matches -->
      <div></div>

      <panel-box title="Match History" subtitle="HP MMR after each game. Parsed date is when the replay was processed." :divider="false" :skeleton="true">
        <div class="overflow-x-auto">
          <div class="md:min-w-max w-full">
            <div class="max-md:hidden flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium">
              <span class="w-[21.25rem] shrink-0 pl-5"><span class="skeleton-text">{{ $t('Match') }}</span></span>
              <span class="w-[8.75rem] shrink-0 px-4"><span class="skeleton-text">{{ $t('Match ID') }}</span></span>
              <span class="w-[16.25rem] shrink-0 px-4"><span class="skeleton-text">{{ $t('Parsed') }}</span></span>
              <span class="w-[8.75rem] shrink-0 px-4 text-center"><span class="skeleton-text">{{ $t('HP MMR') }}</span></span>
              <span class="w-[11.25rem] shrink-0 px-4 text-center"><span class="skeleton-text">{{ $t('Change') }}</span></span>
              <span class="w-[7.5rem] shrink-0 px-4 text-center"><span class="skeleton-text">{{ $t('Result') }}</span></span>
            </div>
            <div v-for="row in 10" :key="row" :class="['flex items-center justify-between rounded overflow-hidden mb-1', row % 2 ? 'bg-surface-1' : 'bg-surface-2']">
              <div class="w-[21.25rem] shrink-0 flex items-center gap-4 pl-5 py-2.5">
                <skeleton-box class="w-[2.625rem] h-[2.625rem] !rounded-full shrink-0"></skeleton-box>
                <div class="flex flex-col items-start whitespace-nowrap">
                  <span class="font-bold skeleton-text">Malthael</span>
                  <span class="text-xs skeleton-text">October 18, 2026 at 9:35 PM</span>
                </div>
              </div>
              <span class="w-[8.75rem] shrink-0 px-4"><span class="skeleton-text">66135342</span></span>
              <span class="w-[16.25rem] shrink-0 px-4 text-sm"><span class="skeleton-text">October 18, 2026 at 9:35 PM</span></span>
              <span class="w-[8.75rem] shrink-0 px-4 text-center font-bold"><span class="skeleton-text">0,000</span></span>
              <span class="w-[11.25rem] shrink-0 px-4 text-center font-medium"><span class="skeleton-text">▲ +0.00</span></span>
              <span class="w-[7.5rem] shrink-0 px-4 text-center"><span class="inline-block px-2 py-0.5 text-xs font-bold skeleton-text">WIN</span></span>
            </div>
          </div>
        </div>
      </panel-box>
    </div>

  </div>

</template>

<script>
import { formatDate as formatLocalDate } from '../../utils/formatGameDate';
import { siteLocale } from '../../utils/translate';
import moment from 'moment-timezone';
import { tierStyle } from '../RankTierPill.vue';

export default {
  name: 'MmrData',
  components: {
  },
  props: {
    filters: Object,
    playerloadsetting: {
      type: [String, Boolean]
    },
    gametypedefault: Array,
    urlparameters: { type: Object, default: () => ({}) },
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    region: {
      type: [String, Number]
    },
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
  },
  data(){
    return {
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      gametype: null,
      season: null,
      startdate: null,
      enddate: null,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      currentPage: 1,
      pageSize: 100,
      leaguedata: null,
      // The chart's visible games, as [first, last] indexes into the oldest-first list
      zoom: [0, 0],
      // By league tier: whether its divisions are showing
      openLeagues: {},
      type: "Player",
      // Hero id and role name
      hero: null,
      role: null,
      // Map name
      map: null,
      infoText: "",
    }
  },
  created(){
    this.gametype = this.startingGameType[0];

    // ?hero=, ?role= or ?map= by name, from the hero, role and map pages' MMR panels
    const hero = this.filters.heroes.find(item => item.name === this.urlparameters?.hero);
    const role = this.filters.role.find(item => item.code === this.urlparameters?.role);
    const map = this.filters.game_maps.find(item => item.code === this.urlparameters?.map);
    if (hero) {
      this.type = 'Hero';
      this.hero = hero.code;
    } else if (role) {
      this.type = 'Role';
      this.role = role.code;
    } else if (map) {
      this.type = 'Map';
      this.map = map.code;
    }
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData();
    }
  },
  computed: {
    // A ?game_type= from an MMR panel link wins over the saved default
    startingGameType() {
      const requested = this.urlparameters?.game_type;
      return requested && this.filters.game_types_full.some(type => type.code === requested) ? [requested] : this.gametypedefault;
    },
    reversedData() {
      if (!Array.isArray(this.data)) {
        console.error("this.data is not an array.");
        return [];
      }
      return [...this.data].reverse();
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
    // The applied game type, and the hero or role for those MMRs, for the section titles
    selectionLabel() {
      const parts = [this.$t(this.gameTypeName)];
      if (this.type === 'Hero' && this.hero) {
        parts.push(this.$heroName(this.filters.heroes.find(item => item.code == this.hero)?.name ?? ''));
      } else if (this.type === 'Role' && this.role) {
        parts.push(this.$t(this.filters.role.find(item => item.code == this.role)?.name ?? this.role));
      } else if (this.type === 'Map' && this.map) {
        parts.push(this.$mapName(this.map));
      }
      return parts.filter(Boolean).join(' · ');
    },
    gameTypeName() {
      return this.filters.game_types_full.find(type => type.code === this.gametype)?.name ?? this.gametype.toUpperCase();
    },
    lastPage() {
      return Math.max(1, Math.ceil((this.data ? this.data.length : 0) / this.pageSize));
    },
    pagedData() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.sortedData.slice(start, start + this.pageSize);
    },
    // The latest game with a calculated MMR
    currentRow() {
      return Array.isArray(this.data) ? this.data.find(row => !this.notCalculated(row)) || null : null;
    },
    // The division found for the current MMR, or the league's own name above Diamond
    currentTier() {
      const found = (this.leaguedata || []).find(row => this.checkIfTierFound(row.min_mmr, row.max_mmr, row.tierFound));
      return found ? this.printLeagueName(found.tier, found.league_tier) : 'Unranked';
    },
    // One row per league, best first; the current league is named by its division
    leagues() {
      const byTier = {};
      (this.leaguedata || []).forEach(row => {
        const league = byTier[row.league_tier] || (byTier[row.league_tier] = { tier: row.league_tier, min: row.min_mmr, max: row.max_mmr, current: false });
        league.min = Math.min(league.min, row.min_mmr);
        league.max = row.max_mmr ? Math.max(league.max || 0, row.max_mmr) : null;
        if (this.checkIfTierFound(row.min_mmr, row.max_mmr, row.tierFound)) {
          league.current = true;
        }
      });
      const format = value => Math.round(value).toLocaleString('en-US');
      return Object.values(byTier).sort((a, b) => b.tier - a.tier).map(league => {
        const name = this.printLeagueName(null, league.tier);
        const divisions = [];
        if (league.max) {
          const step = (league.max - league.min) / 5;
          for (let division = 1; division <= 5; division++) {
            const max = league.max - step * (division - 1);
            const label = `${name} ${division}`;
            divisions.push({ label, range: `${format(max - step)} – ${format(max)}`, current: label === this.currentTier });
          }
        }
        return {
          tier: league.tier,
          current: league.current,
          label: league.current ? this.currentTier : name,
          range: league.max ? `${format(league.min)} – ${format(league.max)}` : `${format(league.min)}+`,
          divisions,
        };
      });
    },
    // Peak, change, games and win rate across the games the filters return
    statTiles() {
      const rows = (Array.isArray(this.data) ? this.data : []).filter(row => !this.notCalculated(row));
      if (!rows.length) {
        return [];
      }
      const peak = rows.reduce((best, row) => (row.mmr > best.mmr ? row : best), rows[0]);
      const change = rows[0].mmr - rows[rows.length - 1].mmr;
      const all = this.data;
      const wins = all.filter(row => row.winner === 'True').length;
      const recent = all.slice(0, 20);
      const recentWins = recent.filter(row => row.winner === 'True').length;
      const winRate = (wins / all.length) * 100;
      return [
        { label: 'Peak MMR', value: peak.mmr.toLocaleString('en-US'), caption: formatLocalDate(peak.game_date, { dateStyle: 'medium', timeZone: this.userTimezone }) },
        { label: 'Change', value: (change >= 0 ? '+' : '') + Math.round(change).toLocaleString('en-US'), class: change >= 0 ? 'text-win' : 'text-loss', caption: this.$t('from :mmr', { mmr: rows[rows.length - 1].mmr.toLocaleString('en-US') }) },
        { label: 'Games', value: all.length.toLocaleString('en-US'), caption: this.$t(':wins W · :losses L', { wins: wins.toLocaleString('en-US'), losses: (all.length - wins).toLocaleString('en-US') }) },
        { label: 'Win rate', value: winRate.toFixed(1) + '%', class: winRate >= 50 ? 'text-win' : 'text-loss', caption: this.$t('last :count: :rate%', { count: recent.length, rate: Math.round((recentWins / recent.length) * 100) }) },
      ];
    },
    // The games between the slider's handles, oldest first
    zoomedData() {
      return this.reversedData.slice(this.zoom[0], this.zoom[1] + 1);
    },
    isZoomed() {
      return this.zoom[0] > 0 || this.zoom[1] < this.reversedData.length - 1;
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
      this.isLoading = true;
      this.currentPage = 1;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/player/mmr", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          battletag: this.battletag,
          game_type: this.gametype,
          type: this.type,
          hero: this.hero,
          role: this.role,
          map: this.type === 'Map' ? this.map : null,
          season: this.season,
          start_date: this.startdate,
          end_date: this.enddate,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data.tableData || '';
        this.leaguedata = response.data.leagueData;
        this.openLeagues = {};
        this.resetZoom();
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
    filterData(filteredData){
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.map = filteredData.single["Map"] ? filteredData.single["Map"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      this.type = filteredData.single["Type"] ? filteredData.single["Type"] : "Player";
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;

      this.data = null;
      this.sortKey = '';
      this.sortDir ='asc';
      this.getData();

    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
      this.currentPage = 1;
    },
    resetZoom() {
      this.zoom = [0, Math.max(0, (Array.isArray(this.data) ? this.data.length : 1) - 1)];
    },
    zoomDate(index) {
      const row = this.reversedData[index];
      return row ? formatLocalDate(row.game_date, { dateStyle: 'medium', timeZone: this.userTimezone }) : '';
    },
    isUnexpectedChange(row) {
      return (row.winner === 'True' && row.mmr_change < 0) || (row.winner === 'False' && row.mmr_change > 0);
    },
    // From the bottom bar, bring the top of the table back into view
    goToPage(page, fromBottom = false) {
      this.currentPage = Math.min(Math.max(page, 1), this.lastPage);
      if (fromBottom) {
        this.$nextTick(() => this.$refs.tablecontainer?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      }
    },
    printLeagueName(tier, tierID){
      if(tier){
        return tier;
      }
      if(tierID == 1){
        return "Bronze";
      }else if(tierID == 2){
        return "Silver";
      }else if(tierID == 3){
        return "Gold";
      }else if(tierID == 4){
        return "Platinum";
      }else if(tierID == 5){
        return "Diamond";
      }else if(tierID == 6){
        return "Master";
      }
    },
    tierStyle,
    notCalculated(row){
      return row.mmr === 1800;
    },
    checkIfTierFound(min_mmr, max_mmr, tierFound){
      if(tierFound){
        return true;
      }

      if(this.data[0].mmr > min_mmr && (!max_mmr || max_mmr == 0 || max_mmr == "")){
        return true;
      }

      return false;
    },
    // In the viewer's own date format and timezone, like the match history rows
    formatRowDate(dateString) {
      return moment.utc(dateString).toDate().toLocaleString(siteLocale(), {
        dateStyle: 'long',
        timeStyle: 'short',
        timeZone: this.userTimezone,
      });
    },
  }
}
</script>