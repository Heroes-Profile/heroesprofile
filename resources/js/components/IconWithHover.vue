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
        // The circle badge sizes itself (1.7em of its own text size)
        'md:w-[1.7em]': icon && !circle,
        'md:h-[1.7em]': icon && !circle,
        
        
      }
      ]"
     ref="container"
     @mouseover="handleMouseOver" @mouseleave="scheduleHide" @click="handleClick">
       
      <!-- Anything can open the popup in place of the icon -->
      <slot v-if="$slots.trigger" name="trigger"></slot>
      <!-- Looks pressed in while pinned open -->
      <span :class="['bg-lighten border border-black text-sm rounded-full w-[1.7em] h-[1.7em] flex justify-center items-center font-bold tracking-normal leading-none transition', { 'bg-surface-1 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)] translate-y-px': pinned }]" v-else-if="circle" >
        <i :class="icon" ></i>
      </span>
      <span class=" rounded-full   flex justify-center pl-[1px] items-center bold " v-else >
        <i :class="icon" ></i>
      </span>
    
   
     
    <!-- Rendered under body so scrolling table wrappers can't clip it -->
    <Teleport to="body">
      <div v-if="showTooltip" ref="popup" @mouseover="handleMouseOver" @mouseleave="scheduleHide" :style="tooltipStyle" :class="[
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
import { popupOpened, popupClosed } from './RoundImage.vue';

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
      hideTimer: null,
      // Clicked open; stays until a click outside or Esc
      pinned: false,
    }
  },
  created(){
  },
  mounted() {
  },
  beforeUnmount() {
    this.hideTooltip();
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
      clearTimeout(this.hideTimer);
      if (this.showTooltip) return;
      popupOpened(this.hideTooltip);
      // Fixed position goes stale once anything scrolls
      window.addEventListener('scroll', this.hideTooltip, true);
      // Hidden for the first frame while keepOnScreen measures it
      this.tooltipStyle = { left: '0px', top: '0px', visibility: 'hidden' };
      this.showTooltip = true;
      this.$nextTick(this.keepOnScreen);
    },
    // Centred over the icon, but slid back inside the window near either edge, and below the icon near the top
    keepOnScreen() {
      const popup = this.$refs.popup;
      if (!popup || !this.$refs.container) return;
      const rect = this.$refs.container.getBoundingClientRect();
      const width = popup.offsetWidth;
      const margin = 8;
      const left = Math.min(Math.max(rect.left + (rect.width / 2) - (width / 2), margin), window.innerWidth - width - margin);
      // The gap is padding on the popup itself, an invisible strip that keeps the pointer inside on its way over
      const vertical = rect.top - popup.offsetHeight < 0
        ? { top: `${rect.bottom}px`, paddingTop: `${margin}px` }
        : { bottom: `${window.innerHeight - rect.top}px`, paddingBottom: `${margin}px` };
      this.tooltipStyle = { left: `${left}px`, ...vertical };
    },
    // Short grace so a quick or slightly off move between icon and popup doesn't close it
    scheduleHide() {
      if (this.pinned) return;
      this.hideTimer = setTimeout(this.hideTooltip, 150);
    },
    handleClick() {
      if (this.pinned) {
        this.hideTooltip();
        return;
      }
      this.handleMouseOver();
      this.pinned = true;
      // After this click has finished, so it doesn't count as a click outside
      setTimeout(() => {
        document.addEventListener('click', this.handleOutsideClick, true);
        document.addEventListener('keydown', this.handleEscape);
      });
    },
    handleOutsideClick(event) {
      if (this.$refs.container?.contains(event.target) || this.$refs.popup?.contains(event.target)) return;
      this.hideTooltip();
    },
    handleEscape(event) {
      if (event.key === 'Escape') this.hideTooltip();
    },
    hideTooltip() {
      clearTimeout(this.hideTimer);
      this.showTooltip = false;
      this.pinned = false;
      popupClosed(this.hideTooltip);
      window.removeEventListener('scroll', this.hideTooltip, true);
      document.removeEventListener('click', this.handleOutsideClick, true);
      document.removeEventListener('keydown', this.handleEscape);
    }
  }
}
</script>