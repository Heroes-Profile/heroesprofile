<template>
  <!-- Every build path through the talent tiers: width is games played, colour is win rate -->
  <details :open="open" @toggle="$emit('update:open', $event.target.open)" class="bg-lighten rounded-lg w-0 min-w-full my-5 group">
    <summary class="px-6 py-4 flex justify-between items-center cursor-pointer list-none">
      <h3 class="text-lg">{{ $t('Alluvial Diagram') }}</h3>
      <i class="fas fa-chevron-down transition-transform group-open:rotate-180"></i>
    </summary>
    <div class="px-3 pb-3">
    <div class="bg-gray-dark text-white rounded-2xl p-3">
      <div class="flex flex-wrap justify-between gap-2 text-sm mb-2">
        <span>{{ paths.length.toLocaleString('en-US') }} build combinations · line width is games played</span>
        <span class="flex items-center gap-2">
          <span>-{{ colourRange.toFixed(1) }}%</span>
          <span class="w-24 h-3 rounded" :style="{ background: `linear-gradient(to right, ${colours.low}, ${colours.mid}, ${colours.high})` }"></span>
          <span>+{{ colourRange.toFixed(1) }}%</span>
          <span>vs builds that reach the same level</span>
        </span>
      </div>
      <div class="relative w-full" :style="{ aspectRatio: `${width} / ${height}` }">
        <svg class="absolute inset-0 w-full h-full" :viewBox="`0 0 ${width} ${height}`">
          <path v-for="line in lines" :key="line.key" :d="line.d" fill="none" stroke-linecap="round" stroke-opacity="0.7" :stroke="line.colour" :stroke-width="line.width">
            <title>{{ line.win_rate.toFixed(2) }}% win rate · {{ line.games_played.toLocaleString('en-US') }} games · {{ line.versus >= 0 ? '+' : '' }}{{ line.versus.toFixed(2) }}% vs average</title>
          </path>
        </svg>
        <template v-for="(level, column) in levels" :key="level">
          <div class="absolute -translate-x-1/2 text-xs uppercase" :style="{ left: percent(x(column), width), top: 0 }">Level {{ level }}</div>
          <div v-for="(talent, row) in data[level]" :key="talent.talent_id" class="absolute -translate-x-1/2 -translate-y-1/2" :style="{ left: percent(x(column), width), top: percent(y(row), height) }">
            <talent-image-wrapper :talent="talent" size="small"></talent-image-wrapper>
          </div>
        </template>
      </div>
    </div>
    </div>
  </details>
</template>

<script>
export default {
  name: 'TalentBuildPaths',
  props: {
    data: Object,
    paths: Array,
    // Kept by the page, since the section remounts on every builder reload
    open: Boolean,
  },
  emits: ['update:open'],
  data() {
    return {
      levels: [1, 4, 7, 10, 13, 16, 20],
      columnWidth: 160,
      rowHeight: 80,
      headerHeight: 30,
      maxStroke: 20,
      // Each path's win rate is pulled toward its level's average as if it had this many extra games
      pullGames: 25,
      // Share of games whose paths fall inside the full red-to-teal range
      colourCoverage: 0.9,
      colours: { low: '#b33616', mid: '#777777', high: '#008b8b' },
    };
  },
  computed: {
    width() {
      return this.levels.length * this.columnWidth;
    },
    height() {
      const rows = Math.max(...this.levels.map(level => (this.data[level] || []).length));
      return this.headerHeight + rows * this.rowHeight;
    },
    // Where each talent's icon sits, by talent id
    points() {
      const points = {};
      this.levels.forEach((level, column) => (this.data[level] || []).forEach((talent, row) => {
        points[talent.talent_id] = { x: this.x(column), y: this.y(row) };
      }));
      return points;
    },
    // Games that reach later levels win more, so each path is compared with the paths that ended at the same level
    averages() {
      const totals = {};
      this.paths.forEach(path => {
        const end = path.talents.length;
        totals[end] = totals[end] || { games: 0, wins: 0 };
        totals[end].games += path.games_played;
        totals[end].wins += path.games_played * path.win_rate / 100;
      });
      const averages = {};
      Object.keys(totals).forEach(end => {
        averages[end] = totals[end].games ? totals[end].wins / totals[end].games * 100 : 50;
      });
      return averages;
    },
    // Each path's pulled win rate minus its level's average
    versus() {
      return this.paths.map(path => {
        const average = this.averages[path.talents.length];
        const wins = path.games_played * path.win_rate / 100;
        return (wins + this.pullGames * average / 100) / (path.games_played + this.pullGames) * 100 - average;
      });
    },
    // How far from average the full red or teal starts, so most games' paths spread across the scale
    colourRange() {
      const spread = this.paths
        .map((path, i) => ({ distance: Math.abs(this.versus[i]), games: path.games_played }))
        .sort((a, b) => a.distance - b.distance);
      const total = spread.reduce((sum, entry) => sum + entry.games, 0);
      let counted = 0;
      for (const entry of spread) {
        counted += entry.games;
        if (counted >= total * this.colourCoverage) {
          return Math.max(entry.distance, 0.5);
        }
      }
      return 0.5;
    },
    lines() {
      const maxGames = Math.max(...this.paths.map(path => path.games_played), 1);
      return this.paths
        .map((path, i) => {
          const stops = [];
          for (const id of path.talents) {
            if (!this.points[id]) {
              break;
            }
            stops.push(this.points[id]);
          }
          return {
            key: path.talents.join('-'),
            d: this.curve(stops),
            width: Math.max(Math.sqrt(path.games_played / maxGames) * this.maxStroke, 0.5),
            colour: this.colour(this.versus[i]),
            games_played: path.games_played,
            win_rate: path.win_rate,
            versus: this.versus[i],
          };
        })
        .filter(line => line.d)
        // Most played drawn last, so they sit on top
        .sort((a, b) => a.games_played - b.games_played);
    },
  },
  methods: {
    x(column) {
      return (column + 0.5) * this.columnWidth;
    },
    y(row) {
      return this.headerHeight + (row + 0.5) * this.rowHeight;
    },
    percent(value, total) {
      return `${value / total * 100}%`;
    },
    // Smooth S-curves between each tier's talent
    curve(stops) {
      if (stops.length < 2) {
        return null;
      }
      let d = `M ${stops[0].x} ${stops[0].y}`;
      for (let i = 1; i < stops.length; i++) {
        const mid = (stops[i - 1].x + stops[i].x) / 2;
        d += ` C ${mid} ${stops[i - 1].y}, ${mid} ${stops[i].y}, ${stops[i].x} ${stops[i].y}`;
      }
      return d;
    },
    colour(versus) {
      const share = Math.min(Math.max(versus / this.colourRange, -1), 1);
      return share < 0
        ? this.mix(this.colours.mid, this.colours.low, -share)
        : this.mix(this.colours.mid, this.colours.high, share);
    },
    mix(from, to, amount) {
      const channels = hex => [1, 3, 5].map(start => parseInt(hex.slice(start, start + 2), 16));
      const a = channels(from);
      const b = channels(to);
      return `rgb(${a.map((value, i) => Math.round(value + (b[i] - value) * amount)).join(',')})`;
    },
  },
};
</script>
