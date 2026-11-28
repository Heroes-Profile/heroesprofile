<template>
  <!-- Stays open until a pick, a click outside, Escape or the close button -->
  <div ref="root" class="relative" :class="textclass" @keydown.esc="close">
    <button type="button" class="inline-flex items-center gap-2 focus:underline focus:outline-none" :aria-label="'Change ' + label" :aria-expanded="open" @click="open = !open">
      <span>{{ displayName(selectedName) }}</span>
      <basil-icon name="arrow" class="rotate-90 text-xs"></basil-icon>
    </button>
    <div v-if="open" class="absolute left-0 top-full z-50 bg-gray-dark border border-white/20 rounded shadow-lg font-normal" style="width: 17.5rem; max-height: 26.25rem;">
      <!-- The search spans the dropdown's full width above the list; only the list scrolls -->
      <div class="flex flex-col overflow-hidden rounded" style="max-height: inherit;">
      <!-- Its own header row, so the close button doesn't read as part of the search -->
      <div class="shrink-0 flex items-center justify-between pl-3 pr-1 py-1">
        <span class="text-xs font-bold uppercase tracking-wider text-subtext">{{ $t(switcherLabel) }}</span>
        <button type="button" class="w-8 h-8 flex items-center justify-center rounded text-subtext hover:text-white hover:bg-lighten transition-colors" :title="$t('Close')" :aria-label="$t('Close')" @click.stop="close"><basil-icon name="cross" class="text-sm"></basil-icon></button>
      </div>
      <input v-if="type !== 'role'" v-model="search" type="text" :placeholder="'Search ' + label.toLowerCase() + '...'" class="shrink-0 block w-full px-3 py-2.5 text-sm bg-field text-white border-y border-white/20 focus:outline-none focus:border-teal" @click.stop />
      <div class="overflow-y-auto">
      <div
        v-for="h in filteredHeroes"
        :key="keyOf(h)"
        class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-lighten text-sm"
        @click="selectHero(h)"
      >
        <hero-image-wrapper v-if="type === 'hero'" :hero="h" :includehover="false" class="shrink-0"></hero-image-wrapper>
        <!-- The whole shield, scaled to fit: a round crop cut its edges into a jagged fringe -->
        <img v-else-if="type === 'role'" :src="`/images/roles/${h.name.toLowerCase()}.PNG`" alt="" class="w-8 h-10 object-contain shrink-0">

        <span>{{ displayName(h.name) }}</span>
      </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeroHeadingSelect',
  emits: ['hero-changed'],
  props: {
    // Heroes by default. With type 'role' or 'map', the filters' { code, name } lists, matched and emitted by name.
    heroes: Array,
    value: [Number, String],
    type: {
      type: String,
      default: 'hero',
    },
    textclass: {
      type: String,
      default: 'text-lg',
    },
  },
  data() {
    return {
      open: false,
      search: '',
    };
  },
  mounted() {
    this.onDocumentClick = event => {
      if (this.open && !this.$refs.root.contains(event.target)) {
        this.close();
      }
    };
    document.addEventListener('click', this.onDocumentClick);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onDocumentClick);
  },
  computed: {
    switcherLabel() {
      return { hero: 'Hero Switcher', role: 'Role Switcher', map: 'Map Switcher' }[this.type];
    },
    label() {
      return { hero: 'Hero', role: 'Role', map: 'Map' }[this.type];
    },
    selectedName() {
      const hero = this.heroes.find(h => this.keyOf(h) == this.value);
      return hero ? hero.name : '';
    },
    filteredHeroes() {
      const q = this.search.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
      if (!q) return this.heroes;
      const fold = (text) => text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
      return this.heroes.filter(h => fold(h.name).includes(q) || fold(this.displayName(h.name)).includes(q));
    },
  },
  methods: {
    displayName(name) {
      return { hero: this.$heroName, map: this.$mapName, role: this.$t }[this.type]?.(name) ?? name;
    },
    keyOf(item) {
      return this.type === 'hero' ? item.id : item.name;
    },
    close() {
      this.open = false;
      this.search = '';
    },
    selectHero(hero) {
      this.close();
      this.$emit('hero-changed', this.keyOf(hero));
    },
  },
};
</script>
