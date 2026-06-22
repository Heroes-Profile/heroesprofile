<template>
  <div class="md:w-[1000px]">
    <div v-for="day in computedDays" :key="day.label" class="items-center gap-10 md:px-20 py-5 justify-center">
      <div class="flex gap-10 text-s">
        <span>{{ day.label }}</span>
        <span>Total Games: {{ (day.wins + day.losses).toLocaleString('en-US') }}</span>
      </div>
      <stat-bar-box size="big" :value="day.winRate.toFixed(2)" :color="day.color"></stat-bar-box>
    </div>
  </div>
</template>

<script>
import moment from 'moment-timezone';

const DAY_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
const DAY_COLORS = ['blue', 'teal', 'red', 'yellow', 'blue', 'red', 'teal'];

export default {
  name: 'PlayerWeekdayWinRates',
  props: {
    data: Object,
  },
  computed: {
    computedDays() {
      const totals = [
        { wins: 0, losses: 0 },
        { wins: 0, losses: 0 },
        { wins: 0, losses: 0 },
        { wins: 0, losses: 0 },
        { wins: 0, losses: 0 },
        { wins: 0, losses: 0 },
        { wins: 0, losses: 0 },
      ];

      if (!this.data) return this.formatDays(totals);

      // Get user's UTC offset in whole hours (e.g. -5 for EST, +1 for CET)
      const offsetHours = moment.tz(moment.tz.guess()).utcOffset() / 60;

      for (const [bucket, stat] of Object.entries(this.data)) {
        const utcHourOfWeek = parseInt(bucket);
        // Shift bucket by timezone offset, wrap within 168-hour week
        const localHourOfWeek = ((utcHourOfWeek + offsetHours) % 168 + 168) % 168;
        const localDay = Math.floor(localHourOfWeek / 24); // 0=Mon ... 6=Sun

        totals[localDay].wins += stat.wins || 0;
        totals[localDay].losses += stat.losses || 0;
      }

      return this.formatDays(totals);
    },
  },
  methods: {
    formatDays(totals) {
      return totals.map((day, i) => {
        const total = day.wins + day.losses;
        return {
          label: DAY_NAMES[i],
          color: DAY_COLORS[i],
          wins: day.wins,
          losses: day.losses,
          winRate: total > 0 ? (day.wins / total) * 100 : 0,
        };
      });
    },
  },
};
</script>
