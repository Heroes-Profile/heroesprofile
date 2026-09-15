<template>
  <div
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
     ref="container"
     @mouseover="handleMouseOver" @mouseleave="hideTooltip">
       
      <span class="bg-lighten border border-black text-sm rounded-full w-[1.7em] h-[1.7em]  flex justify-center pl-[1px] items-center bold " v-if="circle" >
        <i :class="icon" ></i>
      </span>
      <span class=" rounded-full   flex justify-center pl-[1px] items-center bold " v-else >
        <i :class="icon" ></i>
      </span>
    
   
     
    <!-- Rendered under body so scrolling table wrappers can't clip it -->
    <Teleport to="body">
      <div v-if="showTooltip" :style="tooltipStyle" :class="[
          'fixed text-xs z-50',
        {
          'w-[12em]' : popupsize != 'large',
          'w-[20em]' : popupsize == 'large'
        }
        ]" >
          <div v-if="!excludehover" :class="['popup-text block  bg-gray-dark  text-s p-1   text-white  drop-shadow-md  rounded-md px-2 text-center  m-t-auto z-30 max-md:hidden', {

          }]">

            <slot></slot>


          </div>


        <div class="popup-arrow max-md:hidden"></div>
      </div>
    </Teleport>
    <div v-if="!excludehover && !mobileClick" :class="[' md:hidden     text-s p-1    drop-shadow-md  rounded-md px-2 text-center   md:mb-4', {}]">
          
          <slot></slot>
          

        </div>

   
  </div>
</template>

<script>
export default {
  name: 'IconWithHover',
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
    circle: false,
    mobileClick: false
    
  },
  data(){
    return {
      showTooltip: false,
      tooltipStyle: {},
    }
  },
  created(){
  },
  mounted() {
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.hideTooltip, true);
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
      if (!this.showTooltip) {
        // Fixed position goes stale once anything scrolls
        window.addEventListener('scroll', this.hideTooltip, true);
      }
      const rect = this.$refs.container.getBoundingClientRect();
      this.tooltipStyle = {
        left: `${rect.left + (rect.width / 2)}px`,
        bottom: `${window.innerHeight - rect.top + 8}px`,
        transform: 'translateX(-50%)',
      };
      this.showTooltip = true;
    },
    hideTooltip() {
      this.showTooltip = false;
      window.removeEventListener('scroll', this.hideTooltip, true);
    }
  }
}
</script>