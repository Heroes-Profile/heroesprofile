<template>
  <div>
    <!-- Sessions / tilt -->
    <h2 class="text-xl font-bold mb-2 text-teal">Sessions</h2>
    <div class="flex flex-wrap justify-center gap-4 mb-4">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Sessions'" :value="sessions.length" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Games per Session'" :value="round(games.length / Math.max(1, sessions.length))" color="yellow"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate After a Win'" :value="winRate(afterResult(true))" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate After a Loss'" :value="winRate(afterResult(false))" color="red"></stat-box>
    </div>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-2"
      type="bar"
      :labels="['Game 1', 'Game 2', 'Game 3', 'Game 4', 'Game 5', 'Game 6+']"
      :title="'Win rate by game number in a session'"
      :yLabel="'%'"
      :series="[{ label: 'You', data: bySessionPosition, color: 'teal' }]"
    ></multi-series-chart>
    <p class="text-center text-sm mb-8 text-gray-light">A session ends after a 30 minute break.</p>

    <!-- Time of day -->
    <h2 class="text-xl font-bold mb-2 text-teal">When you play</h2>
    <div class="flex flex-wrap gap-6 justify-center mb-2">
      <multi-series-chart
        class="w-[48%] max-md:w-full"
        type="bar"
        :labels="hourBuckets.map(b => b.label)"
        :title="'Win rate by time of day'"
        :yLabel="'%'"
        :series="[{ label: 'You', data: hourBuckets.map(b => winRatePercent(b.games)), color: 'teal' }]"
      ></multi-series-chart>
      <multi-series-chart
        class="w-[48%] max-md:w-full"
        type="bar"
        :labels="dayNames"
        :title="'Win rate by day of week'"
        :yLabel="'%'"
        :series="[{ label: 'You', data: dayNames.map((d, i) => winRatePercent(games.filter(g => g.date.getDay() === i))), color: 'yellow' }]"
      ></multi-series-chart>
    </div>
    <p class="text-center text-sm mb-8 text-gray-light">Times are in your local time zone.</p>

    <!-- Game length -->
    <h2 class="text-xl font-bold mb-2 text-teal">Game length</h2>
    <table class="min-w-0 w-full responsive-table max-w-[1200px] mx-auto mb-8">
      <thead>
        <tr><th>Map</th><th>Games</th><th>Average length</th><th v-for="b in lengthBuckets" :key="b.label">{{ b.label }}</th></tr>
      </thead>
      <tbody>
        <tr v-for="row in byMap" :key="row.map">
          <td>{{ mapName(row.map) }}</td>
          <td>{{ row.games.length }}</td>
          <td>{{ clock(row.games.reduce((sum, g) => sum + g.length_s, 0) / row.games.length) }}</td>
          <td v-for="b in lengthBuckets" :key="b.label">{{ bucketWinRate(row.games, b) }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Disconnects -->
    <h2 class="text-xl font-bold mb-2 text-teal">Disconnects</h2>
    <div class="flex flex-wrap justify-center gap-4">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Games With a Teammate Disconnect'" :value="dc.own_dc_games ?? '-'" :secondstat="percent(dc.win_rate_own_dc)" :secondcaption="'win rate'" color="red"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Games With an Enemy Disconnect'" :value="dc.enemy_dc_games ?? '-'" :secondstat="percent(dc.win_rate_enemy_dc)" :secondcaption="'win rate'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Win Rate Without Disconnects'" :value="percent(dc.win_rate_no_dc)" color="yellow"></stat-box>
    </div>
  </div>
</template>

<script>
import analytics from '../../utils/deepAnalyticsFormat.js';

const SESSION_BREAK_SECONDS = 30 * 60;

export default {
  name: 'DeepAnalyticsSessions',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  data() {
    return {
      dayNames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      lengthBuckets: [
        { label: 'Under 15 min', min: 0, max: 900 },
        { label: '15-20 min', min: 900, max: 1200 },
        { label: '20-25 min', min: 1200, max: 1500 },
        { label: '25+ min', min: 1500, max: Infinity },
      ],
    };
  },
  computed: {
    games() {
      // game_date is stored in UTC
      return this.data.games.map(g => ({ ...g, date: new Date(g.game_date + 'Z') }));
    },
    sessions() {
      const sessions = [];
      let current = [];
      this.games.forEach(game => {
        const previous = current[current.length - 1];
        if (previous && (game.date - previous.date) / 1000 - previous.length_s > SESSION_BREAK_SECONDS) {
          sessions.push(current);
          current = [];
        }
        current.push(game);
      });
      if (current.length) {
        sessions.push(current);
      }
      return sessions;
    },
    bySessionPosition() {
      return [0, 1, 2, 3, 4, 5].map(position => {
        const games = this.sessions.flatMap(s => (position < 5 ? (s[position] ? [s[position]] : []) : s.slice(5)));
        return this.winRatePercent(games);
      });
    },
    hourBuckets() {
      return [0, 4, 8, 12, 16, 20].map(start => ({
        label: `${start}:00-${start + 4}:00`,
        games: this.games.filter(g => g.date.getHours() >= start && g.date.getHours() < start + 4),
      }));
    },
    byMap() {
      const groups = {};
      this.games.forEach(g => {
        (groups[g.game_map] = groups[g.game_map] || []).push(g);
      });
      return Object.entries(groups)
        .map(([map, games]) => ({ map: Number(map), games }))
        .sort((a, b) => b.games.length - a.games.length);
    },
    dc() {
      return (this.data.disconnects || [])[0] || {};
    },
  },
  methods: {
    afterResult(won) {
      return this.sessions.flatMap(s => s.slice(1).filter((g, i) => s[i].winner === won));
    },
    winRatePercent(games) {
      return games.length ? this.round((100 * games.filter(g => g.winner).length) / games.length, 1) : null;
    },
    winRate(games) {
      return games.length ? this.winRatePercent(games) + '%' : '-';
    },
    bucketWinRate(games, bucket) {
      const inBucket = games.filter(g => g.length_s >= bucket.min && g.length_s < bucket.max);
      return inBucket.length ? this.winRate(inBucket) + ' (' + inBucket.length + ')' : '-';
    },
  },
};
</script>
