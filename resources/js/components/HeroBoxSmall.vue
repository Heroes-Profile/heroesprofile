<template>
  <div v-if="this.thisObj" class="relative group flex items-center " @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    CHANGE THIS TO round-box-small
    <img class="card-img-top relative hover:opacity-75 w-10 h-10 rounded-full" :src="getHeroImage()" :alt="this.thisObj.name" >
    <div v-if="includehover && showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-s p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      {{ popuptext }}
    </div>
   
  </div>
</template>

<script>
export default {
  name: 'RoundBoxSmall',
  components: {
  },
  props: {
    obj: Object,
    hero: Object,
    map: Object,
    hovertext: String,
    type: String,
    includehover: Boolean
  },
  data(){
    return {
      showTooltip: false,
      popuptext: "",
      
    }
  },
  created(){
    
    if(this.thisObj){
      this.popuptext = this.hovertext ? this.hovertext : this.thisObj.name;
    }else{
      console.log("obj is null")
    }
  },
  mounted() {
    
      
      

    
  },
  computed: {
    thisObj(){
      
      if(typeof this.hero != 'undefined'){

        return this.hero;
      }
      if(typeof this.map != 'undefined'){
       
        return this.map;
      }
      else{
        return this.obj;
      }
    }
  },
  watch: {
  },
  methods: {
    getHeroImage(){
      if(this.type == "map")
      {
        return `/images/maps/icon/bg_${this.thisObj.sanitized_map_name}.jpg`;
      }
      else{
        return `/images/heroes/${this.thisObj.short_name}.png`;
      }
      
    }
  }
}
</script>