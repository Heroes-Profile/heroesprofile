<template>
  <!-- Need to add a medium value -->
  <div
  :class="[
      'relative group flex items-center w-10 h-10',  
      { 
        block: size === 'big', 
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-14': size === 'medium', 
        'h-14' : size === 'medium',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl',
        'w-72': size === 'large',
        'h-[23em]': size === 'large',
        'overflow-hidden': size === 'large'
        
      }
      ]"
     @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <img :class="[
      'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow   w-full min-h-10 min-w-10',  
      { 
        
        'rounded-full' : rectangle != true,
        'h-full': size === 'large',
        'w-auto': size === 'large',
        'w-14': size === 'medium', 
        'h-14' : size === 'medium',
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl',
        
        
      }
      ]"   
      :src="image" 
      :alt="title" >
    <div v-if=" showTooltip" :class="[
      'absolute hidden group-hover:block left-1/2   transform -translate-x-1/2 text-xs bottom-[1em] -translate-y-[2em]  w-[12em] z-30',
    {
      'bottom-[4.5em] -translate-y-[2em]': size === 'big',
      'text-xs' : size === 'big',
      'bottom-[6em] -translate-y-[3em]' : size == 'xl'
    }

    ]" >
      <div v-if="!excludehover" :class="['popup-text block  bg-gray-dark  text-s p-1   text-white  drop-shadow-md  rounded-md px-2 text-center  m-t-auto z-30 ', {}]">
        <slot></slot>
      </div>


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
    image: String,
    size: String,
    rectangle: Boolean,
    excludehover: Boolean,
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