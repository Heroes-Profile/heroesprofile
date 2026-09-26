<template>
  <div class="relative text-lg" @mouseleave="open = false">
    <button type="button" class="inline-flex items-center gap-2 focus:underline focus:outline-none" aria-label="Change Hero" :aria-expanded="open" @click="open = !open">
      <span>{{ selectedName }}</span>
      <i class="fas fa-chevron-down text-xs"></i>
    </button>
    <div v-if="open" class="absolute left-0 top-full z-50 bg-gray-dark border border-white/20 rounded shadow-lg font-normal" style="width: 220px; max-height: 340px; overflow-y: auto;">
      <input v-model="search" type="text" placeholder="Search hero..." class="w-full px-2 py-1 text-xs bg-gray-dark text-white border-b border-white/20 focus:outline-none focus:border-teal" @click.stop />
      <div
        v-for="h in filteredHeroes"
        :key="h.id"
        class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-lighten text-sm"
        @click="selectHero(h)"
      >
        <hero-image-wrapper :hero="h" :includehover="false" class="flex-shrink-0"></hero-image-wrapper>
        <span>{{ h.name }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeroHeadingSelect',
  emits: ['hero-changed'],
  props: {
    heroes: Array,
    value: [Number, String],
  },
  data() {
    return {
      open: false,
      search: '',
    };
  },
  computed: {
    selectedName() {
      const hero = this.heroes.find(h => h.id == this.value);
      return hero ? hero.name : '';
    },
    filteredHeroes() {
      const q = this.search.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
      if (!q) return this.heroes;
      return this.heroes.filter(h => h.name.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().includes(q));
    },
  },
  methods: {
    selectHero(hero) {
      this.open = false;
      this.search = '';
      this.$emit('hero-changed', hero.id);
    },
  },
};
</script>
