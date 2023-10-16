<template>
  <div>
    <round-image :rectangle="rectangle" :size="size" :image="getHeroImage()" :title="hero.name" :excludehover="excludehover">
      <slot>
        <div v-if="!hasSlotContent">
          <h2>{{ hero.name }}</h2>
        </div>
      </slot>
    </round-image>
    
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
      if(this.rectangle){
        return `/images/heroes_rectangle/${this.hero.short_name}.jpg`;
      }
      else{
      return `/images/heroes/${this.hero.short_name}.png`;
      }
    },
   
  }
}
</script>