<template>
  <div>
    <p v-if="!games.length" class="text-center text-gray-light">No games match the current filters.</p>

    <template v-else>
      <!-- The answers first -->
      <div class="flex flex-wrap justify-center gap-4 mb-8">
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Sessions'" :value="sessions.length" color="teal"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Games per Session'" :value="round(sessions.reduce((sum, s) => sum + s.length, 0) / Math.max(1, sessions.length))" color="yellow"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate After a Win'" :value="winRate(afterResult(true))" color="teal"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'Win Rate After a Loss'" :value="winRate(afterResult(false))" color="red"></stat-box>
      </div>

      <!-- Sessions / tilt -->
      <analytics-heading :title="'Sessions'" :takeaway="sessionTakeaway">
        How your results change with when and how long you play. Every number on this tab is a plain win rate from your own games under the current filters, so it describes your history rather than proving a cause.
        A session is a run of games with no break longer than 30 minutes between the end of one game and the start of the next. Sessions are built from all your games, then only the games matching the current filters are counted, at their place in the session.
        Each bar is your win rate in the 1st, 2nd, 3rd... game of a session; Game 6+ combines every later game. Win Rate After a Win (or Loss) uses only games whose previous game in the same session was a win (or loss), so the first game of each session is not counted. A falling line or a low after-a-loss rate can be a sign of tilt or fatigue.
      </analytics-heading>
      <multi-series-chart
        class="mb-8"
        type="bar"
        format="percent"
        :reference="50"
        :labels="positionLabels"
        :title="'Win rate by game number in a session'"
        :series="positionSeries"
      ></multi-series-chart>

      <!-- Time of day -->
      <analytics-heading :title="'When you play'" :takeaway="timeTakeaway">
        Win rate by the hour and weekday each game started, in your browser's local time zone. Time of day is grouped into four-hour blocks.
      </analytics-heading>
      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <multi-series-chart type="bar" format="percent" :reference="50" :labels="hourBuckets.map(b => b.label)" :title="'Win rate by time of day'" :series="hourSeries"></multi-series-chart>
        <multi-series-chart type="bar" format="percent" :reference="50" :labels="dayNames" :title="'Win rate by day of week'" :series="daySeries"></multi-series-chart>
      </div>

      <!-- Game length -->
      <analytics-heading :title="'Game length'" :takeaway="lengthTakeaway">
        Your win rate on each map split by how long the game lasted. Hover a bar for how many of your games fall in that length range.
      </analytics-heading>
      <multi-series-chart type="bar" format="percent" :reference="50" :labels="byMap.map(row => mapName(row.map))" :title="'Win rate by game length on each map'" :series="lengthSeries"></multi-series-chart>
      <analytics-numbers>
        <table class="min-w-0 w-full responsive-table">
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
        <p class="mt-2 text-gray-light">The number in brackets is how many of your games fall in that length range; - means none.</p>
      </analytics-numbers>

      <!-- Disconnects -->
      <analytics-heading :title="'Disconnects'" :takeaway="dcTakeaway">
        Each box is your win rate in those games, with how many games it covers. A disconnect (DC) is any player leaving more than 30 seconds before the game ended, even if they rejoined later. Leaving in the last 30 seconds is treated as the normal end-of-game exit. "You disconnected" counts games where you disconnected, "a teammate disconnected" games where another player on your team did, and "an enemy disconnected" games where anyone on the enemy team did. A game can count in more than one. No disconnects uses only games where nobody disconnected.
      </analytics-heading>
      <div class="flex flex-wrap justify-center gap-4">
        <!-- Each is your win rate in those games, with how many games beside it -->
        <stat-box class="w-[23%] max-md:w-[48%]" title="You DC'd" :value="percent(dc.win_rate_you_dc)" :secondstat="orDash(dc.you_dc_games)" :secondcaption="'games'" color="red"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" title="Teammate DC'd" :value="percent(dc.win_rate_mate_dc)" :secondstat="orDash(dc.mate_dc_games)" :secondcaption="'games'" color="red"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" title="Enemy DC'd" :value="percent(dc.win_rate_enemy_dc)" :secondstat="orDash(dc.enemy_dc_games)" :secondcaption="'games'" color="teal"></stat-box>
        <stat-box class="w-[23%] max-md:w-[48%]" :title="'No DCs'" :value="percent(dc.win_rate_no_dc)" color="yellow"></stat-box>
      </div>
    </template>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

const SESSION_BREAK_SECONDS = 30 * 60;
// Fewest games a bucket needs before a takeaway calls it best or worst
const TAKEAWAY_MIN_GAMES = 5;

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
      positionLabels: ['Game 1', 'Game 2', 'Game 3', 'Game 4', 'Game 5', 'Game 6+'],
      lengthBuckets: [
        { label: 'Under 15 min', min: 0, max: 900, color: 'teal' },
        { label: '15-20 min', min: 900, max: 1200, color: 'blue' },
        { label: '20-25 min', min: 1200, max: 1500, color: 'yellow' },
        { label: '25+ min', min: 1500, max: Infinity, color: 'red' },
      ],
    };
  },
  computed: {
    // Every game, in order; sessions are built from these so a filter can't split one
    allGames() {
      // game_date is when the game ended, in UTC; date is when it started
      return this.data.games.map(g => {
        const end = new Date(g.game_date + 'Z');
        return { ...g, end, date: new Date(end - g.length_s * 1000), in_filter: g.in_filter !== false && g.in_filter !== 'false' };
      });
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
        if (previous && (game.date - previous.end) / 1000 > SESSION_BREAK_SECONDS) {
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
    // Filtered games at each place in a session; the last bucket is 6+
    bySessionPosition() {
      return [0, 1, 2, 3, 4, 5].map(position => {
        const games = this.sessions.flatMap(s => (position < 5 ? (s[position] ? [s[position]] : []) : s.slice(5)));
        return games.filter(g => g.in_filter);
      });
    },
    positionSeries() {
      return [{ label: 'You', data: this.bySessionPosition.map(games => this.winRatePercent(games)), color: 'teal', n: this.bySessionPosition.map(games => games.length) }];
    },
    hourBuckets() {
      return [0, 4, 8, 12, 16, 20].map(start => ({
        label: `${start}:00-${start + 4}:00`,
        games: this.games.filter(g => g.date.getHours() >= start && g.date.getHours() < start + 4),
      }));
    },
    dayBuckets() {
      return this.dayNames.map((label, i) => ({ label, games: this.games.filter(g => g.date.getDay() === i) }));
    },
    hourSeries() {
      return [{ label: 'You', data: this.hourBuckets.map(b => this.winRatePercent(b.games)), color: 'teal', n: this.hourBuckets.map(b => b.games.length) }];
    },
    daySeries() {
      return [{ label: 'You', data: this.dayBuckets.map(b => this.winRatePercent(b.games)), color: 'teal', n: this.dayBuckets.map(b => b.games.length) }];
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
    lengthSeries() {
      return this.lengthBuckets.map(b => {
        const inBucket = this.byMap.map(row => this.inBucket(row.games, b));
        return { label: b.label, data: inBucket.map(games => this.winRatePercent(games)), color: b.color, n: inBucket.map(games => games.length) };
      });
    },
    dc() {
      return (this.data.disconnects || [])[0] || {};
    },
    sessionTakeaway() {
      const after = { win: this.afterResult(true), loss: this.afterResult(false) };
      if (!after.win.length || !after.loss.length) return null;
      const [win, loss] = [this.winRatePercent(after.win), this.winRatePercent(after.loss)];
      const gap = this.round(Math.abs(win - loss));
      if (gap < 1) return 'You win about as often after a loss (' + loss + '%) as after a win.';
      return 'You win ' + loss + '% after a loss, ' + gap + ' points ' + (loss < win ? 'lower' : 'higher') + ' than after a win.';
    },
    timeTakeaway() {
      const hour = this.best(this.hourBuckets);
      const day = this.best(this.dayBuckets);
      if (!hour || !day) return null;
      return 'You win most from ' + hour.label + ' (' + this.winRatePercent(hour.games) + '%) and on ' + day.label + ' (' + this.winRatePercent(day.games) + '%).';
    },
    lengthTakeaway() {
      const buckets = this.lengthBuckets.map(b => ({ label: b.label, games: this.inBucket(this.games, b) }));
      const best = this.best(buckets);
      const worst = this.best(buckets, true);
      if (!best || !worst || best === worst) return null;
      return 'Across all maps you win most in ' + best.label + ' games (' + this.winRatePercent(best.games) + '%) and least in ' + worst.label + ' games (' + this.winRatePercent(worst.games) + '%).';
    },
    dcTakeaway() {
      if (!this.dc.mate_dc_games || this.dc.win_rate_no_dc === null || this.dc.win_rate_no_dc === undefined) return null;
      return 'When a teammate disconnects you win ' + this.percent(this.dc.win_rate_mate_dc) + ' of games, against ' + this.percent(this.dc.win_rate_no_dc) + ' when nobody does.';
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
    inBucket(games, bucket) {
      return games.filter(g => g.length_s >= bucket.min && g.length_s < bucket.max);
    },
    bucketWinRate(games, bucket) {
      const inBucket = this.inBucket(games, bucket);
      return inBucket.length ? this.winRate(inBucket) + ' (' + inBucket.length + ')' : '-';
    },
    // Highest (or lowest) win rate bucket with enough games to mean something
    best(buckets, lowest = false) {
      const usable = buckets.filter(b => b.games.length >= TAKEAWAY_MIN_GAMES);
      if (!usable.length) return null;
      return usable.reduce((top, b) => {
        const [a, c] = [this.winRatePercent(b.games), this.winRatePercent(top.games)];
        return (lowest ? a < c : a > c) ? b : top;
      });
    },
  },
};
</script>
