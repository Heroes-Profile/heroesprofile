<template>
  <!-- Collapsed until opened; the notes load the first time it opens -->
  <panel-box :class="['w-full max-w-[93.75rem] mx-auto mb-4 md:!pt-4', { 'md:!pb-4 cursor-pointer': !open }]" @click="!open && toggle()">
    <!-- The bar, as in the redesign: title and patch chip, the patch date under them, the legend, then Show/Hide -->
    <!-- Open, a click on the bar's empty space closes it; closed, the whole card opens it -->
    <div class="flex flex-wrap items-center gap-4" @click.stop="barClicked">
      <div class="flex-1 min-w-0 flex flex-col gap-1">
        <div class="flex flex-wrap items-center gap-3">
          <h3 class="text-2xl font-bold">{{ $t('Patch Notes') }}</h3>
          <span class="px-2.5 py-[0.1875rem] rounded-[0.375rem] bg-teal/[0.18] text-teal text-[0.8125rem] font-bold whitespace-nowrap">{{ version }}<template v-if="notes && notes.version === version && notes.title"> · {{ notes.title }}</template></span>
        </div>
        <span v-if="notes && notes.version === version && notes.date" class="text-subtext">{{ notes.date }}</span>
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
    <div v-if="open" class="h-px bg-gray-dark"></div>

    <div v-if="open" class="text-gray-light">
    <div v-if="loading" class="text-center py-4">{{ $t('Loading patch notes...') }}</div>

    <div v-else-if="notes">
      <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
        <h3 class="text-xl font-bold">
          {{ $t('Patch :version', { version: notes.version }) }}<span v-if="notes.date"> &mdash; {{ notes.date }}</span><span v-if="notes.title"> ({{ notes.title }})</span>
        </h3>
        <div class="flex items-center gap-3 text-xs">
          <span v-for="(badge, type) in badges" :key="type" class="flex items-center gap-1">
            <span :class="badge.color">{{ badge.glyph }}</span>{{ badge.label }}
          </span>
        </div>
      </div>
      <p v-if="notes.blizzard_url" class="text-sm text-gray-medium mb-4">
        Full patch notes can be found at
        <a :href="notes.blizzard_url" target="_blank" rel="noopener noreferrer" class="text-teal hover:text-lteal underline">Blizzard Patch Notes</a>
      </p>

      <div v-if="notes.general && notes.general.length" class="mb-4">
        <h4 class="text-lg font-semibold mb-1">{{ $t('General') }}</h4>
        <ul class="list-disc list-inside space-y-1">
          <li v-for="(entry, index) in notes.general" :key="index">
            <span v-if="entry.type" :class="badgeFor(entry.type).color" class="mr-1">{{ badgeFor(entry.type).glyph }}</span>{{ entry.note || entry }}
          </li>
        </ul>
      </div>

      <div v-if="notes.maps && notes.maps.length" class="mb-4">
        <h4 class="text-lg font-semibold mb-2">{{ $t('Maps') }}</h4>
        <div class="flex flex-wrap gap-3">
          <div v-for="map in notes.maps" :key="map.map" class="w-56 rounded overflow-hidden bg-darken">
            <div class="relative">
              <img v-if="map.image" :src="`/images/maps/icon/${map.image}`" :alt="map.map" class="w-full h-20 object-cover" @error="hideImage" />
              <span class="absolute top-1 right-1 text-lg drop-shadow" :class="badgeFor(map.type).color">{{ badgeFor(map.type).glyph }}</span>
            </div>
            <div class="p-2">
              <div class="font-semibold text-sm mb-1">{{ $mapName(map.map) }}</div>
              <ul class="text-xs text-gray-md space-y-1">
                <li v-for="(note, index) in map.notes" :key="index">{{ note }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div v-if="notes.heroes && notes.heroes.length" class="mb-4">
        <h4 class="text-lg font-semibold mb-2">Heroes <span class="text-xs text-gray-medium font-normal">(click a hero for details)</span></h4>
        <div class="flex flex-wrap gap-3">
          <div
            v-for="hero in notes.heroes"
            :key="hero.name"
            class="w-16 cursor-pointer text-center group"
            :title="$heroName(hero)"
            @click="toggleHero(hero.name)"
          >
            <div class="relative inline-block">
              <img
                :src="`/images/heroes/${hero.short_name}.png`"
                :alt="hero.name"
                class="w-12 h-12 rounded-full border-2 transition group-hover:scale-110"
                :class="selectedHero === hero.name ? 'border-teal' : 'border-gray-dark'"
                @error="useFallbackImage"
              />
              <span class="absolute -top-1 -right-1 text-sm leading-none drop-shadow" :class="badgeFor(hero.type).color">{{ badgeFor(hero.type).glyph }}</span>
            </div>
            <div class="text-[0.625rem] leading-tight truncate" :class="selectedHero === hero.name ? 'text-teal' : 'text-gray-md'">{{ $heroName(hero) }}</div>
          </div>
        </div>
        <div v-if="selectedHeroData" class="mt-3 p-3 rounded bg-darken">
          <div class="flex items-center gap-2 mb-2">
            <img :src="`/images/heroes/${selectedHeroData.short_name}.png`" :alt="selectedHeroData.name" class="w-8 h-8 rounded-full bg-surface-3 text-[0rem]" @error="useFallbackImage" />
            <span class="font-semibold">{{ selectedHeroData.name }}</span>
            <span :class="badgeFor(selectedHeroData.type).color" class="text-sm">{{ badgeFor(selectedHeroData.type).glyph }} {{ badgeFor(selectedHeroData.type).label }}</span>
          </div>
          <ul class="list-disc list-inside text-sm space-y-1">
            <li v-for="(note, index) in selectedHeroData.notes" :key="index">{{ note }}</li>
          </ul>
        </div>
      </div>

      <div v-if="bugFixCount">
        <h4 class="text-lg font-semibold mb-1 cursor-pointer select-none" @click="showBugFixes = !showBugFixes">
          Bug Fixes <span class="text-xs text-gray-medium font-normal">{{ showBugFixes ? '(hide)' : '(show)' }}</span>
        </h4>
        <div v-if="showBugFixes">
          <div v-if="notes.bug_fixes.general && notes.bug_fixes.general.length" class="mb-4">
            <h5 class="font-semibold mb-1">{{ $t('General') }}</h5>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li v-for="(note, index) in notes.bug_fixes.general" :key="index">{{ note }}</li>
            </ul>
          </div>
          <div v-if="notes.bug_fixes.maps && notes.bug_fixes.maps.length" class="mb-4">
            <h5 class="font-semibold mb-2">{{ $t('Maps') }}</h5>
            <div class="flex flex-wrap gap-3">
              <div v-for="map in notes.bug_fixes.maps" :key="map.map" class="w-56 rounded overflow-hidden bg-darken">
                <img v-if="map.image" :src="`/images/maps/icon/${map.image}`" :alt="map.map" class="w-full h-20 object-cover" @error="hideImage" />
                <div class="p-2">
                  <div class="font-semibold text-sm mb-1">{{ $mapName(map.map) }}</div>
                  <ul class="text-xs text-gray-md space-y-1">
                    <li v-for="(note, index) in map.notes" :key="index">{{ note }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div v-if="notes.bug_fixes.heroes && notes.bug_fixes.heroes.length">
            <h5 class="font-semibold mb-2">Heroes <span class="text-xs text-gray-medium font-normal">(click a hero for details)</span></h5>
            <div class="flex flex-wrap gap-3">
              <div
                v-for="hero in notes.bug_fixes.heroes"
                :key="hero.name"
                class="w-16 cursor-pointer text-center group"
                :title="$heroName(hero)"
                @click="toggleBugFixHero(hero.name)"
              >
                <img
                  :src="`/images/heroes/${hero.short_name}.png`"
                  :alt="hero.name"
                  class="w-12 h-12 rounded-full border-2 transition group-hover:scale-110 inline-block"
                  :class="selectedBugFixHero === hero.name ? 'border-teal' : 'border-gray-dark'"
                  @error="useFallbackImage"
                />
                <div class="text-[0.625rem] leading-tight truncate" :class="selectedBugFixHero === hero.name ? 'text-teal' : 'text-gray-md'">{{ $heroName(hero) }}</div>
              </div>
            </div>
            <div v-if="selectedBugFixHeroData" class="mt-3 p-3 rounded bg-darken">
              <div class="flex items-center gap-2 mb-2">
                <img :src="`/images/heroes/${selectedBugFixHeroData.short_name}.png`" :alt="selectedBugFixHeroData.name" class="w-8 h-8 rounded-full bg-surface-3 text-[0rem]" @error="useFallbackImage" />
                <span class="font-semibold">{{ selectedBugFixHeroData.name }}</span>
              </div>
              <ul class="list-disc list-inside text-sm space-y-1">
                <li v-for="(note, index) in selectedBugFixHeroData.notes" :key="index">{{ note }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-4 text-gray-medium">{{ $t('No summary available for this patch.') }}</div>
    </div>
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
  },
  data() {
    return {
      open: false,
      // The version the notes on hand are for
      loadedVersion: null,
      loading: false,
      notes: null,
      selectedHero: null,
      selectedBugFixHero: null,
      showBugFixes: false,
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
      this.open = !this.open;
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
