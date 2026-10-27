<template>
  <div class="bg-darken rounded p-3 mt-4">
    <analytics-heading :title="'How each player played'" :takeaway="allowed ? takeaway : null">
      Each player's numbers from this game, against the same hero on the same map in our global sample of games with the same result: a loss against losses, a win against wins, so a good game on a losing team still rates well. 50 is a typical game; higher is better. Each area weights its numbers by how much they go with winning on that hero. Winners usually is the middle half of that hero's wins on the map, to show how far a number is from a winning game.
    </analytics-heading>

    <!-- Patreon supporters only: everyone else gets a greyed-out preview and the way in -->
    <div v-if="!allowed" class="relative">
      <div class="opacity-30 pointer-events-none select-none" aria-hidden="true">
        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr><th>Player</th><th>Overall</th><th v-for="area in areas" :key="area.key">{{ area.label }}</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in players" :key="p.idx">
              <td>{{ p.hero }}</td>
              <td>--</td>
              <td v-for="area in areas" :key="area.key">--</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
        <p class="text-lg font-bold mb-2">Match performance is for Patreon supporters</p>
        <p class="mb-4 max-w-[40em]">See how every player in this game did in combat, survival, teamfights, objectives, positioning, ability use and more, against the same hero on the same map. Subscribe on Patreon at any tier, then link your Patreon account to your Heroes Profile account.</p>
        <div class="flex flex-wrap justify-center gap-2">
          <custom-button :href="'https://www.patreon.com/heroesprofile'" :text="'Subscribe on Patreon'" :alt="'Subscribe on Patreon'" :color="'teal'" :targetblank="true"></custom-button>
          <custom-button v-if="!patreonLinked" :href="'/authenticate/patreon'" :text="'Link Patreon'" :alt="'Link Patreon'"></custom-button>
        </div>
      </div>
    </div>

    <p v-else-if="loading" class="text-center text-gray-light">Working out how everyone played...</p>
    <p v-else-if="error" class="text-center">{{ error }}</p>

    <template v-else>
      <!-- Every player at a glance; click one for their breakdown -->
      <table class="min-w-0 w-full responsive-table mb-6">
        <thead>
          <tr><th>Player</th><th>Overall</th><th v-for="area in areas" :key="area.key">{{ area.label }}</th></tr>
        </thead>
        <tbody>
          <tr
            v-for="row in rows"
            :key="row.idx"
            class="cursor-pointer"
            :class="{ 'bg-lighten': row.idx === selected }"
            @click="pick(row.idx)"
          >
            <td class="text-white border-white border" :class="row.won ? 'bg-teal' : 'bg-red'">
              <span class="inline-flex items-center gap-2">
                <hero-image-wrapper v-if="row.player.short_name" :hero="{ name: row.player.hero, short_name: row.player.short_name }" :size="'small'"></hero-image-wrapper>
                <span>{{ row.player.battletag || row.player.hero }}<span v-if="row.player.you"> (you)</span></span>              </span>
            </td>
            <td class="min-w-[8em]"><stat-bar :value="row.overall ?? 0" :display-text="orDash(row.overall === null ? null : Math.round(row.overall))" color="teal"></stat-bar></td>
            <td v-for="area in areas" :key="area.key">{{ ratingText(row.areas[area.key]) }}</td>
          </tr>
        </tbody>
      </table>

      <div v-if="current" ref="breakdown" class="scroll-mt-4">
        <analytics-heading :title="currentName" :takeaway="playerTakeaway"></analytics-heading>
        <multi-tab-button :options="areaOptions" v-model="area" class="mb-3"></multi-tab-button>
        <p v-if="fellBack" class="text-sm mb-2">Some of these compare with {{ current.player.hero }}'s games on every map, because the sample has too few on this one.</p>
        <table class="min-w-0 w-full responsive-table mb-4">
          <thead>
            <tr>
              <th>{{ area === 'abilities' ? 'Ability or measure' : 'Measure' }}</th>
              <th>This game</th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Rating
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Where this game sits among the hero's games on this map with the same result, turned so higher is better: 50 is typical, 90 is better than 9 in 10. Blank when the number doesn't go with winning on this hero, or the sample has too few games.</p></slot>
                  </round-image>
                </span>
              </th>
              <th>Winners usually</th>
              <th>Better is</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in areaMetrics" :key="m.metric">
              <td>
                <span class="inline-flex items-center gap-2">
                  <img v-if="m.icon" :src="`/images/talents/${m.icon}`" :alt="m.name" class="w-6 h-6 rounded" />
                  {{ label(m) }}
                </span>
              </td>
              <td>{{ format(m, m.value) }}</td>
              <td class="min-w-[8em]">
                <stat-bar v-if="m.rating !== null" :value="m.rating" :display-text="Math.round(m.rating)" color="teal"></stat-bar>
                <span v-else class="text-gray-light">-</span>
              </td>
              <td>{{ m.winners ? format(m, m.winners.p25) + ' to ' + format(m, m.winners.p75) : '-' }}</td>
              <td>{{ m.better === 'higher' ? 'More' : m.better === 'lower' ? 'Less' : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-center text-gray-light">Pick a player for their breakdown.</p>
    </template>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

const AREAS = [
  { key: 'combat', label: 'Combat' },
  { key: 'survival', label: 'Survival' },
  { key: 'teamfights', label: 'Teamfights' },
  { key: 'objectives', label: 'Objectives & map' },
  { key: 'positioning', label: 'Positioning' },
  { key: 'abilities', label: 'Abilities' },
  { key: 'movement', label: 'Movement' },
  { key: 'support', label: 'Support' },
];

// Each measure's name and how its value reads: rate = per minute, rate10 = per 10 minutes (small
// counts), share = a percentage, seconds = m:ss, units = map units, number = as is
const METRICS = {
  hero_damage: ['Hero damage per minute', 'rate'],
  teamfight_hero_damage: ['Teamfight hero damage per minute', 'rate'],
  takedowns: ['Takedowns per 10 min', 'rate10'],
  kills: ['Kills per 10 min', 'rate10'],
  cc_time: ['Stun, root and silence seconds per minute', 'number'],
  deaths: ['Deaths per 10 min', 'rate10'],
  time_dead: ['Time spent dead', 'share'],
  overstay: ['Overstaying lost fights per 10 min', 'rate10'],
  picked_alone: ['Picked off alone per 10 min', 'rate10'],
  caught_first: ['Caught first per 10 min', 'rate10'],
  repeat_deaths: ['Dying to the same hero again per 10 min', 'rate10'],
  bush_deaths: ['Caught at a bush per 10 min', 'rate10'],
  escapes: ['Escapes per 10 min', 'rate10'],
  fight_presence: ['Fights you were in', 'share'],
  lost_fights_missed: ['Lost fights you missed', 'share'],
  costly_fights_missed: ['Costly lost fights you missed', 'share'],
  tier_down_fights_taken: ['Fights taken a talent tier down', 'share'],
  objective_presence: ["At your team's objectives", 'share'],
  camp_presence: ["At your team's camp captures", 'share'],
  siege_damage: ['Siege damage per minute', 'rate'],
  structure_damage: ['Structure damage per minute', 'rate'],
  xp_contribution: ['XP contribution per minute', 'rate'],
  camp_captures: ['Camp captures per 10 min', 'rate10'],
  enemy_half: ['Time in the enemy half', 'share'],
  teammate_distance: ['Distance from teammates (map units)', 'units'],
  heroic_delay: ['First heroic after picking it', 'seconds'],
  commands: ['Orders per minute', 'number'],
  unmounted_travel: ['Travel without a mount', 'share'],
  healing: ['Healing per minute', 'rate'],
  self_healing: ['Self-healing per minute', 'rate'],
  protection: ['Shielding per minute', 'rate'],
  clutch_heals: ['Clutch heals per 10 min', 'rate10'],
  damage_taken: ['Damage taken per minute', 'rate'],
};

// The replay viewer's match performance: every player's ratings, then one player's breakdown.
// `players` and `selected` are the viewer's, so picking a player here or there picks them in both.
export default {
  name: 'AnalyticsReplayPerformance',
  mixins: [analytics],
  props: {
    replayId: Number,
    players: Array,
    selected: { type: Number, default: null },
    allowed: Boolean,
    patreonLinked: Boolean,
  },
  emits: ['select'],
  data() {
    return {
      data: null,
      loading: false,
      error: '',
      area: 'combat',
      areas: AREAS,
    };
  },
  async mounted() {
    if (!this.allowed) return;
    this.loading = true;
    try {
      this.data = (await this.$axios.get('/Analytics/Match/Single/' + this.replayId + '/Performance')).data;
    } catch (e) {
      this.error = 'Performance for this game isn\'t ready yet. Please try again in a minute.';
    } finally {
      this.loading = false;
    }
  },
  computed: {
    // Highest overall first, unrated last
    rows() {
      if (!this.data) return [];
      const byIdx = Object.fromEntries(this.data.players.map(p => [p.idx, p]));
      return this.players
        .filter(p => byIdx[p.idx])
        .map(p => ({ ...byIdx[p.idx], player: p }))
        .sort((a, b) => (b.overall ?? -1) - (a.overall ?? -1) || a.player.team - b.player.team || a.idx - b.idx);
    },
    current() {
      return this.rows.find(r => r.idx === this.selected) || null;
    },
    currentName() {
      const p = this.current.player;
      return p.hero + (p.battletag ? ' - ' + p.battletag : '');
    },
    areaOptions() {
      return AREAS.filter(a => this.current?.areas[a.key]).map(a => ({ key: a.key, label: a.label }));
    },
    areaMetrics() {
      return this.current?.areas[this.area]?.metrics || [];
    },
    fellBack() {
      return this.areaMetrics.some(m => m.scope === 'all_maps');
    },
    // The game's standout performance
    takeaway() {
      const rated = this.rows.filter(r => r.overall !== null);
      if (!rated.length) return null;
      const best = rated.reduce((a, b) => (b.overall > a.overall ? b : a));
      return 'Best game: ' + (best.player.battletag || best.player.hero) + ' on ' + best.player.hero + ' (' + Math.round(best.overall) + ').';
    },
    // The selected player's strongest and weakest areas
    playerTakeaway() {
      const rated = AREAS.map(a => ({ ...a, rating: this.current.areas[a.key]?.rating ?? null })).filter(a => a.rating !== null);
      if (rated.length < 2) return null;
      rated.sort((a, b) => b.rating - a.rating);
      return 'Strongest: ' + rated[0].label + ' (' + Math.round(rated[0].rating) + '). Weakest: ' + rated[rated.length - 1].label + ' (' + Math.round(rated[rated.length - 1].rating) + ').';
    },
  },
  watch: {
    // Keep the open area when the new player has it, otherwise open their weakest
    current(row) {
      if (!row) return;
      const rated = AREAS.filter(a => row.areas[a.key]?.rating != null);
      const keep = row.areas[this.area];
      if (!keep) this.area = rated.length ? rated.reduce((a, b) => (row.areas[b.key].rating < row.areas[a.key].rating ? b : a)).key : AREAS.find(a => row.areas[a.key])?.key;
    },
  },
  methods: {
    // Only clicks here scroll; picking on the map or scoreboard stays put
    pick(idx) {
      this.$emit('select', idx);
      this.$nextTick(() => this.$refs.breakdown?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    },
    ratingText(block) {
      return block && block.rating !== null ? Math.round(block.rating) : '-';
    },
    label(m) {
      if (m.name) return m.name + ' casts per 10 min';
      return METRICS[m.metric]?.[0] ?? m.metric;
    },
    format(m, value) {
      if (value === null || value === undefined) return '-';
      const kind = m.name ? 'rate10' : (METRICS[m.metric]?.[1] ?? 'number');
      if (kind === 'rate') return Math.round(value).toLocaleString('en-US');
      if (kind === 'rate10') return this.round(value * 10, 1);
      if (kind === 'share') return this.round(value * 100, 1) + '%';
      if (kind === 'seconds') return this.clock(value);
      return this.round(value, 1);
    },
  },
};
</script>
