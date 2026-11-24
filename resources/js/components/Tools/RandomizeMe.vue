<template>
  <div>
    <page-heading :heading="'Randomize Me'" :subheading="'Can\'t decide? Let the Nexus pick'"></page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 grid xl:grid-cols-[380fr_1056fr] gap-6 xl:min-h-[40rem]">
      <panel-box title="What to randomize" subtitle="Pick what the randomizer chooses for you." :divider="false">
        <div class="flex flex-col gap-2">
          <check-row v-for="option in optionList" :key="option.key" v-model="options[option.key]" :text="option.text"></check-row>
        </div>

        <div class="border-t border-surface-3 pt-4 flex flex-col gap-3">
          <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Hero pool') }}</span>
          <div class="flex flex-wrap gap-2">
            <toggle-chip :model-value="!roles.length" text="All roles" @update:model-value="roles = []"></toggle-chip>
            <toggle-chip v-for="role in ROLES" :key="role" :model-value="roles.includes(role)" :text="role" @update:model-value="toggleRole(role)"></toggle-chip>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Game modes') }}</span>
          <div class="flex flex-wrap gap-2">
            <toggle-chip v-for="mode in MODES" :key="mode.code" :model-value="modes.includes(mode.code)" :text="mode.name" @update:model-value="toggleMode(mode.code)"></toggle-chip>
          </div>
        </div>
      </panel-box>

      <panel-box :divider="false" :class="['xl:h-full relative', { 'md:!rounded-tl-none': loading }]">
        <loading-component v-if="loading" :compact="true" :tab="true"></loading-component>
        <!-- Before the first roll -->
        <div v-if="!rolled" class="flex-1 flex flex-col items-center justify-center gap-7 text-center py-16">
          <span class="w-[11.25rem] h-[11.25rem] rounded-full border-2 border-dashed border-teal/50 bg-teal/[0.12] flex items-center justify-center">
            <i class="fa-solid fa-shuffle text-[4.5rem] text-teal"></i>
          </span>
          <div class="flex flex-col items-center gap-2">
            <h2 class="text-3xl md:text-4xl font-bold">{{ $t('Ready when you are') }}</h2>
            <p class="text-lg text-subtext">{{ $t('Press Randomize to get a hero, a talent build, a map and a game mode.') }}</p>
          </div>
          <custom-button color="teal" class="font-semibold w-full max-w-[20rem] h-14 py-0 text-xl" :ignoreclick="true" :alt="'Randomize'" @click="roll(['hero', 'map', 'region', 'mode', 'build'])">{{ $t('Randomize!') }}</custom-button>
          <p class="text-sm text-subtext">{{ $t('Tip: choose what to randomize on the left first.') }}</p>
        </div>

        <div v-else :class="['flex-1 grid gap-6', showHero ? 'md:grid-cols-[minmax(0,20rem)_minmax(0,1fr)]' : '']">
          <div v-if="showHero" class="rounded border-2 border-teal overflow-hidden bg-surface-1 max-md:h-[20rem]">
            <img :key="pick.hero.id" class="w-full h-full object-cover" :src="`/images/heroes_rectangle_large/${pick.hero.short_name}.jpg`" :alt="$heroName(pick.hero)">
          </div>

          <div class="flex flex-col gap-4 min-w-0">
            <div v-if="showHero" class="flex flex-col gap-1">
              <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Your random hero') }}</span>
              <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-4xl md:text-5xl font-bold">{{ $heroName(pick.hero) }}</h2>
                <span v-if="pick.hero.new_role" class="px-1.5 py-0.5 rounded-[0.25rem] bg-teal/20 text-lteal text-xs">{{ $t(pick.hero.new_role) }}</span>
              </div>
            </div>

            <div v-if="tiles.length" class="flex flex-wrap gap-3">
              <stat-box v-for="tile in tiles" :key="tile.title" class="!m-0 !min-w-[9rem] flex-1" :title="tile.title" :value="tile.value"></stat-box>
            </div>

            <!-- Talent build -->
            <div v-if="showHero && options.build" class="rounded-control bg-surface-1 p-4 flex flex-col gap-3">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-sm font-semibold">{{ $t('Talent build') }}</span>
                <build-code v-if="pick.build" :build="pick.build" :hero="pick.hero" show="code" align="end"></build-code>
              </div>
              <div class="min-h-[3.75rem]">
              <div v-if="buildLoading" class="flex gap-1.5">
                <skeleton-box v-for="talent in 7" :key="talent" class="w-[2.625rem] h-[2.625rem]"></skeleton-box>
              </div>
              <p v-else-if="buildError" class="text-sm text-red">{{ $t(buildError) }}</p>
              <build-code v-else-if="pick.build" :build="pick.build" :hero="pick.hero" show="talents" align="start" :levels="true" :popup-hoverable="false"></build-code>
              <p v-else class="text-sm text-subtext">{{ $t('No complete build found for this hero.') }}</p>
              </div>
            </div>

            <!-- Hero stats on the map and mode, this patch -->
            <div v-if="showHero" class="rounded-control bg-surface-1 p-4 flex flex-col gap-3">
              <div class="flex flex-wrap items-baseline justify-between gap-2">
                <span class="text-sm font-semibold">{{ statsHeading }}</span>
                <span class="text-xs text-subtext">{{ statsModeName }} · {{ $t('this patch') }}</span>
              </div>
              <div class="flex flex-wrap gap-3">
                <stat-box v-for="tile in statTiles" :key="tile.title" class="!m-0 !min-w-[8rem] flex-1 !bg-surface-3" :title="tile.title" :value="statsLoading ? '…' : tile.value"></stat-box>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <custom-button color="teal" class="font-semibold" :ignoreclick="true" :alt="'Randomize again'" @click="roll(['hero', 'map', 'region', 'mode', 'build'])">{{ $t('Randomize again') }}</custom-button>
              <custom-button v-if="showHero" :href="`/Global/Talents/${pick.hero.name}`" :targetblank="true" class="font-semibold" :alt="'Open builds'">{{ $t('Open :hero builds', { hero: $heroName(pick.hero) }) }}</custom-button>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
              <span class="text-subtext">{{ $t('Reroll only:') }}</span>
              <button v-for="reroll in rerolls" :key="reroll.key" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-control bg-surface-1 hover:bg-surface-3 transition-colors" @click="roll(reroll.parts)">
                <i class="fa-solid fa-shuffle text-[0.625rem] text-subtext"></i>{{ $t(reroll.text) }}
              </button>
            </div>
          </div>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
const ROLES = ['Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank'];
const MODES = [
  { code: 'sl', name: 'Storm League' },
  { code: 'qm', name: 'Quick Match' },
  { code: 'ar', name: 'ARAM' },
];
const REGIONS = ['NA', 'EU', 'KR', 'CN'];

function randomItem(items) {
  return items[Math.floor(Math.random() * items.length)];
}

export default {
  name: 'RandomizeMe',
  props: {
    heroes: { type: Array, required: true },
    maps: { type: Array, default: () => [] },
    defaulttimeframe: { type: String, default: null },
  },
  data() {
    return {
      ROLES,
      MODES,
      optionList: [
        { key: 'hero', text: 'Hero' },
        { key: 'map', text: 'Map' },
        { key: 'region', text: 'Region' },
        { key: 'mode', text: 'Game mode' },
        { key: 'build', text: 'Talent build' },
      ],
      options: { hero: true, map: true, region: true, mode: true, build: true },
      roles: [],
      modes: ['sl', 'qm'],
      rolled: false,
      pick: { hero: null, map: null, region: null, mode: null, build: null },
      buildLoading: false,
      buildError: null,
      buildRequest: 0,
      stats: null,
      statsLoading: false,
      statsRequest: 0,
    };
  },
  computed: {
    loading() {
      return this.buildLoading || this.statsLoading;
    },
    showHero() {
      return this.options.hero && this.pick.hero;
    },
    tiles() {
      const tiles = [];
      if (this.options.mode && this.pick.mode) tiles.push({ title: 'Mode', value: this.$t(this.modeName(this.pick.mode)) });
      if (this.options.region && this.pick.region) tiles.push({ title: 'Region', value: this.pick.region });
      if (this.options.map && this.pick.map) tiles.push({ title: 'Map', value: this.$mapName(this.pick.map.name) });
      return tiles;
    },
    // Stats are for the rolled mode, or Storm League when mode isn't rolled
    statsMode() {
      return this.options.mode && this.pick.mode ? this.pick.mode : 'sl';
    },
    statsMap() {
      return this.options.map && this.pick.map ? this.pick.map.name : null;
    },
    statsModeName() {
      return this.$t(this.modeName(this.statsMode));
    },
    statsHeading() {
      if (!this.pick.hero) return '';
      return this.statsMap
        ? this.$t(':hero on :map', { hero: this.$heroName(this.pick.hero), map: this.$mapName(this.statsMap) })
        : this.$heroName(this.pick.hero);
    },
    statsKey() {
      return this.showHero ? `${this.pick.hero.id}|${this.statsMode}|${this.statsMap}` : null;
    },
    statTiles() {
      const row = this.stats;
      const percent = value => (row && value != null ? Number(value).toFixed(1) + '%' : '—');
      const tiles = [
        { title: 'Win rate', value: percent(row?.win_rate) },
        { title: 'Popularity', value: percent(row?.popularity) },
      ];
      // Ban rate only exists for Storm League
      if (this.statsMode === 'sl') tiles.push({ title: 'Ban rate', value: percent(row?.ban_rate) });
      tiles.push({ title: 'Games', value: row ? Number(row.games_played).toLocaleString('en-US') : '—' });
      return tiles;
    },
    rerolls() {
      const rerolls = [];
      if (this.options.hero) rerolls.push({ key: 'hero', text: 'Hero', parts: ['hero', 'build'] });
      if (this.options.hero && this.options.build) rerolls.push({ key: 'build', text: 'Build', parts: ['build'] });
      if (this.options.map) rerolls.push({ key: 'map', text: 'Map', parts: ['map'] });
      if (this.options.mode) rerolls.push({ key: 'mode', text: 'Mode', parts: ['mode'] });
      return rerolls;
    },
  },
  watch: {
    // Reloads when the hero, map or mode changes, or Map/Game mode is ticked on or off
    statsKey(key) {
      if (key) this.loadStats();
    },
  },
  methods: {
    modeName(code) {
      return MODES.find(mode => mode.code === code)?.name || code;
    },
    toggleRole(role) {
      this.roles = this.roles.includes(role) ? this.roles.filter(item => item !== role) : [...this.roles, role];
    },
    toggleMode(code) {
      // At least one mode stays on
      if (this.modes.includes(code) && this.modes.length === 1) return;
      this.modes = this.modes.includes(code) ? this.modes.filter(item => item !== code) : [...this.modes, code];
    },
    // ARAM rolls an ARAM map, Storm League one in the ranked rotation, other modes any standard map
    mapPool(mode) {
      const standard = this.maps.filter(map => map.type !== 'ARAM');
      const pool = mode === 'ar'
        ? this.maps.filter(map => map.type === 'ARAM')
        : mode === 'sl' ? standard.filter(map => map.ranked_rotation) : standard;
      return pool.length ? pool : standard;
    },
    roll(parts) {
      const rolls = new Set(parts.filter(part => this.options[part]));
      const pick = { ...this.pick };
      const before = { hero: pick.hero };

      if (rolls.has('mode')) {
        pick.mode = randomItem(this.modes);
      }
      const mode = this.options.mode ? pick.mode : null;
      // A new mode can rule out the map, e.g. an ARAM map after leaving ARAM
      if (this.options.map && (rolls.has('map') || !pick.map || !this.mapPool(mode).includes(pick.map))) {
        pick.map = randomItem(this.mapPool(mode));
      }
      if (rolls.has('region')) {
        pick.region = randomItem(REGIONS);
      }
      if (rolls.has('hero')) {
        const pool = this.roles.length ? this.heroes.filter(hero => this.roles.includes(hero.new_role)) : this.heroes;
        pick.hero = randomItem(pool.length ? pool : this.heroes);
      }

      this.pick = pick;
      this.rolled = true;

      if (this.options.hero && this.options.build && (rolls.has('build') || pick.hero !== before.hero)) {
        this.loadBuild();
      }
    },
    async loadBuild() {
      const request = ++this.buildRequest;
      this.buildLoading = true;
      this.buildError = null;
      this.pick.build = null;
      try {
        const response = await this.$axios.post('/api/v1/tools/randomize-me', {
          hero: this.pick.hero.name,
          game_type: this.options.mode ? this.pick.mode : null,
        });
        if (request === this.buildRequest) this.pick.build = response.data.build;
      } catch (error) {
        if (request === this.buildRequest) this.buildError = 'Failed to load a build. Please try again.';
      } finally {
        if (request === this.buildRequest) this.buildLoading = false;
      }
    },
    async loadStats() {
      const request = ++this.statsRequest;
      this.statsLoading = true;
      this.stats = null;
      const heroId = this.pick.hero.id;
      try {
        // The parameters the Drafter sends, so both share the cache entry
        const response = await this.$globalAsyncPost('/api/v1/global/hero', {
          timeframe_type: 'minor',
          timeframe: [this.defaulttimeframe],
          region: null,
          hero_level: null,
          game_type: [this.statsMode],
          game_map: this.statsMap ? [this.statsMap] : null,
          league_tier: null,
          hero_league_tier: null,
          role_league_tier: null,
          statfilter: 'win_rate',
          role: null,
          hero: null,
          mirror: 0,
          groupsize: [],
        });
        if (request === this.statsRequest) {
          this.stats = (response.data.data || []).find(row => row.hero_id === heroId) || null;
        }
      } catch (error) {
        // The tiles show — when the stats can't load
      } finally {
        if (request === this.statsRequest) this.statsLoading = false;
      }
    },
  },
};
</script>
