<template>
  <div>
    <page-heading heading="Heroes" infoText1="Abilities, talents and base stats for every hero, from the current patch."></page-heading>

    <hero-selection :heroes="heroes" linkbase="/Heroes"></hero-selection>
  </div>
</template>

<script>
export default {
  name: 'HeroesIndex',
  props: {
    heroes: {
      type: Array,
      required: true,
    },
  },
  methods: {
    // Called by hero-selection when a hero is clicked
    clickedHero(hero) {
      window.location.href = `/Heroes/${encodeURIComponent(hero.name)}`;
    },
  },
}
</script>
