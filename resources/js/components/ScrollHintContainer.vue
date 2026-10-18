<template>
  <div class="relative">
    <div ref="scroller" :class="['table-container w-full', fitting ? 'overflow-hidden' : 'overflow-x-auto']" @scroll="updateHints">
      <div v-if="fit" ref="content"><slot></slot></div>
      <slot v-else></slot>
    </div>

    <div v-show="canScrollLeft" class="pointer-events-none absolute inset-y-0 left-0 w-12" :style="{ background: 'linear-gradient(to right, rgba(15, 18, 29, 0.9), rgba(15, 18, 29, 0))' }"></div>
    <div v-show="canScrollRight" class="pointer-events-none absolute inset-y-0 right-0 w-20" :style="{ background: 'linear-gradient(to left, rgba(15, 18, 29, 0.9), rgba(15, 18, 29, 0))' }"></div>

    <button v-show="canScrollLeft" type="button" aria-label="Scroll left" class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-blue hover:bg-lblue text-white flex items-center justify-center drop-shadow-md" @click="scrollLeft">
      <i class="fas fa-chevron-left"></i>
    </button>
    <button v-show="canScrollRight" type="button" aria-label="Scroll right" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-blue hover:bg-lblue text-white flex items-center justify-center drop-shadow-md" @click="scrollRight">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>
</template>

<script>
import { scaleTableToFit } from '../utils/scaleTable';

export default {
  name: 'ScrollHintContainer',
  components: {
  },
  props: {
    // Phones shrink the content to fit instead of scrolling
    fit: Boolean,
  },
  data(){
    return {
      canScrollLeft: false,
      canScrollRight: false,
      resizeObserver: null,
      fitting: this.fit && window.matchMedia('(max-width: 767px)').matches,
    }
  },
  created(){
  },
  mounted() {
    if (this.fitting) {
      scaleTableToFit(this.$refs.content, this.$refs.scroller);
      return;
    }
    // Content width changes when tables flip or get scaled, not just on window resize
    this.resizeObserver = new ResizeObserver(() => this.updateHints());
    this.resizeObserver.observe(this.$refs.scroller);
    Array.from(this.$refs.scroller.children).forEach(child => this.resizeObserver.observe(child));
    this.updateHints();
  },
  updated() {
    if (this.fitting) {
      scaleTableToFit(this.$refs.content, this.$refs.scroller);
      return;
    }
    this.updateHints();
  },
  beforeUnmount() {
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
  },
  computed: {
  },
  watch: {
  },
  methods: {
    updateHints() {
      const scroller = this.$refs.scroller;
      if (!scroller) return;

      // 1px slack for fractional widths
      this.canScrollLeft = scroller.scrollLeft > 1;
      this.canScrollRight = scroller.scrollLeft + scroller.clientWidth < scroller.scrollWidth - 1;
    },
    scrollLeft() {
      const scroller = this.$refs.scroller;
      scroller.scrollBy({ left: -scroller.clientWidth * 0.8, behavior: 'smooth' });
    },
    scrollRight() {
      const scroller = this.$refs.scroller;
      scroller.scrollBy({ left: scroller.clientWidth * 0.8, behavior: 'smooth' });
    },
  }
}
</script>
