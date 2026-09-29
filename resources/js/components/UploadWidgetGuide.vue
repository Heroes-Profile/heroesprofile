<template>
  <div>
    <page-heading :heading="'Embed the Replay Uploader'" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[900px] px-4 mt-6 mb-10 space-y-6">
      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <p>
          You can put the Heroes Profile web uploader on your own site with an iframe:
          replays go straight from the visitor's browser to Heroes Profile, and your page is told how
          each one went.
        </p>
        <p>
          No API key is needed and nothing passes through your server.
        </p>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">Live demo</h2>
        <p>This is the widget exactly as it appears on another site. Replays dropped here are really uploaded.</p>

        <iframe
          ref="demo"
          src="/Upload/Embed"
          class="w-full border-0"
          :style="{ height: demoHeight + 'px' }"
          title="Heroes Profile replay uploader"
        ></iframe>

        <div>
          <h3 class="mb-2">Messages this page received</h3>
          <pre v-if="events.length" class="bg-darken p-3 text-xs overflow-x-auto max-h-60">{{ events.join('\n') }}</pre>
          <p v-else class="text-xs text-gray-medium">Nothing yet. Upload a replay above and its result appears here.</p>
        </div>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">1. Add the iframe and script</h2>
        <pre class="bg-darken p-3 text-xs overflow-x-auto">{{ iframeSnippet }}</pre>

        <p>
          Change <code>yoursite</code> to a short name for your site. Uploads are recorded under it, so
          we can see what comes from where.
        </p>
        <ul class="list-disc list-inside space-y-1">
          <li>Lowercase letters, numbers, <code>-</code> and <code>_</code>, up to 32 characters. Anything else is stripped.</li>
          <li><code>desktop</code> and <code>electron</code> belong to the Heroes Profile uploaders and can't be used.</li>
        </ul>
        <p>
          The script sizes the frame to fit the widget and grows it as replays are added. Without it
          the frame stays at the height you give it and scrolls once it's full, and any space below the
          widget is left see-through.
        </p>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">2. Listen for results (optional)</h2>
        <p>
          The widget posts a message to your page after every replay and once the queue is empty. Check
          the origin before trusting a message.
        </p>
        <pre class="bg-darken p-3 text-xs overflow-x-auto">{{ listenerSnippet }}</pre>

        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">type</th>
              <th class="py-2 px-3 text-left text-sm">Sent</th>
              <th class="py-2 px-3 text-left text-sm">Fields</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="py-2 px-3"><code>heroesprofile:upload</code></td>
              <td class="py-2 px-3">After each replay</td>
              <td class="py-2 px-3">
                <code>file</code> the file name, <code>status</code>, and <code>replayID</code> (null unless
                stored). <code>Success</code> and <code>Duplicate</code> mean the game is on Heroes Profile;
                anything else is a failure.
              </td>
            </tr>
            <tr>
              <td class="py-2 px-3"><code>heroesprofile:upload-complete</code></td>
              <td class="py-2 px-3">When the queue empties</td>
              <td class="py-2 px-3"><code>uploaded</code>, <code>duplicates</code>, <code>failed</code>, all counts.</td>
            </tr>
            <tr>
              <td class="py-2 px-3"><code>heroesprofile:resize</code></td>
              <td class="py-2 px-3">When the widget's height changes</td>
              <td class="py-2 px-3"><code>height</code> in pixels. The step 1 script already handles it.</td>
            </tr>
          </tbody>
        </table>

        <p>
          A new upload takes a few minutes to be parsed, so the player won't show up on Heroes Profile,
          or in the API, straight away. To link to a match, add its <code>replayID</code> to
          <code>{{ siteUrl }}/Match/Single/</code>
        </p>
      </div>

      <div class="bg-lighten rounded-lg p-6 text-sm space-y-4">
        <h2 class="text-lg">Good to know</h2>
        <ul class="list-disc list-inside space-y-1">
          <li>Limits are per visitor: 60 replays a minute and 20,000 a day from one IP address. The widget waits out the minute limit on its own.</li>
          <li>Replays are 10 MB at most.</li>
          <li>Games uploaded from the web may not be eligible for leaderboard consideration. For that, and for uploading every game automatically, point players to the <a href="/Upload" class="link">desktop uploader</a>. The widget links to it too.</li>
          <li>Links in the widget open in a new tab, so your page stays where it is.</li>
        </ul>
        <p>
          Questions, or a larger integration in mind? Write to
          <a href="mailto:zemill@heroesprofile.com" class="link">zemill@heroesprofile.com</a>.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UploadWidgetGuide',
  data() {
    return {
      infoText: 'Let players upload their replays to Heroes Profile without leaving your site.',
      // What partners embed, whichever environment is serving this page.
      siteUrl: 'https://www.heroesprofile.com',
      events: [],
      demoHeight: 520,
    }
  },
  computed: {
    iframeSnippet() {
      return `<iframe
  id="heroesprofile-uploader"
  src="${this.siteUrl}/Upload/Embed?source=yoursite"
  style="width: 100%; height: 520px; border: 0"
  title="Upload replays to Heroes Profile"
></iframe>
<script>
  window.addEventListener('message', (event) => {
    if (event.origin !== '${this.siteUrl}' || event.data.type !== 'heroesprofile:resize') return;
    document.getElementById('heroesprofile-uploader').style.height = event.data.height + 'px';
  });
<\/script>`;
    },
    listenerSnippet() {
      return `window.addEventListener('message', (event) => {
  if (event.origin !== '${this.siteUrl}') return;

  const message = event.data;

  if (message.type === 'heroesprofile:upload') {
    // { file, status, replayID }
    console.log(message.file, message.status, message.replayID);
  }

  if (message.type === 'heroesprofile:upload-complete') {
    // { uploaded, duplicates, failed }
    console.log(message.uploaded + ' uploaded');
  }
});`;
    },
  },
  mounted() {
    window.addEventListener('message', this.onMessage);
  },
  beforeUnmount() {
    window.removeEventListener('message', this.onMessage);
  },
  methods: {
    onMessage(event) {
      if (event.source !== this.$refs.demo.contentWindow || !event.data) return;

      if (event.data.type === 'heroesprofile:resize') {
        this.demoHeight = event.data.height;
        return;
      }

      this.events.push(JSON.stringify(event.data));
    },
  },
}
</script>
