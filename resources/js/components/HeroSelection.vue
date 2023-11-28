<template>
  <div class="md:px-20 max-md:px-4">
    <div class="text-center my-5">
      <input 
        type="text" 
        v-model="searchQuery" 
        class="border rounded p-2 text-black" 
        placeholder="Search for a hero..."
      />
    </div>

    <div class="flex flex-wrap gap-2 max-w-[1200px] mx-auto cursor-pointer justify-center max-md:justify-between">
      <hero-image-wrapper class="max-md:w-[30%]" 
        v-for="hero in filteredHeroes" 
        :key="hero.id" 
        :hero="hero" 
        size="big"
        @click="clickedHero(hero)"
      ></hero-image-wrapper>
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
    normalizeString(input) {
      return input.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }
  }
}
</script>
