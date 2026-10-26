<template>
  <div class="space-y-4">
    <div>
      <h3 class="mb-2">Windows</h3>
      <div class="flex flex-wrap gap-2">
        <custom-button :href="link('HeroesProfileUploaderSetup.exe')" text="Download for Windows" alt="Download the Windows replay uploader" color="teal" :targetblank="true"></custom-button>
        <custom-button v-if="showRanks" :href="link('Heroesprofile.Uploader-win-ranks-Setup.exe')" text="Windows, with rank reading" alt="Download the Windows replay uploader with rank reading" color="teal" :targetblank="true"></custom-button>
      </div>
    </div>

    <div>
      <h3 class="mb-2">macOS</h3>
      <div class="flex flex-wrap gap-2">
        <custom-button :href="link('Heroesprofile.Uploader-osx-arm64-Setup.pkg')" text="Apple Silicon (M1 and later)" alt="Download the macOS replay uploader for Apple Silicon" color="teal" :targetblank="true"></custom-button>
        <custom-button :href="link('Heroesprofile.Uploader-osx-x64-Setup.pkg')" text="Intel" alt="Download the macOS replay uploader for Intel Macs" color="teal" :targetblank="true"></custom-button>
        <custom-button v-if="showRanks" :href="link('Heroesprofile.Uploader-osx-arm64-ranks-Setup.pkg')" text="Apple Silicon, with rank reading" alt="Download the macOS replay uploader with rank reading for Apple Silicon" color="teal" :targetblank="true"></custom-button>
        <custom-button v-if="showRanks" :href="link('Heroesprofile.Uploader-osx-x64-ranks-Setup.pkg')" text="Intel, with rank reading" alt="Download the macOS replay uploader with rank reading for Intel Macs" color="teal" :targetblank="true"></custom-button>
      </div>
      <p class="text-xs text-gray-medium mt-2">
        The Mac builds aren't signed yet while we wait on Apple. The first time, right-click the installer
        and choose <strong>Open</strong>, or allow it under System Settings → Privacy &amp; Security.
      </p>
    </div>

    <div>
      <h3 class="mb-2">Linux</h3>
      <div class="flex flex-wrap gap-2">
        <custom-button :href="link('Heroesprofile.Uploader.AppImage')" text="AppImage" alt="Download the Linux replay uploader (AppImage)" color="teal" :targetblank="true"></custom-button>
        <custom-button v-if="showRanks" :href="link('Heroesprofile.Uploader-linux-ranks.AppImage')" text="AppImage, with rank reading" alt="Download the Linux replay uploader with rank reading (AppImage)" color="teal" :targetblank="true"></custom-button>
        <custom-button :href="link('HeroesProfileUploader-linux-x64.tar.gz')" text="tar.gz (headless)" alt="Download the Linux replay uploader as a tar.gz" color="teal" :targetblank="true"></custom-button>
      </div>
    </div>

    <p class="text-xs text-gray-medium">
      All files are on the
      <a class="link" :href="repository + '/releases/latest'" target="_blank" rel="noopener">latest release</a>, with
      <a class="link" :href="repository + '/blob/master/packaging/INSTALL.md'" target="_blank" rel="noopener">install instructions</a>.
    </p>
  </div>
</template>

<script>
export default {
  name: 'UploaderDownloads',
  props: {
    showRanks: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      repository: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader',
    }
  },
  methods: {
    link(file) {
      return `${this.repository}/releases/latest/download/${file}`;
    },
  },
}
</script>
