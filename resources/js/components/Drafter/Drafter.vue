<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Drafter'"></page-heading>

    <div v-show="!started || showFilters">
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
        :includegamemap="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
      ></filters>
    </div>

    <div v-if="!started" class="flex flex-col items-center gap-4 py-10">
      <div class="flex gap-8">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" value="team1" v-model="firstPickTeam" /> Team 1 First Pick
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" value="team2" v-model="firstPickTeam" /> Team 2 First Pick
        </label>
      </div>
      <div class="flex gap-4">
        <button @click="startDraft(false)" class="bg-blue hover:bg-lblue text-white font-bold px-6 py-2 rounded uppercase">Start Draft</button>
        <button @click="startDraft(true)" class="bg-red hover:bg-lred text-white font-bold px-6 py-2 rounded uppercase">Draft With No Data</button>
      </div>
    </div>

    <div v-else class="max-w-[1500px] mx-auto px-4 py-6">
      <div class="flex justify-between mb-4">
        <button v-if="picks.length > 0" @click="undo" :disabled="isLoading" class="bg-blue hover:bg-lblue text-white font-bold px-6 py-2 rounded disabled:opacity-50">
          <i class="fa-solid fa-reply mr-2"></i>Undo
        </button>
        <span v-else></span>
        <button @click="showFilters = !showFilters" class="bg-blue hover:bg-lblue text-white font-bold px-6 py-2 rounded">
          <i class="fa-solid fa-filter mr-2"></i>{{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </button>
      </div>

      <div class="flex gap-4 max-md:flex-col items-start">
        <div v-for="team in ['team1', 'team2']" :key="team + '-bans'" :class="['border border-[gray] rounded-2xl p-2 text-xs uppercase', team === 'team1' ? 'order-1' : 'order-3 md:ml-auto']">
          <span>Bans</span>
          <div class="flex">
            <div v-for="ban in [1, 2, 3]" :key="ban" :class="slotClasses(team, team + '-ban' + ban, 'w-16 h-16')" :style="slotStyle(team + '-ban' + ban)"></div>
          </div>
        </div>

        <div class="flex flex-col items-center gap-2 order-2 flex-1">
          <input type="text" v-model="searchQuery" class="w-60 border rounded p-2 text-black" placeholder="Search Heroes" />
          <div class="flex justify-center">
            <img
              v-for="role in roles"
              :key="role"
              :src="'/images/roles/' + role.toLowerCase() + (selectedRoles.includes(role) ? '-highlighted' : '') + '.PNG'"
              :title="role"
              :alt="role"
              @click="toggleRole(role)"
              class="w-12 cursor-pointer"
            />
          </div>
        </div>
      </div>

      <div class="flex gap-4 mt-8 max-md:flex-col">
        <div v-for="team in ['team1', 'team2']" :key="team + '-picks'" :class="['flex md:flex-col items-center max-md:justify-center shrink-0', team === 'team1' ? 'order-1' : 'order-3']">
          <div v-for="pick in [1, 2, 3, 4, 5]" :key="pick" :class="slotClasses(team, team + '-pick' + pick, 'w-20 h-20 max-md:w-14 max-md:h-14')" :style="slotStyle(team + '-pick' + pick)"></div>
        </div>

        <div class="flex-1 min-w-0 order-2">
          <loading-component v-if="isLoading"></loading-component>
          <div v-else-if="dataError" class="text-center text-red py-10">Failed to load draft data. Please try again.</div>
          <div v-else-if="draftComplete" class="text-center text-gray-medium py-10">Draft complete.</div>
          <div v-else class="flex flex-wrap justify-center">
            <div v-for="hero in visibleSuggestions" :key="hero.id" class="relative m-2 cursor-pointer" @click="pickHero(hero)">
              <i v-if="hero.starred" class="fa-solid fa-star absolute -top-1 -left-1 z-10 text-[gold] text-xl"></i>
              <hero-image-wrapper :hero="hero" size="big">
                <h2>{{ hero.name }}</h2>
                <div v-if="!mockDraft" class="text-left">
                  <p>HP Draft Value: {{ hero.value.toFixed(2) }}</p>
                  <p>{{ isBanStep ? 'Games Banned' : 'Games Played' }}: {{ hero.games.toLocaleString('en-US') }}</p>
                  <p>Influence: {{ hero.influence }}</p>
                  <p v-if="isBanStep">Ban Rate: {{ hero.ban_rate }}%</p>
                  <p>Win Rate: {{ hero.win_rate }}%</p>
                  <p>Win Rate Confidence: &plusmn; {{ hero.confidence }}%</p>
                  <p>Draft Pick Order Rate: {{ hero.pick_order_percent.toFixed(2) }}%</p>
                </div>
              </hero-image-wrapper>
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
      started: false,
      mockDraft: false,
      showFilters: false,
      firstPickTeam: 'team1',
      picks: [],
      searchQuery: '',
      selectedRoles: [],

      isLoading: false,
      dataError: false,
      heroStats: {},
      totalGames: 0,
      draftOrder: {},
      compositionCache: {},
      compositionData: null,

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
  computed: {
    drafterFilters() {
      return { ...this.filters, timeframes: this.filters.timeframes.slice(0, 5) };
    },
    pickOrder() {
      return this.firstPickTeam === 'team1' ? PICK_ORDER_TEAM1_FIRST : PICK_ORDER_TEAM2_FIRST;
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
    visibleSuggestions() {
      const query = this.normalizeString(this.searchQuery).replace(/[^\w\s]/gi, '');
      return this.suggestions.filter(hero => {
        if (this.selectedRoles.length && !this.selectedRoles.includes(hero.new_role)) return false;
        return !query || this.normalizeString(hero.name).replace(/[^\w\s]/gi, '').includes(query);
      });
    },
  },
  methods: {
    normalizeString(input) {
      return (input || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    },
    toggleRole(role) {
      this.selectedRoles = this.selectedRoles.includes(role)
        ? this.selectedRoles.filter(r => r !== role)
        : [...this.selectedRoles, role];
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
    slotClasses(team, slotId, size) {
      const active = !this.draftComplete && this.pickOrder[this.currentPick] === slotId;
      return [
        size, 'm-2 max-md:m-1 rounded-2xl border bg-cover',
        team === 'team1' ? 'bg-[rgba(33,61,122,0.2)]' : 'bg-[rgba(179,54,22,0.2)]',
        active ? 'shadow-[0_0_0.5em_#ffd051] border-[#ffd051]' : 'border-[gray]',
        !active && !this.slotHero(slotId) ? 'opacity-20' : '',
      ];
    },
    startDraft(mock) {
      this.mockDraft = mock;
      this.started = true;
      if (!mock) {
        this.loadBaseData();
      }
    },
    pickHero(hero) {
      if (this.isLoading || this.draftComplete) return;

      this.picks.push({ heroId: hero.id, auto: false });

      // Picking either half of Cho'gall fills the team's next slot with the other half
      const next = this.currentPick;
      if ((hero.id === CHO || hero.id === GALL) && next > 4 && next !== 10 && next !== 11 && next < this.pickOrder.length) {
        this.picks.push({ heroId: hero.id === CHO ? GALL : CHO, auto: true });
      }

      this.loadCompositionData();
    },
    undo() {
      const last = this.picks.pop();
      if (last && last.auto) {
        this.picks.pop();
      }
      this.loadCompositionData();
    },
    filterData(filteredData) {
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes) : this.defaulttimeframe;
      this.region = filteredData.multi.Regions ? Array.from(filteredData.multi.Regions) : null;
      this.herolevel = filteredData.multi['Hero Level'] ? Array.from(filteredData.multi['Hero Level']) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi['HP Player Rank'] ? Array.from(filteredData.multi['HP Player Rank']) : null;
      this.herorank = filteredData.multi['HP Hero Rank'] ? Array.from(filteredData.multi['HP Hero Rank']) : null;
      this.rolerank = filteredData.multi['HP Role Rank'] ? Array.from(filteredData.multi['HP Role Rank']) : null;

      if (this.started && !this.mockDraft) {
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
      this.isLoading = true;
      this.dataError = false;
      this.compositionCache = {};
      this.compositionData = null;

      try {
        // Same parameters the Global Hero Stats page sends, so both share its cache entry
        const [statsResponse, orderResponse] = await Promise.all([
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

        const stats = statsResponse.data.data || [];
        this.totalGames = stats.reduce((sum, row) => sum + row.games_played, 0) / 10;
        this.heroStats = Object.fromEntries(stats.map(row => [row.hero_id, row]));
        this.draftOrder = orderResponse.data || {};
      } catch (error) {
        this.dataError = true;
      } finally {
        this.isLoading = false;
      }

      this.loadCompositionData();
    },
    compositionTeamPicks() {
      const pick = this.currentPick;
      const firstTeam = FIRST_TEAM_PICKS.includes(pick) || pick === 9;
      const picks = firstTeam ? FIRST_TEAM_PICKS : SECOND_TEAM_PICKS;
      return this.picks.filter((p, index) => picks.includes(index)).map(p => p.heroId);
    },
    async loadCompositionData() {
      this.compositionData = null;
      if (this.mockDraft || this.dataError || this.currentPick < 6 || this.draftComplete) return;

      const teamPicks = this.compositionTeamPicks();
      const cacheKey = [...teamPicks].sort().join(',');
      if (this.compositionCache[cacheKey]) {
        this.compositionData = this.compositionCache[cacheKey];
        return;
      }

      this.isLoading = true;
      try {
        const response = await this.$globalAsyncPost('/api/v1/drafter/composition', {
          ...this.filterParameters(),
          mirror: 0,
          team_picks: teamPicks,
        });
        this.compositionCache[cacheKey] = Array.isArray(response.data) ? response.data : [];
        this.compositionData = this.compositionCache[cacheKey];
      } catch (error) {
        this.dataError = true;
      } finally {
        this.isLoading = false;
      }
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
        starred: false,
        games,
        influence: stats.influence ?? 0,
        ban_rate: stats.ban_rate ?? 0,
        win_rate: stats.win_rate ?? 0,
        confidence: stats.confidence_interval ?? 0,
        pick_order_percent: share ? share * 100 : 0,
      };
    },
    scale(entries, starThreshold, max = Math.max(0, ...entries.map(entry => entry.score))) {
      return entries.map(({ hero, score, games, share }) => {
        const suggestion = this.buildSuggestion(hero, max > 0 ? (score / max) * 100 : 0, games, share);
        suggestion.starred = suggestion.value > starThreshold;
        return suggestion;
      });
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
      return this.scale(entries, 50);
    },
    initialSuggestions() {
      const entries = this.heroes
        .filter(hero => this.heroStats[hero.id] && !this.isExcluded(hero.id, [4]))
        .map(hero => {
          const stats = this.heroStats[hero.id];
          const share = this.pickOrderShare(INITIAL_POSITIONS, hero.id);
          return { hero, score: share ? stats.influence * share : 0, games: stats.games_played, share };
        });
      return this.scale(entries, 50);
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
      return this.scale(entries, 75, max);
    },
  },
};
</script>
