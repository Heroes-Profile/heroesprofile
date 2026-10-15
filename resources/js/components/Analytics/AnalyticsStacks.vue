<template>
  <div>
    <h2 class="text-xl font-bold mb-2 text-teal">Stacks (experimental)</h2>
    <p class="text-sm text-gray-light mb-4">
      How you do with and against premade parties. A team's stack is its biggest party; "facing a stack" means the enemy's
      biggest party was 3 or more and bigger than yours. Global numbers come from the global sample under the same filters.
    </p>

    <p v-if="!hasData" class="text-center text-gray-light">Party data is still being loaded for your games. Check back soon.</p>

    <template v-else>
      <analytics-speech-bubble class="mb-6" :talking="talking" @done="talking = false">
        <div v-for="(line, index) in summary" :key="index" :class="{ 'mt-1': index > 0 }">
          <span class="font-bold">{{ line.lead }}</span> {{ line.text }}
        </div>
      </analytics-speech-bubble>

      <h3 class="text-lg font-bold mb-1">Against enemy stacks</h3>
      <table class="min-w-0 w-full responsive-table mb-6">
        <thead>
          <tr>
            <th>Enemy's biggest party</th>
            <th>Your games</th>
            <th>Your win rate</th>
            <th>Global win rate</th>
            <th>Difference</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in enemyRows" :key="row.size">
            <td>{{ partyLabel(row.size, true) }}</td>
            <td>{{ row.you ? row.you.games : 0 }}</td>
            <td>{{ row.you ? percent(row.you.win_rate) : '-' }}</td>
            <td>{{ row.global ? percent(row.global.win_rate) : '-' }}</td>
            <td>{{ difference(row) }}</td>
          </tr>
        </tbody>
      </table>

      <h3 class="text-lg font-bold mb-1">Stack advantage</h3>
      <multi-series-chart
        class="max-w-[1200px] mx-auto mb-2"
        type="bar"
        :labels="advantageLabels"
        :yLabel="'Win rate %'"
        :series="[
          { label: 'You', data: advantage('you'), color: 'teal' },
          { label: 'Global', data: advantage('global'), color: 'yellow' },
        ]"
      ></multi-series-chart>
      <p class="text-center text-sm mb-6 text-gray-light">Enemy's biggest party minus your team's. +3 is, say, a 5-stack against your duo.</p>

      <h3 class="text-lg font-bold mb-1">Your party size</h3>
      <table class="min-w-0 w-full responsive-table mb-6">
        <thead>
          <tr>
            <th>You queued</th>
            <th>Your games</th>
            <th>Your win rate</th>
            <th>Global win rate</th>
            <th>Difference</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in partyRows" :key="row.size">
            <td>{{ partyLabel(row.size, false) }}</td>
            <td>{{ row.you ? row.you.games : 0 }}</td>
            <td>{{ row.you ? percent(row.you.win_rate) : '-' }}</td>
            <td>{{ row.global ? percent(row.global.win_rate) : '-' }}</td>
            <td>{{ difference(row) }}</td>
          </tr>
        </tbody>
      </table>

      <h3 class="text-lg font-bold mb-1">How teams beat bigger stacks</h3>
      <p class="text-sm text-gray-light mb-2">
        Global teams that faced a bigger enemy stack of 3+: how the teams that won played compared with the teams that lost
        ({{ beatTeams.toLocaleString('en-US') }} teams). These are differences in what winning teams did, not proof that each one causes the win.
      </p>
      <table class="min-w-0 w-full responsive-table mb-6">
        <thead>
          <tr>
            <th>Suggestion</th>
            <th>Teams that won</th>
            <th>Teams that lost</th>
            <th>Difference</th>
            <th>Confidence</th>
            <th>You vs stacks</th>
            <th>You otherwise</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in suggestions" :key="row.key">
            <td>
              <div>{{ row.advice }}</div>
              <div class="text-sm text-gray-light">{{ row.name }}</div>
            </td>
            <td>{{ format(row, row.won) }}</td>
            <td>{{ format(row, row.lost) }}</td>
            <td>{{ (row.relative > 0 ? '+' : '') + round(row.relative * 100, 0) + '%' }}</td>
            <td><span :class="confidenceClass(row.confidence)">{{ row.confidence }}</span></td>
            <td>{{ mineVs.facing ? format(row, mineVs.facing[row.key]) : '-' }}</td>
            <td>{{ mineVs.other ? format(row, mineVs.other[row.key]) : '-' }}</td>
          </tr>
        </tbody>
      </table>
    </template>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

// Team behaviours compared between winners and losers against stacks. better: which direction helps.
const METRICS = [
  { key: 'picked_alone', name: 'Deaths with no other death within 15 s, per player', better: 'lower', advice: 'Stay grouped: stacks punish players caught alone.' },
  { key: 'overstay', name: 'Deaths after the team was 2+ deaths behind in a fight, per player', better: 'lower', advice: 'Leave fights that are already lost.' },
  { key: 'caught_first', name: 'Being the first death of a fight, per player', better: 'lower', advice: "Don't be the one caught out first before a fight." },
  { key: 'lost_absent', name: 'Lost fights a player was nowhere near, per player', better: 'lower', advice: "Don't let your team fight a stack without you." },
  { key: 'fight_presence', name: 'Share of fights a player was in or near', better: 'higher', share: true, advice: 'Show up to fights together.' },
  { key: 'objective_presence', name: "Share of the team's camp captures and structure kills a player was there for", better: 'higher', share: true, advice: 'Take objectives and camps as a group.' },
  { key: 'camps', name: 'Camp captures a player was present for', better: 'higher', advice: 'Use camps to keep pace with their coordination.' },
  { key: 'tier_down_fights', name: 'Fights joined a talent tier down, per player', better: 'lower', advice: 'Avoid fighting while they have a talent tier you don’t.' },
  { key: 'bush_deaths', name: 'First or only deaths beside a bush, per player', better: 'lower', advice: "Don't face-check bushes; stacks set up ambushes." },
  { key: 'unmounted_travel', name: 'Share of travel time spent unmounted', better: 'lower', share: true, advice: 'Mount up to rotate faster than they do.' },
  { key: 'deaths', name: 'Deaths per player', better: 'lower', advice: 'Die less: every death gives a stack a free objective window.' },
];

export default {
  name: 'AnalyticsStacks',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  data() {
    return {
      talking: true,
      advantageLabels: ['-3', '-2', '-1', 'Even', '+1', '+2', '+3'],
    };
  },
  computed: {
    hasData() {
      return (this.data.enemy_stack || []).some(r => r.who === 'you');
    },
    enemyRows() {
      return [1, 2, 3, 4, 5].map(size => ({
        size,
        you: (this.data.enemy_stack || []).find(r => r.who === 'you' && Number(r.enemy_party) === size),
        global: (this.data.enemy_stack || []).find(r => r.who === 'global' && Number(r.enemy_party) === size),
      }));
    },
    partyRows() {
      return [1, 2, 3, 4, 5].map(size => ({
        size,
        you: (this.data.party_size || []).find(r => r.who === 'you' && Number(r.party) === size),
        global: (this.data.party_size || []).find(r => r.who === 'global' && Number(r.party) === size),
      }));
    },
    beatTeams() {
      return (this.data.beat_stacks || []).reduce((sum, r) => sum + Number(r.teams), 0);
    },
    // Each behaviour: winners vs losers, relative difference in the helpful direction, and confidence
    // from how big the gap is and how many teams it rests on
    suggestions() {
      const won = (this.data.beat_stacks || []).find(r => Number(r.win) === 1);
      const lost = (this.data.beat_stacks || []).find(r => Number(r.win) === 0);
      if (!won || !lost) {
        return [];
      }
      const teams = Math.min(Number(won.teams), Number(lost.teams));
      return METRICS
        .map(m => {
          const w = Number(won[m.key]);
          const l = Number(lost[m.key]);
          if (!isFinite(w) || !isFinite(l) || l === 0) {
            return null;
          }
          // Positive = the winners were better at it
          const relative = m.better === 'lower' ? (l - w) / l : (w - l) / l;
          const size = Math.abs(relative);
          const confidence = teams >= 300 && size >= 0.2 ? 'High' : teams >= 100 && size >= 0.1 ? 'Medium' : 'Low';
          return { ...m, won: w, lost: l, relative, confidence };
        })
        .filter(r => r && r.relative > 0)
        .sort((a, b) => b.relative - a.relative);
    },
    mineVs() {
      const rows = this.data.mine_vs_stacks || [];
      return {
        facing: rows.find(r => r.facing_stack === true || r.facing_stack === 'true' || r.facing_stack === 1),
        other: rows.find(r => r.facing_stack === false || r.facing_stack === 'false' || r.facing_stack === 0),
      };
    },
    summary() {
      const lines = [];
      const facing = this.mineVs.facing;
      const big = this.enemyRows.filter(r => r.size >= 3);
      const sum = (rows, who, f) => rows.reduce((t, r) => t + (r[who] ? f(r[who]) : 0), 0);
      const yourGames = sum(big, 'you', r => Number(r.games));
      const yourWins = sum(big, 'you', r => Number(r.games) * Number(r.win_rate));
      const globalGames = sum(big, 'global', r => Number(r.games));
      const globalWins = sum(big, 'global', r => Number(r.games) * Number(r.win_rate));
      if (yourGames) {
        const you = yourWins / yourGames;
        const global = globalGames ? globalWins / globalGames : null;
        lines.push({
          lead: 'Against stacks of 3+ you win ' + this.percent(you) + '.',
          text: global === null ? '' : 'Players overall win ' + this.percent(global) + ' of those games' + (you >= global ? ', so you hold up well.' : ', so there is room to improve.'),
        });
      } else {
        lines.push({ lead: 'No games against stacks of 3+ yet', text: 'under these filters.' });
      }
      const top = this.suggestions.filter(s => s.confidence !== 'Low').slice(0, 2);
      if (top.length) {
        lines.push({ lead: 'Teams that beat stacks:', text: top.map(s => s.advice.replace(/\.$/, '').toLowerCase()).join('; ') + '.' });
      }
      if (facing && top.length) {
        const worst = top.find(s => this.mineVs.other && (s.better === 'lower' ? Number(facing[s.key]) > Number(this.mineVs.other[s.key]) : Number(facing[s.key]) < Number(this.mineVs.other[s.key])));
        if (worst) {
          lines.push({ lead: 'For you:', text: 'your ' + worst.name.toLowerCase() + ' gets worse against stacks, so start there.' });
        }
      }
      return lines;
    },
  },
  methods: {
    partyLabel(size, enemy) {
      if (size === 1) {
        return enemy ? 'No party (all solo)' : 'Solo';
      }
      return size === 2 ? 'Duo' : size + '-stack';
    },
    difference(row) {
      if (!row.you || !row.global) {
        return '-';
      }
      const points = (Number(row.you.win_rate) - Number(row.global.win_rate)) * 100;
      return (points >= 0 ? '+' : '') + this.round(points, 1) + ' pts';
    },
    advantage(who) {
      return [-3, -2, -1, 0, 1, 2, 3].map(diff => {
        const row = (this.data.advantage || []).find(r => r.who === who && Number(r.diff) === diff);
        return row ? this.round(Number(row.win_rate) * 100, 1) : null;
      });
    },
    format(metric, value) {
      if (value === null || value === undefined || value === '' || isNaN(Number(value))) {
        return '-';
      }
      return metric.share ? this.round(Number(value) * 100, 1) + '%' : this.round(Number(value), 2);
    },
    confidenceClass(level) {
      return level === 'High' ? 'text-teal' : level === 'Medium' ? 'text-yellow' : 'text-red';
    },
  },
};
</script>
