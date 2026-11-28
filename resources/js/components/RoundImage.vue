<template>
  <div
  ref="container"
  :class="[
    'relative group items-center',
    { 'md:w-10 md:h-10': !icon && !['match', 'row', 'talent', 'medium', 'tier', 'heading'].includes(size) },
    { 
      block: size === 'big', 
      // Phones shrink the big portrait to 40px; hug it so its badges sit on its corners, not the column's
      'max-md:w-fit max-md:mx-auto': size === 'big',
      'md:w-20': size === 'big', 
      'md:h-20': size === 'big',
      'md:w-[6em]': size === 'xl',
      'md:h-[6em]': size === 'xl',
      // Square 64px tiles and round 64px portraits (the Drafter's board). One key: a second identical key would replace it
      'block w-16 h-16 md:w-16 md:h-16': size === 'tile' || size === 'medium',
      // Fixed at every width: match rows (82px), compact rows (42px), talent icons (42px)
      'block w-[5.125rem] h-[5.125rem] md:w-[5.125rem] md:h-[5.125rem]': size === 'match',
      'block w-[2.625rem] h-[2.625rem] md:w-[2.625rem] md:h-[2.625rem]': size === 'row' || size === 'talent',
      // Tier List portraits: 52px, 40px on phones
      'block w-10 h-10 md:w-[3.25rem] md:h-[3.25rem]': size === 'tier',
      // A hero beside a page title: 110px, 80px on phones
      'block w-20 h-20 md:w-[6.875rem] md:h-[6.875rem]': size === 'heading',
      // 224 x 322, as in the redesign
      'md:w-[14rem]': size === 'large',
      'md:h-[20.125rem]': size === 'large',
      // Art is a level-2 card: 16px corners
      'overflow-hidden rounded-control': size === 'large',
      'flex flex-col': !icon,
      // A hook for app.css: in a table header the icon hangs past its label
      'info-icon': isInfoIcon,
      // Badges size themselves (the circle is 1.7em of its own text size)
      'inline-flex': icon,
      
      
    }
  ]"
  
  @mouseover="handleMouseOver" @mouseleave="scheduleHide" @click="handleClick">

    <!-- On the big portrait each icon is centred on a corner of a square just inside it (12% in): crown and star top-left, party top-right,
         eye bottom-left, award bottom-right -->
    <div :class="['absolute z-10 w-9', size === 'big' ? 'bottom-[12%] right-[12%] translate-x-1/2 translate-y-1/2 max-md:w-5' : 'bottom-0 right-0']"  v-if="award">
      <img :src="awardicon"/>
    </div>
    <div :class="['absolute z-10 text-xl leading-none', size === 'big' ? 'top-[12%] left-[12%] -translate-x-1/2 -translate-y-1/2 max-md:text-xs' : '-top-3 -left-1']" v-if="hpowner">
      <!-- {{ "HP Owner" }}; the same visible size as the void eye -->
      <span class="relative inline-flex">
        <i class="fas fa-crown text-[1.35em] owner-crown"></i>
        <owner-sparks class="owner-sparks-small"></owner-sparks>
      </span>
    </div>

    <!-- Every flair the player has shows; beside the crown, the star moves over so they don't overlap -->
    <div :class="['absolute z-10', size === 'big' ? ['top-[12%] -translate-y-1/2 max-md:text-[0.6rem]', hpowner ? 'left-[12%] translate-x-1/2' : 'left-[12%] -translate-x-1/2'] : ['-top-2', hpowner ? 'left-8' : 'left-0']]" v-if="ispatreon">
      <!-- {{ "Patreon Subscriber" }} -->
      <i class="fas fa-star text-gold text-[1.5em]"></i>
    </div>
    <div :class="['absolute z-10 text-xl leading-none', size === 'big' ? 'bottom-[12%] left-[12%] -translate-x-1/2 translate-y-1/2 max-md:text-xs' : 'bottom-0 -left-2']" v-if="voideye">
      <void-eye-flair :force="true" :tooltip="false"></void-eye-flair>
    </div>
    <div :class="['absolute z-10 w-8', size === 'big' ? 'top-[12%] right-[12%] translate-x-1/2 -translate-y-1/2 max-md:w-5' : '-top-2 -right-2']" v-if="party">
      <!--  {{ party }} -->
      <img :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${party}.png`"/>
    </div>
    
    <!-- The info icon, Basil's rounded square with the "i" cut out. In headers it's the dark tint with a white "i" (a white
         block behind shows through the cut-out), darker while pinned open; beside a page title (.page-title-info,
         PageHeading) it's larger and white, light grey while hovered or pinned. The glyph fills 80% of its box, so it's
         drawn at 125% to fill the slot. Other icons keep the round badge. -->
    <span v-if="icon && isInfoIcon" :class="['relative text-sm w-[1.15em] h-[1.15em] [.page-title-info_&]:w-[1.3em] [.page-title-info_&]:h-[1.3em] flex justify-center items-center transition-colors',
      pinned
        ? 'text-surface-3 [.page-title-info_&]:text-soft'
        : 'text-surface-4 hover:text-[#4a4f5f] [.page-title-info_&]:text-white [.page-title-info_&]:hover:text-soft']">
      <span class="absolute inset-x-[38%] inset-y-[16%] bg-white [.page-title-info_&]:hidden" aria-hidden="true"></span>
      <basil-icon name="info" class="relative !w-[125%] !h-[125%]"></basil-icon>
    </span>
    <span v-else-if="icon" :class="['bg-lighten border border-black text-sm rounded-full w-[1.7em] h-[1.7em] flex justify-center items-center font-bold leading-none transition', { 'bg-surface-1 shadow-[inset_0_2px_0.25rem_rgba(0,0,0,0.6)] translate-y-px': pinned }]" >
      <i :class="icon" ></i>
    </span>
  
    <!-- A grey box until the image arrives; the browser's own alt text is collapsed (it can't be centred) -->
    <img v-else loading="eager" :class="[
      { 'bg-surface-3 text-[0rem]': !imageLoaded || imageFailed },
      size === 'tile'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-16 h-16 max-w-none rounded border-2 border-header'
        : size === 'match'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-[5.125rem] h-[5.125rem] max-w-none'
        : size === 'row'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-[2.625rem] h-[2.625rem] max-w-none'
        : size === 'medium'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-16 h-16 max-w-none'
        : size === 'tier'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-10 h-10 md:w-[3.25rem] md:h-[3.25rem] max-w-none'
        : size === 'heading'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-20 h-20 md:w-[6.875rem] md:h-[6.875rem] max-w-none'
        : size === 'talent'
        ? 'object-cover relative hover:brightness-125 hover:drop-shadow w-[2.625rem] h-[2.625rem] max-w-none rounded'
        : size === 'large'
        ? 'object-cover object-center relative hover:brightness-125 hover:drop-shadow max-w-none rounded-control md:w-full md:h-full max-md:w-40 max-md:h-auto'
        : 'object-cover relative hover:brightness-125 hover:drop-shadow max-sm:w-[2.5em] max-sm:h-[2.5em] max-sm:min-w-[2.5em] w-full h-10 min-w-10 max-md:h-10 max-md:w-10 max-w-none md:mr-auto',
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
    <span v-if="!icon && !imageLoaded && !imageFailed && !rectangle && !['match', 'row', 'talent', 'tile', 'large', 'medium', 'tier', 'heading'].includes(size)" :class="['md:hidden block mx-auto rounded-full bg-surface-3', size === 'big' ? 'w-20 h-20 max-sm:w-[2.5em] max-sm:h-[2.5em]' : size === 'xl' ? 'w-[6em] h-[6em]' : 'w-10 h-10 max-sm:w-[2.5em] max-sm:h-[2.5em]']"></span>
    <!-- The name centred on the grey box meanwhile (or for good, if the image failed), on sizes big enough to hold it -->
    <span v-if="!icon && (!imageLoaded || imageFailed) && namedPlaceholder" class="max-md:hidden absolute inset-0 flex items-center justify-center text-center text-xs leading-tight px-2 text-subtext pointer-events-none">{{ title }}</span>
    
    <!-- Rendered under body so scrolling table wrappers can't clip it -->
    <Teleport to="body">
      <div v-if="showTooltip" ref="popup" @mouseover="handleMouseOver" @mouseleave="scheduleHide" :style="tooltipStyle" :class="[
          'fixed text-xs z-[55]',
          // Closes as soon as the pointer leaves the image, even onto the popup
          { 'pointer-events-none': !popupHoverable && !pinned },
        {
          'w-[14em]' : popupsize != 'large' && popupsize != 'xlarge' && popupsize != 'fit' && !hoverBox,
          'w-[25rem] max-w-[90vw]' : popupsize == 'large',
          'w-[32em] max-w-[90vw]' : popupsize == 'xlarge',
          'w-max max-w-[24em]' : popupsize == 'fit',
          // An image-hover-box card: as wide as its rows need
          'w-max min-w-[15rem] max-w-[22rem]' : hoverBox && popupsize != 'large' && popupsize != 'xlarge' && popupsize != 'fit'
        }
        ]" >
        <div v-if="!excludehover" class="popup-text block bg-surface-4 text-sm text-soft text-left p-4 rounded drop-shadow-md z-30">
          <!-- An image-hover-box inside shows these under its header instead -->
          <div v-if="flairBadges.length && !hoverBox" class="flex flex-col gap-1.5">
            <div v-for="badge in flairBadges" :key="badge.key" :class="['flex items-center gap-2.5 rounded-[0.375rem] px-3 py-2 font-medium text-white', badge.class]">
              <!-- The same flair as on the portrait -->
              <span class="w-6 h-6 shrink-0 flex items-center justify-center text-lg leading-none">
                <i v-if="badge.key === 'owner'" class="fas fa-crown owner-crown"></i>
                <i v-else-if="badge.key === 'patreon'" class="fas fa-star text-gold"></i>
                <void-eye-flair v-else-if="badge.key === 'void'" :force="true" :tooltip="false"></void-eye-flair>
                <img v-else-if="badge.key === 'award' && badge.icon" :src="badge.icon" class="w-5 h-5 object-contain" alt="" />
              </span>
              {{ badge.label }}
            </div>
          </div>
          <div ref="hoverContent" class="contents"><slot></slot></div>
          <custom-button v-if="tapped && href" class="mt-2" :href="href" :text="linkText" size="small"></custom-button>
          <custom-button v-else-if="tapped && actionText" class="mt-2" :text="actionText" size="small" :ignoreclick="true" @click="takeAction"></custom-button>
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

import { computed } from 'vue';

export default {
  name: 'RoundImage',
  components: {
  },
  // For an image-hover-box in the popup: the player's flair and the portrait, to lay out in its card
  provide() {
    return {
      popupCard: computed(() => ({ badges: this.flairBadges, image: this.image })),
      claimPopupCard: () => { this.hoverBox = true; },
      // An info icon inside this popup opens over it instead of replacing it
      insidePopup: true,
    };
  },
  inject: {
    insidePopup: { default: false },
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
    // Stays open while the pointer moves onto it, so its links can be clicked and its text copied
    popupHoverable: { type: Boolean, default: false },
    // Grace before closing; defaults to 150ms for a hoverable popup, so a quick move onto it doesn't close it, else none
    hidedelay: {
      type: Number,
      default: null,
    },
    // Phones open the popup on tap instead of following the link, with a button to it
    href: String,
    linkText: String,
    // Phones open the popup on tap with this button, which emits action (the Drafter's Pick / Ban)
    actionText: String,

  },
  emits: ['action'],
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
      // An image-hover-box is in the popup and lays out the flair
      hoverBox: false,
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
    // The popup's flair bars, in this order
    flairBadges() {
      return [
        this.hpowner && { key: 'owner', label: this.$t('Heroes Profile Owner'), class: 'bg-lblue' },
        this.ispatreon && { key: 'patreon', label: this.$t('Patreon Subscriber'), class: 'bg-red' },
        this.voideye && { key: 'void', label: this.$t('Marked by the Void'), class: 'bg-[#4c1d95]' },
        this.award && { key: 'award', label: this.award.title, class: 'bg-teal', icon: this.awardicon, description: this.award.description },
      ].filter(Boolean);
    },
    isInfoIcon() {
      return (this.icon || '').split(' ').includes('fa-info');
    },
    // Name-only portraits and tiles link elsewhere on tap, so phones don't open a popup for them
    noMobileHover() {
      return this.hidemobiletext || this.size === 'tile';
    },
    namedPlaceholder() {
      return ['big', 'xl', 'tile', 'match', 'large'].includes(this.size);
    },
  },
  watch: {
    // Links in a hover open in a new tab
    async showTooltip(shown) {
      if (!shown) return;
      await this.$nextTick();
      this.$refs.hoverContent?.querySelectorAll('a[href]').forEach(link => {
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
      });
    },
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
      } else if (this.actionText && this.isSmallScreen()) {
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
    takeAction() {
      this.hideTooltip();
      this.$emit('action');
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
      const delay = this.hidedelay ?? (this.popupHoverable ? 150 : 0);
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
      if (!this.insidePopup) popupClosed(this.hideTooltip);
      window.removeEventListener('scroll', this.hideTooltip, true);
      document.removeEventListener('click', this.handleOutsideClick, true);
      document.removeEventListener('keydown', this.handleEscape);
    },
    handleMouseOver() {
      // Phones open the popup on tap; name-only portraits and tiles skip it
      if (this.noMobileHover && !this.mobileClick && !this.href && this.isSmallScreen()) return;

      clearTimeout(this.hideTimer);
      if (this.showTooltip) return;
      if (!this.insidePopup) popupOpened(this.hideTooltip);
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