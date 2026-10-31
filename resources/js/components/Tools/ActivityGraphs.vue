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
        class="btn"
        :class="loading
          ? 'bg-gray-md md:ml-10 mt-auto mb-2 max-md:mt-auto max-md:w-full'
          : 'bg-teal md:ml-10 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10'"
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
            @click="activeYears = allYearsActive ? [] : [...availableYears]"
            :class="[
              'px-3 py-1 rounded text-sm font-medium border transition-colors',
              allYearsActive
                ? 'bg-teal text-white border-teal'
                : 'bg-transparent text-gray-400 border-gray-600 hover:border-gray-400'
            ]"
          >
            All
          </button>
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
          :data="filteredData"
          :dataAttribute="'unique_players'"
          :title="'Unique Players per Month'"
          :pointRadius="4"
          :pointHoverRadius="8"
          :projected="projectedCurrentMonth"
          :projectedLabel="'Expected (current month)'"
          :markers="events"
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
      infoText: 'Unique player counts per month based on replays uploaded to Heroes Profile. Data goes back to October 2014. The current month is always calculated in real time, and the dotted line shows its expected total, based on how the previous three months typically grew from the same point.',
      data: [],
      loading: false,
      error: null,
      gametype: null,
      region: null,
      pendingGametype: null,
      pendingRegion: null,
      activeYears: [],
      events: [
        { label: '2015-06', text: 'Heroes of the Storm Release (Jun 2, 2015)' },
        { label: '2017-04', text: 'Heroes of the Storm 2.0 (Apr 25, 2017)' },
        { label: '2018-12', text: 'HGC and Heroes of the Dorm cancellation (Dec 14, 2018)' },
        { label: '2022-07', text: 'Heroes of the Storm enters maintenance mode (Jul 8, 2022)' },
        { label: '2026-09', text: "BlizzCon Xal'atath announcement (Sep 12, 2026)" },
      ],
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
    allYearsActive() {
      return this.availableYears.every(year => this.activeYears.includes(year));
    },
    filteredData() {
      return this.data.filter(d => this.activeYears.includes(d.x_label.split('-')[0]));
    },
    // Current month so far, grown by the median of how the previous months grew
    // from the same point. A median so one unusual month can't skew it.
    projectedCurrentMonth() {
      const shown = this.filteredData[this.filteredData.length - 1];
      const current = this.data[this.data.length - 1];
      if (!shown || shown !== current || !current.previous_months_to_date) {
        return null;
      }

      const ratios = current.previous_months_to_date
        .map(toDate => {
          const month = this.data.find(d => d.x_label === toDate.x_label);
          return month && toDate.unique_players ? month.unique_players / toDate.unique_players : null;
        })
        .filter(ratio => ratio !== null)
        .sort((a, b) => a - b);
      if (!ratios.length) {
        return null;
      }

      const mid = Math.floor(ratios.length / 2);
      const median = ratios.length % 2 ? ratios[mid] : (ratios[mid - 1] + ratios[mid]) / 2;
      return Math.round(current.unique_players * median);
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
          const currentYear = new Date().getFullYear().toString();
          this.activeYears = this.availableYears.includes(currentYear) ? [currentYear] : [...this.availableYears];
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
