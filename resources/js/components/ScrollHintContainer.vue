<template>
  <div class="relative">
    <!-- The fades and arrows sit above a table's sticky first column -->
    <div ref="scroller" :class="['table-container w-full', fitting ? 'overflow-hidden' : 'overflow-x-auto', { 'max-md:snap-x max-md:snap-mandatory': snap }]" @scroll="updateHints">
      <div v-if="fit" ref="content"><slot></slot></div>
      <slot v-else></slot>
    </div>

    <div v-show="canScrollLeft" class="pointer-events-none absolute z-[2] inset-y-0 left-0 w-12" :style="{ background: 'linear-gradient(to right, rgba(15, 18, 29, 0.9), rgba(15, 18, 29, 0))' }"></div>
    <div v-show="canScrollRight" class="pointer-events-none absolute z-[2] inset-y-0 right-0 w-20" :style="{ background: 'linear-gradient(to left, rgba(15, 18, 29, 0.9), rgba(15, 18, 29, 0))' }"></div>

    <button v-show="canScrollLeft" type="button" :aria-label="$t('Scroll left')" class="absolute z-[2] left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-blue hover:bg-lblue text-white flex items-center justify-center drop-shadow-md" @click="scrollLeft">
      <basil-icon name="arrow-small" class="rotate-180"></basil-icon>
    </button>
    <button v-show="canScrollRight" type="button" :aria-label="$t('Scroll right')" class="absolute z-[2] right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-blue hover:bg-lblue text-white flex items-center justify-center drop-shadow-md" @click="scrollRight">
      <basil-icon name="arrow-small"></basil-icon>
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
    // Content that isn't a table: phones snap to its .snap-start columns, and the arrows step from one to the next
    snap: Boolean,
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
    // A table's column edges, and how much of the left its sticky first column covers.
    // A header cell with data-scroll-join belongs to the column before it (a chevron beside its value), so it's no stop of its own
    columns() {
      if (this.snap) {
        const scrollerLeft = this.$refs.scroller.getBoundingClientRect().left - this.$refs.scroller.scrollLeft;
        const edges = Array.from(this.$refs.scroller.querySelectorAll('.snap-start')).map(item => item.getBoundingClientRect().left - scrollerLeft);
        return edges.length ? { covered: 0, edges: edges.sort((a, b) => a - b) } : null;
      }
      const row = this.$refs.scroller.querySelector('thead tr, tr');
      if (!row) {
        return null;
      }
      const cells = Array.from(row.cells);
      const sticky = cells.filter(cell => getComputedStyle(cell).position === 'sticky');
      return {
        covered: sticky.reduce((width, cell) => width + cell.offsetWidth, 0),
        edges: cells.filter(cell => !sticky.includes(cell) && !cell.hasAttribute('data-scroll-join')).map(cell => cell.offsetLeft),
      };
    },
    // A table moves one column at a time, lining it up beside the sticky column; anything else by most of a screen
    scrollLeft() {
      const scroller = this.$refs.scroller;
      const table = this.columns();
      if (!table) {
        scroller.scrollBy({ left: -scroller.clientWidth * 0.8, behavior: 'smooth' });
        return;
      }
      const edge = table.edges.filter(left => left - table.covered < scroller.scrollLeft - 1).pop();
      scroller.scrollTo({ left: edge === undefined ? 0 : edge - table.covered, behavior: 'smooth' });
    },
    scrollRight() {
      const scroller = this.$refs.scroller;
      const table = this.columns();
      if (!table) {
        scroller.scrollBy({ left: scroller.clientWidth * 0.8, behavior: 'smooth' });
        return;
      }
      const edge = table.edges.find(left => left - table.covered > scroller.scrollLeft + 1);
      scroller.scrollTo({ left: edge === undefined ? scroller.scrollWidth : edge - table.covered, behavior: 'smooth' });
    },
  }
}
</script>
