<template>
  <div>
    <round-image :size="size" :title="hero.name" :image="getHeroImage()" :tooltiptex="getToolTip()"></round-image>
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