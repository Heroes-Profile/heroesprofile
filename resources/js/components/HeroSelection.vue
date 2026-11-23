<template>
  <div class="w-full max-w-[120rem] mx-auto px-4 md:px-8">
    <panel-box title="Choose a hero" :subtitle="subtitle">
      <template #right>
        <div class="flex flex-wrap items-center gap-4 max-md:w-full">
          <tab-button v-if="roleOptions.length > 1" :small="true" :dark="true" :tabs="roleOptions" overridedefaultside="All" :ignoreclick="true" @tab-click="role => roleChanged({ value: role })"></tab-button>
          <search-input v-model="searchQuery" :placeholder="$t('Search for a hero...')" class="w-full sm:w-[20rem]"></search-input>
        </div>
      </template>
      <div class="flex flex-wrap gap-3 cursor-pointer max-md:justify-between">
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
        <p v-if="filteredHeroes.length == 0" class="text-subtext">{{ $t('No heroes match your search.') }}</p>
      </div>
    </panel-box>
  </div>
</template>

<script>
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
  },
  data(){
    return {
      searchQuery: "",
      role: 'All',
    }
  },
  computed: {
    filteredHeroes() {
      const normalizedQuery = this.normalizeString(this.searchQuery);
      return this.heroes.filter(hero => (this.role === 'All' || hero.new_role === this.role)
        && (!normalizedQuery || this.normalizeString(hero.name).includes(normalizedQuery)));
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
