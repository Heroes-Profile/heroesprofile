<template>
  <div class="p-4">
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
 * /Upload/Embed, framed by other sites. Tells the host page about each replay
 * with postMessage; nothing in it is private, so any origin may listen.
 */
export default {
  name: 'ReplayUploaderEmbed',
  props: {
    uploadUrl: String,
    maxBytes: Number,
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
