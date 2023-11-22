<template>


      <!-- This probably needs to in the round-image component, but putting it here for now -->
      <!--Awards go in the bottom right corner -->
   


      <!-- This probably needs to in the round-image component, but putting it here for now -->
      <!--Heroes Profile Owner goes in the top left corner -->
   


      <!-- This probably needs to in the round-image component, but putting it here for now -->
      <!--Party Icon go in the bottom right corner -->
    
  <div
  :class="[
      'relative group flex items-center w-10 h-10',  
      { 
        block: size === 'big', 
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl',
        'w-72': size === 'large',
        'h-[23em]': size === 'large',
        'overflow-hidden': size === 'large'
        
      }
      ]"
     @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
        <div class="absolute z-10 bottom-0 right-0 w-9"  v-if="award">
        <!--{{ award.title }} -->
        <img :src="awardicon"/>
      </div>
      <div class="absolute top-0 left-0 z-10" v-if="hpowner">
       <!-- {{ "HP Owner" }} -->
        <i class="fas fa-crown text" style="color:gold;"></i>
      </div>

      <!-- I need to add the logic for this, but this is the icon.  Leaving it true for now so it displays -->
      <div class="absolute z-10 top-0 left-0" v-if="ispatreon">
       <!-- {{ "Patreon Subscriber" }} -->
        <i class="fas fa-star" style="color:gold"></i>
      </div>
        <div class="absolute z-10 top-0 right-0 w-9" v-if="party">
       <!--  {{ party }} -->
        <img :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${party}.png`"/>
      </div>
    <img loading="eager" :class="[
      'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow   w-full h-10 min-w-10',  
      { 
        
        'rounded-full' : rectangle != true,
        'h-full': size === 'large',
        'w-auto': size === 'large',
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl',
        
        
      }
      ]"   
      :src="image" 
      :alt="title" >
    <div v-if=" showTooltip" :class="[
        'absolute hidden group-hover:block left-1/2   transform -translate-x-1/2 text-xs bottom-[1em] -translate-y-[2em]  z-30',
      {
        'bottom-[4.5em] -translate-y-[2em]': size === 'big',
        'text-xs' : size === 'big',
        'bottom-[6em] -translate-y-[3em]' : size == 'xl',
        'w-[12em]' : popupsize != 'large',
        'w-[20em]' : popupsize == 'large'
      }

      ]" >
        <div v-if="!excludehover" :class="['popup-text block  bg-gray-dark  text-s p-1   text-white  drop-shadow-md  rounded-md px-2 text-center  m-t-auto z-30 ', {}]">
          <div class="bg-yellow" v-if="hpowner">Heroes Profile Owner</div>
          <div class="bg-teal" v-if="party">{{ party }}</div>
          <div class="bg-red" v-if="ispatreon">Patreon Subscriber</div>
          <div class="bg-teal" v-if="award">{{award.title}}</div>
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
    popupsize: String,
    award: Object,
    awardicon: String,
    party: String,
    hpowner: Boolean,
    ispatreon: Boolean,
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