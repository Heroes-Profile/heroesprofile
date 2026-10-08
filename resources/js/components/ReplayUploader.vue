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
          src="/images/miscellaneous/windowsUploader.PNG"
          alt="The Heroes Profile replay uploader running on Windows"
          class="w-full mb-4"
        />

        <p class="text-sm mb-4">
          Watches your replay folder and uploads each game as you finish it, with pre-match and
          post-match analysis. Games uploaded this way count towards the leaderboards.
        </p>

        <div class="mb-4 flex flex-wrap gap-2">
          <custom-button
            :href="windowsInstaller"
            text="Download for Windows"
            alt="Download the Windows replay uploader"
            color="teal"
            :targetblank="true"
          ></custom-button>
          <custom-button
            :href="linuxAppImage"
            text="Download for Linux"
            alt="Download the Linux replay uploader (AppImage)"
            color="teal"
            :targetblank="true"
          ></custom-button>
        </div>

        <p class="text-sm mb-4">
          Linux is also available as a <a class="link" :href="linuxTarball" target="_blank" rel="noopener">tar.gz</a>,
          which includes the headless CLI.
        </p>

        <p class="text-sm mb-2">
          macOS
          <a class="link block" :href="electronRelease" target="_blank" rel="noopener">{{ electronRelease }}</a>
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
      windowsInstaller: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploaderSetup.exe',
      linuxAppImage: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploader-linux-x86_64.AppImage',
      linuxTarball: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploader-linux-x64.tar.gz',
      electronRelease: 'https://github.com/Heroes-Profile/heroesprofile-electron-uploader/releases',
      repository: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader',
    }
  },
}
</script>
