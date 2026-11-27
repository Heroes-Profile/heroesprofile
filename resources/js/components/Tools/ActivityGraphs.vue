<template>
  <div>
    <!-- The heading with the filters beside it, as on the player pages -->
    <player-page-top>
      <template #heading>
        <page-heading heading="Activity Graphs" :subheading="$t('Unique players per month')"></page-heading>
      </template>
      <div class="w-full">
        <filters
          :filters="filters"
          :isLoading="loading"
          :onFilter="() => applyFilter()"
          :onChange="payload => payload.field == 'Game Type' ? handleGameTypeChange(payload) : handleRegionChange(payload)"
          :extrafilters="[
            { text: 'Game Type', values: gameTypesWithAll, defaultValue: 'All' },
            { text: 'Region', values: regionsWithAll, defaultValue: 'All' },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>

    <div v-if="loading" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 pb-10 flex flex-col gap-6">
      <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <skeleton-box v-for="card in 4" :key="card" class="h-[6.5rem] rounded-control"></skeleton-box>
      </div>
      <panel-box title="Unique players per month" :subtitle="$t('Players seen in uploaded replays.')" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading activity...') }}</loading-component>
        <skeleton-box class="h-[25rem] w-full rounded-control"></skeleton-box>
      </panel-box>
    </div>

    <div v-else class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 pb-10 flex flex-col gap-6">
      <error-card v-if="loadError" :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
      <template v-else-if="data.length">
        <!-- This year at a glance -->
        <div v-if="summary" class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <stat-box class="!m-0 !min-w-0" color="teal" :title="$t('This month')" :value="formatNumber(summary.current)" :caption="projection ? $t('Expected ~:count by month end', { count: formatNumber(projection) }) : null"></stat-box>
          <stat-box class="!m-0 !min-w-0" color="yellow" :title="$t('Peak in :year', { year: summary.year })" :value="formatNumber(summary.peak.unique_players)" :caption="monthName(summary.peak.x_label)"></stat-box>
          <stat-box v-if="summary.change !== null" class="!m-0 !min-w-0" :color="summary.change < 0 ? 'red' : 'teal'" :title="$t('vs last month')" :value="(summary.change > 0 ? '+' : '') + summary.change + '%'" :value-class="summary.change < 0 ? 'text-lred' : 'text-lteal'" :caption="$t(':before → :after so far', { before: formatNumber(summary.previous), after: formatNumber(summary.current) })"></stat-box>
          <stat-box v-if="summary.average !== null" class="!m-0 !min-w-0" color="gray-dark" :title="$t('Average in :year', { year: summary.year })" :value="formatNumber(summary.average)" :caption="$t('per month')"></stat-box>
        </div>

        <panel-box title="Unique players per month" :subtitle="chartSubtitle" :divider="false">
          <template #right>
            <!-- Any years together, or All -->
            <div class="ml-auto flex flex-wrap gap-1 rounded bg-surface-1 p-1 text-sm select-none">
              <button type="button" :class="['rounded px-3 py-1.5 transition-colors', allYearsActive ? 'bg-teal text-white' : 'text-subtext hover:text-white hover:bg-surface-2']" @click="activeYears = allYearsActive ? [] : [...availableYears]">{{ $t('All') }}</button>
              <button v-for="year in availableYears" :key="year" type="button" :class="['rounded px-3 py-1.5 transition-colors', !allYearsActive && activeYears.includes(year) ? 'bg-teal text-white' : 'text-subtext hover:text-white hover:bg-surface-2']" @click="toggleYear(year)">{{ year }}</button>
            </div>
          </template>
          <line-chart
            :data="filteredData"
            :dataAttribute="'unique_players'"
            :title="$t('Unique Players per Month')"
            :pointRadius="4"
            :pointHoverRadius="8"
            :projected="projectedCurrentMonth"
            :projectedLabel="$t('Expected (current month)')"
            :markers="events.map(event => ({ ...event, text: $t(event.text) }))"
          ></line-chart>
          <p v-if="projectedCurrentMonth" class="flex items-center gap-2.5 text-sm text-subtext"><basil-icon name="info" class="text-lg shrink-0"></basil-icon>{{ $t('The current month is still filling in: the dashed line shows the expected total.') }}</p>
        </panel-box>
      </template>
    </div>
  </div>
</template>

<script>
import { siteLocale } from '../../utils/translate';
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
      loadError: null,
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
    // The latest year: this month, its peak, the change on last month, and its average over the finished months
    summary() {
      const current = this.data[this.data.length - 1];
      if (!current) {
        return null;
      }
      const year = current.x_label.split('-')[0];
      const months = this.data.filter(d => d.x_label.startsWith(year + '-'));
      const finished = months.slice(0, -1);
      const previous = this.data[this.data.length - 2] || null;
      return {
        year,
        current: current.unique_players,
        previous: previous ? previous.unique_players : null,
        change: previous && previous.unique_players ? Math.round((current.unique_players - previous.unique_players) / previous.unique_players * 100) : null,
        peak: months.reduce((best, d) => (d.unique_players > best.unique_players ? d : best), months[0]),
        average: finished.length ? Math.round(finished.reduce((sum, d) => sum + d.unique_players, 0) / finished.length) : null,
      };
    },
    // What the chart counts: all game types and regions, or the ones picked
    chartSubtitle() {
      const gameType = this.gametype ? this.gameTypesWithAll.find(type => type.code === this.gametype)?.name : null;
      const region = this.region ? this.regionsWithAll.find(item => item.code === this.region)?.name : null;
      return [this.$t('Players seen in uploaded replays.'), [gameType, region].filter(Boolean).map(name => this.$t(name)).join(' · ')].filter(Boolean).join(' ');
    },
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
    // The projection, only while the current month is on the chart
    projectedCurrentMonth() {
      const shown = this.filteredData[this.filteredData.length - 1];
      return shown && shown === this.data[this.data.length - 1] ? this.projection : null;
    },
    // Current month so far, grown by the median of how the previous months grew
    // from the same point. A median so one unusual month can't skew it.
    projection() {
      const current = this.data[this.data.length - 1];
      if (!current || !current.previous_months_to_date) {
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
    formatNumber(value) {
      return Number(value).toLocaleString('en-US');
    },
    // "October" from "2026-10"
    monthName(label) {
      const [year, month] = label.split('-');
      return new Date(Date.UTC(year, month - 1, 1)).toLocaleDateString(siteLocale(), { month: 'long', timeZone: 'UTC' });
    },
    toggleYear(year) {
      // From All, a click picks just that year
      if (this.allYearsActive) {
        this.activeYears = [year];
        return;
      }
      const idx = this.activeYears.indexOf(year);
      if (idx === -1) {
        this.activeYears.push(year);
      } else {
        this.activeYears.splice(idx, 1);
      }
    },
    fetchData() {
      this.loading = true;
      this.loadError = null;

      this.$axios.post('/api/v1/tools/activity/players/unique', {
        game_type: this.gametype,
        region: this.region,
      })
        .then(response => {
          this.data = response.data;
          const currentYear = new Date().getFullYear().toString();
          this.activeYears = this.availableYears.includes(currentYear) ? [currentYear] : [...this.availableYears];
        })
        .catch(error => {
          this.loadError = this.$loadError(error, () => this.fetchData());
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>
