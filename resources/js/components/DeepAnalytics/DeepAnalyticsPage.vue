<template>
  <div>
    <page-heading
      :heading="'Deep Analytics'"
      :infoText1="'Deep replay analysis of ' + battletag + '\'s season games (from Oct 2, 2026), compared with a sample of all games.'"
    ></page-heading>

    <filters
      :onFilter="filterData"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :includehero="true"
      :heroinput="getHeroID()"
      :includegametype="true"
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
        <p v-if="processing">Processing your games: {{ status.done }} of {{ status.total }} done. This page updates as they finish.</p>
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

        <loading-component v-if="isLoading"></loading-component>
        <div v-else-if="tabData">
          <deep-analytics-deaths v-if="activeTab === 'deaths'" :data="tabData" :maps="maps" :heroes="filters.heroes"></deep-analytics-deaths>
          <deep-analytics-positions v-else-if="activeTab === 'map'" :data="tabData" :maps="maps" :heroes="filters.heroes"></deep-analytics-positions>
          <deep-analytics-timelines v-else-if="activeTab === 'timelines'" :data="tabData" :maps="maps" :heroes="filters.heroes"></deep-analytics-timelines>
          <deep-analytics-activity v-else-if="activeTab === 'activity'" :data="tabData" :maps="maps" :heroes="filters.heroes"></deep-analytics-activity>
          <deep-analytics-sessions v-else-if="activeTab === 'sessions'" :data="tabData" :maps="maps" :heroes="filters.heroes"></deep-analytics-sessions>
          <deep-analytics-games v-else-if="activeTab === 'games'" :data="tabData" :maps="maps" :heroes="filters.heroes"></deep-analytics-games>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DeepAnalyticsPage',
  props: {
    battletag: String,
    initialStatus: Object,
    filters: Object,
    gametypedefault: Array,
    maps: Object,
    urlparameters: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      status: { ...this.initialStatus },
      requesting: false,
      processing: false,
      pollTimer: null,
      error: '',
      tabs: [
        { key: 'deaths', label: 'Deaths' },
        { key: 'map', label: 'Map & Positions' },
        { key: 'timelines', label: 'Timelines & Objectives' },
        { key: 'activity', label: 'Activity' },
        { key: 'sessions', label: 'Sessions' },
        { key: 'games', label: 'Games' },
      ],
      activeTab: 'deaths',
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
  },
  beforeUnmount() {
    clearInterval(this.pollTimer);
  },
  computed: {
    percent() {
      return this.status.total > 0 ? Math.floor((this.status.done / this.status.total) * 100) : 0;
    },
    tabData() {
      return this.tabCache[this.cacheKey()];
    },
  },
  watch: {
    activeTab() {
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
        const response = await this.$axios.post('/DeepAnalytics/Data/' + this.activeTab, {
          game_type: this.gametype,
          game_map: this.gamemap,
          hero: this.getHeroID(),
        });
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
        await this.$axios.post('/DeepAnalytics/Request');
        this.startPolling();
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
    },
    async refresh() {
      try {
        const response = await this.$axios.get('/DeepAnalytics/Status');
        this.status = response.data;
        if (this.status.done >= this.status.total) {
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
