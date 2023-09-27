<template>
  <div>
    <round-image v-if="!rectangle" :size="size" :image="getHeroImage()" :title="hero.name" :excludehover="excludehover">
      <slot>
        <div v-if="!hasSlotContent">
          <h2>{{ hero.name }}</h2>
        </div>
      </slot>
    </round-image>
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
    excludehover: Boolean,
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
    hasSlotContent() {
      return !!this.$slots.default;
    },
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
  }
}
</script>