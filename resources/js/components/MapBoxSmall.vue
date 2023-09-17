<template>
  <div v-if="map" class="relative group flex items-center " @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    CHANGE THIS TO round-box-small
    <img class="card-img-top relative hover:opacity-75 w-10 h-10 rounded-full" :src="getMapImage()" :alt="map.name" >
    <div v-if="includehover && showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-s p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      {{ popuptext }}
    </div>
   
  </div>
</template>

<script>
export default {
  name: 'MapBoxSmall',
  components: {
  },
  props: {
    map: Object,
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

    if(this.map){
      this.popuptext = this.hovertext ? this.hovertext : this.hero.name;
    }else{
      console.log("map is null")
    }
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    getMapImage(){
      return `/images/maps/icon/bg_${this.map.sanitized_map_name}.jpg`;
    }
  }
}
</script>