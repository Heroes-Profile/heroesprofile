<template>
  <div>
    <page-heading :heading="'The New Replay Uploader'" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[900px] px-4 mt-6 mb-10 space-y-6">
      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <p>
          The Heroes Profile uploader has been rewritten. It's now one app for Windows, macOS and Linux,
          and it replaces the old Windows-only uploader. It looks and works the same: it watches your
          replay folder and uploads each game when it finishes, with the pre-match and post-match pages.
        </p>

        <img
          src="/images/miscellaneous/uploader.png"
          alt="The Heroes Profile replay uploader"
          class="w-full"
        />
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">Moving from the old uploader</h2>
        <p>The old uploader won't update itself to the new one, so this is a one-time install.</p>
        <ol class="list-decimal list-inside space-y-1">
          <li>Download and install the new uploader below. It installs for your user only, so no admin is needed.</li>
          <li>On its first start it copies your settings from the old uploader, including your Twitch key.</li>
          <li>It also copies the old uploader's record of what's already been uploaded, so nothing is uploaded twice.</li>
          <li>It then offers to uninstall the old uploader. Say yes, or remove it later from Settings → Apps ("Heroesprofile").</li>
        </ol>
        <p>
          Don't run both at once, or they'll both upload every game. The new one warns you if the old one is
          still running.
        </p>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">What's new</h2>
        <ul class="list-disc list-inside space-y-1">
          <li>Windows, macOS (Apple Silicon and Intel) and Linux, all from the same app.</li>
          <li>On Linux, point it at your Wine/Proton prefix. There's also a plain tar.gz build that can run headless as a systemd service.</li>
          <li>Nothing else to install (no .NET), and it updates itself. When an update is ready you'll see a <strong>Restart now</strong> banner.</li>
          <li>The Windows builds are code-signed, and the Linux downloads come with signed checksums. Mac signing is waiting on Apple.</li>
          <li>A new pre-match page, and a second theme to choose from.</li>
          <li>Each replay in the list shows its Heroes Profile ID and links to its match page.</li>
          <li>AI games and Brawls are skipped. AI games don't open a pre-match page either.</li>
        </ul>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">Download</h2>
        <uploader-downloads :show-ranks="true"></uploader-downloads>
      </div>

      <div id="rank-reading" class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">Rank reading (Windows, opt-in)</h2>
        <p>
          The game doesn't put your Storm League rank in the replay file, so Heroes Profile has never had
          real in-game ranks. It does show them on the loading screen, though. The "Windows, with rank
          reading" download is the same uploader plus a reader for that loading screen.
        </p>
        <ul class="list-disc list-inside space-y-1">
          <li>It's off until you turn on <strong>Read ranks from the loading screen</strong> in Settings.</li>
          <li>When it's on, it captures the game window for the first ~15 seconds of the loading screen.</li>
          <li>Once the game reports the mode, Storm League games send the rank card strips from the loading screen to Heroes Profile. Every other mode is thrown away and never sent.</li>
          <li>It doesn't read ranks yet. For now we're collecting samples to build and test the reader. When we have enough, the site tells it to stop sending.</li>
          <li>Windows may draw a yellow outline around the game while it captures. On Windows 11 you can hide it in Settings. Windows 10 always shows it.</li>
          <li>It updates on its own channel, so a normal install is never moved onto it, and the normal download doesn't include any of the capture code.</li>
        </ul>
        <p>
          If you play Storm League on Windows and want to help, this is the one to install. The more ranks,
          regions and languages we get samples from, the better the reader will be.
        </p>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">Problems?</h2>
        <p>
          Open an issue on the
          <a class="link" href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/issues" target="_blank" rel="noopener">uploader's GitHub</a>
          and include the log. <strong>Show log</strong> in the uploader opens the folder.
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
