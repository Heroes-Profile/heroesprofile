<template>
  <transition name="void-stage-up">
    <div v-if="visible" class="void-stage-up fixed inset-0 z-[9999] flex items-center justify-center px-6">
      <div :class="['void-stage-up-card relative text-center text-white rounded-lg px-8 py-8 max-w-lg w-full', { 'void-stage-up-shake': shaking }]">
        <!-- Fixed aspect box (frames are 1000x1042) so nothing jumps while the images load -->
        <div class="void-stage-up-logo relative mx-auto mb-6 w-56 md:w-64 aspect-[1000/1042]">
          <img class="absolute inset-0 block w-full h-full" :src="frame(from)" alt="" />
          <img :class="['absolute inset-0 block w-full h-full void-stage-up-reveal', { 'is-revealed': revealed }]" :src="frame(to)" alt="" />
        </div>
        <p class="text-sm uppercase tracking-widest opacity-75 mb-2">{{ away ? 'While you were away' : 'The Void spreads' }}</p>
        <h2 class="font-logo text-3xl md:text-4xl mb-3">Stage {{ to }} of 5</h2>
        <p class="italic mb-6">{{ line }}</p>
        <custom-button :text="'Continue'" :size="'small'" :ignoreclick="true" class="px-8" @click="close"></custom-button>
      </div>
    </div>
  </transition>
</template>

<script>
const STORAGE_KEY = 'voidLastSeenStage';

const LINES = [
  'Another crack in your precious Nexus.',
  'You played. I grew. How generous of you.',
  'Every game you queue pulls me closer.',
  'Your logo was so tidy. Was.',
  'Keep uploading. I am almost through.',
];

export default {
  name: 'VoidStageUp',
  props: {
    stage: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      visible: false,
      away: false,
      from: this.stage,
      to: this.stage,
      revealed: false,
      shaking: false,
      line: '',
      timers: [],
    };
  },
  mounted() {
    window.addEventListener('void-stage-up', this.onLiveStageUp);

    let lastSeen = null;
    try {
      lastSeen = localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      // storage blocked; no returning-visitor popup
    }

    // Stage 5 has its own splash.
    if (lastSeen !== null && Number(lastSeen) < this.stage && this.stage < 5) {
      this.play(Number(lastSeen), this.stage, true);
    } else {
      this.remember(this.stage);
    }
  },
  beforeUnmount() {
    window.removeEventListener('void-stage-up', this.onLiveStageUp);
    this.timers.forEach(clearTimeout);
  },
  methods: {
    // PNG here: the reveal animates over two full frames, and the SVG glow is costly to redraw every frame.
    frame(stage) {
      return `/images/event/xalatath/xalatath-logo-stage-${stage}.png`;
    },
    svgFrame(stage) {
      return `/images/event/xalatath/xalatath-logo-stage-${stage}.svg`;
    },
    onLiveStageUp(event) {
      const { from, to } = event.detail;
      if (to >= 5) {
        this.applyStage(to);
        return;
      }
      this.play(from, to, false);
    },
    async play(from, to, away) {
      this.timers.forEach(clearTimeout);
      this.from = from;
      this.to = to;
      this.away = away;
      this.revealed = false;
      this.line = LINES[Math.floor(Math.random() * LINES.length)];
      this.visible = true;

      // Don't start the reveal over images that haven't arrived yet.
      await Promise.all([this.preload(this.frame(from)), this.preload(this.frame(to))]);

      const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      this.timers.push(setTimeout(() => {
        this.revealed = true;
        if (!reduced) {
          this.shaking = true;
          this.timers.push(setTimeout(() => { this.shaking = false; }, 600));
        }
      }, 500));
    },
    preload(src) {
      return new Promise((resolve) => {
        const img = new Image();
        const done = () => resolve();
        img.onload = done;
        img.onerror = done;
        setTimeout(done, 1500);
        img.src = src;
      });
    },
    close() {
      if (!this.visible) {
        return;
      }
      this.timers.forEach(clearTimeout);
      this.visible = false;
      this.applyStage(this.to);
    },
    remember(stage) {
      try {
        localStorage.setItem(STORAGE_KEY, String(stage));
      } catch (e) {
        // storage blocked
      }
    },
    // Switch the page underneath to the new stage without a reload.
    applyStage(stage) {
      this.remember(stage);

      document.querySelectorAll('.js-void-logo').forEach((img) => {
        img.src = this.svgFrame(stage);
      });
      document.querySelectorAll('link[data-void-favicon]').forEach((link) => {
        link.href = link.href.replace(/xalatath-logo-stage-\d/, `xalatath-logo-stage-${stage}`);
      });
      Array.from(document.body.classList).forEach((cls) => {
        if (cls.startsWith('void-stage-')) {
          document.body.classList.remove(cls);
        }
      });
      if (stage > 0) {
        document.body.classList.add(`void-stage-${stage}`);
      }

      window.dispatchEvent(new CustomEvent('void-stage-changed', { detail: { stage } }));
    },
  },
};
</script>
