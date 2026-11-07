<template>
  <div
  ref="container"
  :class="[
    'relative group items-center',
    { 'md:w-10 md:h-10': !icon && !['match', 'row', 'talent'].includes(size) },
    { 
      block: size === 'big', 
      'md:w-20': size === 'big', 
      'md:h-20': size === 'big',
      'md:w-[6em]': size === 'xl',
      'md:h-[6em]': size === 'xl',
      // Square 64px tile with a light border
      'block w-16 h-16 md:w-16 md:h-16': size === 'tile',
      // Fixed at every width: match rows (82px), compact rows (42px), talent icons (42px)
      'block w-[5.125rem] h-[5.125rem] md:w-[5.125rem] md:h-[5.125rem]': size === 'match',
      'block w-[2.625rem] h-[2.625rem] md:w-[2.625rem] md:h-[2.625rem]': size === 'row' || size === 'talent',
      'md:w-72': size === 'large',
      'md:h-[23em]': size === 'large',
      'overflow-hidden rounded': size === 'large',
      'flex flex-col': !icon,
      // Badges size themselves (the circle is 1.7em of its own text size)
      'inline-flex': icon,
      
      
    }
  ]"
  
  @mouseover="handleMouseOver" @mouseleave="scheduleHide" @click="handleClick">

    <div :class="['absolute z-10 bottom-0 right-0 w-9']"  v-if="award">
      <img :src="awardicon"/>
    </div>
    <div :class="['absolute -top-2 left-0 z-10']" v-if="hpowner">
      <!-- {{ "HP Owner" }} -->
      <i class="fas fa-crown text" style="color:gold;"></i>
    </div>

    <div :class="['absolute z-10 -top-2 left-0']" v-else-if="ispatreon">
      <!-- {{ "Patreon Subscriber" }} -->
      <i class="fas fa-star" style="color:gold"></i>
    </div>
    <div :class="['absolute z-10 bottom-0 -left-2 text-xl leading-none']" v-if="voideye">
      <void-eye-flair :force="true" :tooltip="false"></void-eye-flair>
    </div>
    <div class="absolute z-10 -top-2 -right-2 w-8" v-if="party">
      <!--  {{ party }} -->
      <img :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${party}.png`"/>
    </div>
    
    <!-- Looks pressed in while pinned open -->
    <span :class="['bg-lighten border border-black text-sm rounded-full w-[1.7em] h-[1.7em] flex justify-center items-center font-bold tracking-normal leading-none transition', { 'bg-surface-1 shadow-[inset_0_2px_0.25rem_rgba(0,0,0,0.6)] translate-y-px': pinned }]" v-if="icon" >
      <i :class="icon" ></i>
    </span>
  
    <!-- A grey box until the image arrives; the browser's own alt text is collapsed (it can't be centred) -->
    <img v-else loading="eager" :class="[
      { 'bg-surface-3 text-[0rem]': !imageLoaded || imageFailed },
      size === 'tile'
        ? 'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow w-16 h-16 max-w-none rounded border-2 border-header'
        : size === 'match'
        ? 'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow w-[5.125rem] h-[5.125rem] max-w-none'
        : size === 'row'
        ? 'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow w-[2.625rem] h-[2.625rem] max-w-none'
        : size === 'talent'
        ? 'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow w-[2.625rem] h-[2.625rem] max-w-none rounded'
        : size === 'large'
        ? 'card-img-top object-cover object-center relative hover:brightness-125 hover:drop-shadow max-w-none rounded md:w-full md:h-full max-md:w-40 max-md:h-auto'
        : 'card-img-top object-cover relative hover:brightness-125 hover:drop-shadow max-sm:w-[2.5em] max-sm:h-[2.5em] max-sm:min-w-[2.5em] w-full h-10 min-w-10 max-md:h-10 max-md:w-10 max-w-none md:mr-auto  max-md: m-0',
      { 
        
        'rounded-full' : rectangle != true && size !== 'tile' && size !== 'talent',
        //'max-md:w-20 max-md:h-20' : size != 'large', 
        'w-20': size === 'big', 
        'h-20': size === 'big',
        'w-[6em]': size === 'xl',
        'h-[6em]': size === 'xl',
        
        
      }
      ]"   
      :src="image" 
      :alt="title"
      @load="imageLoaded = true"
      @error="imageFailed = true">
    <!-- Phones collapse an unloaded image to its alt text, so a grey circle the image's size holds its place -->
    <span v-if="!icon && !imageLoaded && !imageFailed && !rectangle && !['match', 'row', 'talent', 'tile', 'large'].includes(size)" :class="['md:hidden block mx-auto rounded-full bg-surface-3', size === 'big' ? 'w-20 h-20 max-sm:w-[2.5em] max-sm:h-[2.5em]' : size === 'xl' ? 'w-[6em] h-[6em]' : 'w-10 h-10 max-sm:w-[2.5em] max-sm:h-[2.5em]']"></span>
    <!-- The name centred on the grey box meanwhile (or for good, if the image failed), on sizes big enough to hold it -->
    <span v-if="!icon && (!imageLoaded || imageFailed) && namedPlaceholder" class="max-md:hidden absolute inset-0 flex items-center justify-center text-center text-xs leading-tight px-2 text-subtext pointer-events-none">{{ title }}</span>
    
    <!-- Rendered under body so scrolling table wrappers can't clip it -->
    <Teleport to="body">
      <div v-if="showTooltip" ref="popup" @mouseover="handleMouseOver" @mouseleave="scheduleHide" :style="tooltipStyle" :class="[
          'fixed text-xs z-50',
        {
          'w-[14em]' : popupsize != 'large' && popupsize != 'xlarge' && popupsize != 'fit',
          'w-[25rem] max-w-[90vw]' : popupsize == 'large',
          'w-[32em] max-w-[90vw]' : popupsize == 'xlarge',
          'w-max max-w-[24em]' : popupsize == 'fit'
        }
        ]" >
        <div v-if="!excludehover" class="popup-text block bg-surface-4 text-sm text-soft tracking-normal text-left p-4 rounded drop-shadow-md z-30">
          <div class="bg-yellow" v-if="hpowner">Heroes Profile Owner</div>
          <div class="bg-red" v-if="ispatreon">Patreon Subscriber</div>
          <div class="bg-[#4c1d95]" v-if="voideye">Marked by the Void</div>
          <div class="bg-teal" v-if="award">{{award.title}}</div>
          <slot></slot>
          <custom-button v-if="tapped" class="mt-2" :href="href" :text="linkText" size="small"></custom-button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
// One popup open at a time, across RoundImage and IconWithHover: opening one closes the other at once,
// skipping its grace period, so moving between icons swaps popups instead of showing two
let closeOpenPopup = null;
export function popupOpened(close) {
  if (closeOpenPopup && closeOpenPopup !== close) {
    closeOpenPopup();
  }
  closeOpenPopup = close;
}
export function popupClosed(close) {
  if (closeOpenPopup === close) {
    closeOpenPopup = null;
  }
}

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
    // Name-only hero portraits link elsewhere on tap, so phones show neither the inline text nor a popup
    hidemobiletext: Boolean,
    // Grace before closing, so a quick or slightly off move between icon and popup doesn't close it
    hidedelay: {
      type: Number,
      default: 150,
    },
    // Phones open the popup on tap instead of following the link, with a button to it
    href: String,
    linkText: String,

  },
  data(){
    return {
      showTooltip: false,
      tooltipStyle: {},
      hideTimer: null,
      tapped: false,
      // Clicked open; stays until a click outside or Esc
      pinned: false,
      imageLoaded: false,
      imageFailed: false,
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
    // Name-only portraits and tiles link elsewhere on tap, so phones don't open a popup for them
    noMobileHover() {
      return this.hidemobiletext || this.size === 'tile';
    },
    namedPlaceholder() {
      return ['big', 'xl', 'tile', 'match', 'large'].includes(this.size);
    },
  },
  watch: {
    image() {
      this.imageLoaded = false;
      this.imageFailed = false;
    },
  },
  methods: {
    isSmallScreen() {
      return window.innerWidth < 1024; // You can adjust the threshold as needed
    },
    // Info icons pin open on click; linked tiles on phones open instead of following the link
    handleClick(event) {
      if (this.href && this.isSmallScreen()) {
        event.preventDefault();
        this.tapped = true;
      } else if (!this.icon) {
        return;
      } else if (this.pinned) {
        this.hideTooltip();
        return;
      }
      this.handleMouseOver();
      this.pin();
    },
    pin() {
      if (this.pinned) return;
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
    scheduleHide() {
      if (this.pinned) return;
      const delay = this.hidedelay;
      if (delay === 0) {
        this.hideTooltip();
      } else {
        this.hideTimer = setTimeout(() => { this.hideTooltip(); }, delay);
      }
    },
    hideTooltip() {
      clearTimeout(this.hideTimer);
      this.showTooltip = false;
      this.tapped = false;
      this.pinned = false;
      popupClosed(this.hideTooltip);
      window.removeEventListener('scroll', this.hideTooltip, true);
      document.removeEventListener('click', this.handleOutsideClick, true);
      document.removeEventListener('keydown', this.handleEscape);
    },
    handleMouseOver() {
      // Phones open the popup on tap; name-only portraits and tiles skip it
      if (this.noMobileHover && !this.mobileClick && !this.href && this.isSmallScreen()) return;

      clearTimeout(this.hideTimer);
      if (this.showTooltip) return;
      popupOpened(this.hideTooltip);
      // Fixed position goes stale once anything scrolls
      window.addEventListener('scroll', this.hideTooltip, true);
      // Hidden for the first frame while calculateTooltipPosition measures it
      this.tooltipStyle = { left: '0px', top: '0px', visibility: 'hidden' };
      this.showTooltip = true;
      this.$nextTick(this.calculateTooltipPosition);
    },
    // Above the icon (below it near the top of the window), centred but slid back inside the window near either edge
    calculateTooltipPosition() {
      const popup = this.$refs.popup;
      if (!popup || !this.$refs.container) return;
      const rect = this.$refs.container.getBoundingClientRect();
      const width = popup.offsetWidth;
      const margin = 8;
      const left = Math.min(Math.max(rect.left + (rect.width / 2) - (width / 2), margin), window.innerWidth - width - margin);
      // The gap is padding on the popup itself, an invisible strip that keeps the pointer inside on its way over
      const vertical = rect.top - popup.offsetHeight - margin < 0
        ? { top: `${rect.bottom}px`, paddingTop: `${margin}px` }
        : { bottom: `${window.innerHeight - rect.top}px`, paddingBottom: `${margin}px` };
      this.tooltipStyle = { left: `${left}px`, ...vertical };
    }
  }
}
</script>