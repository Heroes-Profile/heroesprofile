<template>
  <transition name="void-whisper">
    <div v-if="current" ref="whisper" :style="placement" class="void-whisper rounded text-sm italic md:absolute md:left-1/2 md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:z-30 md:px-4 md:py-1 md:text-center md:leading-snug max-md:fixed max-md:bottom-6 max-md:left-6 max-md:z-30 max-md:max-w-xs max-md:px-4 max-md:py-3">
      {{ current }}
    </div>
  </transition>
</template>

<script>
import WHISPERS from './voidWhisperLines';

export default {
  name: 'VoidWhispers',
  props: {
    stage: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      current: null,
      timers: [],
      running: false,
      placement: {},
    };
  },
  mounted() {
    window.addEventListener('void-stage-changed', this.onStageChanged);
    this.start(this.stage);
  },
  beforeUnmount() {
    window.removeEventListener('void-stage-changed', this.onStageChanged);
    this.timers.forEach(clearTimeout);
  },
  methods: {
    onStageChanged(event) {
      this.start(event.detail.stage);
    },
    // Whispers begin at stage 2.
    start(stage) {
      if (this.running || stage < 2) {
        return;
      }
      this.running = true;
      this.schedule(5000);
    },
    // Centre on the page, but slide left (and wrap if needed) so it never covers the battletags/search in the row.
    place() {
      const el = this.$refs.whisper;
      const row = el && el.parentElement;
      if (!el || !row || window.innerWidth < 768) {
        return;
      }

      const gap = 16;
      const rowRect = row.getBoundingClientRect();
      const others = Array.from(row.children)
        .filter((child) => child !== el)
        .map((child) => child.getBoundingClientRect())
        .filter((rect) => rect.width > 0);
      const rightLimit = (others.length ? Math.min(...others.map((rect) => rect.left)) : rowRect.right) - rowRect.left - gap;
      const available = Math.max(rightLimit - gap, 120);

      // Measure at its natural width (from the left edge so it isn't squeezed), capped to the free space.
      el.style.left = '0px';
      el.style.maxWidth = `${available}px`;
      el.style.transform = 'translateY(-50%)';
      const width = el.getBoundingClientRect().width;

      const pageCentre = window.innerWidth / 2 - rowRect.left;
      const left = Math.min(Math.max(pageCentre - width / 2, gap), rightLimit - width);

      this.placement = { left: `${Math.max(left, gap)}px`, maxWidth: `${available}px`, transform: 'translateY(-50%)' };
    },
    schedule(delay) {
      this.timers.push(setTimeout(() => {
        this.placement = {};
        this.current = WHISPERS[Math.floor(Math.random() * WHISPERS.length)];
        this.$nextTick(this.place);
        // VoidGlitch syncs the nav to these.
        window.dispatchEvent(new CustomEvent('void-whisper-show'));
        this.timers.push(setTimeout(() => {
          this.current = null;
          window.dispatchEvent(new CustomEvent('void-whisper-hide'));
          this.schedule(30000 + Math.random() * 10000);
        }, 5000));
      }, delay));
    },
  },
};
</script>
