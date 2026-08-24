<template>
  <button
    v-show="visible"
    class="fixed bottom-6 right-6 z-40 bg-teal hover:bg-lteal transition-colors text-white rounded-full w-12 h-12 shadow-sm"
    title="Back to top"
    aria-label="Back to top"
    @click="toTop"
  >
    <i class="fas fa-arrow-up"></i>
  </button>
</template>

<script>
/*
 * Appears once the page has scrolled far enough that the top is out of reach.
 * Long reference pages otherwise mean a lot of scrolling to get back to the
 * filter or the section list.
 */
export default {
  name: 'ScrollToTop',
  props: {
    /* How far down before it appears, in pixels. */
    after: {
      type: Number,
      default: 600,
    },
  },
  data() {
    return {
      visible: false,
    }
  },
  mounted() {
    // Passive: this only reads scroll position and never blocks it.
    window.addEventListener('scroll', this.onScroll, { passive: true });
    this.onScroll();
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.onScroll);
  },
  methods: {
    onScroll() {
      this.visible = window.scrollY > this.after;
    },

    toTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
  },
}
</script>
