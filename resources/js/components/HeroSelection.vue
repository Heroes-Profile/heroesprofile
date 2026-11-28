<template>
  <div class="w-full max-w-[120rem] mx-auto px-4 md:px-8">
    <panel-box title="Choose a hero" :subtitle="subtitle">
      <!-- A page can put the filters elsewhere (the Heroes page, beside its heading) and pass them in -->
      <template v-if="roleFilter === null && searchFilter === null" #right>
        <div class="flex flex-wrap items-center gap-4 max-md:w-full">
          <tab-button v-if="roleOptions.length > 1" :small="true" :tabs="roleOptions" overridedefaultside="All" :ignoreclick="true" @tab-click="role => roleChanged({ value: role })"></tab-button>
          <search-input v-model="searchQuery" :placeholder="$t('Search for a hero...')" class="w-full sm:w-[20rem]"></search-input>
        </div>
      </template>
      <!-- grouped: a row per role, its name and count on the left, as in the Tier List -->
      <div v-if="grouped && filteredHeroes.length" class="flex flex-col gap-2">
        <div v-for="group in roleGroups" :key="group.role" class="flex rounded-control bg-surface-1 overflow-hidden min-w-0">
          <div class="w-[8.5rem] md:w-[10.625rem] shrink-0 bg-surface-2 px-4 md:px-5 py-3 flex flex-col">
            <span class="font-bold">{{ $t(group.role) }}</span>
            <span class="text-xs text-subtext">{{ $t(':count heroes', { count: group.heroes.length }) }}</span>
          </div>
          <div class="flex flex-wrap items-center gap-2 px-4 md:px-5 py-3 min-w-0 cursor-pointer">
            <template v-for="hero in group.heroes" :key="hero.id">
              <a v-if="linkbase" :href="linkbase + '/' + encodeURIComponent(hero.name)" @click="linkClicked($event, hero)">
                <hero-image-wrapper :hero="hero" size="tier"></hero-image-wrapper>
              </a>
              <hero-image-wrapper v-else :hero="hero" size="tier" @click="clickedHero(hero)"></hero-image-wrapper>
            </template>
          </div>
        </div>
      </div>
      <div v-else-if="!grouped" class="flex flex-wrap gap-3 cursor-pointer max-md:justify-between">
        <template v-for="hero in filteredHeroes" :key="hero.id">
          <a v-if="linkbase" :href="linkbase + '/' + encodeURIComponent(hero.name)" @click="linkClicked($event, hero)">
            <hero-image-wrapper :hero="hero" size="medium"></hero-image-wrapper>
          </a>
          <hero-image-wrapper v-else
            :hero="hero"
            size="medium"
            @click="clickedHero(hero)"
          ></hero-image-wrapper>
        </template>
      </div>
      <p v-if="filteredHeroes.length == 0" class="text-subtext">{{ $t('No heroes match your search.') }}</p>
    </panel-box>
  </div>
</template>

<script>
// Roles in the filters' order (alphabetical), so they read the same everywhere
export const ROLE_ORDER = ['Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank'];

export default {
  name: 'HeroSelection',
  components: {
  },
  props: {
    heroes: Array,
    heroimages: Object,
    // When set, each hero is a real link to linkbase/{hero} so crawlers can follow it.
    linkbase: { type: String, default: '' },
    subtitle: { type: String, default: 'Open a hero to see their stats.' },
    // Rows by role, as on the Heroes page; false for one grid
    grouped: { type: Boolean, default: true },
    // Set by a page that shows the role and search filters itself
    roleFilter: { type: String, default: null },
    searchFilter: { type: String, default: null },
  },
  data(){
    return {
      searchQuery: "",
      role: 'All',
    }
  },
  computed: {
    filteredHeroes() {
      const normalizedQuery = this.normalizeString(this.searchFilter ?? this.searchQuery);
      const role = this.roleFilter ?? this.role;
      return this.heroes.filter(hero => (role === 'All' || hero.new_role === role)
        && (!normalizedQuery || this.normalizeString(hero.name).includes(normalizedQuery)));
    },
    // The rows for grouped, in the filters' role order; roles with no hero left out
    roleGroups() {
      return ROLE_ORDER
        .map(role => ({ role, heroes: this.filteredHeroes.filter(hero => hero.new_role === role) }))
        .filter(group => group.heroes.length);
    },
    // Role chips for the roles the heroes have, after All
    roleOptions() {
      const roles = [...new Set(this.heroes.map(hero => hero.new_role).filter(role => role))].sort();
      return roles.length ? [{ key: 'All', text: this.$t('All') }, ...roles.map(role => ({ key: role, text: this.$t(role) }))] : [];
    },
  },
  watch: {
    searchQuery(value) {
      this.$emit('search', value);
    },
  },
  methods: {
    roleChanged(event) {
      this.role = event.value;
      this.$emit('role', this.role);
    },
    clickedHero(hero) {
      this.$parent.clickedHero(hero);
    },
    // A plain click stays in the page as before; ctrl/cmd/shift-click opens the link normally.
    linkClicked(event, hero) {
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) {
        return;
      }
      event.preventDefault();
      this.clickedHero(hero);
    },
    normalizeString(input) {
      return input.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }
  }
}
</script>
