<template>
  <div v-if="this.thisObj" class="relative group flex items-center " @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <img :class="[
      'card-img-top relative hover:opacity-75  rounded-full',  
      { 
        block: size === 'big', 
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-10': size != 'big', 
        'h-10': size != 'big', 
        
      }
      ]"   :src="getImage()" :alt="this.thisObj.name" >
    <div v-if=" showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-s p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      {{ this.thisObj.name }}
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
    includehover: Boolean,
    popuptext: String,
    size: String
  },
  data(){
    return {
      showTooltip: false,
      staticClasses: ""
      
    }
  },
  created(){
    
    
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
    getImage(){
     console.log(this);
      if(typeof this.thisObj.sanitized_map_name != 'undefined')
      {
        console.log('map image');
        return `/images/maps/icon/bg_${this.thisObj.sanitized_map_name}.jpg`;
      }
      else{
        return `/images/heroes/${this.thisObj.short_name}.png`;
      }
      
    }
  }
}
</script>