<template>
  <div>
    <round-image v-if="!rectangle" :size="size" :title="hero.name" :image="getHeroImage()" :tooltiptex="getToolTip()"></round-image>
    <div v-if="rectangle">
      <!-- I am putting this here for now -->
      <img :src="getHeroImageRectangle()" :alt="title" >
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeroImageWrapper',
  components: {
  },
  props: {
    hero: Object,
    size: String,
    includehover: Boolean,
    rectangle: Boolean,
    heroImage: String,
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
    },
    getHeroImageRectangle(){
      return `/images/heroes_rectangle/${this.heroImage}.jpg`;
    },
    getToolTip(){
      /*
        Need to style the tool tips with this info

        this.hero.name
        this.hero.win_rate
        this.hero.games_played

      */
      return this.hero.name;
    },
  }
}
</script>