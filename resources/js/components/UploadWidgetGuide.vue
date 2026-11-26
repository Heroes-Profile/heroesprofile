<template>
  <doc-page :cards="true" :heading="$t('Embed the Replay Uploader')" :subheading="$t('For site owners · No API key needed')" :sections="sections" :links="links">
    <template #overview>
      <p>{{ $t("You can put the Heroes Profile web uploader on your own site with an iframe: replays go straight from the visitor's browser to Heroes Profile, and your page is told how each one went.") }}</p>
      <div class="grid md:grid-cols-3 gap-4">
        <div v-for="point in points" :key="point.title" class="rounded-control bg-surface-1 p-4 flex items-center gap-4">
          <span class="shrink-0 w-9 h-9 rounded-full bg-teal text-white flex items-center justify-center"><i class="fas fa-check text-sm"></i></span>
          <div class="flex flex-col gap-0.5">
            <span class="font-semibold text-white">{{ $t(point.title) }}</span>
            <span class="text-sm">{{ $t(point.text) }}</span>
          </div>
        </div>
      </div>
    </template>

    <template #demo>
      <!-- The widget inside a pretend browser window, as another site would show it -->
      <div class="rounded-control bg-surface-1 overflow-hidden">
        <div class="flex items-center gap-3 bg-surface-3 px-4 py-2.5">
          <span class="flex gap-1.5 shrink-0"><span class="w-2.5 h-2.5 rounded-full bg-loss"></span><span class="w-2.5 h-2.5 rounded-full bg-yellow"></span><span class="w-2.5 h-2.5 rounded-full bg-win"></span></span>
          <span class="flex-1 min-w-0 truncate rounded bg-surface-1 px-3 py-1 font-mono text-xs">yoursite.com/upload</span>
        </div>
        <div class="p-4 md:p-8">
          <iframe ref="demo" src="/Upload/Embed" class="w-full border-0" :style="{ height: demoHeight + 'px' }" :title="$t('Heroes Profile replay uploader')"></iframe>
        </div>
      </div>
      <div class="rounded-control bg-surface-1 p-4 flex flex-col gap-2">
        <span class="flex items-center gap-2 text-sm font-semibold text-white"><span class="w-2 h-2 rounded-full bg-win"></span>{{ $t('Messages this page received') }}</span>
        <pre v-if="events.length" class="font-mono text-xs overflow-x-auto max-h-60">{{ events.join('\n') }}</pre>
        <p v-else class="font-mono text-xs">{{ $t('Nothing yet. Upload a replay above and its result appears here.') }}</p>
      </div>
    </template>

    <template #iframe>
      <p><template v-for="(part, index) in codeParts($t('Paste this where the uploader should appear. Change {yoursite} to a short name for your site: uploads are recorded under it, so we can see what comes from where.'))" :key="index"><code v-if="part.code" class="text-white">{{ part.text }}</code><template v-else>{{ part.text }}</template></template></p>
      <div class="rounded-control bg-surface-1 overflow-hidden">
        <div class="flex items-center justify-between gap-3 bg-surface-3 px-4 py-2 text-xs">
          <span class="font-semibold">HTML</span>
          <button type="button" class="inline-flex items-center gap-1.5 rounded bg-surface-1 hover:bg-surface-4 px-2.5 py-1 font-semibold text-white transition-colors" @click="copy('iframe', iframeSnippet)"><i :class="copied === 'iframe' ? 'fas fa-check text-lteal' : 'fas fa-copy'"></i>{{ copied === 'iframe' ? $t('Copied') : $t('Copy') }}</button>
        </div>
        <pre class="p-5 font-mono text-sm leading-relaxed text-white overflow-x-auto" v-html="highlight(iframeSnippet)"></pre>
      </div>
      <div class="rounded-control bg-surface-1 p-5 flex flex-col gap-3 text-sm">
        <h3 class="font-semibold text-white">{{ $t('Rules for source') }}</h3>
        <ul class="flex flex-col gap-2">
          <li v-for="rule in sourceRules" :key="rule" class="flex gap-3"><span class="mt-[0.45rem] w-1.5 h-1.5 shrink-0 rounded-full bg-teal"></span><span><template v-for="(part, index) in codeParts($t(rule))" :key="index"><code v-if="part.code" class="text-white">{{ part.text }}</code><template v-else>{{ part.text }}</template></template></span></li>
        </ul>
      </div>
      <div class="flex gap-3 rounded bg-surface-1 border-l-4 border-teal px-5 py-3 text-sm">
        <basil-icon name="info" class="text-teal text-xl shrink-0"></basil-icon>
        <span>{{ $t("The script sizes the frame to fit the widget and grows it as replays are added. Without it the frame stays at the height you give it and scrolls once it's full, and any space below the widget is left see-through.") }}</span>
      </div>
    </template>

    <template #listen>
      <p>{{ $t('The widget posts a message to your page after every replay and once the queue is empty. Check the origin before trusting a message.') }}</p>
      <div class="rounded-control bg-surface-1 overflow-hidden">
        <div class="flex items-center justify-between gap-3 bg-surface-3 px-4 py-2 text-xs">
          <span class="font-semibold">JavaScript</span>
          <button type="button" class="inline-flex items-center gap-1.5 rounded bg-surface-1 hover:bg-surface-4 px-2.5 py-1 font-semibold text-white transition-colors" @click="copy('listener', listenerSnippet)"><i :class="copied === 'listener' ? 'fas fa-check text-lteal' : 'fas fa-copy'"></i>{{ copied === 'listener' ? $t('Copied') : $t('Copy') }}</button>
        </div>
        <pre class="p-5 font-mono text-sm leading-relaxed text-white overflow-x-auto" v-html="highlight(listenerSnippet)"></pre>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">type</th>
              <th class="py-2 px-3 text-left text-sm">{{ $t('Sent') }}</th>
              <th class="py-2 px-3 text-left text-sm">{{ $t('Fields') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="message in messageTypes" :key="message.type">
              <td class="py-2 px-3"><code class="text-win whitespace-nowrap">{{ message.type }}</code></td>
              <td class="py-2 px-3">{{ $t(message.sent) }}</td>
              <td class="py-2 px-3"><template v-for="(part, index) in codeParts($t(message.fields))" :key="index"><code v-if="part.code" class="text-white">{{ part.text }}</code><template v-else>{{ part.text }}</template></template></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-3 rounded bg-surface-1 border-l-4 border-teal px-5 py-3 text-sm">
        <basil-icon name="info" class="text-teal text-xl shrink-0"></basil-icon>
        <span class="min-w-0 break-words"><template v-for="(part, index) in codeParts($t(&quot;A new upload takes a few minutes to be parsed, so the player won't show up on Heroes Profile, or in the API, straight away. To link to a match, add its {replayID} to {url}&quot;))" :key="index"><code v-if="part.code" class="text-white">{{ part.text === 'url' ? siteUrl + '/Match/Single/' : part.text }}</code><template v-else>{{ part.text }}</template></template></span>
      </div>
    </template>

    <template #good>
      <div class="grid md:grid-cols-2 gap-4">
        <div v-for="fact in facts" :key="fact.title" class="rounded-control bg-surface-1 p-5 flex flex-col gap-1">
          <span class="text-3xl font-bold text-white">{{ $t(fact.value) }}</span>
          <span class="font-semibold text-white">{{ $t(fact.title) }}</span>
          <p class="text-sm mt-1"><template v-for="(part, index) in codeParts($t(fact.text))" :key="index"><a v-if="part.link" href="/Upload" class="link">{{ part.text }}</a><template v-else>{{ part.text }}</template></template></p>
        </div>
      </div>
      <div class="rounded-control bg-surface-1 p-4 md:pl-5 flex flex-wrap items-center justify-between gap-4 text-sm">
        <p>{{ $t('Questions, or a larger integration in mind? Write to') }} <a href="mailto:zemill@heroesprofile.com" class="link">zemill@heroesprofile.com</a></p>
        <custom-button href="/Contact" color="teal" class="font-semibold" :alt="'Contact us'">{{ $t('Contact us') }}</custom-button>
      </div>
    </template>
  </doc-page>
</template>

<script>
// What the snippets colour: comments, strings, tags and operators, attribute names, keywords and the browser globals
const TOKENS = [
  ['text-subtext', /\/\/[^\n]*/y],
  ['text-win', /'[^'\n]*'|"[^"\n]*"/y],
  ['text-loss', /<\/?[a-z]+|\/?>/y],
  ['text-yellow', /[a-z-]+(?==")/y],
  ['text-loss', /(?:if|return|const)\b|=>|!==|===|\|\|/y],
  ['text-teal', /(?:window|document|console|event|message)\b/y],
  [null, /[A-Za-z0-9_$]+|[\s\S]/y],
];

function escapeHtml(text) {
  return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

export default {
  name: 'UploadWidgetGuide',
  data() {
    return {
      // What partners embed, whichever environment is serving this page.
      siteUrl: 'https://www.heroesprofile.com',
      events: [],
      demoHeight: 520,
      copied: null,
      points: [
        { title: 'No API key', text: 'Paste two snippets and you are done.' },
        { title: 'Nothing on your server', text: 'Replays go from the browser straight to Heroes Profile.' },
        { title: 'Results on your page', text: 'Your page gets a message for every replay.' },
      ],
      // {x} shows x as code, [x] links it
      sourceRules: [
        'Lowercase letters, numbers, {-} and {_}, up to 32 characters. Anything else is stripped.',
        "{desktop} and {electron} belong to the Heroes Profile uploaders and can't be used.",
      ],
      messageTypes: [
        { type: 'heroesprofile:upload', sent: 'After each replay', fields: '{file} the file name, {status}, and {replayID} ({null} unless stored). {Success} and {Duplicate} mean the game is on Heroes Profile; anything else is a failure.' },
        { type: 'heroesprofile:upload-complete', sent: 'When the queue empties', fields: '{uploaded}, {duplicates}, {failed}, all counts.' },
        { type: 'heroesprofile:resize', sent: "When the widget's height changes", fields: '{height} in pixels. The step 1 script already handles it.' },
      ],
      facts: [
        { value: '60 / min', title: 'Per visitor limit', text: '60 replays a minute and 20,000 a day from one IP address. The widget waits out the minute limit on its own.' },
        { value: '10 MB', title: 'Largest replay', text: 'Bigger files are skipped and reported as failed.' },
        { value: 'Leaderboards', title: 'Use the desktop uploader', text: 'Games uploaded from the web may not count for the leaderboards. For that, and for uploading every game automatically, point players to the [desktop uploader]. The widget links to it too.' },
        { value: 'New tab', title: 'Links stay out of your way', text: 'Links in the widget open in a new tab, so your page stays where it is.' },
      ],
    }
  },
  computed: {
    sections() {
      return [
        { id: 'overview', title: this.$t('Overview') },
        { id: 'demo', title: this.$t('Live demo'), subtitle: this.$t('This is the widget exactly as it appears on another site. Replays dropped here are really uploaded.') },
        { id: 'iframe', title: this.$t('Add the iframe and script'), step: 1 },
        { id: 'listen', title: this.$t('Listen for results'), step: 2, badge: this.$t('Optional') },
        { id: 'good', title: this.$t('Good to know') },
      ];
    },
    links() {
      return [
        { text: this.$t('Replay Uploader'), href: '/Upload' },
        { text: this.$t('Heroes Profile API'), href: '/Api' },
        { text: this.$t('FAQ'), href: '/FAQ' },
      ];
    },
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
    clearTimeout(this.copyTimer);
  },
  methods: {
    onMessage(event) {
      if (event.source !== this.$refs.demo?.contentWindow || !event.data) return;

      if (event.data.type === 'heroesprofile:resize') {
        this.demoHeight = event.data.height;
        return;
      }

      this.events.push(JSON.stringify(event.data));
    },
    // A translated sentence split into plain text, {code} and [link] parts
    codeParts(text) {
      return text.split(/(\{[^}]+\}|\[[^\]]+\])/).filter(part => part !== '').map(part => {
        if (part.startsWith('{') && part.endsWith('}')) return { text: part.slice(1, -1), code: true };
        if (part.startsWith('[') && part.endsWith(']')) return { text: part.slice(1, -1), link: true };
        return { text: part };
      });
    },
    // The snippet as HTML with each token in its colour
    highlight(code) {
      let html = '';
      let position = 0;
      while (position < code.length) {
        for (const [colour, pattern] of TOKENS) {
          pattern.lastIndex = position;
          const match = pattern.exec(code);
          if (!match) continue;
          const text = escapeHtml(match[0]);
          html += colour ? `<span class="${colour}">${text}</span>` : text;
          position += match[0].length;
          break;
        }
      }
      return html;
    },
    async copy(key, text) {
      try {
        await navigator.clipboard.writeText(text);
      } catch (error) {
        return;
      }
      this.copied = key;
      clearTimeout(this.copyTimer);
      this.copyTimer = setTimeout(() => { this.copied = null; }, 2000);
    },
  },
}
</script>
