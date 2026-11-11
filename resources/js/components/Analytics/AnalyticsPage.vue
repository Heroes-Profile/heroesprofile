<template>
  <div>
    <analytics-disclaimer-popup v-if="showDisclaimer" @popupclosed="showDisclaimer = false"></analytics-disclaimer-popup>
    <page-heading
      :heading="'Analytics (Pre-Alpha)'"
      :infoText1="sample
        ? 'A sample: replay analysis of ' + battletag + '\'s season games as of ' + sample.captured_at + ', compared with a sample of all games.'
        : 'Replay analysis of ' + battletag + '\'s season games (from Oct 2, 2026), compared with a sample of all games.'"
      :infoText2="'Pre-alpha: this is in very early development. Numbers, views and features will change, and some may be wrong or missing.'"
    ></page-heading>

    <!-- Remounted when a tab picks a map, so the filter shows it. One match sets its own hero, map and mode -->
    <filters
      v-if="!game && !locked"
      :key="filtersKey"
      :onFilter="filterData"
      :gamemapinput="gamemap.length ? gamemap : null"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :includehero="!sample"
      :heroinput="getHeroID()"
      :includegametypefull="!sample"
      :includegamemap="true"
      :hideadvancedfilteringbutton="true"
    ></filters>

    <div class="max-w-[1500px] mx-auto px-4 mt-4">
      <!-- Free trial: days left, and the ways to keep Analytics in its last two days -->
      <p v-if="trialDaysLeft !== null" class="text-center text-sm mb-4">
        {{ trialStage }} free trial: {{ trialDaysLeft }} {{ trialDaysLeft === 1 ? 'day' : 'days' }} left.
        <template v-if="trialDaysLeft <= 2">
          To keep Analytics after that, <a class="link" href="https://www.patreon.com/heroesprofile" target="_blank">subscribe on Patreon</a> and link it to your account.
        </template>
      </p>

      <!-- Processing status: one line, so the tab's own numbers come first -->
      <div v-if="!sample" class="text-center mb-6">
        <div v-if="processing" class="max-w-[600px] mx-auto">
          <p v-if="status.done >= status.total && status.outdated > 0" class="text-sm">Updating your games: {{ status.outdated }} left. You can leave this page; it carries on.</p>
          <p v-else class="text-sm">Processing your games: {{ status.done }} of {{ status.total }} done. You can leave this page; it carries on.</p>
          <stat-bar-box class="w-full" size="full" :title="'Progress'" :value="percent" :displaytext="status.done + ' of ' + status.total"></stat-bar-box>
        </div>
        <div v-else class="flex flex-wrap items-center justify-center gap-3 text-sm">
          <span>{{ status.done.toLocaleString('en-US') }} of {{ status.total.toLocaleString('en-US') }} season games processed ({{ percent }}%)</span>
          <custom-button
            v-if="status.done < status.total && !locked && (!status.free || status.free.left > 0)"
            @click="request"
            :text="processText"
            :alt="processText"
            size="small"
            :loading="requesting"
            :disabled="requesting"
            :ignoreclick="true"
          ></custom-button>
        </div>
        <!-- Games processed before a newer version: what they're missing, and the way to update them -->
        <p v-if="!processing && status.outdated > 0 && !locked" class="text-sm mt-2">
          {{ status.outdated.toLocaleString('en-US') }} of your games were processed before Analytics added
          {{ (status.whats_new || []).join('; ') }}.
          <custom-button
            class="ml-2"
            @click="update"
            :text="'Update my games'"
            :alt="'Process those games again with the newest version'"
            size="small"
            :loading="requesting"
            :disabled="requesting"
            :ignoreclick="true"
          ></custom-button>
        </p>
        <!-- Without a subscription: a set number of games per stage -->
        <p v-if="status.free && !locked" class="text-sm mt-2">
          Your {{ trialStage }} free processing covers {{ status.free.limit }} games, newest first, including games processed in the replay viewer:
          {{ status.free.left }} left.
          <template v-if="status.free.left < status.total - status.done">
            To process the rest, <a class="link" href="https://www.patreon.com/heroesprofile" target="_blank">subscribe on Patreon</a>
            or link a paid <a class="link" href="/Api">API plan</a>.
          </template>
        </p>
        <p v-if="error" class="mt-4 text-red">{{ error }}</p>
      </div>

      <!-- Trial used up: the ways to keep Analytics; the page below stays greyed out -->
      <div v-if="locked" class="bg-darken rounded p-4 mb-6">
        <analytics-subscribe
          :title="'Your ' + (trialStage || '').toLowerCase() + ' free trial has ended'"
          :text="'Your processed games are kept, so everything below is back as soon as you subscribe on Patreon at any tier and link it to your Heroes Profile account. The next stage of development will come with a new free trial.'"
          :patreon-linked="patreonLinked"
        ></analytics-subscribe>
      </div>

      <!-- Nothing processed yet: what Analytics is, while the first games process -->
      <analytics-summary v-if="status.done === 0 && !sample" :path-for="pathFor" @open="activeTab = $event"></analytics-summary>
    </div>

    <!-- Analytics (greyed out and inert when locked) -->
    <div v-if="status.done > 0" :class="{ 'opacity-30 pointer-events-none select-none': locked }" :aria-hidden="locked">
      <!-- The match picker and tabs use the page's full width, like Match History below them -->
      <div class="px-4">
        <!-- One match at a time: pick one, or go back to all of them -->
        <div v-if="!sample || game" class="flex flex-wrap items-center justify-center gap-3 mb-4">
          <single-select-filter
            v-if="!sample"
            :key="'game' + (game || 0) + gameOptions.length"
            :values="gameOptions"
            :text="'Match'"
            :defaultValue="game || 0"
            :disabledeselectfilters="true"
            @input-changed="selectGame(Number($event.value) || null)"
          ></single-select-filter>
          <template v-if="game">
            <span v-if="gameInfo">
              One match: <span class="font-bold">{{ gameInfo.hero }}</span> on {{ gameInfo.game_map }} &middot; {{ gameInfo.game_type }} &middot;
              <span :class="gameInfo.winner ? 'text-teal' : 'text-red'">{{ gameInfo.winner ? 'Win' : 'Loss' }}</span> &middot; {{ formatDate(gameInfo.game_date) }}.
              Compared with {{ gameInfo.hero }} on {{ gameInfo.game_map }} in {{ gameInfo.game_type }}, and your other {{ gameInfo.hero }} matches.
            </span>
            <custom-button :href="replayLink(game)" :text="'Watch Replay'" :alt="'Watch this match in the replay viewer'" size="small"></custom-button>
            <custom-button :text="'Back to all matches'" :alt="'Back to all matches'" size="small" :ignoreclick="true" @click="selectGame(null)"></custom-button>
          </template>
        </div>

        <!-- Phones get a dropdown; the tabs don't fit across -->
        <multi-tab-button class="mb-2 max-md:hidden" :options="shownTabs" v-model="activeTab"></multi-tab-button>
        <div class="md:hidden flex justify-center mb-2">
          <single-select-filter
            :values="tabOptions"
            :text="'Section'"
            :defaultValue="activeTab"
            :disabledeselectfilters="true"
            @input-changed="activeTab = $event.value"
          ></single-select-filter>
        </div>
        <p v-if="activeTab !== 'summary'" class="text-center text-sm mb-6"><a class="link cursor-pointer" @click="toMethodology">How is this calculated?</a></p>
      </div>

      <div class="max-w-[1500px] mx-auto px-4">
        <analytics-summary v-if="activeTab === 'summary'" :path-for="pathFor" @open="activeTab = $event"></analytics-summary>

        <p v-if="tabError" class="text-center text-red mb-4">{{ tabError }}</p>
        <div v-if="!isLoading && tabData">
          <analytics-deaths v-if="activeTab === 'deaths'" :game="gameInfo" :data="tabData" :maps="maps" :heroes="filters.heroes" :game-maps="filters.game_maps" :selected-maps="gamemap" :pickable-maps="sample ? sample.maps : null" @pick-map="pickMap"></analytics-deaths>
          <analytics-positions v-else-if="activeTab === 'map'" :game="gameInfo" :data="tabData" :maps="maps" :heroes="filters.heroes" :game-maps="filters.game_maps" :selected-maps="gamemap" :pickable-maps="sample ? sample.maps : null" @pick-map="pickMap"></analytics-positions>
          <analytics-positioning v-else-if="activeTab === 'positioning'" :game="gameInfo" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-positioning>
          <analytics-objectives v-else-if="activeTab === 'objectives'" :game="gameInfo" :data="tabData"></analytics-objectives>
          <analytics-timelines v-else-if="activeTab === 'timelines'" :game="gameInfo" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-timelines>
          <analytics-activity v-else-if="activeTab === 'activity'" :game="gameInfo" :data="tabData" :maps="maps" :heroes="filters.heroes" :filter-params="filterParams" :data-url="dataUrl"></analytics-activity>
          <analytics-sessions v-else-if="activeTab === 'sessions'" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-sessions>
          <analytics-coaching v-else-if="activeTab === 'coaching'" :game="gameInfo" :data="tabData" :maps="maps" :heroes="filters.heroes" :filter-params="filterParams" :replay-link="replayLink"></analytics-coaching>
          <analytics-stacks v-else-if="activeTab === 'stacks'" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-stacks>
        </div>
      </div>
    </div>

    <!-- Page level, like the Talent Builder, so it fills the screen width -->
    <loading-component v-if="status.done > 0 && isLoading"></loading-component>

    <!-- Full page width, like Match History, so its cards and table lay out the same -->
    <analytics-games
      v-if="status.done > 0 && !isLoading && tabData && activeTab === 'games'"
      :data="tabData"
      :maps="maps"
      :heroes="filters.heroes"
      :playermatchtablestyle="playermatchtablestyle"
      :authenticated="!sample"
      :sample="!!sample"
      :sample-match="sampleMatch"
      :replay-link="replayLink"
      @analyze="selectGame"
    ></analytics-games>

    <!-- How the open tab's numbers are made; the wrapper is what "How is this calculated?" scrolls to -->
    <div ref="methodology">
      <analytics-paper
        v-if="status.done > 0 && !isLoading && tabData"
        :key="activeTab"
        :tab="activeTab"
      ></analytics-paper>
    </div>
  </div>
</template>

<script>
import { ANALYTICS_DISCLAIMER_KEY } from './AnalyticsDisclaimerPopup.vue';

// Tabs that need many matches, left out when showing one match
const ONE_GAME_HIDDEN = ['sessions', 'stacks'];

// Each section's path after /Analytics (or after /Analytics/Match/Single/{id} for one match)
const TAB_PATHS = {
  summary: '',
  deaths: '/Deaths',
  map: '/Map',
  positioning: '/Positioning',
  objectives: '/Objectives',
  timelines: '/Timelines',
  activity: '/Activity',
  sessions: '/Sessions',
  coaching: '/Coaching',
  stacks: '/Stacks',
  games: '/Match/History',
};

export default {
  name: 'AnalyticsPage',
  props: {
    battletag: String,
    initialStatus: Object,
    filters: Object,
    gametypedefault: Array,
    maps: Object,
    urlparameters: { type: Object, default: () => ({}) },
    // The player's Match History style setting: cards (true) or table
    playermatchtablestyle: { type: [String, Boolean, Number], default: false },
    // The public sample's details (captured_at, maps, replay_id); null on the real page
    sample: { type: Object, default: null },
    // On a free trial: when it ends (ISO), for the days-left notice
    trialEndsAt: { type: String, default: null },
    trialStage: { type: String, default: null },
    // Trial used up and no Patreon: the page greyed out with the ways to keep Analytics, no data loaded
    locked: Boolean,
    patreonLinked: Boolean,
    // The section and match from the page's path
    initialTab: { type: String, default: null },
    initialMatch: { type: Number, default: null },
  },
  data() {
    return {
      // The pre-alpha notice, until it has been acknowledged in this browser
      showDisclaimer: !this.sample && (() => { try { return !localStorage.getItem(ANALYTICS_DISCLAIMER_KEY); } catch (e) { return true; } })(),
      status: { ...this.initialStatus },
      requesting: false,
      processing: Boolean(this.initialStatus.processing),
      pollTimer: null,
      error: '',
      tabs: [
        { key: 'deaths', label: 'Deaths' },
        { key: 'map', label: 'Map & Positions' },
        { key: 'positioning', label: 'Positioning' },
        { key: 'objectives', label: 'Objectives & Bosses' },
        { key: 'timelines', label: 'Timelines & Objectives' },
        { key: 'activity', label: 'Activity' },
        { key: 'sessions', label: 'Sessions' },
        { key: 'coaching', label: 'Coaching' },
        { key: 'stacks', label: 'Stacks' },
        { key: 'games', label: 'Match History' },
      ],
      // The section comes from the page's path (/Analytics/Deaths...); the sample still uses ?tab=
      activeTab: this.sample
        ? (Object.keys(TAB_PATHS).includes((this.urlparameters || {}).tab) && this.urlparameters.tab !== 'summary' ? this.urlparameters.tab : 'deaths')
        : this.initialTab || 'summary',
      // /Analytics/Match/Single/{id}: every section for that one match. The sample has only its own, in ?game=
      game: this.sample
        ? (Number((this.urlparameters || {}).game) && Number(this.urlparameters.game) === this.sample.replay_id && (this.sample.match_tabs || []).length ? this.sample.replay_id : null)
        : this.initialMatch || null,
      gameOptions: [],
      tabCache: {},
      tabError: '',
      isLoading: false,
      gametype: this.gametypedefault,
      gamemap: [],
      filtersKey: 0,
      hero: null,
    };
  },
  created() {
    if (this.urlparameters) {
      if (this.urlparameters['hero']) this.hero = this.urlparameters['hero'];
      if (this.urlparameters['game_type']) this.gametype = this.urlparameters['game_type'].split(',');
      if (this.urlparameters['game_map']) this.gamemap = this.urlparameters['game_map'].split(',');
    }
    // The public sample only has map-level data for its own map(s): open on them
    if (this.sample && !this.gamemap.length && (this.sample.maps || []).length) {
      this.gamemap = [this.sample.maps[0]];
    }
  },
  mounted() {
    // Locked: the summary only, and nothing loaded
    if (this.locked) {
      this.activeTab = 'summary';
      return;
    }
    if (this.game && (ONE_GAME_HIDDEN.includes(this.activeTab) || this.activeTab === 'games')) {
      this.activeTab = 'deaths';
    }
    if (this.status.done > 0) {
      this.loadTab();
      this.loadGameOptions();
    }
    // A request still running from an earlier visit
    if (this.processing && !this.sample) {
      this.startPolling();
    }
    document.addEventListener('visibilitychange', this.onVisible);
    window.addEventListener('focus', this.onVisible);
  },
  beforeUnmount() {
    clearInterval(this.pollTimer);
    document.removeEventListener('visibilitychange', this.onVisible);
    window.removeEventListener('focus', this.onVisible);
  },
  computed: {
    // The sample match that Analyze can open (older samples have none)
    sampleMatch() {
      return this.sample && (this.sample.match_tabs || []).length ? this.sample.replay_id : null;
    },
    trialDaysLeft() {
      if (!this.trialEndsAt) return null;
      return Math.max(1, Math.ceil((new Date(this.trialEndsAt) - Date.now()) / 86400000));
    },
    processText() {
      const left = this.status.free ? this.status.free.left : null;
      return left !== null && left < this.status.total - this.status.done ? 'Process my latest ' + left + ' games' : 'Process my games';
    },
    percent() {
      return this.status.total > 0 ? Math.floor((this.status.done / this.status.total) * 100) : 0;
    },
    filterParams() {
      const params = { game_type: this.gametype, game_map: this.gamemap, hero: this.getHeroID() };
      return this.game ? { ...params, game: this.game } : params;
    },
    dataUrl() {
      return this.sample ? '/Analytics/Sample/Data/' : '/Analytics/Data/';
    },
    tabData() {
      return this.tabCache[this.cacheKey()];
    },
    // The game being shown, as the server describes it (hero, map, mode, result, date)
    gameInfo() {
      return this.game ? this.tabData?.game || null : null;
    },
    // Sessions and Stacks need many games, so one game leaves them out
    shownTabs() {
      if (this.game) return this.tabs.filter(t => !ONE_GAME_HIDDEN.includes(t.key) && t.key !== 'games');
      // Older samples were captured before Positioning, so show only the tabs a sample has
      if (this.sample) return this.tabs.filter(t => this.sample.tabs ? this.sample.tabs.includes(t.key) || t.key === 'games' : t.key !== 'positioning');
      return [{ key: 'summary', label: 'Summary' }, ...this.tabs];
    },
    // The filters, written plainly like the other pages' URLs (game_type=sl,qm); game types only when not the
    // player's default. The sample keeps its section in ?tab=
    queryString() {
      const defaultTypes = [...(this.gametypedefault || [])].sort().join(',');
      let queryString = this.sample ? `&tab=${this.activeTab}` + (this.game ? `&game=${this.game}` : '') : '';
      if (this.gametype.length && [...this.gametype].sort().join(',') !== defaultTypes) {
        queryString += `&game_type=${this.gametype}`;
      }
      if (this.gamemap.length) {
        queryString += `&game_map=${this.gamemap}`;
      }
      if (this.hero) {
        queryString += `&hero=${this.hero}`;
      }
      return queryString ? '?' + queryString.slice(1) : '';
    },
    tabOptions() {
      return this.shownTabs.map(t => ({ code: t.key, name: t.label }));
    },
  },
  watch: {
    activeTab(tab) {
      // Each section is its own address, so it can be linked to and comes back on reload
      history.pushState(null, null, this.pathFor(tab) + this.queryString);
      this.loadTab();
    },
  },
  methods: {
    // A section's address: /Analytics/Deaths, or /Analytics/Match/Single/{id}/Deaths for one match
    pathFor(tab) {
      if (this.sample) return window.location.pathname;
      if (this.game && tab !== 'games') return '/Analytics/Match/Single/' + this.game + TAB_PATHS[tab];
      return '/Analytics' + TAB_PATHS[tab];
    },
    // Where a match's replay opens; the sample has only its one match
    replayLink(replayId) {
      if (!this.sample) return '/Analytics/Match/Single/' + replayId + '/Replay';
      return replayId === this.sample.replay_id ? '/Analytics/Sample/Match/Single' : null;
    },
    getHeroID() {
      if (this.hero) {
        const match = this.filters.heroes.find(h => h.name === this.hero);
        return match ? match.code : null;
      }
      return null;
    },
    filterData(filteredData) {
      this.gametype = filteredData.multi['Game Type'] ? Array.from(filteredData.multi['Game Type']) : [];
      this.gamemap = filteredData.multi['Map'] ? Array.from(filteredData.multi['Map']) : [];
      const heroId = filteredData.single['Heroes'] ? filteredData.single['Heroes'] : null;
      this.hero = heroId ? this.filters.heroes.find(h => h.code === heroId)?.name : null;
      this.applyFilters();
    },
    // A map picked inside a tab: the same as picking it in the Map filter
    pickMap(name) {
      this.gamemap = [name];
      this.filtersKey++;
      this.applyFilters();
    },
    applyFilters() {
      // In the URL, so a filtered view can be linked and survives a reload
      history.replaceState(null, null, this.pathFor(this.activeTab) + this.queryString);
      this.loadTab();
    },
    toMethodology() {
      this.$refs.methodology?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    cacheKey() {
      return [this.activeTab, this.status.done, this.gametype.join(','), this.gamemap.join(','), this.hero || '', this.activeTab === 'games' ? '' : this.game || ''].join('|');
    },
    // One match, or null for all matches again
    selectGame(replayId) {
      this.game = replayId || null;
      if (this.game && (['games', 'summary'].includes(this.activeTab) || ONE_GAME_HIDDEN.includes(this.activeTab))) {
        this.activeTab = 'deaths';
      }
      history.pushState(null, null, this.pathFor(this.activeTab) + this.queryString);
      this.loadTab();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    // The Match dropdown: your processed matches, newest first
    async loadGameOptions() {
      if (this.sample) return;
      try {
        const games = (await this.$axios.post(this.dataUrl + 'games', {})).data.games || [];
        this.gameOptions = [
          { code: 0, name: 'All matches' },
          ...games.filter(g => g.processed).slice(0, 100).map(g => ({
            code: g.replayID,
            name: [this.formatDate(g.game_date), g.hero?.name, g.game_map?.name, g.winner ? 'Win' : 'Loss'].filter(Boolean).join(' · '),
          })),
        ];
      } catch (e) {
        // The dropdown just stays empty; Match History still has every match
      }
    },
    formatDate(date) {
      return new Date(String(date).replace(' ', 'T') + 'Z').toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    },
    async loadTab() {
      const key = this.cacheKey();
      this.tabError = '';
      if (this.activeTab === 'summary' || this.tabCache[key]) {
        return;
      }
      this.isLoading = true;
      try {
        const response = await this.$axios.post(this.dataUrl + this.activeTab, this.filterParams);
        this.tabCache = { ...this.tabCache, [key]: response.data };
      } catch (e) {
        this.tabError = 'Could not load this tab. Please try again in a minute.';
      } finally {
        this.isLoading = false;
      }
    },
    async request() {
      this.requesting = true;
      this.error = '';
      try {
        const response = await this.$axios.post('/Analytics/Request');
        if (response.data.limit_reached) {
          this.error = 'You have used your free processing for this stage. Subscribe on Patreon or link a paid API plan to process the rest.';
        } else if (response.data.already_requested) {
          this.error = 'Your games were already requested in the last 10 minutes. Try again in a few minutes if they are not processing.';
        } else if (response.data.queued === 0) {
          this.error = 'There are no unprocessed games to queue.';
        } else {
          this.startPolling();
        }
      } catch (e) {
        this.error = 'Could not start processing. Please try again in a minute.';
      } finally {
        this.requesting = false;
      }
    },
    // Older-version games again; free, and the page's numbers refresh as they land
    async update() {
      this.requesting = true;
      this.error = '';
      try {
        const response = await this.$axios.post('/Analytics/Update');
        if (response.data.already_requested) {
          this.error = 'Your games were already sent for updating in the last 10 minutes.';
        } else if (response.data.queued > 0) {
          this.startPolling();
        }
      } catch (e) {
        this.error = 'Could not start the update. Please try again in a minute.';
      } finally {
        this.requesting = false;
      }
    },
    startPolling() {
      if (this.pollTimer) {
        return;
      }
      this.processing = true;
      this.pollTimer = setInterval(this.refresh, 5000);
      this.refresh();
    },
    // Browsers slow or pause timers in background tabs; catch up as soon as the page is looked at
    onVisible() {
      if (this.processing && document.visibilityState === 'visible') {
        this.refresh();
      }
    },
    async refresh() {
      try {
        const response = await this.$axios.get('/Analytics/Status');
        this.status = response.data;
        if (!this.status.processing) {
          clearInterval(this.pollTimer);
          this.pollTimer = null;
          this.processing = false;
          this.loadTab();
        }
      } catch (e) {
        // A missed poll is retried on the next tick.
      }
    },
  },
};
</script>
