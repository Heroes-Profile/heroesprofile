<template>
  <div>
    <page-heading
      heading="Upload Replays"
      :infoText1="'Upload replays from your browser, or install the uploader and have it done for you as you play.'"
    ></page-heading>

    <div class="mx-auto max-w-[1300px] px-4 mt-6 grid gap-6 md:grid-cols-2 items-start">

      <div class="bg-lighten p-6">
        <h2 class="text-2xl mb-4">Replay Uploader</h2>

        <img
          src="/images/miscellaneous/uploader.png"
          alt="The Heroes Profile replay uploader"
          class="w-full mb-4"
        />

        <p class="text-sm mb-4">
          Watches your replay folder and uploads each game as you finish it, with pre-match and
          post-match analysis. Games uploaded this way count towards the leaderboards.
        </p>

        <p class="text-sm mb-4">
          The uploader has been rewritten for Windows, macOS and Linux.
          <a class="link" href="/Upload/Uploader">See what's new and how to move over from the old one</a>.
        </p>

        <uploader-downloads class="text-sm mb-4" :show-ranks="true"></uploader-downloads>

        <p class="text-sm mb-4">
          <strong>Windows, with rank reading</strong> is the same uploader plus an opt-in reader for the Storm
          League loading screen, which helps us get real in-game ranks onto Heroes Profile.
          <a class="link" href="/Upload/Uploader#rank-reading">How rank reading works</a>.
        </p>

        <p class="text-sm">
          Source
          <a class="link block" :href="repository" target="_blank" rel="noopener">{{ repository }}</a>
        </p>
      </div>

      <div class="bg-lighten p-6">
        <h2 class="text-2xl mb-4">Web Uploader</h2>

        <replay-upload-dropzone :upload-url="uploadUrl" :max-bytes="maxBytes"></replay-upload-dropzone>

        <p class="text-xs text-gray-medium mt-4">
          <a class="link" href="/Upload/Widget">Embed this uploader</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReplayUploader',
  props: {
    uploadUrl: String,
    maxBytes: Number,
  },
  data() {
    return {
      repository: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader',
    }
  },
}
</script>
