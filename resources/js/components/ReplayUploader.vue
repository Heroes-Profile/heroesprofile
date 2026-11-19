<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'Upload replays from your browser, or install the uploader and have it done for you as you play.'" -->
    <page-heading
      heading="Upload Replays"
    ></page-heading>
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 -mt-2 mb-4">
      <span class="uppercase text-gray-medium">{{ $t('Send your games to Heroes Profile') }}</span>
    </div>

    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 grid gap-6 xl:grid-cols-[1288fr_600fr] items-start">

      <div class="bg-surface rounded p-4 md:p-8 flex flex-col gap-7 min-w-0">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-control bg-surface-1 border-l-4 border-teal px-5 py-3 text-sm">
          <basil-icon name="info" class="text-teal text-xl shrink-0"></basil-icon>
          <span class="flex-1 min-w-0">{{ $t('The uploader has been rewritten for Windows, macOS and Linux.') }}</span>
          <a class="group/bt inline-flex items-center" href="/Upload/Uploader"><span class="group-hover/bt:underline">{{ $t("See what's new") }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
        </div>

        <div class="grid md:grid-cols-[1fr_auto] gap-8 items-center">
          <div class="flex flex-col gap-4 min-w-0">
            <div class="flex flex-wrap items-center gap-3">
              <h2 class="text-2xl font-bold">{{ $t('Replay Uploader') }}</h2>
              <span class="bg-teal text-white text-xs font-bold uppercase rounded-control px-2.5 py-1">{{ $t('Recommended') }}</span>
            </div>
            <p class="text-subtext">{{ $t('Watches your replay folder and uploads each game as you finish it.') }}</p>
            <ul class="flex flex-col gap-4">
              <li v-for="feature in features" :key="feature" class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-[0.25rem] bg-teal flex items-center justify-center shrink-0"><basil-icon name="check" class="text-xs text-white"></basil-icon></span>
                <span class="font-medium">{{ $t(feature) }}</span>
              </li>
            </ul>
          </div>
          <img
            src="/images/miscellaneous/uploader.png"
            alt="The Heroes Profile replay uploader"
            class="w-full md:w-[23.75rem] rounded-control"
          />
        </div>

        <div class="h-px bg-gray-dark"></div>

        <h3 class="flex items-center gap-3 text-xl font-bold"><basil-icon name="download" class="text-2xl"></basil-icon>{{ $t('Downloads') }}</h3>

        <uploader-downloads :show-ranks="true" :links="false"></uploader-downloads>

        <div class="flex flex-col gap-2.5 text-sm">
          <p class="text-subtext">{{ $t('"With rank reading" is the same uploader plus an opt-in reader for the Storm League loading screen, which helps us get real in-game ranks onto Heroes Profile.') }}</p>
          <div class="flex flex-wrap gap-x-6 gap-y-2">
            <a class="group/bt inline-flex items-center" href="/Upload/Uploader#rank-reading"><span class="group-hover/bt:underline">{{ $t('How rank reading works') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
            <a class="group/bt inline-flex items-center" :href="repository + '/releases/latest'" target="_blank" rel="noopener"><span class="group-hover/bt:underline">{{ $t('Latest release') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
            <a class="group/bt inline-flex items-center" :href="repository + '/blob/master/packaging/INSTALL.md'" target="_blank" rel="noopener"><span class="group-hover/bt:underline">{{ $t('Install instructions') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
            <a class="group/bt inline-flex items-center" :href="repository" target="_blank" rel="noopener"><span class="group-hover/bt:underline">{{ $t('Source on GitHub') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
          </div>
        </div>
      </div>

      <div class="bg-surface rounded p-4 md:p-8 flex flex-col gap-6 min-w-0">
        <div class="flex flex-col gap-3">
          <h2 class="text-2xl font-bold">{{ $t('Web Uploader') }}</h2>
          <p class="text-sm text-subtext">{{ $t('Upload replay files right from your browser.') }}</p>
        </div>

        <replay-upload-dropzone :upload-url="uploadUrl" :max-bytes="maxBytes"></replay-upload-dropzone>

        <a class="group/bt inline-flex items-center text-sm w-fit" href="/Upload/Widget"><span class="group-hover/bt:underline">{{ $t('Embed this uploader') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
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
      features: ['Uploads automatically after every game', 'Pre-match and post-match analysis', 'Games count towards the leaderboards'],
    }
  },
}
</script>
