<template>
  <div
  :class="[
      $slots.trigger ? 'relative inline-flex items-center min-w-0' : 'relative group items-center md:w-10 md:h-10 ',
      $slots.trigger ? {} : { 
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
       
      <!-- Anything can open the popup in place of the icon -->
      <slot v-if="$slots.trigger" name="trigger"></slot>
      <span class="bg-lighten border border-black text-sm rounded-full w-[1.7em] h-[1.7em]  flex justify-center pl-[1px] items-center bold " v-else-if="circle" >
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
          'w-[14em]' : popupsize != 'large' && popupsize != 'fit',
          'w-[400px] max-w-[90vw]' : popupsize == 'large',
          'w-max max-w-[24em]' : popupsize == 'fit'
        }
        ]" >
          <div v-if="!excludehover" class="popup-text block bg-surface-4 text-sm text-soft tracking-normal text-left p-4 rounded drop-shadow-md z-30">

            <slot></slot>


          </div>


      </div>
    </Teleport>

   
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
      return window.innerWidth <= 1024; // You can adjust the threshold as needed
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