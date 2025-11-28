<template>
  <div
  ref="container"
  :class="[
    'relative group items-center md:w-10 md:h-10 ',  
    { 
      block: size === 'big', 
      'md:w-20': size === 'big', 
      'md:h-20': size === 'big',
      'md:w-[6em]': size === 'xl',
      'md:h-[6em]': size === 'xl',
      'md:w-72': size === 'large',
      'md:h-[23em]': size === 'large',
      'overflow-hidden': size === 'large',
      'inline-block' : icon,
      'flex flex-col': !icon,
      'md:w-[1.7em]': icon,
      'md:h-[1.7em]': icon,
      
      
    }
  ]"
  
  @mouseover="handleMouseOver" @mouseleave="showTooltip = false">

    <div class="absolute z-10 bottom-0 right-0 w-9"  v-if="award">
      <img :src="awardicon"/>
    </div>
    <div class="absolute -top-2 left-0 z-10" v-if="hpowner">
      <!-- {{ "HP Owner" }} -->
      <i class="fas fa-crown text" style="color:gold;"></i>
    </div>

    <div class="absolute z-10 -top-2 left-0" v-else-if="ispatreon">
      <!-- {{ "Patreon Subscriber" }} -->
      <i class="fas fa-star" style="color:gold"></i>
    </div>
    <div class="absolute z-10 -top-2 -right-2 w-8" v-if="party">
      <!--  {{ party }} -->
      <img :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${party}.png`"/>
    </div>
    
    <span class="bg-lighten border border-black text-sm rounded-full w-[1.7em] h-[1.7em]  flex justify-center pl-[1px] items-center bold " v-if="icon" >
      <i :class="icon" ></i>
    </span>
  
    <img v-else loading="eager" :class="[
      'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow max-sm:w-[2.5em] max-sm:h-[2.5em]  w-full h-10 min-w-10 max-md:h-10 max-md:w-10 max-w-none md:mr-auto  max-md: m-0',  
      { 
        
        'rounded-full' : rectangle != true,
        //'max-md:w-20 max-md:h-20' : size != 'large', 
        'h-full max-md:max-w-[70%]': size === 'large',
        'w-auto': size === 'large',
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl',
        
        
      }
      ]"   
      :src="image" 
      :alt="title">
    
    <div v-show="showTooltip" :class="[
        'absolute hidden group-hover:block text-xs z-40',
      {
        // Default top positioning
        'left-1/2 transform -translate-x-1/2 bottom-[1em] -translate-y-[2em]': tooltipPosition === 'top',
        'bottom-[4.5em] -translate-y-[2em]': tooltipPosition === 'top' && size === 'big',
        'bottom-[6em] -translate-y-[3em]': tooltipPosition === 'top' && size === 'xl',
        
        // Right positioning (when too close to left edge)
        'left-full top-1/2 -translate-y-1/2 ml-2': tooltipPosition === 'right',
        
        // Left positioning (when too close to right edge)
        'right-full top-1/2 -translate-y-1/2 mr-2': tooltipPosition === 'left',
        
        'text-xs' : size === 'big',
        'w-[12em]' : popupsize != 'large',
        'w-[20em]' : popupsize == 'large'
      }

      ]" >
      <div v-if="!excludehover" :class="['popup-text block  bg-gray-dark  text-s p-1   text-white  drop-shadow-md  rounded-md px-2 text-center  m-t-auto z-30 ', {
        
      }]">
        <div class="bg-yellow" v-if="hpowner">Heroes Profile Owner</div>
        <div class="bg-red" v-if="ispatreon">Patreon Subscriber</div>
        <div class="bg-teal" v-if="award">{{award.title}}</div>
        <slot></slot>
      </div>
      <div v-if="!excludehover && tooltipPosition === 'top'" class="popup-arrow max-md:hidden"></div>
    </div>
    <div v-if="!excludehover && !mobileClick" :class="[' md:hidden     text-s p-1    drop-shadow-md  rounded-md px-2 text-center   md:mb-4', {}]">
      <div class="bg-yellow" v-if="hpowner">Heroes Profile Owner</div>
      <div class="bg-red" v-else-if="ispatreon">Patreon Subscriber</div>
      <slot></slot>
      <div class="bg-teal" v-if="award">{{award.title}}</div>
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
    icon: String,
    mobileClick: false
    
  },
  data(){
    return {
      showTooltip: false,
      tooltipPosition: 'top', // 'top', 'right', or 'left'
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
    isSmallScreen() {
      return window.innerWidth <= 768; // You can adjust the threshold as needed
    },
    handleMouseOver() {
      this.showTooltip = true;
      this.calculateTooltipPosition();
    },
    calculateTooltipPosition() {
      if (!this.$refs.container) return;
      
      const rect = this.$refs.container.getBoundingClientRect();
      const tooltipWidth = this.popupsize === 'large' ? 320 : 192; // 20em or 12em in pixels (approx)
      const halfTooltip = tooltipWidth / 2;
      const screenWidth = window.innerWidth;
      
      // Check if tooltip would overflow left edge when centered
      const leftEdgeWhenCentered = rect.left + (rect.width / 2) - halfTooltip;
      // Check if tooltip would overflow right edge when centered
      const rightEdgeWhenCentered = rect.left + (rect.width / 2) + halfTooltip;
      
      if (leftEdgeWhenCentered < 10) {
        // Too close to left edge, show tooltip on the right
        this.tooltipPosition = 'right';
      } else if (rightEdgeWhenCentered > screenWidth - 10) {
        // Too close to right edge, show tooltip on the left
        this.tooltipPosition = 'left';
      } else {
        // Default: show tooltip on top
        this.tooltipPosition = 'top';
      }
    }
  }
}
</script>