<template>
  <div class="card" style="width: 18rem;">
    <img class="card-img-top" :src="getHeroImage()" :alt="hero.name">
    {{ hero.name }}
  </div>
</template>

<script>
export default {
  name: 'HeroBox',
  components: {
  },
  props: {
    hero: Object
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    getHeroImage(){
      return `/images/heroes/${this.hero.short_name}.png`;
    }
  }
}
</script>