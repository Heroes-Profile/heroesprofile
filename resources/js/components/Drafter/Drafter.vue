<template>
  <div>
    <!-- Title on the left, the filters in their card beside it, as on the player pages -->
    <player-page-top>
      <template #heading>
        <page-heading :heading="'Drafter'" :subheading="'Draft helper'"></page-heading>
      </template>
      <filters
        :isLoading="isLoading"
        :onFilter="filterData"
        :filters="drafterFilters"
        :timeframetypeinput="'minor'"
        :timeframeinput="timeframe"
        :gametypeinput="gametype"
        :regioninput="region"
        :herolevelinput="herolevel"
        :gamemapinput="gamemap"
        :playerrankinput="playerrank"
        :herorankinput="herorank"
        :rolerankinput="rolerank"
        :gametypedefault="gametypedefault"
        :includetimeframe="true"
        :includeregion="true"
        :includeherolevel="true"
        :includesinglegamemap="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
        :advancedfiltersinfo="'Advanced filters may take a lot longer to run.'"
        :autoapply="true"
      ></filters>
    </player-page-top>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <!-- Whose turn it is, the whole ban and pick order, and the draft controls -->
      <div class="bg-surface rounded px-4 md:px-8 py-3 flex flex-wrap items-center gap-x-6 gap-y-3">
        <div class="shrink-0 w-[15rem]">
          <span class="block text-xs font-semibold uppercase tracking-wider text-subtext">{{ $t('Now') }}</span>
          <span class="flex items-center gap-2.5 text-lg font-semibold">
            <span v-if="!draftComplete" :class="['w-2.5 h-2.5 rounded-full shrink-0', currentTeam === 'team1' ? 'bg-teal' : 'bg-red']"></span>
            {{ stepLabel }}
          </span>
        </div>
        <!-- The draft as a timeline: a lane per team, each step in its own column in draft order, grouped by phase.
             Steps taken show their hero and can be clicked to go back to them -->
        <!-- Padded inside and pulled back out, so the scroll box doesn't crop the current step's glow -->
        <div class="flex items-stretch gap-4 min-w-0 overflow-x-auto p-3 -m-3">
          <div class="grid grid-rows-[1rem_2.5rem_2.5rem] gap-y-2 shrink-0 text-sm font-semibold">
            <span></span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-teal"></span>{{ $t('You') }}</span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red"></span>{{ $t('Enemy') }}</span>
          </div>
          <template v-for="(phase, phaseIndex) in draftPhases" :key="phaseIndex">
            <div v-if="phaseIndex > 0" class="w-px bg-gray-dark shrink-0"></div>
            <div class="grid grid-rows-[1rem_2.5rem_2.5rem] gap-y-2 gap-x-1.5 shrink-0" :style="{ gridTemplateColumns: `repeat(${phase.steps.length}, 2.625rem)` }">
              <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-subtext whitespace-nowrap" :style="{ gridColumn: `1 / span ${phase.steps.length}` }">
                <span v-if="phase.ban" class="text-sm font-normal leading-none">⊘</span>{{ $t(phase.ban ? 'Bans' : 'Picks') }}
              </span>
              <button v-for="(index, column) in phase.steps" :key="pickOrder[index]" type="button" :disabled="index >= currentPick || isLoading"
                :style="{ gridColumn: column + 1, gridRow: pickOrder[index].startsWith('team1') ? 2 : 3 }"
                :title="index < currentPick ? $t('Go back to :step', { step: slotLabel(pickOrder[index]) }) : slotLabel(pickOrder[index])"
                :class="['relative w-full h-full flex items-center justify-center text-xs font-bold disabled:cursor-default', stepClass(index)]"
                @click="goBackTo(index)">
                <template v-if="index < currentPick && slotHero(pickOrder[index])">
                  <span v-if="phase.ban" class="relative w-10 h-10">
                    <span class="absolute inset-0 rounded-full bg-cover bg-center opacity-60" :style="slotStyle(pickOrder[index])"></span>
                    <span :class="['absolute inset-0 rounded-full border-2', pickOrder[index].startsWith('team1') ? 'border-[rgba(0,153,148,0.8)]' : 'border-[rgba(228,74,35,0.8)]']"></span>
                  </span>
                  <span v-else :class="['w-10 h-10 rounded-full bg-cover bg-center border-2', pickOrder[index].startsWith('team1') ? 'border-teal' : 'border-red']" :style="slotStyle(pickOrder[index])"></span>
                  <!-- A ban's portrait is struck through -->
                  <span v-if="phase.ban" :class="['absolute inset-0 m-auto w-10 h-10 rounded-full', pickOrder[index].startsWith('team1') ? 'ban-strike-teal' : 'ban-strike-red']"></span>
                </template>
                <template v-else-if="phase.ban">⊘&nbsp;{{ slotNumber(pickOrder[index]) }}</template>
                <template v-else>P{{ slotNumber(pickOrder[index]) }}</template>
              </button>
            </div>
          </template>
        </div>
        <div class="flex flex-wrap items-center gap-3 ml-auto">
          <label class="flex items-center gap-2 text-sm text-subtext cursor-pointer">
            <input type="checkbox" class="form-checkbox" :checked="mockDraft" @change="setMockDraft($event.target.checked)">{{ $t('No data') }}
          </label>
          <custom-button :text="'Swap sides'" :alt="'Swap sides'" color="blue" :ignoreclick="true" @click="swapSides"></custom-button>
          <custom-button :text="'Reset draft'" :alt="'Reset draft'" color="red" :ignoreclick="true" @click="restartDraft"></custom-button>
        </div>
      </div>

      <div class="grid xl:grid-cols-[320fr_1220fr_320fr] gap-6 items-stretch">
        <!-- Your team, then the hero board, then the enemy team -->
        <div v-for="team in ['team1', 'team2']" :key="team" :class="['bg-surface rounded p-4 md:p-6 flex flex-col gap-5 min-w-0', team === 'team1' ? 'xl:order-1' : 'xl:order-3']">
          <h3 class="flex items-center gap-2.5 text-xl font-bold">
            <span :class="['w-1 h-5 rounded-full', team === 'team1' ? 'bg-teal' : 'bg-red']"></span>{{ team === 'team1' ? $t('Your team') : $t('Enemy team') }}
          </h3>
          <div class="flex flex-col gap-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-subtext">{{ $t('Bans') }}</span>
            <div class="grid grid-cols-3 gap-2">
              <div v-for="ban in [1, 2, 3]" :key="ban"
                :class="['h-[4.75rem] rounded-control flex flex-col items-center justify-center gap-1 px-1 min-w-0 border-2', slotHero(team + '-ban' + ban) ? 'bg-surface-1 border-surface-1'
                  : ['!border-[1.5px] border-dashed', team === 'team1' ? 'border-[#009994]' : 'border-lred', isActive(team + '-ban' + ban) ? (team === 'team1' ? 'bg-teal/15' : 'bg-red/15') : 'bg-surface-1']]">
                <template v-if="slotHero(team + '-ban' + ban)">
                  <span class="relative w-10 h-10 shrink-0">
                    <span class="absolute inset-0 rounded-full bg-cover bg-center opacity-60" :style="slotStyle(team + '-ban' + ban)"></span>
                    <span :class="['absolute inset-0 rounded-full border-2', team === 'team1' ? 'border-[rgba(0,153,148,0.8)]' : 'border-[rgba(228,74,35,0.8)]']"></span>
                    <span :class="['absolute inset-0 rounded-full', team === 'team1' ? 'ban-strike-teal' : 'ban-strike-red']"></span>
                  </span>
                  <span class="text-xs text-subtext truncate max-w-full">{{ $heroName(slotHero(team + '-ban' + ban)) }}</span>
                </template>
              </div>
            </div>
          </div>
          <div class="flex flex-col gap-3 flex-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-subtext">{{ $t('Picks') }}</span>
            <div class="flex flex-col gap-2 flex-1">
              <div v-for="pick in [1, 2, 3, 4, 5]" :key="pick"
                :class="['flex-1 min-h-[4rem] rounded-control bg-surface-1 px-3 py-2 flex items-center gap-3 border-2', isActive(team + '-pick' + pick) ? (team === 'team1' ? 'border-teal' : 'border-red') : 'border-surface-1']">
                <span :class="['w-12 h-12 shrink-0 rounded-full bg-cover bg-center', slotHero(team + '-pick' + pick) ? (team === 'team1' ? 'border-2 border-teal' : 'border-2 border-red')
                  : isActive(team + '-pick' + pick) ? (team === 'team1' ? 'border-2 border-dashed border-teal bg-teal/20' : 'border-2 border-dashed border-red bg-red/20') : 'bg-surface-3']" :style="slotStyle(team + '-pick' + pick)"></span>
                <span class="flex flex-col min-w-0">
                  <span class="font-semibold truncate">{{ slotHero(team + '-pick' + pick) ? $heroName(slotHero(team + '-pick' + pick)) : $t('Pick :number', { number: pick }) }}</span>
                  <span v-if="slotHero(team + '-pick' + pick)" class="text-sm text-subtext truncate">{{ $t('Pick :number', { number: pick }) }} · {{ $t(slotHero(team + '-pick' + pick).new_role) }}</span>
                  <span v-else-if="isActive(team + '-pick' + pick)" :class="['text-sm truncate', team === 'team1' ? 'text-lteal' : 'text-lred']">{{ $t('Picking now...') }}</span>
                  <span v-else class="text-sm text-subtext truncate">{{ $t('Waiting') }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-surface rounded p-4 md:p-6 flex flex-col gap-5 min-w-0 xl:order-2">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <search-input v-model="searchQuery" :placeholder="$t('Search heroes')" class="w-full sm:w-[19.75rem]"></search-input>
            <div class="flex flex-wrap items-center gap-3">
              <tab-button :small="true" :tabs="roleTabs" :ignoreclick="true" :overridedefaultside="selectedRole" @tab-click="selectedRole = $event"></tab-button>
              <tab-button v-if="!mockDraft" :small="true" :tabs="sortTabs" :ignoreclick="true" :overridedefaultside="sortMode" @tab-click="sortMode = $event"></tab-button>
              <tab-button v-if="!mockDraft" :small="true" :tabs="suggestTabs" :ignoreclick="true" :overridedefaultside="suggestBy" @tab-click="suggestBy = $event"></tab-button>
            </div>
          </div>

          <!-- The three best HP Draft Values for this step. It always keeps its place, so the board doesn't move as the draft goes on -->
          <div v-if="!mockDraft" :class="['rounded-control bg-[rgba(245,197,66,0.08)] px-5 py-4 min-h-[4.75rem] flex flex-wrap items-center gap-x-6 gap-y-3', { invisible: !waiting && !topSuggestions.length }]">
            <loading-component v-if="waiting" :compact="true" :textoverride="true">{{ $t('Loading draft options...') }}</loading-component>
            <template v-else>
            <div class="flex flex-col">
              <span class="font-semibold text-[#f5c542]">{{ isBanStep ? $t('Suggested bans') : $t('Suggested picks') }}</span>
              <span class="text-sm text-subtext">{{ $t(suggestMetric.caption) }}</span>
            </div>
            <button v-for="hero in topSuggestions" :key="hero.id" type="button" class="flex items-center gap-2.5 rounded-full bg-surface-1 hover:bg-surface-3 transition-colors pl-1 pr-4 py-1" @click="pickHero(hero)">
              <img :src="'/images/heroes/' + hero.short_name + '.png'" :alt="$heroName(hero)" class="w-9 h-9 rounded-full object-cover">
              <span class="flex flex-col items-start leading-tight">
                <span class="text-sm font-semibold">{{ $heroName(hero) }}</span>
                <span class="text-xs text-header">{{ suggestMetric.format(hero) }}</span>
              </span>
            </button>
            </template>
          </div>

          <div v-if="dataError || (needsCompositions && compositionsError)" class="text-center text-red py-10">{{ $t('Failed to load draft data. Please try again.') }}</div>
          <div v-else-if="waiting" class="grid grid-cols-[repeat(auto-fill,minmax(4.75rem,1fr))] gap-y-3">
            <div v-for="hero in visibleSuggestions.length" :key="hero" class="flex flex-col items-center gap-1.5">
              <skeleton-box class="w-16 h-16 !rounded-full"></skeleton-box>
              <span class="text-xs skeleton-text">Abathur</span>
            </div>
          </div>
          <div v-else class="grid grid-cols-[repeat(auto-fill,minmax(4.75rem,1fr))] gap-y-3">
            <div v-for="hero in visibleSuggestions" :key="hero.id" :class="['relative flex flex-col items-center gap-1.5 min-w-0', hero.unavailable ? 'opacity-30 grayscale' : 'cursor-pointer']" @click="!hero.unavailable && pickHero(hero)">
              <!-- Suggested heroes glow in the suggestions strip's gold -->
              <hero-image-wrapper :hero="hero" size="medium" :popup-hoverable="false" :class="['rounded-full', { 'ring-[3px] ring-[#f5c542] shadow-[0_0_1.25rem_0.25rem_rgba(245,197,66,0.8)]': glowIds.has(hero.id) }]">
                <h2>{{ $heroName(hero) }}</h2>
                <div v-if="!mockDraft && !hero.unavailable" class="text-left">
                  <p>{{ $t('HP Draft Value: :value', { value: hero.value.toFixed(2) }) }}</p>
                  <p>{{ isBanStep ? $t('Games Banned') : $t('Games Played') }}: {{ hero.games.toLocaleString('en-US') }}</p>
                  <p>{{ $t('Influence: :influence', { influence: hero.influence }) }}</p>
                  <p v-if="isBanStep">{{ $t('Ban Rate: :rate%', { rate: hero.ban_rate }) }}</p>
                  <p>{{ $t('Win Rate: :rate%', { rate: hero.win_rate }) }}</p>
                  <p>Win Rate Confidence: &plusmn; {{ hero.confidence }}%</p>
                  <p>{{ $t('Draft Pick Order Rate: :percent%', { percent: hero.pick_order_percent.toFixed(2) }) }}</p>
                </div>
              </hero-image-wrapper>
              <span v-if="takenHeroes[hero.id]" :class="['absolute top-6 left-1/2 -translate-x-1/2 z-10 px-1.5 py-0.5 rounded-[0.25rem] text-[0.625rem] font-bold uppercase tracking-wide text-white', takenHeroes[hero.id].ban ? 'bg-surface-4' : takenHeroes[hero.id].ours ? 'bg-teal' : 'bg-red']">{{ takenHeroes[hero.id].ban ? $t('Banned') : $t('Picked') }}</span>
              <span class="text-xs text-subtext text-center truncate max-w-full">{{ $heroName(hero) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const PICK_ORDER_TEAM1_FIRST = [
  'team1-ban1', 'team2-ban1', 'team1-ban2', 'team2-ban2',
  'team1-pick1', 'team2-pick1', 'team2-pick2', 'team1-pick2', 'team1-pick3',
  'team2-ban3', 'team1-ban3',
  'team2-pick3', 'team2-pick4', 'team1-pick4', 'team1-pick5', 'team2-pick5',
];
const PICK_ORDER_TEAM2_FIRST = PICK_ORDER_TEAM1_FIRST.map(slot => slot.startsWith('team1') ? slot.replace('team1', 'team2') : slot.replace('team2', 'team1'));

// Pick numbers belonging to the first and second picking team
const FIRST_TEAM_PICKS = [4, 7, 8, 13, 14];
const SECOND_TEAM_PICKS = [5, 6, 11, 12, 15];

// Pick positions each phase's draft order rate is normalised over
const BAN_POSITIONS = [0, 1, 2, 3];
const INITIAL_POSITIONS = [4, 5, 6, 7, 8, 11, 12, 13, 14, 15];
const COMPOSITION_POSITIONS = [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];

// Cho'gall picks where the other half would land on the opposing team
const CHO_GALL_BLOCKED_PICKS = [4, 6, 8, 12, 14, 15];
const CHO = 11;

// What the suggestions can rank by: the field on each suggestion, the strip's caption, and how each hero's figure reads
const SUGGEST_METRICS = {
  value: { label: 'HP Value', field: 'value', caption: 'Best HP Draft Value for this step', format: 'Value :value', round: value => Math.round(value) },
  win_rate: { label: 'Win Rate', field: 'win_rate', caption: 'Highest win rate in these filters', format: ':value% win', round: value => value.toFixed(1) },
  influence: { label: 'Influence', field: 'influence', caption: 'Highest influence in these filters', format: 'Influence :value', round: value => Math.round(value) },
  pick_order: { label: 'Draft Pick Order', field: 'pick_order_percent', caption: 'Most often taken at this step', format: ':value% at this step', round: value => value.toFixed(1) },
};
const GALL = 18;

export default {
  name: 'Drafter',
  props: {
    heroes: {
      type: Array,
      required: true,
    },
    filters: {
      type: Object,
      required: true,
    },
    gametypedefault: Array,
    defaulttimeframe: Array,
    patreonUser: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      infoText: 'Get ban and pick suggestions at every step of a Storm League draft. HP Draft Value combines how often a hero is taken at that point in the draft with their win rate, bans, and the team compositions they fit into.',
      roles: ['Tank', 'Bruiser', 'Healer', 'Support', 'Melee Assassin', 'Ranged Assassin'],
      mockDraft: false,
      // team1 is your team, team2 the enemy; this is whichever bans and picks first
      firstPickTeam: 'team1',
      picks: [],
      searchQuery: '',
      selectedRole: 'All',
      // The hero board: best HP Draft Value first, or A-Z
      sortMode: 'value',
      // What the suggestions rank by: one of suggestTabs' keys
      suggestBy: 'value',

      isLoading: false,
      dataError: false,
      heroStats: {},
      totalGames: 0,
      draftOrder: {},
      compositions: {},
      compositionsLoading: false,
      compositionsError: false,
      loadId: 0,

      timeframe: null,
      gametype: null,
      region: null,
      herolevel: null,
      gamemap: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
    };
  },
  created() {
    this.timeframe = this.defaulttimeframe;
    this.gametype = this.gametypedefault;
  },
  mounted() {
    this.loadBaseData();
  },
  computed: {
    drafterFilters() {
      return { ...this.filters, timeframes: this.filters.timeframes.slice(0, 5) };
    },
    pickOrder() {
      return this.firstPickTeam === 'team1' ? PICK_ORDER_TEAM1_FIRST : PICK_ORDER_TEAM2_FIRST;
    },
    currentTeam() {
      return (this.pickOrder[this.currentPick] || '').startsWith('team1') ? 'team1' : 'team2';
    },
    // "Your team · Ban 1"
    stepLabel() {
      return this.draftComplete ? this.$t('Draft complete') : this.slotLabel(this.pickOrder[this.currentPick]);
    },
    draftPhases() {
      return [
        { ban: true, steps: [0, 1, 2, 3] },
        { ban: false, steps: [4, 5, 6, 7, 8] },
        { ban: true, steps: [9, 10] },
        { ban: false, steps: [11, 12, 13, 14, 15] },
      ];
    },
    // Hero id -> who took it and how, for the board's Picked / Banned badges
    takenHeroes() {
      return Object.fromEntries(this.picks.map((pick, index) => {
        const slotId = this.pickOrder[index] || '';
        return [pick.heroId, { ours: slotId.startsWith('team1'), ban: slotId.includes('ban') }];
      }));
    },
    roleTabs() {
      return [{ key: 'All', text: 'All' }, ...this.roles.map(role => ({ key: role, text: role.replace(' Assassin', '') }))];
    },
    sortTabs() {
      return [{ key: 'value', text: 'Suggested' }, { key: 'alpha', text: 'A–Z' }];
    },
    suggestTabs() {
      return Object.entries(SUGGEST_METRICS).map(([key, metric]) => ({ key, text: metric.label }));
    },
    suggestMetric() {
      const metric = SUGGEST_METRICS[this.suggestBy] || SUGGEST_METRICS.value;
      return { ...metric, format: hero => this.$t(metric.format, { value: metric.round(Number(hero[metric.field]) || 0) }) };
    },
    // The heroes that can be taken now, best first by the chosen metric
    rankedSuggestions() {
      const field = this.suggestMetric.field;
      return this.suggestions.slice().sort((a, b) => (Number(b[field]) || 0) - (Number(a[field]) || 0));
    },
    topSuggestions() {
      const field = this.suggestMetric.field;
      return this.rankedSuggestions.filter(hero => Number(hero[field]) > 0).slice(0, 3);
    },
    // The strip's heroes glow on the board too
    glowIds() {
      return new Set(this.mockDraft ? [] : this.topSuggestions.map(hero => hero.id));
    },
    currentPick() {
      return this.picks.length;
    },
    draftComplete() {
      return this.currentPick >= this.pickOrder.length;
    },
    isBanStep() {
      return this.currentPick <= 3 || this.currentPick === 9 || this.currentPick === 10;
    },
    pickedIds() {
      return this.picks.map(pick => pick.heroId);
    },
    heroesById() {
      return Object.fromEntries(this.heroes.map(hero => [hero.id, hero]));
    },
    suggestions() {
      if (this.draftComplete) return [];
      if (this.mockDraft) {
        return this.heroes
          .filter(hero => !this.pickedIds.includes(hero.id))
          .map(hero => this.buildSuggestion(hero, 0, 0, null));
      }

      let list;
      if (this.currentPick <= 3) {
        list = this.banSuggestions();
      } else if (this.currentPick <= 5) {
        list = this.initialSuggestions();
      } else {
        list = this.compositionSuggestions();
      }
      return list.sort((a, b) => b.value - a.value);
    },
    // Heroes from the most played composition containing the team's current roles,
    // with games played zeroed for roles the team has already filled
    // Compositions are only used from pick 6 on
    needsCompositions() {
      return !this.mockDraft && this.currentPick >= 6 && !this.draftComplete;
    },
    waiting() {
      return this.isLoading || (this.needsCompositions && this.compositionsLoading);
    },
    compositionData() {
      if (this.mockDraft || this.dataError || this.currentPick < 6 || this.draftComplete) return null;

      const knownRoles = new Set(Object.values(this.compositions).flatMap(composition => composition.roles));
      const teamRoles = this.compositionTeamPicks()
        .map(heroId => this.heroesById[heroId]?.new_role)
        .filter(role => knownRoles.has(role));

      const remaining = roles => {
        const counts = {};
        roles.forEach(role => { counts[role] = (counts[role] || 0) + 1; });
        teamRoles.forEach(role => { counts[role] = (counts[role] || 0) - 1; });
        return counts;
      };

      let best = null;
      for (const composition of Object.values(this.compositions)) {
        if (best && composition.games <= best.games) continue;
        if (Object.values(remaining(composition.roles)).every(count => count >= 0)) {
          best = composition;
        }
      }
      if (!best) return [];

      const remainingRoles = remaining(best.roles);
      return Object.entries(best.heroes)
        .filter(([heroId]) => this.heroesById[heroId])
        .map(([heroId, games]) => ({
          hero_id: Number(heroId),
          games_played: remainingRoles[this.heroesById[heroId].new_role] ? games : 0,
        }));
    },
    visibleSuggestions() {
      // Unicode letters, so translated names (Cyrillic, Hangul, CJK) survive; matches English or translated name
      const strip = (text) => this.normalizeString(text).replace(/[^\p{L}\p{N}\s]/gu, '');
      const query = strip(this.searchQuery);
      const matches = hero => (this.selectedRole === 'All' || hero.new_role === this.selectedRole)
        && (!query || strip(hero.name).includes(query) || strip(this.$heroName(hero)).includes(query));
      const byName = (a, b) => this.$heroName(a).localeCompare(this.$heroName(b));
      // Every hero, so the board keeps its size; ones that can't be taken now are marked and go last
      const available = new Set(this.suggestions.map(hero => hero.id));
      const unavailable = this.heroes
        .filter(hero => !available.has(hero.id) && matches(hero))
        .map(hero => ({ ...hero, unavailable: true }))
        .sort(byName);
      const heroes = this.rankedSuggestions.filter(matches);
      return this.sortMode === 'alpha' || this.mockDraft
        ? [...heroes, ...unavailable].sort(byName)
        : [...heroes, ...unavailable];
    },
  },
  methods: {
    normalizeString(input) {
      return (input || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    },
    slotLabel(slotId) {
      const [, team, kind, number] = slotId.match(/^(team\d)-(ban|pick)(\d)$/);
      return (team === 'team1' ? this.$t('Your team') : this.$t('Enemy team')) + ' · ' + (kind === 'ban' ? this.$t('Ban :number', { number }) : this.$t('Pick :number', { number }));
    },
    isActive(slotId) {
      return !this.draftComplete && this.pickOrder[this.currentPick] === slotId;
    },
    // The order bar: the current step solid, the rest tinted by team, steps already taken faded
    slotNumber(slotId) {
      return slotId.slice(-1);
    },
    // Timeline steps: taken ones are their portrait; picks to come are solid team-coloured chips, bans to come dashed
    // outlines, and the current step stands out with a ring
    stepClass(index) {
      const slotId = this.pickOrder[index];
      const ours = slotId.startsWith('team1');
      if (index < this.currentPick) {
        return 'rounded-full hover:brightness-125 transition-[filter]';
      }
      const current = index === this.currentPick && !this.draftComplete;
      const ring = current ? (ours ? 'ring-2 ring-lteal shadow-[0_0_0.75rem_rgba(0,192,192,0.6)]' : 'ring-2 ring-lred shadow-[0_0_0.75rem_rgba(228,74,35,0.6)]') : '';
      if (slotId.includes('ban')) {
        return ['rounded-control border-[1.5px] border-dashed bg-surface-1', ours ? 'border-[#009994] text-[#009994]' : 'border-lred text-lred', ring];
      }
      return ['rounded-control text-white', ours ? 'bg-teal' : 'bg-red', ring];
    },
    swapSides() {
      this.firstPickTeam = this.firstPickTeam === 'team1' ? 'team2' : 'team1';
    },
    setMockDraft(mock) {
      this.mockDraft = mock;
      if (!mock && !Object.keys(this.heroStats).length) {
        this.loadBaseData();
      }
    },
    slotHero(slotId) {
      const index = this.pickOrder.indexOf(slotId);
      const pick = this.picks[index];
      return pick ? this.heroesById[pick.heroId] : null;
    },
    slotStyle(slotId) {
      const hero = this.slotHero(slotId);
      return hero ? { backgroundImage: 'url(/images/heroes/' + hero.short_name + '.png)' } : {};
    },
    pickHero(hero) {
      if (this.waiting || this.draftComplete) return;

      this.picks.push({ heroId: hero.id, auto: false });

      // Picking either half of Cho'gall fills the team's next slot with the other half
      const next = this.currentPick;
      if ((hero.id === CHO || hero.id === GALL) && next > 4 && next !== 10 && next !== 11 && next < this.pickOrder.length) {
        this.picks.push({ heroId: hero.id === CHO ? GALL : CHO, auto: true });
      }
    },
    // Undo every step from this one on; a Cho'gall half filled in automatically goes back with the half that brought it
    goBackTo(index) {
      if (this.isLoading || index >= this.currentPick) return;
      const keep = this.picks[index]?.auto ? index - 1 : index;
      // Going back further than the last step asks first (the count includes a Cho'gall half that goes with it)
      const undone = this.currentPick - keep;
      if (this.currentPick - index > 1 && !confirm(this.$t('Go back to :step? This undoes the last :count bans and picks.', { step: this.slotLabel(this.pickOrder[keep]), count: undone }))) return;
      this.picks = this.picks.slice(0, keep);
    },
    restartDraft() {
      this.picks = [];
    },
    filterData(filteredData) {
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes) : this.defaulttimeframe;
      this.region = filteredData.multi.Regions ? Array.from(filteredData.multi.Regions) : null;
      this.herolevel = filteredData.multi['Hero Level'] ? Array.from(filteredData.multi['Hero Level']) : null;
      this.gamemap = filteredData.single.Map ? [filteredData.single.Map] : null;
      this.playerrank = filteredData.multi['HP Player Rank'] ? Array.from(filteredData.multi['HP Player Rank']) : null;
      this.herorank = filteredData.multi['HP Hero Rank'] ? Array.from(filteredData.multi['HP Hero Rank']) : null;
      this.rolerank = filteredData.multi['HP Role Rank'] ? Array.from(filteredData.multi['HP Role Rank']) : null;

      if (!this.mockDraft) {
        this.loadBaseData();
      }
    },
    filterParameters() {
      return {
        timeframe_type: 'minor',
        timeframe: this.timeframe,
        region: this.region,
        hero_level: this.herolevel,
        game_type: this.gametype,
        game_map: this.gamemap,
        league_tier: this.playerrank,
        hero_league_tier: this.herorank,
        role_league_tier: this.rolerank,
      };
    },
    async loadBaseData() {
      // Filters apply as they change, so an older load can finish after a newer one
      const loadId = ++this.loadId;
      this.isLoading = true;
      this.dataError = false;
      this.compositions = {};
      this.compositionsLoading = true;
      this.compositionsError = false;

      // Same parameters the Global Hero Stats page sends, so both share its cache entry
      const baseRequests = Promise.all([
        this.$globalAsyncPost('/api/v1/global/hero', {
          ...this.filterParameters(),
          statfilter: 'win_rate',
          role: null,
          hero: null,
          mirror: 0,
          groupsize: [],
        }),
        this.$globalAsyncPost('/api/v1/drafter/draft-order', this.filterParameters()),
      ]);

      // Slower and not needed until pick 6, so it's sent last and doesn't hold up the first suggestions
      this.$globalAsyncPost('/api/v1/drafter/composition', { ...this.filterParameters(), mirror: 0 })
        .then(response => { if (loadId === this.loadId) this.compositions = response.data || {}; })
        .catch(() => { if (loadId === this.loadId) this.compositionsError = true; })
        .finally(() => { if (loadId === this.loadId) this.compositionsLoading = false; });

      try {
        const [statsResponse, orderResponse] = await baseRequests;
        if (loadId !== this.loadId) return;

        const stats = statsResponse.data.data || [];
        this.totalGames = stats.reduce((sum, row) => sum + row.games_played, 0) / 10;
        this.heroStats = Object.fromEntries(stats.map(row => [row.hero_id, row]));
        this.draftOrder = orderResponse.data || {};
      } catch (error) {
        if (loadId === this.loadId) this.dataError = true;
      } finally {
        if (loadId === this.loadId) this.isLoading = false;
      }
    },
    compositionTeamPicks() {
      const pick = this.currentPick;
      const firstTeam = FIRST_TEAM_PICKS.includes(pick) || pick === 9;
      const picks = firstTeam ? FIRST_TEAM_PICKS : SECOND_TEAM_PICKS;
      return this.picks.filter((p, index) => picks.includes(index)).map(p => p.heroId);
    },
    // Share of a hero's picks within `positions` that happened at the current pick; null when never picked there
    pickOrderShare(positions, heroId) {
      const count = this.draftOrder[this.currentPick]?.[heroId];
      if (count == null) return null;
      const total = positions.reduce((sum, position) => sum + (this.draftOrder[position]?.[heroId] || 0), 0);
      return total > 0 ? count / total : null;
    },
    isExcluded(heroId, blockChoGallAtPicks) {
      if (this.pickedIds.includes(heroId)) return true;
      if (heroId !== CHO && heroId !== GALL) return false;
      if (this.pickedIds.includes(CHO) || this.pickedIds.includes(GALL)) return true;
      return blockChoGallAtPicks.includes(this.currentPick);
    },
    buildSuggestion(hero, value, games, share) {
      const stats = this.heroStats[hero.id] || {};
      return {
        ...hero,
        value,
        games,
        influence: stats.influence ?? 0,
        ban_rate: stats.ban_rate ?? 0,
        win_rate: stats.win_rate ?? 0,
        confidence: stats.confidence_interval ?? 0,
        pick_order_percent: share ? share * 100 : 0,
      };
    },
    scale(entries, max = Math.max(0, ...entries.map(entry => entry.score))) {
      return entries.map(({ hero, score, games, share }) => this.buildSuggestion(hero, max > 0 ? (score / max) * 100 : 0, games, share));
    },
    // global/hero only returns ban rate, so the count is derived back from it
    estimatedBans(heroId) {
      return Math.round(((this.heroStats[heroId]?.ban_rate || 0) / 100) * this.totalGames);
    },
    banSuggestions() {
      const entries = this.heroes
        .filter(hero => !this.pickedIds.includes(hero.id))
        .map(hero => {
          const bans = this.estimatedBans(hero.id);
          const share = this.pickOrderShare(BAN_POSITIONS, hero.id);
          return { hero, score: share ? bans * share : 0, games: bans, share };
        });
      return this.scale(entries);
    },
    initialSuggestions() {
      const entries = this.heroes
        .filter(hero => this.heroStats[hero.id] && !this.isExcluded(hero.id, [4]))
        .map(hero => {
          const stats = this.heroStats[hero.id];
          const share = this.pickOrderShare(INITIAL_POSITIONS, hero.id);
          return { hero, score: share ? stats.influence * share : 0, games: stats.games_played, share };
        });
      return this.scale(entries);
    },
    compositionSuggestions() {
      const compositionGames = Object.fromEntries((this.compositionData || []).map(row => [row.hero_id, row.games_played]));
      const score = heroId => {
        const share = this.pickOrderShare(COMPOSITION_POSITIONS, heroId);
        return share ? (compositionGames[heroId] || 0) * share : 0;
      };
      // Scaled against the whole composition, picked heroes included
      const max = Math.max(0, ...Object.keys(compositionGames).map(heroId => score(Number(heroId))));

      const entries = this.heroes
        .filter(hero => !this.isExcluded(hero.id, CHO_GALL_BLOCKED_PICKS))
        .map(hero => ({
          hero,
          score: score(hero.id),
          games: this.isBanStep ? this.estimatedBans(hero.id) : (this.heroStats[hero.id]?.games_played || 0),
          share: this.pickOrderShare(COMPOSITION_POSITIONS, hero.id),
        }));
      return this.scale(entries, max);
    },
  },
};
</script>
