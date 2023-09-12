<template>
  <div v-if="hero" class="relative group flex items-center " @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <img class="card-img-top relative hover:opacity-75 w-10 h-10 rounded-full" :src="getHeroImage()" :alt="hero.name" >
    <div v-if="includehover && showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-s p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      {{ popuptext }}
    </div>
   
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
    includehover: Boolean
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