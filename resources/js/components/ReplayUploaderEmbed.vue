<template>
  <div ref="card" class="p-4" :style="cardStyle">
    <a class="flex justify-center items-center font-logo text-2xl mb-4" href="/" target="_blank" rel="noopener">
      Heroes
      <img :class="['w-10 mx-2', { '-translate-y-[8%]': eventRunning }]" :src="logoSrc" alt="Heroes Profile Logo" />
      Profile
    </a>

    <replay-upload-dropzone
      :upload-url="uploadUrl"
      :max-bytes="maxBytes"
      :new-tab="true"
      @uploaded="onUploaded"
      @finished="onFinished"
    ></replay-upload-dropzone>

    <p class="text-xs text-gray-medium mt-4">
      Upload every game automatically with the
      <a class="link" href="/Upload" target="_blank" rel="noopener">Heroes Profile desktop uploader</a>.
    </p>

    <p class="text-xs text-gray-medium mt-1">
      Powered by <a class="link" href="/" target="_blank" rel="noopener">Heroes Profile</a>
    </p>
  </div>
</template>

<script>
/*
 * /Upload/Embed, framed by other sites. Tells the host page about each replay,
 * and its own height so the frame can fit it, with postMessage; nothing in it
 * is private, so any origin may listen.
 */
export default {
  name: 'ReplayUploaderEmbed',
  props: {
    uploadUrl: String,
    maxBytes: Number,
    // Xal'atath event stage, null when the event isn't running.
    voidStage: {
      type: Number,
      default: null,
    },
  },
  computed: {
    eventRunning() {
      return this.voidStage !== null;
    },
    logoSrc() {
      return this.eventRunning
        ? `/images/event/xalatath/xalatath-logo-stage-${this.voidStage}.svg`
        : '/images/logo/heroesprofilelogo.png';
    },
    // The site's stage 1 glow and stage 4 palette, on the card: the page behind it is the host's.
    cardStyle() {
      const style = { backgroundColor: '#0F121D' };

      if (this.voidStage >= 1) {
        // Inset: the card fills the frame, so an outer glow would be clipped.
        style.boxShadow = 'inset 0 0 22px rgba(123, 63, 228, 0.45)';
      }

      if (this.voidStage >= 4) {
        style.backgroundColor = '#110822';
        style.backgroundImage = 'radial-gradient(ellipse at top, rgba(91, 33, 182, 0.35), transparent 60%)';
      }

      return style;
    },
  },
  mounted() {
    this.observer = new ResizeObserver(() => {
      this.notify({ type: 'heroesprofile:resize', height: Math.ceil(this.$refs.card.getBoundingClientRect().height) });
    });
    this.observer.observe(this.$refs.card);
  },
  beforeUnmount() {
    this.observer.disconnect();
  },
  methods: {
    onUploaded(file) {
      this.notify({
        type: 'heroesprofile:upload',
        file: file.name,
        status: file.status,
        replayID: file.replayID,
      });
    },

    onFinished(totals) {
      this.notify({ type: 'heroesprofile:upload-complete', ...totals });
    },

    notify(message) {
      if (window.parent !== window) {
        window.parent.postMessage(message, '*');
      }
    },
  },
}
</script>
