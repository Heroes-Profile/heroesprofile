<template>
  <div>
    <h2 class="text-xl font-bold mb-2 text-teal">Activity over the game</h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-2"
      :labels="minutes.map(m => m + ' min')"
      :yLabel="'Ability commands per minute'"
      :series="[
        { label: 'You', data: apm('you'), color: 'teal' },
        { label: 'Baseline', data: apm('baseline'), color: 'yellow' },
      ]"
    ></multi-series-chart>
    <p class="text-center text-sm mb-8 text-gray-light">Counts ability and attack orders, not mouse movement.</p>

    <h2 class="text-xl font-bold mb-2 text-teal">When you pick talents</h2>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-2"
      type="bar"
      :labels="tierLabels"
      :yLabel="'Game time (seconds)'"
      :series="[
        { label: 'You', data: talents('you'), color: 'teal' },
        { label: 'Baseline', data: talents('baseline'), color: 'yellow' },
      ]"
    ></multi-series-chart>
    <p class="text-center text-sm mb-8 text-gray-light">Average game time of each pick. Choose a hero in the filters to compare like for like.</p>

    <h2 class="text-xl font-bold mb-2 text-teal">Ability usage</h2>
    <p class="text-center text-gray-light">Coming soon: ability names are being mapped from game data.</p>
  </div>
</template>

<script>
import analytics from './deepAnalyticsFormat.js';

export default {
  name: 'DeepAnalyticsActivity',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  data() {
    return {
      tierLabels: ['Level 1', 'Level 4', 'Level 7', 'Level 10', 'Level 13', 'Level 16', 'Level 20'],
    };
  },
  computed: {
    minutes() {
      return [...new Set(this.data.apm.map(r => r.minute))].sort((a, b) => a - b);
    },
  },
  methods: {
    apm(who) {
      return this.minutes.map(m => {
        const row = this.data.apm.find(r => r.who === who && r.minute === m);
        return row ? this.round(row.commands, 1) : null;
      });
    },
    talents(who) {
      return [0, 1, 2, 3, 4, 5, 6].map(tier => {
        const row = this.data.talent_timing.find(r => r.who === who && r.tier === tier);
        return row ? Math.round(row.avg_seconds) : null;
      });
    },
  },
};
</script>
