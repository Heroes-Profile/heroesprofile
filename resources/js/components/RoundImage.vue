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
  
  @mouseover="handleMouseOver" @mouseleave="scheduleHide">

    <!-- On mobile the inline text block below shows these icons beside their labels instead -->
    <div :class="['absolute z-10 bottom-0 right-0 w-9', { 'max-md:hidden': hasMobileText }]"  v-if="award">
      <img :src="awardicon"/>
    </div>
    <div :class="['absolute -top-2 left-0 z-10', { 'max-md:hidden': hasMobileText }]" v-if="hpowner">
      <!-- {{ "HP Owner" }} -->
      <i class="fas fa-crown text" style="color:gold;"></i>
    </div>

    <div :class="['absolute z-10 -top-2 left-0', { 'max-md:hidden': hasMobileText }]" v-else-if="ispatreon">
      <!-- {{ "Patreon Subscriber" }} -->
      <i class="fas fa-star" style="color:gold"></i>
    </div>
    <div :class="['absolute z-10 bottom-0 -left-2 text-xl leading-none', { 'max-md:hidden': hasMobileText }]" v-if="voideye">
      <void-eye-flair :force="true" :tooltip="false"></void-eye-flair>
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
    
    <!-- Rendered under body so scrolling table wrappers can't clip it -->
    <Teleport to="body">
      <div v-if="showTooltip" @mouseover="handleMouseOver" @mouseleave="scheduleHide" :style="tooltipStyle" :class="[
          'fixed text-xs z-50',
        {
          'w-[12em]' : popupsize != 'large' && popupsize != 'xlarge',
          'w-[20em]' : popupsize == 'large',
          'w-[28em]' : popupsize == 'xlarge'
        }
        ]" >
        <div v-if="!excludehover" :class="['popup-text block  bg-gray-dark  text-s p-1   text-white  drop-shadow-md  rounded-md px-2 text-center  m-t-auto z-30 ', {

        }]">
          <div class="bg-yellow" v-if="hpowner">Heroes Profile Owner</div>
          <div class="bg-red" v-if="ispatreon">Patreon Subscriber</div>
          <div class="bg-[#4c1d95]" v-if="voideye">Marked by the Void</div>
          <div class="bg-teal" v-if="award">{{award.title}}</div>
          <slot></slot>
        </div>
        <div v-if="!excludehover && tooltipPosition === 'top'" class="popup-arrow max-md:hidden"></div>
      </div>
    </Teleport>
    <div v-if="hasMobileText" :class="[' md:hidden     text-s p-1    drop-shadow-md  rounded-md px-2 text-center   md:mb-4', {}]">
      <div class="bg-yellow flex items-center justify-center gap-2" v-if="hpowner"><i class="fas fa-crown" style="color:gold;"></i>Heroes Profile Owner</div>
      <div class="bg-red flex items-center justify-center gap-2" v-else-if="ispatreon"><i class="fas fa-star" style="color:gold"></i>Patreon Subscriber</div>
      <div class="bg-[#4c1d95] flex items-center justify-center gap-2" v-if="voideye"><void-eye-flair :force="true" :tooltip="false"></void-eye-flair>Marked by the Void</div>
      <slot></slot>
      <div class="bg-teal flex items-center justify-center gap-2" v-if="award"><img v-if="awardicon" :src="awardicon" class="w-7 h-7" alt="" />{{award.title}}</div>
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
    voideye: Boolean,
    icon: String,
    mobileClick: false,
    hidedelay: {
      type: Number,
      default: 0,
    }
    
  },
  data(){
    return {
      showTooltip: false,
      tooltipPosition: 'top',
      tooltipStyle: {},
      hideTimer: null,
    }
  },
  created(){
  },
  mounted() {
  },
  beforeUnmount() {
    clearTimeout(this.hideTimer);
    window.removeEventListener('scroll', this.hideTooltip, true);
  },
  computed: {
    // Mobile shows the hover text inline under the image.
    hasMobileText() {
      return !this.excludehover && !this.mobileClick;
    },
  },
  watch: {
  },
  methods: {
    isSmallScreen() {
      return window.innerWidth <= 768; // You can adjust the threshold as needed
    },
    scheduleHide() {
      if (this.hidedelay === 0) {
        this.hideTooltip();
      } else {
        this.hideTimer = setTimeout(() => { this.hideTooltip(); }, this.hidedelay);
      }
    },
    hideTooltip() {
      this.showTooltip = false;
      window.removeEventListener('scroll', this.hideTooltip, true);
    },
    handleMouseOver() {
      // Mobile already shows this text under the image
      if (this.hasMobileText && this.isSmallScreen()) return;

      clearTimeout(this.hideTimer);
      if (!this.showTooltip) {
        // Fixed position goes stale once anything scrolls
        window.addEventListener('scroll', this.hideTooltip, true);
      }
      this.showTooltip = true;
      this.calculateTooltipPosition();
    },
    calculateTooltipPosition() {
      if (!this.$refs.container) return;

      const rect = this.$refs.container.getBoundingClientRect();
      const tooltipWidth = this.popupsize === 'xlarge' ? 448 : this.popupsize === 'large' ? 320 : 192;
      const tooltipHeight = 120; // approximate height of tooltip popup
      const halfTooltip = tooltipWidth / 2;
      const screenWidth = window.innerWidth;

      const leftEdgeWhenCentered = rect.left + (rect.width / 2) - halfTooltip;
      const rightEdgeWhenCentered = rect.left + (rect.width / 2) + halfTooltip;
      const tooCloseToTop = rect.top < tooltipHeight;

      if (tooCloseToTop) {
        // Not enough space above — show below instead
        this.tooltipPosition = 'bottom';
      } else if (leftEdgeWhenCentered < 10) {
        this.tooltipPosition = 'right';
      } else if (rightEdgeWhenCentered > screenWidth - 10) {
        this.tooltipPosition = 'left';
      } else {
        this.tooltipPosition = 'top';
      }

      const gap = 8;
      const centerX = rect.left + (rect.width / 2);
      const centerY = rect.top + (rect.height / 2);

      if (this.tooltipPosition === 'top') {
        this.tooltipStyle = { left: `${centerX}px`, bottom: `${window.innerHeight - rect.top + gap}px`, transform: 'translateX(-50%)' };
      } else if (this.tooltipPosition === 'bottom') {
        this.tooltipStyle = { left: `${centerX}px`, top: `${rect.bottom + gap}px`, transform: 'translateX(-50%)' };
      } else if (this.tooltipPosition === 'right') {
        this.tooltipStyle = { left: `${rect.right + gap}px`, top: `${centerY}px`, transform: 'translateY(-50%)' };
      } else {
        this.tooltipStyle = { right: `${screenWidth - rect.left + gap}px`, top: `${centerY}px`, transform: 'translateY(-50%)' };
      }
    }
  }
}
</script>