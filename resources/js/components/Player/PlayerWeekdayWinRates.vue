<template>
  <div class="flex flex-col gap-3">
    <div v-for="day in computedDays" :key="day.label" class="flex flex-col gap-1.5">
      <div class="flex justify-between">
        <span class="text-header">{{ day.label }}</span>
        <span class="text-subtext">Games: {{ (day.wins + day.losses).toLocaleString('en-US') }}</span>
      </div>
      <stat-bar :value="day.winRate.toFixed(2)" suffix="%" :color="$winRateColor(day.winRate)"></stat-bar>
    </div>
  </div>
</template>

<script>
import moment from 'moment-timezone';

const DAY_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

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
        const winRate = total > 0 ? (day.wins / total) * 100 : 0;
        return {
          label: DAY_NAMES[i],
          wins: day.wins,
          losses: day.losses,
          winRate,
        };
      });
    },
  },
};
</script>
