<template>
  <div>
    <h2 class="text-xl font-bold mb-2 text-teal">Coaching (experimental)</h2>
    <p class="text-sm text-gray-light mb-4">
      Patterns from your processed games compared with the global sample. Each signal has two ratings:
      <b>Signal</b> is how strongly that behaviour relates to winning across all players (it updates nightly as more games come in),
      and <b>Your sample</b> is how many of your games back your number. Click a row for recent examples, each linked into the replay viewer.
      <span v-if="computedAt">Updated {{ computedAt }}.</span>
    </p>

    <p v-if="!mine.games" class="text-center text-gray-light">No coaching data for these filters yet. It's built nightly from processed games.</p>

    <template v-else>
      <!-- The replay viewer's announcer, summing up what to work on -->
      <analytics-speech-bubble class="mb-6" :talking="talking" @done="talking = false">
        <div v-for="(line, index) in summary" :key="index" :class="{ 'mt-1': index > 0 }">
          <span class="font-bold">{{ line.lead }}</span> {{ line.text }}
        </div>
      </analytics-speech-bubble>

      <!-- Are you improving: your last few games against your earlier ones, per signal -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
        <h3 class="text-lg font-bold">Are you improving?</h3>
        <tab-button :tab1text="'Last 3 games'" :tab2text="'Last 5 games'" :ignoreclick="true" @tab-click="setRecentCount" :overridedefaultside="recentCount === 3 ? 'left' : 'right'"></tab-button>
      </div>
      <p class="text-sm text-gray-light mb-2">
        Your last {{ recentCount }} games compared with your earlier games under these filters. A change only counts once it is bigger than your
        usual game-to-game swing; with so few games, most small changes are noise. New games appear here the day after they are processed.
      </p>
      <p v-if="recentGames.length < recentCount + 5" class="text-center text-gray-light mb-8">
        Not enough games yet: this needs at least {{ recentCount + 5 }} processed games under these filters (you have {{ recentGames.length }}).
      </p>
      <table v-else class="min-w-0 w-full responsive-table mb-8">
        <thead>
          <tr>
            <th>Signal</th>
            <th>Earlier games</th>
            <th>Last {{ recentCount }}</th>
            <th>Change</th>
            <th>Verdict</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="row in progressRows" :key="row.key">
            <tr class="cursor-pointer" :class="{ 'font-bold': progressSelected === row.key }" @click="progressSelected = progressSelected === row.key ? null : row.key">
              <td>
                {{ row.name }}
                <span v-if="row.focus" class="text-sm text-yellow ml-1">(working on)</span>
              </td>
              <td>{{ format(row, row.earlier) }}</td>
              <td>{{ format(row, row.recent) }}</td>
              <td>{{ changeText(row) }}</td>
              <td :title="'Usual swing for ' + recentCount + ' games: ' + format(row, row.swing)"><span :class="verdictClass(row.verdict)">{{ row.verdict }}</span></td>
            </tr>
            <tr v-if="progressSelected === row.key">
              <td colspan="5">
                <multi-series-chart
                  :labels="trend(row).labels"
                  :yLabel="row.name"
                  :series="trendSeries(row)"
                ></multi-series-chart>
                <p class="text-center text-sm text-gray-light">Your last {{ trend(row).labels.length }} games under these filters, oldest to newest.</p>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <h3 class="text-lg font-bold mb-1">All signals</h3>
      <table class="min-w-0 w-full responsive-table">
        <thead>
          <tr>
            <th>Signal</th>
            <th>You</th>
            <th>Global median</th>
            <th>Standing</th>
            <th>Signal</th>
            <th>Your sample</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="signal in rows" :key="signal.key">
            <tr class="cursor-pointer" :class="{ 'font-bold': selected === signal.key }" @click="selected = selected === signal.key ? null : signal.key">
              <td>
                <div>{{ signal.name }}</div>
                <div class="text-sm text-gray-light">{{ signal.description }}</div>
              </td>
              <td>{{ format(signal, signal.value) }}</td>
              <td>{{ signal.median === null ? '-' : format(signal, signal.median) }}</td>
              <td :title="signal.basis">{{ signal.percentile === null ? '-' : standingText(signal) }}</td>
              <td :title="evidenceText(signal)">
                <span :class="confidenceClass(signal.confidence)">{{ signal.confidence }}</span>
              </td>
              <td :title="signal.sampleGames + ' games'">
                <span :class="confidenceClass(signal.sample)">{{ signal.sample }}</span>
              </td>
            </tr>
            <tr v-if="selected === signal.key">
              <td colspan="6">
                <p v-if="signal.caveat" class="text-sm text-gray-light mb-2">{{ signal.caveat }}</p>
                <p class="text-sm text-gray-light mb-2">{{ evidenceText(signal) }}</p>
                <p v-if="signal.basis" class="text-sm text-gray-light mb-2">Compared with {{ signal.basis }}.</p>
                <p v-if="!examples(signal).length" class="text-sm">No recent examples.</p>
                <table v-if="examples(signal).length" class="min-w-0 w-full responsive-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Map</th>
                      <th>Hero</th>
                      <th>Time</th>
                      <th>What happened</th>
                      <th>Replay</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(event, index) in examples(signal)" :key="index">
                      <td>{{ formatDate(event.game_date) }}</td>
                      <td>{{ mapName(event.game_map) }}</td>
                      <td>{{ heroName(event.hero_id) }}</td>
                      <td>{{ clock(event.t_s) }}</td>
                      <td>{{ event.detail }}<template v-if="event.ref_hero_id"> ({{ heroName(event.ref_hero_id) }})</template></td>
                      <td>
                        <custom-button
                          :href="'/Analytics/Match/Single/' + event.replay_id + '?t=' + event.t_s"
                          :text="'Watch'"
                          :alt="'Watch this moment in the replay viewer'"
                          size="small"
                        ></custom-button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </template>
  </div>
</template>

<script>
import moment from 'moment-timezone';
import analytics from '../../utils/analyticsFormat.js';

// Each signal: how to read your number from the coaching totals, which way is better, and which
// flagged moments are its examples. Order is roughly by how much evidence there is so far.
const SIGNALS = [
  { key: 'overstay', name: 'Overstaying lost fights', description: 'Deaths after your team was already 2+ deaths behind in that fight', per: 'game', better: 'lower',
    value: m => m.overstay / m.games, events: ['overstay'] },
  { key: 'missed_lost_fight', name: 'Missing fights your team lost', description: 'Lost fights (2+ deaths) you were nowhere near', per: 'game', better: 'lower',
    value: m => m.lost_absent / m.games, events: ['missed_lost_fight'] },
  { key: 'missed_costly_fight', name: 'Missing costly lost fights', description: 'Lost fights you missed where the team then gave up an objective, camp or structure', per: 'game', better: 'lower',
    value: m => m.lost_absent_costly / m.games, events: ['missed_lost_fight'], filter: e => e.detail.includes('gave up') },
  { key: 'objective_presence', name: 'Objective presence', description: 'Share of your team\'s camp captures and enemy structure kills you were there for', share: true, better: 'higher',
    value: m => (m.obj_events ? m.obj_present / m.obj_events : null), events: ['missed_objective'] },
  { key: 'tier_down_fights', name: 'Fights a talent tier down', description: 'Fights you took part in while the enemy had a talent tier (10 or 20) your team didn\'t', per: 'game', better: 'lower',
    value: m => m.tier_down_present / m.games, events: ['tier_down_fight'],
    caveat: 'This mostly reflects your team being behind on levels, not just the choice to fight, so read it alongside the result.' },
  { key: 'caught_first', name: 'Caught first', description: 'Being the first death of a fight', per: 'game', better: 'lower',
    value: m => m.caught_first / m.games, events: ['caught_first'] },
  { key: 'picked_alone', name: 'Picked off alone', description: 'Deaths with no other death within 15 seconds', per: 'game', better: 'lower',
    value: m => m.picked_alone / m.games, events: ['picked_alone'] },
  { key: 'fight_presence', name: 'Fight presence', description: 'Share of fights (2+ deaths) you were in or near', share: true, better: 'higher',
    value: m => (m.fights ? m.fights_present / m.fights : null), events: [] },
  { key: 'unmounted_travel', name: 'Walking without mounting', description: 'Share of your travel time (outside fights) spent unmounted (games without mounts are skipped)', share: true, better: 'lower',
    value: m => m.unmounted_share, sampleGames: m => m.travel_games, events: ['unmounted_trip'],
    caveat: 'Travel follows where you click, so it is approximate; some heroes (e.g. Abathur) barely register.' },
  { key: 'camp_duty', name: 'Camp duty', description: 'On heroes that are strong at camps: your camp presence compared with the norm for that hero', delta: true, better: 'higher',
    value: m => m.camp_duty, sampleGames: m => m.camp_games, events: ['skipped_camp'] },
  { key: 'repeat_nemesis', name: 'Dying to the same hero again', description: 'Your 3rd and later deaths in a game to the same enemy hero in a duel or small gank', per: 'game', better: 'lower',
    value: m => m.repeat_deaths / m.games, events: ['repeat_nemesis'] },
  { key: 'matchup_unadapted', name: 'Not adapting to a losing matchup', description: 'Died twice to the same hero before 8:00, then again within 5 minutes', per: 'game', better: 'lower',
    value: m => m.matchups_unadapted / m.games, events: ['matchup_unadapted'] },
  { key: 'bush_deaths', name: 'Caught at a bush', description: 'First or only death of a fight, within 3 units of a bush (the closest we can get to face-checking)', per: 'game', better: 'lower',
    value: m => (m.bush_games ? m.bush_deaths / m.bush_games : null), sampleGames: m => m.bush_games, events: ['bush_death'],
    caveat: 'We know where bushes are but not what you could see, so this counts deaths beside bushes, not proven face-checks.' },
  { key: 'heroic_delay', name: 'Time to first heroic', description: 'Seconds from picking your heroic to first casting it', seconds: true, better: 'lower',
    value: m => m.heroic_delay, sampleGames: m => m.heroic_games, events: ['slow_heroic', 'unused_heroic'] },
];

// Each signal's column in your per-game rows (the "recent" query)
const GAME_COLUMN = {
  overstay: 'overstay',
  missed_lost_fight: 'lost_absent',
  missed_costly_fight: 'lost_absent_costly',
  objective_presence: 'objective_presence',
  tier_down_fights: 'tier_down_present',
  caught_first: 'caught_first',
  picked_alone: 'picked_alone',
  fight_presence: 'fight_presence',
  unmounted_travel: 'unmounted_travel',
  camp_duty: 'camp_duty',
  repeat_nemesis: 'repeat_deaths',
  matchup_unadapted: 'matchups_unadapted',
  bush_deaths: 'bush_deaths',
  heroic_delay: 'heroic_delay_s',
};

export default {
  name: 'AnalyticsCoaching',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
    // The page's current filters, so "global" is compared under the same ones
    filterParams: Object,
  },
  data() {
    return {
      selected: null,
      talking: true,
      recentCount: 5,
      progressSelected: null,
    };
  },
  computed: {
    mine() {
      const row = (this.data.mine || [])[0] || {};
      return Object.fromEntries(Object.entries(row).map(([key, value]) => [key, value === null ? null : Number(value)]));
    },
    stats() {
      return Object.fromEntries((this.data.signals || []).map(s => [s.signal, s]));
    },
    computedAt() {
      const any = (this.data.signals || [])[0];
      return any ? moment.utc(any.computed_at).local().format('MMM D, YYYY') : null;
    },
    compare() {
      return Object.fromEntries((this.data.compare || []).map(c => [c.signal, c]));
    },
    filtered() {
      const f = this.filterParams || {};
      return (f.game_type || []).length > 0 || (f.game_map || []).length > 0 || !!f.hero;
    },
    rows() {
      return SIGNALS.map(def => {
        const stat = this.stats[def.key] || {};
        const value = this.mine.games ? def.value(this.mine) : null;
        const { percentiles, basis } = this.comparison(def.key, stat);
        const sampleGames = def.sampleGames ? def.sampleGames(this.mine) : this.mine.games;
        return {
          ...def,
          value,
          median: percentiles.length ? percentiles[50] : null,
          percentile: value === null || !percentiles.length ? null : percentiles.filter(p => p < value).length,
          confidence: stat.confidence || 'Low',
          stat,
          sampleGames,
          sample: sampleGames >= 30 ? 'High' : sampleGames >= 10 ? 'Medium' : 'Low',
          basis,
        };
      });
    },
    // Signals with enough evidence (not Low, and not tier-down fights, which mostly mirror being
    // behind), ranked by how many players do better than you
    ranked() {
      return this.rows
        .filter(r => r.confidence !== 'Low' && r.percentile !== null && r.key !== 'tier_down_fights')
        .map(r => ({ ...r, ahead: this.playersAhead(r) }))
        .sort((a, b) => b.ahead - a.ahead);
    },
    recentGames() {
      return this.data.recent || [];
    },
    // The signals the summary tells you to work on
    workKeys() {
      return this.ranked.filter(r => r.ahead > 50).slice(0, 2).map(r => r.key);
    },
    // Last few games vs earlier games for every signal with enough evidence, the ones you are working on first
    progressRows() {
      const n = this.recentCount;
      return this.rows
        .filter(r => r.confidence !== 'Low' && GAME_COLUMN[r.key])
        .map(r => {
          const values = this.recentGames.map(g => g[GAME_COLUMN[r.key]]).filter(v => v !== null && v !== undefined && v !== '').map(Number);
          const recent = values.slice(0, n);
          const earlier = values.slice(n);
          if (recent.length < Math.min(n, 2) || earlier.length < 5) {
            return null;
          }
          const mean = list => list.reduce((a, b) => a + b, 0) / list.length;
          const earlierMean = mean(earlier);
          const sd = Math.sqrt(earlier.reduce((t, v) => t + (v - earlierMean) ** 2, 0) / (earlier.length - 1));
          // The usual swing of a few-game average: one standard error, from your earlier games
          const swing = sd / Math.sqrt(recent.length);
          const diff = mean(recent) - earlierMean;
          const better = r.better === 'lower' ? diff < 0 : diff > 0;
          // A floor of 1% of the earlier average, so a signal that never varies doesn't flag rounding noise
          const threshold = Math.max(swing, Math.abs(earlierMean) * 0.01, 1e-6);
          const verdict = Math.abs(diff) <= threshold ? 'About the same' : better ? 'Improving' : 'Worse';
          return { ...r, earlier: earlierMean, recent: mean(recent), diff, swing, verdict, focus: this.workKeys.includes(r.key) };
        })
        .filter(Boolean)
        .sort((a, b) => Number(b.focus) - Number(a.focus));
    },
    // What the announcer says: up to two things to work on, and your best area
    summary() {
      const work = this.ranked.filter(r => r.ahead > 50).slice(0, 2);
      const best = [...this.ranked].reverse().find(r => r.ahead < 50);
      const lines = [];
      if (work.length) {
        lines.push({ lead: 'Work on ' + work[0].name.toLowerCase() + '.', text: this.standingText(work[0]) + '.' });
        if (work[1]) {
          lines.push({ lead: 'Then ' + work[1].name.toLowerCase() + '.', text: this.standingText(work[1]) + '.' });
        }
      } else {
        lines.push({ lead: 'Nothing stands out to fix yet.', text: 'You are at or above most players on every signal with enough evidence behind it.' });
      }
      if (best) {
        lines.push({ lead: 'Keep it up: ' + best.name.toLowerCase() + '.', text: this.standingText(best) + '.' });
      }
      if (this.mine.games < 10) {
        lines.push({ lead: 'Early read:', text: 'only ' + this.mine.games + ' games so far, so this will firm up as more are processed.' });
      }
      return lines;
    },
  },
  methods: {
    // Global players under the same filters with 5+ games; with too few of them, global games under
    // the filters; without filters, the nightly all-data numbers (players with 10+ games)
    comparison(key, stat) {
      const c = this.compare[key];
      if (c && Number(c.players) >= 20) {
        return { percentiles: c.player_percentiles.map(Number), basis: Number(c.players).toLocaleString('en-US') + ' players with 5+ games on these filters' };
      }
      if (c && Number(c.game_n) >= 50) {
        return { percentiles: c.game_percentiles.map(Number), basis: Number(c.game_n).toLocaleString('en-US') + ' games on these filters (too few players for a per-player comparison)' };
      }
      // Unfiltered, or a result from before the filtered comparison existed
      if ((!this.filtered || !this.data.compare) && stat.percentiles) {
        return { percentiles: stat.percentiles.map(Number), basis: 'players with 10+ games' };
      }
      return { percentiles: [], basis: this.filtered ? 'nothing yet: not enough global games on these filters' : '' };
    },
    format(signal, value) {
      if (value === null || value === undefined || isNaN(value)) {
        return '-';
      }
      if (signal.share) {
        return this.round(value * 100, 1) + '%';
      }
      if (signal.seconds) {
        return Math.round(value) + ' s';
      }
      if (signal.delta) {
        return (value >= 0 ? '+' : '') + this.round(value * 100, 1) + ' pts';
      }
      return this.round(value, 2) + ' / game';
    },
    // Share of players (10+ games) doing better than you on this signal
    playersAhead(signal) {
      const below = Math.max(0, Math.min(100, signal.percentile));
      return signal.better === 'lower' ? below : 100 - below;
    },
    // Whichever way reads naturally: "Better than 70% of players" or "61% of players are better than you"
    standingText(signal) {
      const ahead = this.playersAhead(signal);
      return ahead > 50 ? ahead + '% of players are better than you' : 'Better than ' + (100 - ahead) + '% of players';
    },
    evidenceText(signal) {
      const s = signal.stat || {};
      const parts = [];
      if (s.early_effect_pts !== null && s.early_effect_pts !== undefined) {
        parts.push('early-game effect on win rate ' + (s.early_effect_pts > 0 ? '+' : '') + this.round(s.early_effect_pts, 1) + ' pts');
      }
      if (s.partial_r !== null && s.partial_r !== undefined) {
        parts.push('link to win rate beyond deaths r=' + this.round(s.partial_r, 2));
      }
      if (s.consistency_r !== null && s.consistency_r !== undefined) {
        parts.push('habit consistency r=' + this.round(s.consistency_r, 2));
      }
      return parts.length ? 'Evidence: ' + parts.join(', ') + ' (' + Number(s.player_games || 0).toLocaleString('en-US') + ' player-games).' : 'Not enough data yet.';
    },
    setRecentCount(side) {
      this.recentCount = side === 'left' ? 3 : 5;
    },
    changeText(row) {
      const sign = row.diff >= 0 ? '+' : '';
      if (row.share || row.delta) {
        return sign + this.round(row.diff * 100, 1) + ' pts';
      }
      if (row.seconds) {
        return sign + Math.round(row.diff) + ' s';
      }
      return sign + this.round(row.diff, 2) + ' / game';
    },
    verdictClass(verdict) {
      return verdict === 'Improving' ? 'text-teal' : verdict === 'Worse' ? 'text-red' : 'text-gray-light';
    },
    // Your last 20 games for one signal, oldest first, with a 5-game rolling average
    trend(row) {
      const games = this.recentGames
        .map(g => ({ date: g.game_date, value: g[GAME_COLUMN[row.key]] }))
        .filter(g => g.value !== null && g.value !== undefined && g.value !== '')
        .slice(0, 20)
        .reverse();
      const scale = v => (row.share || row.delta ? Number(v) * 100 : Number(v));
      const values = games.map(g => this.round(scale(g.value), 2));
      const rolling = values.map((v, i) => {
        const window = values.slice(Math.max(0, i - 4), i + 1);
        return window.length < 3 ? null : this.round(window.reduce((a, b) => a + b, 0) / window.length, 2);
      });
      return { labels: games.map(g => moment.tz(g.date, 'Atlantic/Reykjavik').clone().tz(moment.tz.guess()).format('MM/DD')), values, rolling };
    },
    trendSeries(row) {
      const t = this.trend(row);
      return [
        { label: 'Each game', data: t.values, color: 'teal' },
        { label: '5-game average', data: t.rolling, color: 'yellow', dashed: true },
      ];
    },
    confidenceClass(level) {
      return level === 'High' ? 'text-teal' : level === 'Medium' ? 'text-yellow' : 'text-red';
    },
    examples(signal) {
      return (this.data.events || [])
        .filter(e => signal.events.includes(e.kind) && (!signal.filter || signal.filter(e)))
        .slice(0, 10);
    },
    formatDate(value) {
      return moment.tz(value, 'Atlantic/Reykjavik').clone().tz(moment.tz.guess()).format('MM/DD/YYYY');
    },
  },
};
</script>
