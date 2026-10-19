<template>
  <div>
    <analytics-heading :title="'Coaching (experimental)'">
      Coaching looks for 14 habits (signals) in your processed games, like dying after a fight is already lost or walking without mounting, and compares your rate with other players under the same filters. Each signal has two ratings. Signal says how strongly that habit goes with winning or losing across all players: High means strong evidence, Medium some, Low not enough yet. Your sample says how many of your games back your number: High is 30+, Medium 10-29, Low under 10. The tips focus on signals that are both rated Medium or High and where most players do better than you. Click any signal for recent examples, each linked into the replay viewer.
    </analytics-heading>
    <p v-if="computedAt" class="text-sm text-gray-light mb-4">Signal ratings updated {{ computedAt }}.</p>

    <p v-if="!mine.games" class="text-center text-gray-light">No coaching data for these filters yet. It's built nightly from processed games.</p>

    <template v-else>
      <!-- The replay viewer's announcer, summing up what to work on -->
      <analytics-speech-bubble class="mb-6" :talking="talking" @done="talking = false">
        <div v-for="(line, index) in summary" :key="index" :class="{ 'mt-1': index > 0 }">
          <span class="font-bold">{{ line.lead }}</span> {{ line.text }}
        </div>
      </analytics-speech-bubble>

      <!-- Your last game: this one game's signals against your usual, and its flagged moments -->
      <template v-if="lastGame">
        <analytics-heading :title="'Your last game'" :takeaway="lastGameTakeaway">
          Your most recent game under these filters, broken down signal by signal. Your usual is your average over your earlier games; a value counts as better or worse than usual only when it is further from that average than your games normally vary. Coaching is rebuilt overnight, so a game you just played shows up here the day after it is processed. Check the game below is the one you expect.
        </analytics-heading>
        <!-- Coaching is rebuilt once a day, so the newest games can be missing -->
        <p v-if="notYet > 0" class="text-sm bg-darken rounded p-3 mb-2 border-l-4 border-yellow">
          This isn't your most recent game: {{ notYet }} newer {{ notYet === 1 ? "game hasn't" : "games haven't" }} been added to coaching yet. Coaching updates once a day; check back after {{ nextUpdate }}.
        </p>
        <div class="flex flex-wrap items-center justify-between gap-4 bg-darken rounded p-3 mb-2">
          <div>
            <span class="font-bold">{{ heroName(lastGame.hero_id) }}</span> on {{ mapName(lastGame.game_map) }}
            &middot; <span :class="isWin(lastGame) ? 'text-teal' : 'text-red'">{{ isWin(lastGame) ? 'Win' : 'Loss' }}</span>
            <div class="text-sm text-gray-light">Game ended {{ formatDateTime(lastGame.game_date) }} &middot; Replay {{ lastGame.replay_id }}</div>
          </div>
          <custom-button
            :href="'/Analytics/Match/Single/' + lastGame.replay_id"
            :text="'Watch this game'"
            :alt="'Open this game in the replay viewer'"
            size="small"
          ></custom-button>
        </div>
        <table class="min-w-0 w-full responsive-table mb-4">
          <thead>
            <tr>
              <th>Signal</th>
              <th>This game</th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Your usual
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Your average over your earlier games under these filters (up to 200). Needs at least 5 earlier games.</p></slot>
                  </round-image>
                </span>
              </th>
              <th>Global median</th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Against your usual
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Better or Worse when this game is further from your usual than one game normally swings (the spread of your earlier games). Otherwise Typical.</p></slot>
                  </round-image>
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in lastGameRows" :key="row.key">
              <td>{{ row.name }}</td>
              <td>{{ format(row, row.game) }}</td>
              <td>{{ format(row, row.usual) }}</td>
              <td>{{ format(row, row.median) }}</td>
              <td><span :class="verdictClass(row.direction)">{{ row.verdict }}</span></td>
            </tr>
          </tbody>
        </table>
        <p v-if="!lastEvents.length" class="text-sm text-gray-light mb-8">Nothing flagged in this game.</p>
        <table v-else class="min-w-0 w-full responsive-table mb-8">
          <thead>
            <tr>
              <th>Time</th>
              <th>Signal</th>
              <th>What happened</th>
              <th>Replay</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(event, index) in lastEvents" :key="index">
              <td>{{ clock(event.t_s) }}</td>
              <td>{{ eventSignal(event) }}</td>
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
      </template>

      <!-- Are you improving: your last few games against your earlier ones, per signal -->
      <div class="flex flex-wrap items-start justify-between gap-4">
        <analytics-heading :title="'Are you improving?'" :takeaway="progressTakeaway">
          Your average over your last {{ recentCount }} games compared with your average over your earlier games under these filters, for each signal rated Medium or High. Improving or Worse only shows when the change is bigger than your usual swing, worked out from how much your earlier games vary (shown under each verdict); anything smaller is About the same, because a few games are mostly noise. Signals marked (working on) are the ones the summary above tells you to work on. Click a signal for a chart of up to your last 20 games. New games appear here the day after they are processed.
        </analytics-heading>
        <tab-button :tab1text="'Last 3 games'" :tab2text="'Last 5 games'" :ignoreclick="true" @tab-click="setRecentCount" :overridedefaultside="recentCount === 3 ? 'left' : 'right'"></tab-button>
      </div>
      <p v-if="recentGames.length < recentCount + 5" class="text-center text-gray-light mb-8">
        Not enough games yet: this needs at least {{ recentCount + 5 }} processed games under these filters (you have {{ recentGames.length }}).
      </p>
      <table v-else class="min-w-0 w-full responsive-table mb-8">
        <thead>
          <tr>
            <th>Signal</th>
            <th>Earlier games</th>
            <th>Last {{ recentCount }}</th>
            <th>
              <span class="inline-flex items-center gap-1">
                Change
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">Last {{ recentCount }} minus earlier games. Whether up is good depends on the signal: for deaths and missed fights, a minus is better.</p></slot>
                </round-image>
              </span>
            </th>
            <th>
              <span class="inline-flex items-center gap-1">
                Verdict
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">Improving or Worse when the change is bigger than your usual swing for {{ recentCount }} games (your earlier games' spread divided by the square root of {{ recentCount }}). Otherwise About the same.</p></slot>
                </round-image>
              </span>
            </th>
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
              <td>
                <span :class="verdictClass(row.direction)">{{ row.verdict }}</span>
                <div class="text-sm text-gray-light font-normal">usual swing {{ format(row, row.swing) }}</div>
              </td>
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

      <analytics-heading :title="'All signals'" :takeaway="allTakeaway">
        Every signal, whatever its rating. Shares are a percentage of chances (fights, objectives, travel time); counts are per game; Camp duty is in percentage points above or below the norm for your hero. The Standing bar runs from worse (left) to better (right), with the global median marked in the middle. Click a signal for the numbers behind its ratings, who you are compared with, and recent examples.
      </analytics-heading>
      <table class="min-w-0 w-full responsive-table">
        <thead>
          <tr>
            <th>Signal</th>
            <th>
              <span class="inline-flex items-center gap-1">
                You
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">Your rate across all your processed games under these filters.</p></slot>
                </round-image>
              </span>
            </th>
            <th>
              <span class="inline-flex items-center gap-1">
                Global median
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">The middle player in the global sample under these filters: half of players are above it, half below. Click a signal to see who it is compared with.</p></slot>
                </round-image>
              </span>
            </th>
            <th>
              <span class="inline-flex items-center gap-1">
                Standing
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">Where you rank among those players, taking into account which way is better for that signal (fewer deaths, more presence).</p></slot>
                </round-image>
              </span>
            </th>
            <th>
              <span class="inline-flex items-center gap-1">
                Signal
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">How strongly this habit goes with winning or losing across all players, re-checked nightly. High: it passes all three tests (linked to win rate beyond deaths, a 3+ point win-rate effect when it happens in the first 10 minutes, and consistent from game to game). Medium: one of the first two. Low: neither yet.</p></slot>
                </round-image>
              </span>
            </th>
            <th>
              <span class="inline-flex items-center gap-1">
                Your sample
                <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                  <slot><p class="text-sm p-2 text-left">How many of your games back your number. High: 30+, Medium: 10-29, Low: under 10. Some signals only count certain games (maps with bushes, games with mounts, strong camp heroes).</p></slot>
                </round-image>
              </span>
            </th>
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
              <td :title="signal.basis">
                <template v-if="signal.percentile === null">-</template>
                <template v-else>
                  <!-- Worse to better, median tick in the middle -->
                  <div class="relative h-2 bg-lighten rounded min-w-[80px] my-2" :title="standingText(signal)">
                    <div class="absolute -top-1 h-4 w-px bg-gray-medium left-1/2"></div>
                    <div class="absolute top-1/2 w-3 h-3 rounded-full -translate-x-1/2 -translate-y-1/2" :class="playersAhead(signal) > 50 ? 'bg-red' : 'bg-teal'" :style="{ left: standingPosition(signal) + '%' }"></div>
                  </div>
                  <div class="text-sm">{{ standingText(signal) }}</div>
                </template>
              </td>
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
                <div class="text-sm text-gray-light mb-2">
                  <span class="inline-flex items-center gap-1">
                    {{ evidenceText(signal) }}
                    <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                      <slot><p class="text-sm p-2 text-left">Early-game effect: how much win rate changes when this happens in the first 10 minutes, in games that were equally close on deaths at that point (3+ points counts). Link beyond deaths: how closely players' rate tracks their win rate once deaths per game are accounted for (r from -1 to 1; 0.15+ in either direction counts). Habit consistency: how similar a player's odd and even games are (0.2+ counts).</p></slot>
                    </round-image>
                  </span>
                </div>
                <p v-if="signal.basis" class="text-sm text-gray-light mb-2">Compared with {{ signal.basis }}.</p>
                <p class="text-sm text-gray-light mb-2">Your sample: {{ signal.sampleGames || 0 }} games.</p>
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
    notYet() {
      return Number(((this.data.not_yet || [])[0] || {}).games || 0);
    },
    // The nightly rebuild runs at 14:00 UTC; shown in the player's own time
    nextUpdate() {
      const next = new Date();
      next.setUTCHours(14, 0, 0, 0);
      if (next <= new Date()) next.setUTCDate(next.getUTCDate() + 1);
      return next.toLocaleString(undefined, { weekday: 'short', hour: 'numeric', minute: '2-digit' });
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
          // Percentile points above your value; points equal to it are ties, counted neither way
          above: value === null || !percentiles.length ? null : percentiles.filter(p => p > value).length,
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
    lastGame() {
      return this.recentGames[0] || null;
    },
    // The last game's value for each signal against your earlier games; a one-game swing is the
    // spread (standard deviation) of those earlier games
    lastGameRows() {
      if (!this.lastGame) return [];
      return this.rows
        .filter(r => GAME_COLUMN[r.key])
        .map(r => {
          const raw = this.lastGame[GAME_COLUMN[r.key]];
          if (raw === null || raw === undefined || raw === '') return null;
          const game = Number(raw);
          const earlier = this.recentGames.slice(1).map(g => g[GAME_COLUMN[r.key]]).filter(v => v !== null && v !== undefined && v !== '').map(Number);
          if (earlier.length < 5) return { ...r, game, usual: null, verdict: '-', direction: null };
          const c = this.againstEarlier(r, [game], earlier);
          return { ...r, game, usual: c.usual, direction: c.direction, verdict: { better: 'Better', worse: 'Worse', same: 'Typical' }[c.direction] };
        })
        .filter(Boolean);
    },
    lastEvents() {
      return this.data.last_events || [];
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
          const c = this.againstEarlier(r, recent, earlier);
          const verdict = { better: 'Improving', worse: 'Worse', same: 'About the same' }[c.direction];
          return { ...r, earlier: c.usual, recent: c.mean, diff: c.diff, swing: c.swing, direction: c.direction, verdict, focus: this.workKeys.includes(r.key) };
        })
        .filter(Boolean)
        .sort((a, b) => Number(b.focus) - Number(a.focus));
    },
    lastGameTakeaway() {
      const count = d => this.lastGameRows.filter(r => r.direction === d).length;
      if (!this.lastGameRows.some(r => r.direction)) return null;
      return 'Better than your usual on ' + count('better') + ' signals, worse on ' + count('worse') + '.';
    },
    progressTakeaway() {
      if (!this.progressRows.length) return null;
      const names = d => this.progressRows.filter(r => r.direction === d).map(r => r.name.toLowerCase());
      const [up, down] = [names('better'), names('worse')];
      if (!up.length && !down.length) return 'Your last ' + this.recentCount + ' games look like your earlier ones on every signal.';
      return [up.length ? 'Improving: ' + up.join(', ') + '.' : '', down.length ? 'Slipping: ' + down.join(', ') + '.' : ''].filter(Boolean).join(' ');
    },
    allTakeaway() {
      const rated = this.rows.filter(r => r.percentile !== null);
      if (!rated.length) return null;
      const ahead = rated.filter(r => this.playersAhead(r) <= 50).length;
      return 'You are at or above the median on ' + ahead + ' of ' + rated.length + ' signals.';
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
    // Share of players doing better than you on this signal (ties count neither way)
    playersAhead(signal) {
      const below = Math.max(0, Math.min(100, signal.percentile));
      const above = Math.max(0, Math.min(100, signal.above));
      return signal.better === 'lower' ? below : above;
    },
    playersBehind(signal) {
      const below = Math.max(0, Math.min(100, signal.percentile));
      const above = Math.max(0, Math.min(100, signal.above));
      return signal.better === 'lower' ? above : below;
    },
    // Whichever way reads naturally: "Better than 70% of players" or "61% of players are better than you",
    // plus the tied share when many players share your value (e.g. lots of players at 0)
    standingText(signal) {
      const ahead = this.playersAhead(signal);
      const behind = this.playersBehind(signal);
      const same = Math.max(0, 100 - ahead - behind);
      const text = ahead > 50 ? ahead + '% of players are better than you' : 'Better than ' + behind + '% of players';
      return same >= 10 ? text + ', ' + same + '% the same' : text;
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
    // Recent values (one game, or the last few) against your earlier games. The usual swing of an
    // n-game average is one standard error of the earlier games; a floor of 1% of the earlier
    // average stops a signal that never varies from flagging rounding noise.
    againstEarlier(signal, recent, earlier) {
      const mean = list => list.reduce((a, b) => a + b, 0) / list.length;
      const usual = mean(earlier);
      const sd = Math.sqrt(earlier.reduce((t, v) => t + (v - usual) ** 2, 0) / (earlier.length - 1));
      const swing = sd / Math.sqrt(recent.length);
      const diff = mean(recent) - usual;
      const better = signal.better === 'lower' ? diff < 0 : diff > 0;
      const direction = Math.abs(diff) <= Math.max(swing, Math.abs(usual) * 0.01, 1e-6) ? 'same' : better ? 'better' : 'worse';
      return { usual, mean: mean(recent), diff, swing, direction };
    },
    verdictClass(direction) {
      return direction === 'better' ? 'text-teal' : direction === 'worse' ? 'text-red' : 'text-gray-light';
    },
    // Where the standing marker sits, worse to better; ties split down the middle
    standingPosition(signal) {
      const behind = this.playersBehind(signal);
      const same = Math.max(0, 100 - this.playersAhead(signal) - behind);
      return Math.max(2, Math.min(98, behind + same / 2));
    },
    // Your last 20 games for one signal, oldest first, with a 5-game rolling average
    trend(row) {
      const games = this.recentGames
        .map(g => ({ date: g.game_date, value: g[GAME_COLUMN[row.key]], win: this.isWin(g) }))
        .filter(g => g.value !== null && g.value !== undefined && g.value !== '')
        .slice(0, 20)
        .reverse();
      const scale = v => (row.share || row.delta ? Number(v) * 100 : Number(v));
      const values = games.map(g => this.round(scale(g.value), 2));
      const rolling = values.map((v, i) => {
        const window = values.slice(Math.max(0, i - 4), i + 1);
        return window.length < 3 ? null : this.round(window.reduce((a, b) => a + b, 0) / window.length, 2);
      });
      return { labels: games.map(g => moment.tz(g.date, 'Atlantic/Reykjavik').clone().tz(moment.tz.guess()).format('MM/DD')), values, rolling, wins: games.map(g => g.win) };
    },
    trendSeries(row) {
      const t = this.trend(row);
      return [
        // Each game's dot is teal for a win, red for a loss
        { label: 'Each game (teal win, red loss)', data: t.values, color: 'teal', points: t.wins.map(win => (win ? 'teal' : 'red')) },
        { label: '5-game average', data: t.rolling, color: 'yellow', dashed: true },
      ];
    },
    examples(signal) {
      return (this.data.events || [])
        .filter(e => signal.events.includes(e.kind) && (!signal.filter || signal.filter(e)))
        .slice(0, 10);
    },
    isWin(game) {
      return game.win === true || game.win === 'true' || Number(game.win) === 1;
    },
    // The signal a flagged moment belongs to
    eventSignal(event) {
      const signal = SIGNALS.find(s => s.events.includes(event.kind) && (!s.filter || s.filter(event)));
      return signal ? signal.name : event.kind;
    },
    // game_date is when the game ended, in UTC
    formatDateTime(value) {
      return moment.tz(value, 'Atlantic/Reykjavik').clone().tz(moment.tz.guess()).format('MM/DD/YYYY h:mm A');
    },
    formatDate(value) {
      return moment.tz(value, 'Atlantic/Reykjavik').clone().tz(moment.tz.guess()).format('MM/DD/YYYY');
    },
  },
};
</script>
