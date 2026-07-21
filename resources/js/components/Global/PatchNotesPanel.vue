<template>
  <div class="max-w-[1500px] mx-auto mb-4 p-4 rounded bg-lighten text-gray-light">
    <div v-if="loading" class="text-center py-4">Loading patch notes...</div>

    <div v-else-if="notes">
      <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
        <h3 class="text-xl font-bold">
          Patch {{ notes.version }}<span v-if="notes.date"> &mdash; {{ notes.date }}</span><span v-if="notes.title"> ({{ notes.title }})</span>
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
        <h4 class="text-lg font-semibold mb-1">General</h4>
        <ul class="list-disc list-inside space-y-1">
          <li v-for="(entry, index) in notes.general" :key="index">
            <span v-if="entry.type" :class="badgeFor(entry.type).color" class="mr-1">{{ badgeFor(entry.type).glyph }}</span>{{ entry.note || entry }}
          </li>
        </ul>
      </div>

      <div v-if="notes.maps && notes.maps.length" class="mb-4">
        <h4 class="text-lg font-semibold mb-2">Maps</h4>
        <div class="flex flex-wrap gap-3">
          <div v-for="map in notes.maps" :key="map.map" class="w-56 rounded overflow-hidden bg-darken">
            <div class="relative">
              <img v-if="map.image" :src="`/images/maps/icon/${map.image}`" :alt="map.map" class="w-full h-20 object-cover" @error="hideImage" />
              <span class="absolute top-1 right-1 text-lg drop-shadow" :class="badgeFor(map.type).color">{{ badgeFor(map.type).glyph }}</span>
            </div>
            <div class="p-2">
              <div class="font-semibold text-sm mb-1">{{ map.map }}</div>
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
            :title="hero.name"
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
            <div class="text-[10px] leading-tight truncate" :class="selectedHero === hero.name ? 'text-teal' : 'text-gray-md'">{{ hero.name }}</div>
          </div>
        </div>
        <div v-if="selectedHeroData" class="mt-3 p-3 rounded bg-darken">
          <div class="flex items-center gap-2 mb-2">
            <img :src="`/images/heroes/${selectedHeroData.short_name}.png`" :alt="selectedHeroData.name" class="w-8 h-8 rounded-full" @error="useFallbackImage" />
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
            <h5 class="font-semibold mb-1">General</h5>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li v-for="(note, index) in notes.bug_fixes.general" :key="index">{{ note }}</li>
            </ul>
          </div>
          <div v-if="notes.bug_fixes.maps && notes.bug_fixes.maps.length" class="mb-4">
            <h5 class="font-semibold mb-2">Maps</h5>
            <div class="flex flex-wrap gap-3">
              <div v-for="map in notes.bug_fixes.maps" :key="map.map" class="w-56 rounded overflow-hidden bg-darken">
                <img v-if="map.image" :src="`/images/maps/icon/${map.image}`" :alt="map.map" class="w-full h-20 object-cover" @error="hideImage" />
                <div class="p-2">
                  <div class="font-semibold text-sm mb-1">{{ map.map }}</div>
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
                :title="hero.name"
                @click="toggleBugFixHero(hero.name)"
              >
                <img
                  :src="`/images/heroes/${hero.short_name}.png`"
                  :alt="hero.name"
                  class="w-12 h-12 rounded-full border-2 transition group-hover:scale-110 inline-block"
                  :class="selectedBugFixHero === hero.name ? 'border-teal' : 'border-gray-dark'"
                  @error="useFallbackImage"
                />
                <div class="text-[10px] leading-tight truncate" :class="selectedBugFixHero === hero.name ? 'text-teal' : 'text-gray-md'">{{ hero.name }}</div>
              </div>
            </div>
            <div v-if="selectedBugFixHeroData" class="mt-3 p-3 rounded bg-darken">
              <div class="flex items-center gap-2 mb-2">
                <img :src="`/images/heroes/${selectedBugFixHeroData.short_name}.png`" :alt="selectedBugFixHeroData.name" class="w-8 h-8 rounded-full" @error="useFallbackImage" />
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

    <div v-else class="text-center py-4 text-gray-medium">No summary available for this patch.</div>
  </div>
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
  watch: {
    version() {
      this.loadNotes();
    },
  },
  mounted() {
    this.loadNotes();
  },
  methods: {
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
