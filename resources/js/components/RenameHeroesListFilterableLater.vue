<template>
  <div>
    <div class="text-center my-5">
      <input 
        type="text" 
        v-model="searchQuery" 
        class="border rounded p-2" 
        placeholder="Search for a hero..."
      />
    </div>

    <div class="flex flex-wrap gap-1">
      <round-box-small 
        v-for="hero in filteredHeroes" 
        :key="hero.id" 
        :hero="hero" 
        @click="clickedHero(hero)"
      ></round-box-small>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RenameHeroesListFilterableLater',
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
        return this.heroes.filter(hero => 
          hero.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      return this.heroes;
    },
  },
  methods: {
    clickedHero(hero) {
      this.$parent.clickedHero(hero);
    }
  }
}
</script>
