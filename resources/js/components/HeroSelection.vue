<template>
  <div class="md:px-20 max-md:px-4">
    <div class="text-center my-5">
      <input 
        type="text" 
        v-model="searchQuery"
        @input="$emit('search', searchQuery)"
        class="border rounded p-2 text-black"
        placeholder="Search for a hero..."
      />
    </div>

    <div class="flex flex-wrap gap-2 max-w-[1200px] mx-auto cursor-pointer justify-center max-md:justify-between">
      <template v-for="hero in filteredHeroes" :key="hero.id">
        <a v-if="linkbase" class="max-md:w-[30%]" :href="linkbase + '/' + encodeURIComponent(hero.name)" @click="linkClicked($event, hero)">
          <hero-image-wrapper :hero="hero" size="big"></hero-image-wrapper>
        </a>
        <hero-image-wrapper v-else class="max-md:w-[30%]"
          :hero="hero"
          size="big"
          @click="clickedHero(hero)"
        ></hero-image-wrapper>
      </template>
    </div>
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
  },
  data(){
    return {
      searchQuery: "",
    }
  },
  computed: {
    filteredHeroes() {
      if (this.searchQuery) {
        const normalizedQuery = this.normalizeString(this.searchQuery);

        return this.heroes.filter(hero => {
          const normalizedHeroName = this.normalizeString(hero.name);
          return normalizedHeroName.includes(normalizedQuery);
        });
      }

      return this.heroes;
    }, 
  },
  methods: {
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
