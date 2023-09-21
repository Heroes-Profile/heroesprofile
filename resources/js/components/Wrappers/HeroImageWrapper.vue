<template>
  <div>
    <round-image v-if="!rectangle && hovertextstyleoverride" :size="size" :title="hero.name" :image="getHeroImage()" :tooltiptex="getToolTip()" :hovertextstyleoverride="hovertextstyleoverride">
      <slot>
      </slot>
    </round-image>
    <round-image v-else-if="!rectangle" :size="size" :title="hero.name" :image="getHeroImage()" :tooltiptex="getToolTip()"></round-image>
    <div v-else>
      <img :src="getHeroImageRectangle()" :alt="hero.name" >
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
      return `/images/heroes_rectangle/${this.hero.short_name}.jpg`;
    },
    getToolTip(){
      return this.hero.name;
    },
  }
}
</script>