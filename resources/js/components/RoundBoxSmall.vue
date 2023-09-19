<template>
  <div v-if="this.thisObj"
  :class="[
      'relative group flex items-center w-10 h-10',  
      { 
        block: size === 'big', 
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl'
        
      }
      ]"
     @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <img :class="[
      'card-img-top relative hover:brightness-125 hover:drop-shadow  rounded-full w-full h-10 w-10  ',  
      { 
        block: size === 'big', 
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl' 
        
      }
      ]"   
      :src="getImage()" 
      :alt="this.thisObj.name" >
    <div v-if=" showTooltip" :class="[
      'absolute hidden group-hover:block left-1/2   transform -translate-x-1/2 text-xs bottom-[1em] -translate-y-[2em]  w-[12em] z-50',
    {
      'bottom-[4.5em] -translate-y-[2em]': size === 'big',
      'text-xs' : size === 'big',
      'bottom-[6em] -translate-y-[3em]' : size == 'xl'
    }

    ]" >
      <div  :class="['popup-text block  bg-gray-dark  text-s p-1    text-white  drop-shadow-md  rounded-md px-2 text-center  m-t-auto z-50 ',
      
      {
        
       
      }
      ]
    ">{{ this.thisObj.name }} {{this.tooltiptext}}</div>
    
    <div class="popup-arrow"></div>
    </div>

   
  </div>
</template>

<script>
export default {
  name: 'RoundBoxSmall', // Change this to RoundImageWrapper
  components: {
  },
  props: {
    obj: Object,
    tooltiptext: String,
    image: String,
    size: String,
    includehover: Boolean,

    hero: Object,
    map: Object,
    hovertext: String,
    type: String,    
    popuptext: String,
    
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
      //remove this - put it in the wrapper component!
      
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
      //remove this - put it in the wrapper component
      if(typeof this.thisObj.sanitized_map_name != 'undefined')
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