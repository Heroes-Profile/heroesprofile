<template>
  <div>
    <infobox class="max-w-[1200px] mx-auto mb-4" :input="'How your results change with when and how long you play. A session is a run of games played back to back: a new session starts when more than 30 minutes pass between the end of one game and the start of the next. Every number on this tab is a plain win rate from your own games under the current filters, so it describes your history rather than proving a cause.'"></infobox>

    <!-- Sessions / tilt -->
    <h2 class="text-xl font-bold mb-2 text-teal"><span class="inline-flex items-center gap-1">Sessions <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000"><slot><p class="text-sm p-2 text-left">A session is a run of games with no break longer than 30 minutes between the end of one game and the start of the next. Sessions are built from all your games, then only the games matching the current filters are counted, at their place in the session.</p></slot></round-image></span></h2>
    <div class="flex flex-wrap justify-center gap-4 mb-4">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Sessions'" :value="sessions.length" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Games per Session'" :value="round(sessions.reduce((sum, s) => sum + s.length, 0) / Math.max(1, sessions.length))" color="yellow"></stat-box>
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
    <infobox class="max-w-[1200px] mx-auto mb-8 text-center" :input="'Each bar is your win rate in the 1st, 2nd, 3rd... game of a session; Game 6+ combines every later game. Win Rate After a Win (or Loss) uses only games whose previous game in the same session was a win (or loss), so the first game of each session is not counted. Sessions include every game you played, so a filtered game keeps its real place in the session. A falling line or a low after-a-loss rate can be a sign of tilt or fatigue.'"></infobox>

    <!-- Time of day -->
    <h2 class="text-xl font-bold mb-2 text-teal"><span class="inline-flex items-center gap-1">When you play <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000"><slot><p class="text-sm p-2 text-left">Win rate by the hour and weekday each game started, in your browser's local time zone. Time of day is grouped into four-hour blocks.</p></slot></round-image></span></h2>
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
    <p class="text-center text-sm mb-8 text-gray-light">Times are in your local time zone, by the time each game started.</p>

    <!-- Game length -->
    <h2 class="text-xl font-bold mb-2 text-teal"><span class="inline-flex items-center gap-1">Game length <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000"><slot><p class="text-sm p-2 text-left">Your win rate on each map split by how long the game lasted. The number in brackets is how many of your games fall in that length range; - means none.</p></slot></round-image></span></h2>
    <table class="min-w-0 w-full responsive-table max-w-[1200px] mx-auto mb-8">
      <thead>
        <tr><th>Map</th><th>Games</th><th><span class="inline-flex items-center gap-1">Average length <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000"><slot><p class="text-sm p-2 text-left">Mean game length on that map, in minutes:seconds.</p></slot></round-image></span></th><th v-for="b in lengthBuckets" :key="b.label">{{ b.label }}</th></tr>
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
    <h2 class="text-xl font-bold mb-2 text-teal"><span class="inline-flex items-center gap-1">Disconnects <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000"><slot><p class="text-sm p-2 text-left">A disconnect is any player leaving more than 30 seconds before the game ended, even if they rejoined later. Leaving in the last 30 seconds is treated as the normal end-of-game exit.</p></slot></round-image></span></h2>
    <infobox class="max-w-[1200px] mx-auto mb-4 text-center" :input="'You Disconnected counts games where you disconnected. Teammate DC Games counts games where another player on your team disconnected. Enemy DC Games counts games where anyone on the enemy team disconnected. A game can count in more than one. Win Rate, No DCs uses only games where nobody disconnected.'"></infobox>
    <div class="flex flex-wrap justify-center gap-4">
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'You Disconnected'" :value="dc.you_dc_games ?? '-'" :secondstat="percent(dc.win_rate_you_dc)" :secondcaption="'win rate'" color="red"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Teammate DC Games'" :value="dc.mate_dc_games ?? '-'" :secondstat="percent(dc.win_rate_mate_dc)" :secondcaption="'win rate'" color="red"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Enemy DC Games'" :value="dc.enemy_dc_games ?? '-'" :secondstat="percent(dc.win_rate_enemy_dc)" :secondcaption="'win rate'" color="teal"></stat-box>
      <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate, No DCs'" :value="percent(dc.win_rate_no_dc)" color="yellow"></stat-box>
    </div>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

const SESSION_BREAK_SECONDS = 30 * 60;

export default {
  name: 'AnalyticsSessions',
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
    // Every game, in order; sessions are built from these so a filter can't split one
    allGames() {
      // game_date is stored in UTC
      return this.data.games.map(g => ({ ...g, date: new Date(g.game_date + 'Z'), in_filter: g.in_filter !== false && g.in_filter !== 'false' }));
    },
    // The games the stats count: the ones matching the filters
    games() {
      return this.allGames.filter(g => g.in_filter);
    },
    // Sessions with at least one filtered game
    sessions() {
      return this.allSessions.filter(s => s.some(g => g.in_filter));
    },
    allSessions() {
      const sessions = [];
      let current = [];
      this.allGames.forEach(game => {
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
        return this.winRatePercent(games.filter(g => g.in_filter));
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
      return this.sessions.flatMap(s => s.slice(1).filter((g, i) => g.in_filter && s[i].winner === won));
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
