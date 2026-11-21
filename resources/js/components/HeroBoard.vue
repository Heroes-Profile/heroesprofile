<template>
  <!-- Every hero as a portrait with its name, with a search box and role tabs (Drafter, Auto Battler).
       The page orders the heroes and may mark some `unavailable`; the board filters them and emits pick -->
  <div class="bg-surface rounded p-4 md:p-6 flex flex-col gap-5 min-w-0">
    <slot name="header"></slot>

    <div class="flex flex-wrap items-center justify-between gap-4">
      <search-input v-model="searchQuery" :placeholder="$t('Search heroes')" class="w-full sm:w-[19.75rem]"></search-input>
      <div class="flex flex-wrap items-center gap-3">
        <tab-button :small="true" :tabs="roleTabs" :ignoreclick="true" :overridedefaultside="selectedRole" @tab-click="selectedRole = $event"></tab-button>
        <slot name="controls"></slot>
      </div>
    </div>

    <slot name="above"></slot>

    <div v-if="error" class="text-center text-red py-10">{{ error }}</div>
    <div v-else-if="loading" class="grid grid-cols-[repeat(auto-fill,minmax(4.75rem,1fr))] gap-y-3">
      <div v-for="hero in visibleHeroes.length" :key="hero" class="flex flex-col items-center gap-1.5">
        <skeleton-box class="w-16 h-16 !rounded-full"></skeleton-box>
        <span class="text-xs skeleton-text">Abathur</span>
      </div>
    </div>
    <div v-else class="grid grid-cols-[repeat(auto-fill,minmax(4.75rem,1fr))] gap-y-3">
      <div v-for="hero in visibleHeroes" :key="hero.id" :class="['relative flex flex-col items-center gap-1.5 min-w-0', hero.unavailable ? 'opacity-30 grayscale' : 'cursor-pointer']" @click="!hero.unavailable && $emit('pick', hero)">
        <hero-image-wrapper :hero="hero" size="medium" :popup-hoverable="false" :class="['rounded-full', { 'ring-[3px] ring-[#f5c542] shadow-[0_0_1.25rem_0.25rem_rgba(245,197,66,0.8)]': glowIds.has(hero.id) }]">
          <slot name="popup" :hero="hero"><h2>{{ $heroName(hero) }}</h2></slot>
        </hero-image-wrapper>
        <slot name="badge" :hero="hero"></slot>
        <span class="text-xs text-subtext text-center truncate max-w-full">{{ $heroName(hero) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
const ROLES = ['Tank', 'Bruiser', 'Healer', 'Support', 'Melee Assassin', 'Ranged Assassin'];

export default {
  name: 'HeroBoard',
  props: {
    heroes: { type: Array, required: true },
    // Grey portraits in the board's shape while the page loads
    loading: { type: Boolean, default: false },
    error: { type: String, default: null },
    // Heroes that glow gold, e.g. the Drafter's suggestions
    glowIds: { type: Set, default: () => new Set() },
  },
  emits: ['pick'],
  data() {
    return {
      searchQuery: '',
      selectedRole: 'All',
      roleTabs: [{ key: 'All', text: 'All' }, ...ROLES.map(role => ({ key: role, text: role.replace(' Assassin', '') }))],
    };
  },
  computed: {
    visibleHeroes() {
      // Unicode letters, so translated names (Cyrillic, Hangul, CJK) survive; matches English or translated name
      const strip = (text) => (text || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^\p{L}\p{N}\s]/gu, '');
      const query = strip(this.searchQuery);
      return this.heroes.filter(hero => (this.selectedRole === 'All' || hero.new_role === this.selectedRole)
        && (!query || strip(hero.name).includes(query) || strip(this.$heroName(hero)).includes(query)));
    },
  },
  methods: {
    clearSearch() {
      this.searchQuery = '';
    },
  },
};
</script>
