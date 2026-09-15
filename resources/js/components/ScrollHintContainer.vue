<template>
  <div class="relative">
    <div ref="scroller" class="table-container w-full overflow-x-auto" @scroll="updateHints">
      <slot></slot>
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
export default {
  name: 'ScrollHintContainer',
  components: {
  },
  props: {
  },
  data(){
    return {
      canScrollLeft: false,
      canScrollRight: false,
      resizeObserver: null,
    }
  },
  created(){
  },
  mounted() {
    // Content width changes when tables flip or get scaled, not just on window resize
    this.resizeObserver = new ResizeObserver(() => this.updateHints());
    this.resizeObserver.observe(this.$refs.scroller);
    Array.from(this.$refs.scroller.children).forEach(child => this.resizeObserver.observe(child));
    this.updateHints();
  },
  updated() {
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
