<template>
  <!-- Collapsed until opened; the notes load the first time it opens -->
  <!-- With bodyTarget the notes open in their own card there, below a row of bars (Global Hero Stats) -->
  <panel-box :class="['w-full max-w-[93.75rem] mx-auto mb-4', { 'md:!py-4 cursor-pointer': !open || bodyTarget }]" @click="!open && toggle()">
    <!-- The bar, as in the redesign: title and patch chip, the patch date under them, the legend, then Show/Hide -->
    <!-- Open, a click on the bar's empty space closes it; closed, the whole card opens it -->
    <div class="flex flex-wrap items-center gap-4" @click.stop="barClicked">
      <div class="flex-1 min-w-0 flex flex-col gap-1">
        <div class="flex flex-wrap items-center gap-3">
          <h3 class="text-2xl font-bold">{{ $t('Patch Notes') }}</h3>
          <span class="px-2.5 py-[0.1875rem] rounded-[0.375rem] bg-teal/[0.18] text-teal text-[0.8125rem] font-bold whitespace-nowrap">{{ version }}<template v-if="notes && notes.version === version && notes.title"> · {{ notes.title }}</template></span>
        </div>
        <span v-if="open && notes && notes.version === version && notes.date" class="text-subtext">{{ notes.date }}</span>
      </div>
      <div class="max-md:hidden flex items-center gap-4 whitespace-nowrap">
        <span v-for="(badge, type) in usedBadges" :key="type" class="flex items-center gap-1">
          <span :class="['text-xs font-bold', badge.color]">{{ badge.glyph }}</span><span class="text-sm text-subtext">{{ $t(badge.label) }}</span>
        </span>
      </div>
      <button type="button" class="flex items-center gap-1.5 md:pl-4 text-[0.9375rem] font-medium text-subtext hover:text-white transition-colors" :aria-expanded="open" @click.stop="toggle">
        {{ open ? $t('Hide') : $t('Show') }}
        <basil-icon name="arrow-small" :class="['text-xs transition-transform', open ? '-rotate-90' : 'rotate-90']"></basil-icon>
      </button>
    </div>

    <Teleport defer :to="bodyTarget" :disabled="!bodyTarget">
    <component :is="bodyTarget ? 'panel-box' : 'div'" v-if="open" class="text-gray-light">
    <div v-if="loading" class="text-center py-4">{{ $t('Loading patch notes...') }}</div>

    <!-- As the Figma card: a row each for General, maps and heroes, chips to open a hero's notes, then Bug fixes and
         the link to Blizzard's notes at the end of the heroes row -->
    <div v-else-if="notes" class="flex flex-col gap-4">
      <div v-if="notes.general && notes.general.length" :class="rowClass">
        <h4 :class="labelClass">{{ $t('General') }}</h4>
        <p class="flex-1 min-w-0 text-sm leading-relaxed text-subtext">
          <template v-for="(entry, index) in notes.general" :key="index"><span v-if="index" class="mx-2 text-subtext">·</span>{{ entry.note || entry }}</template>
        </p>
      </div>

      <div v-if="notes.maps && notes.maps.length" :class="rowClass">
        <h4 :class="labelClass">{{ $t('Maps changed') }}</h4>
        <div class="flex-1 min-w-0 flex flex-wrap gap-2">
          <button v-for="map in notes.maps" :key="map.map" type="button" :class="chipClass(selectedMap === map.map)" @click="toggleMap(map.map)">
            <img v-if="map.image" :src="`/images/maps/icon/${map.image}`" alt="" class="w-9 h-9 rounded-full object-cover" @error="hideImage" />
            <span>{{ $mapName(map.map) }}</span><span :class="['text-xs', badgeFor(map.type).color]">{{ badgeFor(map.type).glyph }}</span>
          </button>
        </div>
      </div>
      <div v-if="selectedMapData" :class="detailClass">
        <div class="flex items-center gap-2 mb-2">
          <span class="font-semibold">{{ $mapName(selectedMapData.map) }}</span>
          <span :class="badgeFor(selectedMapData.type).color" class="text-sm">{{ badgeFor(selectedMapData.type).glyph }} {{ $t(badgeFor(selectedMapData.type).label) }}</span>
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
          <li v-for="(note, index) in selectedMapData.notes" :key="index">{{ note }}</li>
        </ul>
      </div>

      <div v-if="(notes.heroes && notes.heroes.length) || bugFixCount || notes.blizzard_url" :class="rowClass">
        <template v-if="notes.heroes && notes.heroes.length">
          <h4 :class="labelClass">{{ $t('Heroes changed') }}</h4>
          <div class="flex-1 min-w-0 flex flex-wrap gap-2">
            <button v-for="hero in notes.heroes" :key="hero.name" type="button" :class="chipClass(selectedHero === hero.name)" @click="toggleHero(hero.name)">
              <img :src="`/images/heroes/${hero.short_name}.png`" alt="" class="w-9 h-9 rounded-full bg-surface-1 text-[0rem]" @error="useFallbackImage" />
              <span>{{ $heroName(hero) }}</span><span :class="['text-xs', badgeFor(hero.type).color]">{{ badgeFor(hero.type).glyph }}</span>
            </button>
          </div>
        </template>
        <div class="flex items-center gap-8 ml-auto whitespace-nowrap text-sm">
          <button v-if="bugFixCount" type="button" class="flex items-center gap-1.5 font-medium text-subtext hover:text-white transition-colors" :aria-expanded="showBugFixes" @click="showBugFixes = !showBugFixes">
            {{ $t('Bug fixes') }}
            <basil-icon name="arrow-small" :class="['text-xs transition-transform', showBugFixes ? '-rotate-90' : 'rotate-90']"></basil-icon>
          </button>
          <a v-if="notes.blizzard_url" :href="notes.blizzard_url" target="_blank" rel="noopener noreferrer" class="group/bt font-medium text-teal"><span class="group-hover/bt:underline">{{ $t('Blizzard patch notes') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
        </div>
      </div>
      <div v-if="selectedHeroData" :class="detailClass">
        <div class="flex items-center gap-2 mb-2">
          <img :src="`/images/heroes/${selectedHeroData.short_name}.png`" :alt="selectedHeroData.name" class="w-8 h-8 rounded-full bg-surface-3 text-[0rem]" @error="useFallbackImage" />
          <span class="font-semibold">{{ $heroName(selectedHeroData) }}</span>
          <span :class="badgeFor(selectedHeroData.type).color" class="text-sm">{{ badgeFor(selectedHeroData.type).glyph }} {{ $t(badgeFor(selectedHeroData.type).label) }}</span>
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
          <li v-for="(note, index) in selectedHeroData.notes" :key="index">{{ note }}</li>
        </ul>
      </div>

      <!-- Bug fixes, opened from the heroes row -->
      <div v-if="showBugFixes && bugFixCount" :class="[detailClass, 'flex flex-col gap-4']">
        <div v-if="notes.bug_fixes.general && notes.bug_fixes.general.length">
          <h5 class="font-semibold mb-1">{{ $t('General') }}</h5>
          <ul class="list-disc list-inside text-sm space-y-1">
            <li v-for="(note, index) in notes.bug_fixes.general" :key="index">{{ note }}</li>
          </ul>
        </div>
        <div v-if="notes.bug_fixes.maps && notes.bug_fixes.maps.length">
          <h5 class="font-semibold mb-1">{{ $t('Maps') }}</h5>
          <div v-for="map in notes.bug_fixes.maps" :key="map.map" class="mb-2">
            <div class="text-sm font-semibold">{{ $mapName(map.map) }}</div>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li v-for="(note, index) in map.notes" :key="index">{{ note }}</li>
            </ul>
          </div>
        </div>
        <div v-if="notes.bug_fixes.heroes && notes.bug_fixes.heroes.length">
          <h5 class="font-semibold mb-2">{{ $t('Heroes') }}</h5>
          <div class="flex flex-wrap gap-2">
            <button v-for="hero in notes.bug_fixes.heroes" :key="hero.name" type="button" :class="chipClass(selectedBugFixHero === hero.name, 'bg-surface-1')" @click="toggleBugFixHero(hero.name)">
              <img :src="`/images/heroes/${hero.short_name}.png`" alt="" class="w-8 h-8 rounded-full bg-surface-3 text-[0rem]" @error="useFallbackImage" />
              <span>{{ $heroName(hero) }}</span>
            </button>
          </div>
          <ul v-if="selectedBugFixHeroData" class="mt-3 list-disc list-inside text-sm space-y-1">
            <li v-for="(note, index) in selectedBugFixHeroData.notes" :key="index">{{ note }}</li>
          </ul>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-4 text-gray-medium">{{ $t('No summary available for this patch.') }}</div>
    </component>
    </Teleport>
  </panel-box>
</template>

<script>
export default {
  name: 'PatchNotesPanel',
  props: {
    version: {
      type: String,
      required: true
    },
    // A selector to open the notes into, instead of under the bar
    bodyTarget: {
      type: String,
      default: null
    },
    // Lets the page close it (another card opened)
    expanded: {
      type: Boolean,
      default: null
    },
  },
  emits: ['toggle'],
  data() {
    return {
      open: false,
      // The version the notes on hand are for
      loadedVersion: null,
      loading: false,
      notes: null,
      selectedHero: null,
      selectedBugFixHero: null,
      selectedMap: null,
      showBugFixes: false,
      rowClass: 'rounded-control bg-surface-1 px-5 py-4 flex flex-wrap md:flex-nowrap items-center gap-x-6 gap-y-3',
      labelClass: 'shrink-0 font-bold',
      detailClass: 'rounded-control bg-surface-1 px-5 py-4',
      badges: {
        buff: { glyph: '▲', label: 'Buff', color: 'text-[#4ade80]' },
        nerf: { glyph: '▼', label: 'Nerf', color: 'text-lred' },
        mixed: { glyph: '◆', label: 'Mixed', color: 'text-[#c084fc]' },
        change: { glyph: '●', label: 'Change', color: 'text-yellow' },
        new: { glyph: '★', label: 'New', color: 'text-[#5aa9ff]' },
      },
    }
  },
  computed: {
    // The bar's legend: only the change types this patch has, in the usual order
    usedBadges() {
      if (!this.notes || this.notes.version !== this.version) {
        return {};
      }
      const types = new Set([
        ...(this.notes.general || []).map(entry => entry.type),
        ...(this.notes.maps || []).map(map => map.type),
        ...(this.notes.heroes || []).map(hero => hero.type),
      ].filter(Boolean));
      return Object.fromEntries(Object.entries(this.badges).filter(([type]) => types.has(type)));
    },
    bugFixCount() {
      const bugFixes = this.notes && this.notes.bug_fixes;
      if (!bugFixes) {
        return 0;
      }
      return (bugFixes.general ? bugFixes.general.length : 0)
        + (bugFixes.maps ? bugFixes.maps.length : 0)
        + (bugFixes.heroes ? bugFixes.heroes.length : 0);
    },
    selectedHeroData() {
      if (!this.selectedHero || !this.notes || !this.notes.heroes) {
        return null;
      }
      return this.notes.heroes.find(hero => hero.name === this.selectedHero) || null;
    },
    selectedMapData() {
      if (!this.selectedMap || !this.notes || !this.notes.maps) {
        return null;
      }
      return this.notes.maps.find(map => map.map === this.selectedMap) || null;
    },
    selectedBugFixHeroData() {
      if (!this.selectedBugFixHero || !this.bugFixCount || !this.notes.bug_fixes.heroes) {
        return null;
      }
      return this.notes.bug_fixes.heroes.find(hero => hero.name === this.selectedBugFixHero) || null;
    },
  },
  // The bar shows the patch's date and title, so the notes (a static file) load straight away
  mounted() {
    this.loadNotes();
  },
  watch: {
    expanded(value) {
      if (value !== null && value !== this.open) {
        this.open = value;
      }
    },
    version() {
      this.loadNotes();
    },
  },
  methods: {
    // Closed, anywhere on the bar opens it; open, only its empty space closes it. Stopped here so the
    // card's own open-on-click doesn't reopen it in the same click
    barClicked(event) {
      if (!this.open || !event.target.closest('h3, span, button, a')) {
        this.toggle();
      }
    },
    toggle() {
      // A click that closes an open filter menu only closes the menu
      if (document.querySelector('.expandable-dropdown')) return;
      this.open = !this.open;
      this.$emit('toggle', this.open);
      if (this.open && this.loadedVersion !== this.version) {
        this.loadNotes();
      }
    },
    badgeFor(type) {
      return this.badges[type] || this.badges.change;
    },
    toggleHero(name) {
      this.selectedHero = this.selectedHero === name ? null : name;
    },
    toggleMap(name) {
      this.selectedMap = this.selectedMap === name ? null : name;
    },
    // A hero or map chip, ringed while its notes are open
    chipClass(selected, background = 'bg-surface-3') {
      return ['inline-flex items-center gap-2.5 rounded-full pl-1.5 pr-3.5 py-1.5 text-sm font-semibold text-white transition-shadow', background, selected ? 'ring-2 ring-teal' : 'hover:ring-1 hover:ring-teal'];
    },
    toggleBugFixHero(name) {
      this.selectedBugFixHero = this.selectedBugFixHero === name ? null : name;
    },
    hideImage(event) {
      event.target.style.display = 'none';
    },
    useFallbackImage(event) {
      event.target.src = '/images/heroes/no-image.png';
    },
    async loadNotes() {
      this.loadedVersion = this.version;
      this.loading = true;
      this.notes = null;
      this.selectedHero = null;
      this.selectedBugFixHero = null;
      this.selectedMap = null;
      this.showBugFixes = false;
      try {
        const response = await fetch(`/patch-notes/${this.version}.json`);
        if (response.ok) {
          this.notes = await response.json();
        }
      } catch (e) {
        this.notes = null;
      } finally {
        this.loading = false;
      }
    },
  },
}
</script>
