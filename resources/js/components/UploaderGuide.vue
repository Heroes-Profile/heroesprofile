<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading :heading="'The New Replay Uploader'"></page-heading>

    <div class="mx-auto max-w-[56.25rem] px-4 mt-6 mb-10 space-y-6">
      <div class="bg-lighten rounded p-6 text-sm space-y-4">
        <p>
          {{ $t("The Heroes Profile uploader has been rewritten. It's now one app for Windows, macOS and Linux, and it replaces the old Windows-only uploader. It looks and works the same: it watches your replay folder and uploads each game when it finishes, with the pre-match and post-match pages.") }}
        </p>

        <img
          src="/images/miscellaneous/uploader.png"
          alt="The Heroes Profile replay uploader"
          class="w-full rounded-[0.5rem]"
        />
      </div>

      <div class="bg-lighten rounded p-6 text-sm space-y-4">
        <h2 class="text-lg">{{ $t('Moving from the old uploader') }}</h2>
        <p>{{ $t("The old uploader won't update itself to the new one, so this is a one-time install.") }}</p>
        <ol class="list-decimal list-inside space-y-1">
          <li>{{ $t('Download and install the new uploader below. It installs for your user only, so no admin is needed.') }}</li>
          <li>{{ $t('On its first start it copies your settings from the old uploader, including your Twitch key.') }}</li>
          <li>{{ $t("It also copies the old uploader's record of what's already been uploaded, so nothing is uploaded twice.") }}</li>
          <li>{{ $t('It then offers to uninstall the old uploader. Say yes, or remove it later from Settings → Apps ("Heroesprofile").') }}</li>
        </ol>
        <p>
          {{ $t("Don't run both at once, or they'll both upload every game. The new one warns you if the old one is still running.") }}
        </p>
      </div>

      <div class="bg-lighten rounded p-6 text-sm space-y-4">
        <h2 class="text-lg">{{ $t("What's new") }}</h2>
        <ul class="list-disc list-inside space-y-1">
          <li>{{ $t('Windows, macOS (Apple Silicon and Intel) and Linux, all from the same app.') }}</li>
          <li>{{ $t("On Linux, point it at your Wine/Proton prefix. There's also a plain tar.gz build that can run headless as a systemd service.") }}</li>
          <li>{{ $t("Nothing else to install (no .NET), and it updates itself. When an update is ready you'll see a") }} <strong>{{ $t('Restart now') }}</strong> banner.</li>
          <li>{{ $t('The Windows builds are code-signed, and the Linux downloads come with signed checksums. Mac signing is waiting on Apple.') }}</li>
          <li>{{ $t('A new pre-match page, and a second theme to choose from.') }}</li>
          <li>{{ $t('Each replay in the list shows its Heroes Profile ID and links to its match page.') }}</li>
          <li>{{ $t("AI games and Brawls are skipped. AI games don't open a pre-match page either.") }}</li>
        </ul>
      </div>

      <div class="bg-lighten rounded p-6 text-sm space-y-4">
        <h2 class="text-lg">{{ $t('Download') }}</h2>
        <uploader-downloads :show-ranks="true"></uploader-downloads>
      </div>

      <div id="rank-reading" class="bg-lighten rounded p-6 text-sm space-y-4">
        <h2 class="text-lg">{{ $t('Rank reading (opt-in)') }}</h2>
        <p>
          The game doesn't put your Storm League rank in the replay file, so Heroes Profile couldn't get
          real in-game ranks from replays. It does show them on the loading screen, though. The "with rank reading"
          downloads, for Windows, macOS and Linux, are the same uploader plus a reader for that loading screen.
        </p>
        <ul class="list-disc list-inside space-y-1">
          <li>{{ $t("It's off until you turn on") }} <strong>{{ $t('Read ranks from the loading screen') }}</strong> in Settings.</li>
          <li>{{ $t("When it's on, it captures the game window for the first ~15 seconds of the loading screen.") }}</li>
          <li>{{ $t("In Storm League games it reads each player's rank off the loading screen. Once the game's replay is uploaded, those ranks are sent to Heroes Profile, stored, and shown on each player's profile. Every other mode is thrown away and never sent.") }}</li>
          <li>{{ $t("A rank is only kept for a player who is in that game's replay, and only when the reader is confident the rank card is theirs.") }}</li>
          <li>{{ $t('Beta builds also send a few loading-screen card strips so we can keep improving the reader. When we have enough, the site tells them to stop.') }}</li>
          <li>{{ $t('Windows: Windows 10 (2004) or later. Windows may draw a yellow outline around the game while it captures. On Windows 11 you can hide it in Settings. Windows 10 always shows it.') }}</li>
          <li>macOS: macOS 14 (Sonoma) or later. The first time it captures, macOS asks whether the uploader may record the screen: allow it under System Settings → Privacy &amp; Security → Screen Recording, then restart the uploader.</li>
          <li>{{ $t('Linux: the game under Wine or Proton, on X11 or a Wayland desktop with XWayland (the usual setup).') }}</li>
          <li>{{ $t("It updates on its own channel, so a normal install is never moved onto it, and the normal download doesn't include any of the capture code.") }}</li>
        </ul>
        <p>
          {{ $t('If you play Storm League, this is the one to install: your real rank, and the ranks of everyone in your games, show up on Heroes Profile.') }}
        </p>
      </div>

      <div class="bg-lighten rounded p-6 text-sm space-y-4">
        <h2 class="text-lg">{{ $t('Problems?') }}</h2>
        <p>
          {{ $t('Open an issue on the') }}
          <a class="link" href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/issues" target="_blank" rel="noopener">uploader's GitHub</a>
          and include the log. <strong>{{ $t('Show log') }}</strong> in the uploader opens the folder.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UploaderGuide',
  data() {
    return {
      infoText: "What changed, how to move over from the old uploader, and the opt-in rank reader.",
    }
  },
}
</script>
