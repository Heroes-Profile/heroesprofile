<template>
  <div v-if="hero" class="card relative" style="width: 18rem;">
    <img class="card-img-top relative hover:opacity-75" :src="getHeroImage()" :alt="hero.name" @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <div v-if="showTooltip" class="absolute top-0 left-0 bg-gray-700 text-white text-xs p-1 opacity-90" :style="{ left: '50%', transform: 'translateX(-50%)' }">
      {{ popuptext }}
    </div>
    {{ hero.name }}
  </div>
</template>

<script>
export default {
  name: 'HeroBoxSmall',
  components: {
  },
  props: {
    hero: Object,
    hovertext: String,
  },
  data(){
    return {
      showTooltip: false,
      popuptext: "",
    }
  },
  created(){
    if(this.hero){
      this.popuptext = this.hovertext ? this.hovertext : this.hero.name;
    }else{
      console.log("hero is null")
    }
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