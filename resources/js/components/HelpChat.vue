<template>
  <!-- Bottom-left help (the video ad takes bottom-right). One box: the FAQ and site pages answer first, the assistant only when asked. Sits above the cookie banner while it shows. -->
  <div class="fixed left-4 z-50 flex flex-col items-start gap-3 text-left" :style="{ bottom: 'calc(var(--disclaimer-height, 0px) + 1rem)' }">
    <div v-if="open" :class="expanded ? 'w-[47.5rem] h-[calc(100dvh-8rem)]' : 'w-[26.25rem] h-[35rem]'" class="max-w-[calc(100vw-2rem)] max-h-[calc(100dvh-8rem)] bg-black rounded-2xl border border-white/20 shadow-[0_0.5rem_2rem_rgba(0,0,0,0.7)] flex flex-col overflow-hidden">
      <!-- Window controls top-right: expand, divider, close -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
        <div class="flex items-center gap-4">
          <span class="font-bold text-lg">Help</span>
          <button v-if="messages.length" class="text-sm underline" :disabled="!!loading" @click="newChat">New chat</button>
        </div>
        <div class="flex items-center gap-2">
          <button class="w-8 h-8 rounded flex items-center justify-center hover:bg-white/10 transition-colors" :aria-label="expanded ? 'Make help smaller' : 'Make help bigger'" :title="expanded ? 'Smaller' : 'Bigger'" @click="toggleExpanded">
            <i :class="expanded ? 'fas fa-down-left-and-up-right-to-center' : 'fas fa-up-right-and-down-left-from-center'"></i>
          </button>
          <span class="w-px h-5 bg-white/20" aria-hidden="true"></span>
          <button class="w-8 h-8 rounded flex items-center justify-center hover:bg-white/10 transition-colors" aria-label="Close help" title="Close" @click="toggle">
            <i class="fas fa-xmark text-lg"></i>
          </button>
        </div>
      </div>

      <div ref="messageList" class="relative flex-1 min-h-0 overflow-y-auto px-5 py-4 flex flex-col gap-3 text-sm">
        <!-- The Heroes Profile logo answers, like the Analytics announcer -->
        <!-- First visit: the panel opens itself with this on the first page only -->
        <analytics-speech-bubble v-if="!messages.length && showIntro" :talking="true">
          <span class="font-bold">New:</span>
          Got a question about Heroes Profile? Ask it here, like "What's the best Valla build?" or "How do I upload my replays?" I'll find the right page or FAQ answer, and if you're logged in with Battle.net the assistant can help with the rest.
        </analytics-speech-bubble>
        <analytics-speech-bubble v-else-if="!messages.length">Ask me anything about Heroes Profile, like "What's the best Valla build?" or "How do I upload my replays?"</analytics-speech-bubble>
        <template v-for="(message, index) in messages" :key="index">
          <!-- What the FAQ and site pages had, with the choice to ask the assistant -->
          <analytics-speech-bubble v-if="message.role === 'results'" :data-message="index" :logo="message.logo || undefined" :talking="talkingIndex === index" @done="talkingIndex = null">
            <div class="flex flex-col gap-3">
              <span v-if="message.line" class="italic font-medium">{{ message.line }}</span>
              <template v-if="message.faq.length || message.pages.length">
                <span class="text-xs opacity-70">Here's what I found</span>
                <!-- Pages first: usually where they want to go -->
                <div v-if="message.pages.length" class="flex flex-col gap-1">
                  <span class="font-bold">Pages</span>
                  <div v-for="page in message.pages" :key="page.url">
                    <a v-if="!page.url.includes('{')" :href="page.url" class="underline">{{ page.name }}</a>
                    <span v-else class="font-medium">{{ page.name }}</span>
                    <span>: {{ page.description }}</span>
                    <span v-if="page.url.includes('/Zemill/')" class="text-xs opacity-70"> (an example profile; search your own BattleTag)</span>
                  </div>
                </div>
                <span v-if="message.faq.length" class="font-bold">From the FAQ</span>
                <div v-for="item in message.faq" :key="item.q" class="flex flex-col gap-1">
                  <span class="font-semibold">{{ item.q }}</span>
                  <div v-html="item.a"></div>
                  <a v-if="item.id" :href="'/FAQ#' + item.id" class="underline text-xs">Open in the FAQ</a>
                </div>
              </template>
              <span v-else>I couldn't find that in the FAQ.</span>
            </div>
          </analytics-speech-bubble>
          <analytics-speech-bubble v-else-if="message.role === 'assistant'" :data-message="index" :logo="message.logo || undefined" :talking="talkingIndex === index" @done="talkingIndex = null">
            <span class="whitespace-pre-line" v-html="linkify(message.content)"></span>
          </analytics-speech-bubble>
          <div v-else-if="message.role === 'user'" :data-message="index" class="self-end max-w-[85%] rounded-xl px-3 py-2 bg-blue whitespace-pre-line">{{ message.content }}</div>
          <!-- Under the latest search result: the way on to the assistant -->
          <template v-if="message.role === 'results' && index === messages.length - 1 && !loading">
            <custom-button v-if="loggedIn" class="w-full text-center" color="blue" :text="message.faq.length || message.pages.length ? 'Not what you needed? Ask the assistant' : 'Ask the assistant'" :ignoreclick="true" @click="askAssistant(message)"></custom-button>
            <custom-button v-else class="w-full text-center" color="blue" href="/Authenticate/Battlenet" text="Log in with Battle.net to ask the assistant"></custom-button>
          </template>
        </template>
        <analytics-speech-bubble v-if="loading" :logo="persona?.logo" :talking="true">{{ loading === 'assistant' ? 'Thinking…' : 'Looking…' }}</analytics-speech-bubble>
        <p v-if="error" class="text-red">{{ error }}</p>
      </div>

      <form class="px-5 py-4 border-t border-white/10 flex flex-col gap-2" @submit.prevent="send">
        <div class="flex gap-2">
          <input v-model="question" type="text" maxlength="500" placeholder="Ask a question" aria-label="Ask a question" class="form-control search-input flex-1 min-w-0 text-black text-sm" :disabled="!!loading" />
          <custom-button color="teal" text="Send" size="small" :ignoreclick="true" :disabled="!!loading || !question.trim()" @click="send"></custom-button>
        </div>
        <span v-if="loggedIn && remaining !== null" class="text-xs opacity-70">{{ remaining }} assistant {{ remaining === 1 ? 'question' : 'questions' }} left today</span>
      </form>
    </div>

    <div class="flex items-center gap-3">
      <button
        class="w-14 h-14 shrink-0 rounded-full bg-teal hover:bg-lteal text-white text-2xl shadow-[0_0.25rem_1rem_rgba(0,0,0,0.5)] flex items-center justify-center transition-colors"
        :aria-label="open ? 'Close help' : 'Open help'"
        :aria-expanded="open"
        @click="toggle"
      >
        <i :class="open ? 'fas fa-xmark' : 'fas fa-question'"></i>
      </button>
      <!-- Phones' first visit, instead of opening the panel over the page -->
      <div v-if="teaser && !open" class="help-teaser animate-bounce flex items-center gap-2 rounded-xl pl-3 pr-1 py-1 text-sm">
        <button class="text-left" @click="toggle"><span class="font-bold">New:</span> questions? Ask here</button>
        <button class="w-6 h-6 flex items-center justify-center rounded hover:bg-black/10" aria-label="Dismiss" title="Dismiss" @click="teaser = false">
          <i class="fas fa-xmark"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
// The conversation lasts for the browser tab, so moving between pages keeps it. The panel itself starts closed on every page, except the first visit.
const STORAGE_KEY = 'helpChat';
// Bump the number to show the intro to everyone again
const INTRO_KEY = 'helpChatIntro_1';

export default {
  name: 'HelpChat',
  props: {
    loggedIn: Boolean,
  },
  data() {
    return {
      open: false,
      // The larger panel, for easier reading
      expanded: false,
      // Diablo or Xal'atath, once someone asks for a manager or gets rude; kept until New chat
      persona: null,
      question: '',
      messages: [],
      remaining: null,
      // false, 'search' or 'assistant'
      loading: false,
      error: null,
      // The reply whose logo is mid-bounce
      talkingIndex: null,
      showIntro: false,
      teaser: false,
    };
  },
  created() {
    try {
      // Only the first page they land on, closed or not
      if (!localStorage.getItem(INTRO_KEY)) {
        // Phones get the small note instead: the panel would cover the page
        if (window.matchMedia('(min-width: 768px)').matches) {
          this.showIntro = true;
          this.open = true;
        } else {
          this.teaser = true;
        }
        localStorage.setItem(INTRO_KEY, 'true');
      }
    } catch (e) {
      // Storage blocked: skip the intro rather than show it on every page
    }
    try {
      const saved = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || 'null');
      if (saved) {
        this.messages = (saved.messages || []).filter(item => ['user', 'assistant', 'results'].includes(item.role));
        this.remaining = saved.remaining ?? null;
        this.expanded = !!saved.expanded;
        this.persona = saved.persona || null;
      }
    } catch (e) {
      // Storage blocked: start fresh
    }
  },
  mounted() {
    // The nav's Help > Help Assistant link
    window.addEventListener('open-help-chat', this.openFromNav);
  },
  beforeUnmount() {
    window.removeEventListener('open-help-chat', this.openFromNav);
  },
  methods: {
    openFromNav() {
      if (!this.open) {
        this.toggle();
      }
    },
    toggle() {
      this.open = !this.open;
      // Opened from the phone note: the panel greets them with the intro
      if (this.open && this.teaser) {
        this.teaser = false;
        this.showIntro = true;
      }
      // Reopening on the same page shows the usual prompt
      if (!this.open) {
        this.showIntro = false;
      }
      this.save();
      if (this.open) {
        this.scrollToEnd();
      }
    },
    toggleExpanded() {
      this.expanded = !this.expanded;
      this.save();
    },
    // Start over; the chat otherwise lasts until the tab closes
    newChat() {
      this.messages = [];
      this.persona = null;
      this.error = null;
      this.question = '';
      this.talkingIndex = null;
      this.save();
    },
    // Every question searches the FAQ and site pages first, for free
    async send() {
      const question = this.question.trim();
      if (!question || this.loading) {
        return;
      }

      // Kept out of the assistant's history unless they ask it
      const message = { role: 'user', content: question, searchOnly: true };
      this.messages.push(message);
      this.question = '';

      const ok = await this.request('search', '/api/v1/help/search', { question }, data => {
        // The first rude or manager message switches the logo; its line opens this reply
        const line = data.persona && data.persona.name !== this.persona?.name ? data.persona.line : null;
        if (data.persona) {
          this.persona = data.persona;
        }
        this.messages.push({ role: 'results', faq: data.faq || [], pages: data.pages || [], logo: this.persona?.logo, line });
      });

      if (!ok) {
        // Not answered, so not kept in the conversation
        this.messages.splice(this.messages.indexOf(message), 1);
        this.question = question;
      }
    },
    // The results didn't help: send the same question to Claude
    async askAssistant(results) {
      const message = this.messages[this.messages.indexOf(results) - 1];
      message.searchOnly = false;

      const history = this.messages
        .filter(item => item.role === 'assistant' || (item.role === 'user' && !item.searchOnly))
        .map(item => ({ role: item.role, content: item.content }))
        .slice(-20);

      const ok = await this.request('assistant', '/api/v1/help/chat', {
        messages: history,
        faq_question: results.faq[0]?.q || results.pages[0]?.name || null,
      }, data => {
        this.messages.push({ role: 'assistant', content: data.answer, logo: this.persona?.logo });
        this.remaining = data.remaining;
      });

      if (!ok) {
        message.searchOnly = true;
      }
    },
    async request(kind, url, payload, onSuccess) {
      this.error = null;
      this.loading = kind;
      this.scrollToEnd();

      try {
        const response = await this.$axios.post(url, payload);
        onSuccess(response.data);
        // Every reply lands last, so that's the one the logo says
        this.talkingIndex = this.messages.length - 1;
        this.scrollToReply();
        return true;
      } catch (error) {
        this.error = error.response?.data?.error || 'Something went wrong. Try again.';
        if (error.response?.data?.remaining !== undefined) {
          this.remaining = error.response.data.remaining;
        }
        return false;
      } finally {
        this.loading = false;
        this.save();
        if (this.error) {
          this.scrollToEnd();
        }
      }
    },
    save() {
      try {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ messages: this.messages, remaining: this.remaining, expanded: this.expanded, persona: this.persona }));
      } catch (e) {
        // Storage blocked: the chat just won't survive a page change
      }
    },
    // Long replies read from the top: put the question that asked for it at the top of the panel
    scrollToReply() {
      this.$nextTick(() => {
        const list = this.$refs.messageList;
        const last = this.messages.length - 1;
        // After "Ask the assistant" the message above is the search result, so start at the answer itself
        const from = this.messages[last - 1]?.role === 'user' ? last - 1 : last;
        const target = list?.querySelector(`[data-message="${from}"]`);
        if (target) {
          list.scrollTop = target.offsetTop - 16;
        }
      });
    },
    scrollToEnd() {
      this.$nextTick(() => {
        const list = this.$refs.messageList;
        if (list) {
          list.scrollTop = list.scrollHeight;
        }
      });
    },
    // Answers are plain text: escape them, then turn site paths and links into anchors
    linkify(text) {
      const escaped = String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

      // Paths can carry a query (?hero=Li%20Li) and names with apostrophes (Kel'Thuzad)
      return escaped.replace(/(^|[\s(])((?:https:\/\/[^\s<)]+)|(?:\/[A-Za-z][A-Za-z0-9_\-\/#?=&%.']*))/g, (match, lead, url) => {
        const trimmed = url.replace(/[.,!?']+$/, '');
        const rest = url.slice(trimmed.length);
        const external = trimmed.startsWith('https://');
        let shown = trimmed;
        try {
          shown = decodeURIComponent(trimmed);
        } catch (e) {
          // A stray % that isn't an escape: show the path as written
        }
        return `${lead}<a href="${trimmed}" class="underline"${external ? ' target="_blank" rel="noopener"' : ''}>${shown}</a>${rest}`;
      });
    },
  },
};
</script>

<style scoped>
/* The speech bubble's colours */
.help-teaser {
  background: #f4f4f8;
  color: #111;
  border: 2px solid #111;
}
</style>
