<template>
  <!-- Party stats for a player or for everyone: party size win rates and a summary, then every party against every enemy party.
       With skeleton, the same layout with grey blocks while it loads; the default slot (e.g. the loading message) goes on the first panel. -->
  <div class="flex flex-col gap-8">
    <panel-box :class="{ 'relative md:!rounded-tl-none': $slots.default }">
      <slot></slot>
      <div class="grid xl:grid-cols-[3fr_2fr] gap-6">
        <!-- Built from team setups, so a size is the largest party on the team, not necessarily the player's own -->
        <player-party-size-panel :data="partySizeData" :skeleton="skeleton" :title="mode === 'global' ? 'Win Rate by Largest Party on the Team' : 'Win Rate by Largest Party on Your Team'"></player-party-size-panel>

        <panel-box :inner="true" :title="$t('Summary')">
          <div class="grid sm:grid-cols-2 gap-4">
            <div v-for="tile in summaryTiles" :key="tile.label" class="bg-surface-2 rounded-control p-4 flex flex-col gap-1 min-w-0">
              <span :class="['text-sm text-subtext w-fit', { 'skeleton-text': skeleton }]">{{ $t(tile.label) }}</span>
              <skeleton-box v-if="skeleton" class="h-9 w-28"></skeleton-box>
              <span v-else :class="['text-3xl font-semibold leading-[1.2]', tile.class || 'text-header']">{{ tile.value }}</span>
              <skeleton-box v-if="skeleton" class="h-4 w-40 max-w-full mt-1"></skeleton-box>
              <span v-else class="text-sm text-subtext">{{ tile.caption }}</span>
            </div>
          </div>
          <p :class="['text-xs text-subtext w-fit', { 'skeleton-text': skeleton }]">{{ $t('Best / toughest: matchups with :count+ games.', { count: summaryMinGames }) }}</p>
        </panel-box>
      </div>
    </panel-box>

    <panel-box :title="$t('Party Matchups')" :subtitle="mode === 'global' ? 'Win rate of each party composition against every enemy composition, across all players.' : 'Your win rate for every combination of your party against the enemy party.'">
      <template #right>
        <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-sm text-subtext">
          <span v-for="item in legend" :key="item.label" class="flex items-center gap-2">
            <span :class="['w-3.5 h-3.5 rounded-[0.25rem]', item.swatch]"></span>
            <span :class="{ 'skeleton-text': skeleton }">{{ $t(item.label, { value: item.value }) }}</span>
          </span>
        </div>
      </template>

      <!-- Wide enough for every column; on small screens it scrolls sideways, with the site's fade and arrow showing there's more -->
      <!-- snap: on phones a swipe or arrow moves one column at a time -->
      <scroll-hint-container :snap="true">
        <div class="grid grid-cols-[minmax(13rem,1.4fr)_repeat(7,minmax(7.5rem,1fr))] gap-2 min-w-[70rem]">
          <!-- Which side is which -->
          <div class="snap-start flex items-center gap-2 py-2 text-sm font-bold uppercase tracking-wide">
            <span class="w-2.5 h-2.5 rounded-full bg-teal shrink-0"></span>
            <span :class="{ 'skeleton-text': skeleton }">{{ $t('Your Team') }}</span>
            <span class="flex-1 h-px bg-gray-dark"></span>
          </div>
          <div class="col-span-7 flex items-center gap-3 py-2 text-sm font-bold uppercase tracking-wide">
            <span class="flex-1 h-px bg-gray-dark"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-loss shrink-0"></span>
            <span :class="{ 'skeleton-text': skeleton }">{{ $t('Enemy Team') }}</span>
            <span class="flex-1 h-px bg-gray-dark"></span>
          </div>

          <!-- The enemy parties across the top -->
          <div></div>
          <!-- Clicking an enemy party (or one of yours, down the side) fades every other column (row); click it again to clear -->
          <component :is="skeleton ? 'div' : 'button'" v-for="column in compositions" :key="column.code" :type="skeleton ? null : 'button'" :title="skeleton ? null : $t('Focus this column')"
            :class="['snap-start flex flex-col items-center gap-2 py-1.5 rounded-control text-sm text-center transition-[colors,opacity]', skeleton ? 'text-subtext' : focusColumn === column.code ? 'bg-teal/20 ring-2 ring-teal text-white' : ['text-subtext hover:bg-surface-1 hover:text-white', { 'opacity-40': focusColumn }]]"
            @click="!skeleton && toggleFocus('column', column.code)">
            <span :class="['inline-flex items-center gap-1', { 'skeleton-text': skeleton }]">
              <span v-for="(size, index) in column.groups" :key="index" :class="['inline-flex gap-0.5 p-[0.1875rem] rounded-full', { 'bg-teal': size > 1 }, { invisible: skeleton }]">
                <span v-for="dot in size" :key="dot" :class="['w-2 h-2 rounded-full', size > 1 ? 'bg-white' : 'bg-subtext']"></span>
              </span>
            </span>
            <span :class="{ 'skeleton-text': skeleton }">{{ $t(column.label) }}</span>
          </component>

          <!-- Your party down the side, then a tile per enemy party -->
          <template v-for="row in compositions" :key="row.code">
            <component :is="skeleton ? 'div' : 'button'" :type="skeleton ? null : 'button'" :title="skeleton ? null : $t('Focus this row')"
              :class="['bg-surface-1 rounded-control px-4 py-3 flex items-center gap-4 min-w-0 text-left transition-[box-shadow,opacity]', { 'hover:bg-surface-3': !skeleton }, focusRow === row.code ? 'ring-2 ring-teal' : { 'opacity-40': focusRow }]"
              @click="!skeleton && toggleFocus('row', row.code)">
              <span :class="['inline-flex items-center gap-1 shrink-0', { 'skeleton-text': skeleton }]">
                <span v-for="(size, index) in row.groups" :key="index" :class="['inline-flex gap-0.5 p-[0.1875rem] rounded-full', { 'bg-teal': size > 1 }, { invisible: skeleton }]">
                  <span v-for="dot in size" :key="dot" :class="['w-2 h-2 rounded-full', size > 1 ? 'bg-white' : 'bg-subtext']"></span>
                </span>
              </span>
              <span :class="['font-medium truncate', { 'skeleton-text': skeleton }]">{{ $t(row.label) }}</span>
            </component>
            <div v-for="column in compositions" :key="column.code" :class="['rounded-control px-2 py-3 flex flex-col items-center justify-center text-center transition-[opacity,transform]', skeleton ? 'bg-surface-3 animate-pulse' : cellClass(cell(row, column)), { 'opacity-25': isFaded(row, column), 'ring-2 ring-teal scale-105 relative z-[1]': isCombo(row, column) }]">
              <template v-if="skeleton">
                <span class="invisible text-lg font-semibold leading-tight">00.00%</span>
                <span class="invisible text-xs">000 W – 000 L</span>
              </template>
              <template v-else-if="cell(row, column)">
                <span class="text-lg font-semibold leading-tight">{{ cell(row, column).win_rate.toFixed(2) }}%</span>
                <span class="text-xs text-subtext">{{ cell(row, column).wins.toLocaleString('en-US') }} W – {{ cell(row, column).losses.toLocaleString('en-US') }} L</span>
              </template>
              <template v-else>
                <span class="text-lg font-semibold leading-tight text-subtext">–</span>
                <span class="text-xs text-subtext invisible">0 W – 0 L</span>
              </template>
            </div>
          </template>
        </div>
      </scroll-hint-container>
    </panel-box>
  </div>
</template>

<script>
// Every party a team of five can be, in the order the grid shows them. code is the API's combo: players in a five-stack,
// quad, triple, double and solo, one digit each. key is where partydata keeps that party's matchups.
const COMPOSITIONS = [
  { key: 'solo', code: '00005', label: '5 Solo', groups: [1, 1, 1, 1, 1] },
  { key: 'double', code: '00023', label: '1 Duo, 3 Solo', groups: [2, 1, 1, 1] },
  { key: 'double_double', code: '00041', label: '2 Duo, 1 Solo', groups: [2, 2, 1] },
  { key: 'triple', code: '00302', label: '1 Trio, 2 Solo', groups: [3, 1, 1] },
  { key: 'triple_double', code: '00320', label: '1 Trio, 1 Duo', groups: [3, 2] },
  { key: 'quadruple', code: '04001', label: '1 Quad, 1 Solo', groups: [4, 1] },
  { key: 'quintuple', code: '50000', label: '5 Stack', groups: [5] },
];

// The party size bars: the size of the party you're in, whatever the rest of your team is
const PARTY_SIZES = [
  { field: 'one', label: 'Solo', keys: ['solo'] },
  { field: 'two', label: '2 - Stack', keys: ['double', 'double_double'] },
  { field: 'three', label: '3 - Stack', keys: ['triple', 'triple_double'] },
  { field: 'four', label: '4 - Stack', keys: ['quadruple'] },
  { field: 'five', label: '5 - Stack', keys: ['quintuple'] },
];

export default {
  name: 'PartyStatsDisplay',
  props: {
    partydata: {
      type: Object,
      default: () => ({}),
    },
    // 'player': one player's own parties. 'global': everyone's, with tighter colour bands and no faded tiles.
    mode: {
      type: String,
      default: 'global',
    },
    skeleton: Boolean,
  },
  data() {
    return {
      compositions: COMPOSITIONS,
      summaryMinGames: 30,
      // The party picked down the side and across the top; each fades the other rows / columns
      focusRow: null,
      focusColumn: null,
    };
  },
  computed: {
    // Win rate bands for the tiles, and the game count under which a player's tile fades
    bands() {
      return this.mode === 'global' ? { high: 52, low: 48, fade: 0 } : { high: 55, low: 45, fade: 15 };
    },
    legend() {
      const items = [
        { label: ':value% and higher', value: this.bands.high, swatch: 'bg-teal/25 border border-teal/50' },
        { label: ':value% and lower', value: this.bands.low, swatch: 'bg-loss/25 border border-loss/50' },
      ];
      if (this.bands.fade) {
        items.push({ label: 'Faded — fewer than :value games', value: this.bands.fade, swatch: 'border border-subtext/60' });
      }
      return items;
    },
    // Each party's row of matchups, keyed by the enemy party's code
    matchups() {
      const byRow = {};
      COMPOSITIONS.forEach(row => {
        const rows = this.partydata?.[row.key];
        byRow[row.code] = {};
        (Array.isArray(rows) ? rows : Object.values(rows || {})).forEach(item => {
          byRow[row.code][item.enemy_combo] = item;
        });
      });
      return byRow;
    },
    // The party size card's fields, named as the profile's are (stack_one_wins and so on)
    partySizeData() {
      const data = {};
      PARTY_SIZES.forEach(size => {
        let wins = 0;
        let losses = 0;
        size.keys.forEach(key => {
          const row = COMPOSITIONS.find(composition => composition.key === key);
          Object.values(this.matchups[row.code] || {}).forEach(item => {
            wins += item.wins;
            losses += item.losses;
          });
        });
        data[`stack_${size.field}_wins`] = wins;
        data[`stack_${size.field}_losses`] = losses;
        data[`stack_${size.field}_win_rate`] = wins + losses ? (wins / (wins + losses)) * 100 : 0;
      });
      return data;
    },
    totals() {
      let wins = 0;
      let losses = 0;
      PARTY_SIZES.forEach(size => {
        wins += this.partySizeData[`stack_${size.field}_wins`];
        losses += this.partySizeData[`stack_${size.field}_losses`];
      });
      return { wins, losses, games: wins + losses };
    },
    // The matchups with enough games, best and worst first
    rankedMatchups() {
      const list = [];
      COMPOSITIONS.forEach(row => {
        COMPOSITIONS.forEach(column => {
          const item = this.matchups[row.code]?.[column.code];
          if (item && item.wins + item.losses >= this.summaryMinGames) {
            list.push({ row, column, item, games: item.wins + item.losses });
          }
        });
      });
      return list.sort((a, b) => b.item.win_rate - a.item.win_rate);
    },
    summaryTiles() {
      const number = value => value.toLocaleString('en-US');
      const matchupTile = (label, entry, colour) => entry
        ? { label, value: entry.item.win_rate.toFixed(2) + '%', class: colour, caption: `${this.$t(entry.row.label)} vs ${this.$t(entry.column.label)} · ${this.$t(':count games', { count: number(entry.games) })}` }
        : { label, value: '–', caption: this.$t('No matchup with :count+ games', { count: this.summaryMinGames }) };

      const tiles = [{
        label: 'Total games',
        value: number(this.totals.games),
        caption: this.$t(this.mode === 'global' ? 'All party sizes, all players' : 'All party sizes'),
      }];

      if (this.mode === 'global') {
        const sizes = PARTY_SIZES.map(size => ({ label: size.label, games: this.partySizeData[`stack_${size.field}_wins`] + this.partySizeData[`stack_${size.field}_losses`] }));
        const top = sizes.reduce((best, size) => (size.games > best.games ? size : best), sizes[0]);
        tiles.push({
          label: 'Most common party',
          value: this.$t(top.label),
          caption: `${this.$t(':count games', { count: number(top.games) })} · ${this.$t(':percent% of all', { percent: this.totals.games ? ((top.games / this.totals.games) * 100).toFixed(1) : '0.0' })}`,
        });
      } else {
        tiles.push({
          label: 'Overall win rate',
          value: (this.totals.games ? (this.totals.wins / this.totals.games) * 100 : 0).toFixed(2) + '%',
          caption: `${number(this.totals.wins)} W – ${number(this.totals.losses)} L`,
        });
      }

      const ranked = this.rankedMatchups;
      tiles.push(matchupTile('Best matchup', ranked[0], 'text-win'));
      tiles.push(matchupTile('Toughest matchup', ranked.length > 1 ? ranked[ranked.length - 1] : null, 'text-loss'));
      return tiles;
    },
  },
  methods: {
    toggleFocus(axis, code) {
      const key = axis === 'row' ? 'focusRow' : 'focusColumn';
      this[key] = this[key] === code ? null : code;
    },
    // A row and a column both picked: their crossing tile is ringed
    isCombo(row, column) {
      return this.focusRow === row.code && this.focusColumn === column.code;
    },
    isFaded(row, column) {
      return (this.focusRow && this.focusRow !== row.code) || (this.focusColumn && this.focusColumn !== column.code);
    },
    cell(row, column) {
      return this.matchups[row.code]?.[column.code] || null;
    },
    // Teal at the high band and up, red at the low band and down, faded under the player's game count
    cellClass(item) {
      if (!item) return 'bg-surface-1 opacity-50';
      const classes = [];
      if (item.win_rate >= this.bands.high) classes.push('bg-teal/25 text-win');
      else if (item.win_rate <= this.bands.low) classes.push('bg-loss/25 text-loss');
      else classes.push('bg-surface-1 text-header');
      if (this.bands.fade && item.wins + item.losses < this.bands.fade) classes.push('opacity-50');
      return classes.join(' ');
    },
  },
};
</script>
