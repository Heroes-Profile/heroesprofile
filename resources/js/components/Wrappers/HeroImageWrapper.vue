<template>
  <div>
    <round-image v-if="!rectangle && hovertextstyleoverride" :size="size" :title="hero.name" :image="getHeroImage()" :tooltiptex="getToolTip()" :hovertextstyleoverride="hovertextstyleoverride">
      <slot>
      </slot>
    </round-image>
    <round-image v-else-if="!rectangle" :size="size" :title="hero.name" :image="getHeroImage()" :tooltiptex="getToolTip()"></round-image>
    <div v-else>
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
    hovertextstyleoverride: Boolean,
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