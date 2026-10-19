<template>
  <div>
    <analytics-disclaimer-popup v-if="showDisclaimer" @popupclosed="showDisclaimer = false"></analytics-disclaimer-popup>
    <page-heading
      :heading="'Analytics (Pre-Alpha)'"
      :infoText1="'Replay analysis of ' + battletag + '\'s season games (from Oct 2, 2026), compared with a sample of all games.'"
      :infoText2="'Pre-alpha: this is in very early development. Numbers, views and features will change, and some may be wrong or missing.'"
    ></page-heading>

    <!-- Remounted when a tab picks a map, so the filter shows it -->
    <filters
      :key="filtersKey"
      :onFilter="filterData"
      :gamemapinput="gamemap.length ? gamemap : null"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :includehero="true"
      :heroinput="getHeroID()"
      :includegametypefull="true"
      :includegamemap="true"
      :hideadvancedfilteringbutton="true"
    ></filters>

    <div class="max-w-[1500px] mx-auto px-4 mt-4">
      <!-- Processing status: one line, so the tab's own numbers come first -->
      <div class="text-center mb-6">
        <div v-if="processing" class="max-w-[600px] mx-auto">
          <p class="text-sm">Processing your games: {{ status.done }} of {{ status.total }} done. You can leave this page; it carries on.</p>
          <stat-bar-box class="w-full" size="full" :title="'Progress'" :value="percent" :displaytext="status.done + ' of ' + status.total"></stat-bar-box>
        </div>
        <div v-else class="flex flex-wrap items-center justify-center gap-3 text-sm">
          <span>{{ status.done.toLocaleString('en-US') }} of {{ status.total.toLocaleString('en-US') }} season games processed ({{ percent }}%)</span>
          <custom-button
            v-if="status.done < status.total"
            @click="request"
            :text="'Process my games'"
            :alt="'Process my games'"
            size="small"
            :loading="requesting"
            :disabled="requesting"
            :ignoreclick="true"
          ></custom-button>
        </div>
        <p v-if="error" class="mt-4 text-red">{{ error }}</p>
      </div>

      <!-- Analytics -->
      <div v-if="status.done > 0">
        <!-- Phones get a dropdown; the eight tabs don't fit across -->
        <multi-tab-button class="mb-2 max-md:hidden" :options="tabs" v-model="activeTab"></multi-tab-button>
        <div class="md:hidden flex justify-center mb-2">
          <single-select-filter
            :values="tabOptions"
            :text="'Section'"
            :defaultValue="activeTab"
            :disabledeselectfilters="true"
            @input-changed="activeTab = $event.value"
          ></single-select-filter>
        </div>
        <p class="text-center text-sm mb-6"><a class="link cursor-pointer" @click="toMethodology">How is this calculated?</a></p>

        <p v-if="tabError" class="text-center text-red mb-4">{{ tabError }}</p>
        <div v-if="!isLoading && tabData">
          <analytics-deaths v-if="activeTab === 'deaths'" :data="tabData" :maps="maps" :heroes="filters.heroes" :game-maps="filters.game_maps" :selected-maps="gamemap" @pick-map="pickMap"></analytics-deaths>
          <analytics-positions v-else-if="activeTab === 'map'" :data="tabData" :maps="maps" :heroes="filters.heroes" :game-maps="filters.game_maps" :selected-maps="gamemap" @pick-map="pickMap"></analytics-positions>
          <analytics-timelines v-else-if="activeTab === 'timelines'" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-timelines>
          <analytics-activity v-else-if="activeTab === 'activity'" :data="tabData" :maps="maps" :heroes="filters.heroes" :filter-params="filterParams"></analytics-activity>
          <analytics-sessions v-else-if="activeTab === 'sessions'" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-sessions>
          <analytics-coaching v-else-if="activeTab === 'coaching'" :data="tabData" :maps="maps" :heroes="filters.heroes" :filter-params="filterParams"></analytics-coaching>
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
      :authenticated="true"
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
  },
  data() {
    return {
      // The pre-alpha notice, until it has been acknowledged in this browser
      showDisclaimer: (() => { try { return !localStorage.getItem(ANALYTICS_DISCLAIMER_KEY); } catch (e) { return true; } })(),
      status: { ...this.initialStatus },
      requesting: false,
      processing: Boolean(this.initialStatus.processing),
      pollTimer: null,
      error: '',
      tabs: [
        { key: 'deaths', label: 'Deaths' },
        { key: 'map', label: 'Map & Positions' },
        { key: 'timelines', label: 'Timelines & Objectives' },
        { key: 'activity', label: 'Activity' },
        { key: 'sessions', label: 'Sessions' },
        { key: 'coaching', label: 'Coaching' },
        { key: 'stacks', label: 'Stacks' },
        { key: 'games', label: 'Games' },
      ],
      // ?tab= in the URL opens that tab
      activeTab: ['deaths', 'map', 'timelines', 'activity', 'sessions', 'coaching', 'stacks', 'games'].includes((this.urlparameters || {}).tab) ? this.urlparameters.tab : 'deaths',
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
  },
  mounted() {
    if (this.status.done > 0) {
      this.loadTab();
    }
    // A request still running from an earlier visit
    if (this.processing) {
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
    percent() {
      return this.status.total > 0 ? Math.floor((this.status.done / this.status.total) * 100) : 0;
    },
    filterParams() {
      return { game_type: this.gametype, game_map: this.gamemap, hero: this.getHeroID() };
    },
    tabData() {
      return this.tabCache[this.cacheKey()];
    },
    // Written plainly like the other pages' URLs (game_type=sl,qm); game types only when not the player's default
    queryString() {
      const defaultTypes = [...(this.gametypedefault || [])].sort().join(',');
      let queryString = `?tab=${this.activeTab}`;
      if (this.gametype.length && [...this.gametype].sort().join(',') !== defaultTypes) {
        queryString += `&game_type=${this.gametype}`;
      }
      if (this.gamemap.length) {
        queryString += `&game_map=${this.gamemap}`;
      }
      if (this.hero) {
        queryString += `&hero=${this.hero}`;
      }
      return queryString;
    },
    tabOptions() {
      return this.tabs.map(t => ({ code: t.key, name: t.label }));
    },
  },
  watch: {
    activeTab(tab) {
      // ?tab= in the URL, so a tab can be linked to and comes back on reload
      history.pushState(null, null, `${window.location.pathname}${this.queryString}`);
      this.loadTab();
    },
  },
  methods: {
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
      // In the URL like the tab, so a filtered view can be linked and survives a reload
      history.replaceState(null, null, `${window.location.pathname}${this.queryString}`);
      this.loadTab();
    },
    toMethodology() {
      this.$refs.methodology?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    cacheKey() {
      return [this.activeTab, this.status.done, this.gametype.join(','), this.gamemap.join(','), this.hero || ''].join('|');
    },
    async loadTab() {
      const key = this.cacheKey();
      this.tabError = '';
      if (this.tabCache[key]) {
        return;
      }
      this.isLoading = true;
      try {
        const response = await this.$axios.post('/Analytics/Data/' + this.activeTab, this.filterParams);
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
        if (response.data.already_requested) {
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
        if (this.status.done >= this.status.total || !this.status.processing) {
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
