<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Activity Graphs'"></page-heading>

    <div class="flex justify-center max-w-[1500px] mx-auto flex-wrap max-md:flex-col max-md:items-center">
      <single-select-filter
        :values="gameTypesWithAll"
        :text="'Game Type'"
        :defaultValue="'All'"
        @input-changed="handleGameTypeChange"
      ></single-select-filter>
      <single-select-filter
        :values="regionsWithAll"
        :text="'Region'"
        :defaultValue="'All'"
        @input-changed="handleRegionChange"
      ></single-select-filter>
      <button
        :disabled="loading"
        @click="applyFilter"
        :class="loading
          ? 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 max-md:mt-auto max-md:w-full'
          : 'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10'"
      >
        Filter
      </button>
    </div>

    <div v-if="loading">
      <loading-component></loading-component>
    </div>

    <div v-else class="max-w-[1500px] mx-auto px-4 py-10">
      <div v-if="error" class="text-center text-red-400 py-20">{{ error }}</div>
      <template v-else-if="data.length">
        <div class="flex flex-wrap gap-2 mb-6">
          <button
            v-for="year in availableYears"
            :key="year"
            @click="toggleYear(year)"
            :class="[
              'px-3 py-1 rounded text-sm font-medium border transition-colors',
              activeYears.includes(year)
                ? 'bg-teal text-white border-teal'
                : 'bg-transparent text-gray-400 border-gray-600 hover:border-gray-400'
            ]"
          >
            {{ year }}
          </button>
        </div>
        <line-chart
          :key="chartKey"
          :data="filteredData"
          :dataAttribute="'unique_players'"
          :title="'Unique Players per Month'"
        ></line-chart>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ActivityGraphs',
  props: {
    filters: { type: Object, required: true },
    gametypedefault: { type: Array, required: true },
    patreonUser: { type: Boolean, default: false },
  },
  data() {
    return {
      infoText: 'Unique player counts per month based on replays uploaded to Heroes Profile. Data goes back to October 2014. The current month is always calculated in real time.',
      data: [],
      loading: false,
      error: null,
      gametype: null,
      region: null,
      pendingGametype: null,
      pendingRegion: null,
      activeYears: [],
    };
  },
  computed: {
    gameTypesWithAll() {
      return [{ code: 'All', name: 'All' }, ...this.filters.game_types_full];
    },
    regionsWithAll() {
      return [{ code: 'All', name: 'All' }, ...this.filters.regions];
    },
    availableYears() {
      return [...new Set(this.data.map(d => d.x_label.split('-')[0]))].sort();
    },
    filteredData() {
      return this.data.filter(d => this.activeYears.includes(d.x_label.split('-')[0]));
    },
    chartKey() {
      return this.activeYears.join(',');
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    handleGameTypeChange(payload) {
      this.pendingGametype = payload.value === 'All' ? null : payload.value;
    },
    handleRegionChange(payload) {
      this.pendingRegion = payload.value === 'All' ? null : payload.value;
    },
    applyFilter() {
      this.gametype = this.pendingGametype;
      this.region = this.pendingRegion;
      this.fetchData();
    },
    toggleYear(year) {
      const idx = this.activeYears.indexOf(year);
      if (idx === -1) {
        this.activeYears.push(year);
      } else {
        this.activeYears.splice(idx, 1);
      }
    },
    fetchData() {
      this.loading = true;
      this.error = null;

      this.$axios.post('/api/v1/tools/activity/players/unique', {
        game_type: this.gametype,
        region: this.region,
      })
        .then(response => {
          this.data = response.data;
          this.activeYears = [...this.availableYears];
        })
        .catch(() => {
          this.error = 'Failed to load activity data.';
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>
