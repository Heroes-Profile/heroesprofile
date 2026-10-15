<template>
  <div>
    <page-heading
      :heading="'Analytics (Pre-Alpha)'"
      :infoText1="'Replay analysis of ' + battletag + '\'s season games (from Oct 2, 2026), compared with a sample of all games.'"
      :infoText2="'Pre-alpha: this is in very early development. Numbers, views and features will change, and some may be wrong or missing.'"
    ></page-heading>

    <filters
      :onFilter="filterData"
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
      <!-- Processing status -->
      <div class="flex flex-wrap justify-center gap-4 mb-4">
        <stat-box class="w-[30%] max-md:w-[48%]" :title="'Season Games'" :value="status.total.toLocaleString('en-US')"></stat-box>
        <stat-box class="w-[30%] max-md:w-[48%]" :title="'Processed'" :value="status.done.toLocaleString('en-US')" color="teal"></stat-box>
        <stat-box class="w-[30%] max-md:w-[48%]" :title="'Complete'" :value="percent + '%'" color="yellow"></stat-box>
      </div>

      <div class="text-center mb-6">
        <div v-if="processing" class="max-w-[600px] mx-auto">
          <p>Processing your games: {{ status.done }} of {{ status.total }} done. This page updates as they finish.</p>
          <p class="text-sm text-gray-light">You can leave this page; processing carries on, and your progress shows here when you come back.</p>
          <stat-bar-box class="w-full" size="full" :title="'Progress'" :value="percent" :displaytext="status.done + ' of ' + status.total"></stat-bar-box>
        </div>
        <custom-button
          v-else-if="status.done < status.total"
          @click="request"
          :text="'Process my games'"
          :alt="'Process my games'"
          :loading="requesting"
          :disabled="requesting"
          :ignoreclick="true"
        ></custom-button>
        <p v-if="error" class="mt-4 text-red">{{ error }}</p>
      </div>

      <!-- Analytics -->
      <div v-if="status.done > 0">
        <multi-tab-button class="mb-6" :options="tabs" v-model="activeTab"></multi-tab-button>

        <div v-if="!isLoading && tabData">
          <analytics-deaths v-if="activeTab === 'deaths'" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-deaths>
          <analytics-positions v-else-if="activeTab === 'map'" :data="tabData" :maps="maps" :heroes="filters.heroes"></analytics-positions>
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

    <!-- How the open tab's numbers are made -->
    <analytics-paper
      v-if="status.done > 0 && !isLoading && tabData && papers[activeTab]"
      :key="activeTab"
      :paper="papers[activeTab]"
    ></analytics-paper>
  </div>
</template>

<script>
import papers from '../../utils/analyticsPapers.js';

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
      papers,
      activeTab: ['deaths', 'map', 'timelines', 'activity', 'sessions', 'coaching', 'stacks', 'games'].includes((this.urlparameters || {}).tab) ? this.urlparameters.tab : 'deaths',
      tabCache: {},
      isLoading: false,
      gametype: this.gametypedefault,
      gamemap: [],
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
  },
  watch: {
    activeTab(tab) {
      // ?tab= in the URL, so a tab can be linked to and comes back on reload
      const params = new URLSearchParams(window.location.search);
      params.set('tab', tab);
      history.pushState(null, null, `${window.location.pathname}?${params.toString()}`);
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
      this.loadTab();
    },
    cacheKey() {
      return [this.activeTab, this.status.done, this.gametype.join(','), this.gamemap.join(','), this.hero || ''].join('|');
    },
    async loadTab() {
      const key = this.cacheKey();
      if (this.tabCache[key]) {
        return;
      }
      this.isLoading = true;
      try {
        const response = await this.$axios.post('/Analytics/Data/' + this.activeTab, this.filterParams);
        this.tabCache = { ...this.tabCache, [key]: response.data };
      } catch (e) {
        this.error = 'Could not load this tab. Please try again in a minute.';
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
