<template>
  <div class="relative" :class="textclass" @mouseleave="open = false">
    <button type="button" class="inline-flex items-center gap-2 focus:underline focus:outline-none" :aria-label="'Change ' + label" :aria-expanded="open" @click="open = !open">
      <span>{{ displayName(selectedName) }}</span>
      <i class="fas fa-chevron-down text-xs"></i>
    </button>
    <div v-if="open" class="absolute left-0 top-full z-50 bg-gray-dark border border-white/20 rounded shadow-lg font-normal tracking-normal" style="width: 13.75rem; max-height: 21.25rem; overflow-y: auto;">
      <input v-if="type !== 'role'" v-model="search" type="text" :placeholder="'Search ' + label.toLowerCase() + '...'" class="w-full px-2 py-1 text-xs bg-field text-white border-b border-white/20 focus:outline-none focus:border-teal" @click.stop />
      <div
        v-for="h in filteredHeroes"
        :key="keyOf(h)"
        class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-lighten text-sm"
        @click="selectHero(h)"
      >
        <hero-image-wrapper v-if="type === 'hero'" :hero="h" :includehover="false" class="flex-shrink-0"></hero-image-wrapper>
        <round-image v-else-if="type === 'role'" :image="`/images/roles/${h.name.toLowerCase()}.PNG`" :excludehover="true" class="flex-shrink-0"></round-image>
        <span>{{ displayName(h.name) }}</span>
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
  computed: {
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
      return { hero: this.$heroName, map: this.$mapName }[this.type]?.(name) ?? name;
    },
    keyOf(item) {
      return this.type === 'hero' ? item.id : item.name;
    },
    selectHero(hero) {
      this.open = false;
      this.search = '';
      this.$emit('hero-changed', this.keyOf(hero));
    },
  },
};
</script>
