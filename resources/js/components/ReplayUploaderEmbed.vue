<template>
  <!-- The Upload page's Web Uploader card, with the Heroes Profile logo, linking home, in front of its title -->
  <div ref="card" class="flex justify-center">
    <div class="w-full max-w-[36rem] bg-surface rounded-panel p-4 md:p-8 flex flex-col gap-6 min-w-0">
      <div class="flex flex-col gap-3">
        <div class="flex items-center gap-4">
          <a class="shrink-0" href="/" target="_blank" rel="noopener" title="Heroes Profile"><img class="w-14" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" /></a>
          <h2 class="text-2xl font-bold">{{ $t('Web Uploader') }}</h2>
        </div>
        <p class="text-sm text-subtext">{{ $t('Upload replay files to Heroes Profile right from your browser.') }}</p>
      </div>

      <replay-upload-dropzone
        :upload-url="uploadUrl"
        :max-bytes="maxBytes"
        :new-tab="true"
        @uploaded="onUploaded"
        @finished="onFinished"
      ></replay-upload-dropzone>

      <p class="text-xs text-subtext">
        Upload every game automatically with the
        <a class="link" href="/Upload" target="_blank" rel="noopener">Heroes Profile desktop uploader</a>.
      </p>
    </div>
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
