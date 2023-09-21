<template>
  <!-- Need to add a medium value -->
  <div
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
      :src="image" 
      :alt="title" >
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
    ">{{ title }}</div>
    <!--- Need to somehow pass in the extra talent info and display it correctly 
     <div v-if="showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      <h2>{{ talent.title }}</h2>
      <p>{{ this.removeNumbers(talent.hotkey) }}</p>
      <p>{{ talent.description }}</p>
    </div>
    -->
    <div class="popup-arrow"></div>
    </div>

   
  </div>
</template>

<script>
export default {
  name: 'RoundImage',
  components: {
  },
  props: {
    title: String,
    tooltiptext: String,
    image: String,
    size: String,
  },
  data(){
    return {
      showTooltip: false,
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
  }
}
</script>