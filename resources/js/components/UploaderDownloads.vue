<template>
  <div class="flex flex-col gap-4">
    <!-- One card per platform: its downloads, then the rank reading builds as links -->
    <div class="grid md:grid-cols-3 gap-4">
      <div v-for="platform in platforms" :key="platform.name" class="bg-surface-1 rounded-control p-6 flex flex-col gap-4 min-w-0">
        <h4 class="flex items-center gap-3 text-xl font-bold"><i :class="[platform.icon, 'text-[1.75rem] w-7 text-center']"></i>{{ $t(platform.name) }}</h4>
        <div class="flex flex-col gap-2.5">
          <custom-button v-for="file in platform.files" :key="file.file" :href="link(file.file)" :text="file.text" :alt="file.alt" color="teal" :targetblank="true"></custom-button>
        </div>
        <div v-if="showRanks" class="flex flex-col gap-1.5 text-sm">
          <span class="text-xs uppercase text-subtext">{{ $t('With rank reading') }}</span>
          <a v-for="file in platform.ranks" :key="file.file" class="group/bt inline-flex items-center w-fit" :href="link(file.file)" target="_blank" rel="noopener" :title="file.alt"><span class="group-hover/bt:underline">{{ $t(file.text) }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
        </div>
      </div>
    </div>

    <p v-if="links" class="text-xs text-gray-medium">
      {{ $t('All files are on the') }}
      <a class="link" :href="repository + '/releases/latest'" target="_blank" rel="noopener">{{ $t('latest release') }}</a>{{ $t(', with') }}
      <a class="link" :href="repository + '/blob/master/packaging/INSTALL.md'" target="_blank" rel="noopener">{{ $t('install instructions') }}</a>.
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
    // The release and install instructions line under the cards
    links: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      repository: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader',
      platforms: [
        {
          name: 'macOS',
          icon: 'fab fa-apple',
          files: [
            { file: 'Heroesprofile.Uploader-osx-arm64-Setup.pkg', text: 'Apple Silicon (M1 and later)', alt: 'Download the macOS replay uploader for Apple Silicon' },
            { file: 'Heroesprofile.Uploader-osx-x64-Setup.pkg', text: 'Intel', alt: 'Download the macOS replay uploader for Intel Macs' },
          ],
          ranks: [
            { file: 'Heroesprofile.Uploader-osx-arm64-ranks-Setup.pkg', text: 'Apple Silicon', alt: 'Download the macOS replay uploader with rank reading for Apple Silicon' },
            { file: 'Heroesprofile.Uploader-osx-x64-ranks-Setup.pkg', text: 'Intel', alt: 'Download the macOS replay uploader with rank reading for Intel Macs' },
          ],
        },
        {
          name: 'Linux',
          icon: 'fab fa-linux',
          files: [
            { file: 'Heroesprofile.Uploader.AppImage', text: 'AppImage', alt: 'Download the Linux replay uploader (AppImage)' },
            { file: 'HeroesProfileUploader-linux-x64.tar.gz', text: 'tar.gz (headless)', alt: 'Download the Linux replay uploader as a tar.gz' },
          ],
          ranks: [
            { file: 'Heroesprofile.Uploader-linux-ranks.AppImage', text: 'AppImage', alt: 'Download the Linux replay uploader with rank reading (AppImage)' },
          ],
        },
        {
          name: 'Windows',
          icon: 'fab fa-windows',
          files: [
            { file: 'HeroesProfileUploaderSetup.exe', text: 'Download for Windows', alt: 'Download the Windows replay uploader' },
          ],
          ranks: [
            { file: 'Heroesprofile.Uploader-win-ranks-Setup.exe', text: 'Windows', alt: 'Download the Windows replay uploader with rank reading' },
          ],
        },
      ],
    }
  },
  methods: {
    link(file) {
      return `${this.repository}/releases/latest/download/${file}`;
    },
  },
}
</script>
