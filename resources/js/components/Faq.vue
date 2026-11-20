<template>
  <div>
    <page-heading :heading="'FAQ'" subheading="Frequently asked questions"></page-heading>
    <scroll-to-top></scroll-to-top>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 grid xl:grid-cols-[400fr_1488fr] gap-6 xl:gap-8 items-start">
      <!-- Search and the topics; on wide screens it stays in view, and marks the topic being read -->
      <aside class="bg-surface rounded px-4 py-6 flex flex-col gap-4 min-w-0 xl:sticky xl:top-4 xl:max-h-[calc(100vh-2rem)] xl:overflow-y-auto">
        <search-input v-model="search" :placeholder="$t('Search questions')" class="w-full"></search-input>

        <div class="max-xl:hidden flex flex-col gap-4">
          <span class="px-2 text-xs font-bold uppercase tracking-wider text-subtext">{{ $t('Topics') }}</span>
          <nav class="flex flex-col gap-1">
            <a v-for="category in filteredCategories" :key="category.name" :href="'#' + slug(category.name)"
              :class="['flex items-center gap-3 rounded-control pl-4 pr-3 py-2.5 transition-colors', activeTopic === slug(category.name) ? 'bg-teal font-semibold' : 'hover:bg-surface-2']">
              <span class="flex-1 min-w-0">{{ $t(category.name) }}</span>
              <span class="px-2 py-0.5 rounded-[0.375rem] bg-surface-2 text-xs font-semibold text-subtext">{{ category.items.length }}</span>
            </a>
          </nav>
          <div class="h-px bg-gray-dark"></div>
          <span class="px-2 text-xs font-bold uppercase tracking-wider text-subtext">{{ $t('Reference') }}</span>
          <nav class="flex flex-col gap-1">
            <a v-for="ref in references" :key="ref.id" :href="'#' + ref.id"
              :class="['flex items-center gap-3 rounded-control pl-4 pr-3 py-2.5 transition-colors', activeTopic === ref.id ? 'bg-teal font-semibold' : 'hover:bg-surface-2']">
              <span class="flex-1 min-w-0">{{ $t(ref.title) }}</span>
              <basil-icon name="arrow-small" class="text-sm"></basil-icon>
            </a>
          </nav>
          <div class="h-px bg-gray-dark"></div>
          <div class="flex flex-col gap-3 pt-2">
            <span class="px-2 font-semibold">{{ $t("Can't find an answer?") }}</span>
            <!-- Opens the help assistant, as the nav's Help Assistant link does -->
            <button type="button" class="btn bg-teal hover:bg-lteal font-semibold text-center" @click="openHelpChat">{{ $t('Ask a question') }}</button>
          </div>
        </div>
      </aside>

      <div class="flex flex-col gap-6 xl:gap-8 min-w-0">
        <div v-if="filteredCategories.length === 0" class="bg-surface rounded p-6 text-center text-subtext">
          {{ $t('No results found for ":search". Try a different keyword or ask us below.', { search: search }) }}
        </div>

        <!-- One card per topic: closed questions are rows, an open one is a panel with its answer -->
        <panel-box v-for="category in filteredCategories" :key="category.name" :id="slug(category.name)" :title="category.name" :subtitle="topicSubtitles[category.name]" :divider="false" class="scroll-mt-4" data-faq-topic>
          <template #right>
            <span class="px-3 py-1 rounded-control bg-surface-2 text-sm font-medium text-subtext whitespace-nowrap">{{ $t(':count questions', { count: category.items.length }) }}</span>
          </template>
          <div class="flex flex-col">
            <template v-for="(item, index) in category.items" :key="index">
              <div v-if="index > 0 && !isOpen(category.name, index) && !isOpen(category.name, index - 1)" class="h-px bg-gray-dark"></div>
              <div :id="item.id" :class="['scroll-mt-4', isOpen(category.name, index) ? 'bg-surface-2 rounded-xl border-l-4 border-teal p-5 flex flex-col gap-3' : '']">
                <button type="button" :aria-expanded="isOpen(category.name, index)" @click="toggle(category.name, index)"
                  :class="['w-full text-left flex items-center gap-4 text-lg', isOpen(category.name, index) ? 'font-semibold' : 'font-medium py-4 hover:text-lteal transition-colors']">
                  <span class="flex-1 min-w-0">{{ item.q }}</span>
                  <basil-icon name="arrow-small" :class="['text-sm shrink-0 transition-transform', isOpen(category.name, index) ? '-rotate-90' : 'rotate-90']"></basil-icon>
                </button>
                <div v-if="isOpen(category.name, index)" class="text-subtext leading-relaxed faq-answer" v-html="item.a"></div>
              </div>
            </template>
          </div>
        </panel-box>

        <!-- Site Pages: config/faq.php, shared with the help chat -->
        <panel-box id="site-pages" title="Site Pages" subtitle="Every page on the site and its address. Examples use Zemill." :divider="false" class="scroll-mt-4" data-faq-topic>
          <div class="flex flex-wrap items-center gap-4">
            <tab-button :small="true" :tabs="faqPages.map(section => ({ key: section.name, text: section.name }))" :ignoreclick="true" :overridedefaultside="sitePagesSection.name" @tab-click="sitePagesTab = $event; sitePagesPage = 1"></tab-button>
            <span v-if="sitePagesSection.note && !sitePagesSearch" class="text-sm text-subtext">{{ sitePagesSection.note }}</span>
            <!-- Searching looks through every tab -->
            <search-input v-model="sitePagesSearch" :placeholder="$t('Search pages')" class="w-full sm:w-[16rem] ml-auto" @update:model-value="sitePagesPage = 1"></search-input>
          </div>
          <div class="min-w-0 overflow-x-auto">
            <table class="min-w-0 w-full responsive-table rounded-table">
              <thead>
                <tr>
                  <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Page') }}</th>
                  <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('URL') }}</th>
                  <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('What it does') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="page in pageOf(sitePagesRows, sitePagesPage)" :key="page.url">
                  <td class="py-3 px-4 whitespace-nowrap">{{ page.name }}</td>
                  <td class="py-3 px-4 whitespace-nowrap">
                    <a v-if="!page.url.includes('{')" :href="page.url" class="group/bt inline-flex items-center text-header"><span class="group-hover/bt:underline">{{ page.url }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                    <span v-else>{{ page.url }}</span>
                    <div v-if="page.hero_url" class="text-xs text-subtext">{{ $t('One hero:') }} <a :href="page.hero_url.replace('{hero}', 'Abathur')" class="underline">{{ page.hero_url.replace('{hero}', 'Abathur') }}</a></div>
                  </td>
                  <td class="py-3 px-4 text-subtext">{{ page.description }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-4">
            <span class="text-sm text-subtext">{{ showing(sitePagesRows, sitePagesPage, 'pages') }}</span>
            <pagination-bar v-if="pageCount(sitePagesRows) > 1" :current="sitePagesPage" :last="pageCount(sitePagesRows)" @page="sitePagesPage = $event"></pagination-bar>
          </div>
        </panel-box>

        <div class="grid 2xl:grid-cols-2 gap-6 xl:gap-8 items-start">
          <panel-box id="patch-history" title="Patch & Season History" subtitle="Which game version belongs to which season." :divider="false" class="scroll-mt-4 min-w-0" data-faq-topic>
            <table class="min-w-0 w-full responsive-table rounded-table">
              <thead>
                <tr>
                  <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Season') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Game Version') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('Start Date') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('End Date') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in pageOf(patchHistory, patchPage)" :key="i">
                  <td class="py-3 px-4 text-center">{{ row.season }}</td>
                  <td class="py-3 px-4 text-center">
                    <a v-if="row.patch_notes_url" :href="row.patch_notes_url" target="_blank" class="group/bt inline-flex items-center text-header"><span class="group-hover/bt:underline">{{ row.game_version }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
                    <span v-else>{{ row.game_version }}</span>
                  </td>
                  <td class="py-3 px-4 text-center">{{ formatDate(row.start_date) }}</td>
                  <td class="py-3 px-4 text-center">{{ row.end_date ? formatDate(row.end_date) : $t('Present') }}</td>
                </tr>
              </tbody>
            </table>
            <div class="flex flex-wrap items-center justify-between gap-4">
              <span class="text-sm text-subtext">{{ showing(patchHistory, patchPage) }}</span>
              <pagination-bar v-if="pageCount(patchHistory) > 1" :current="patchPage" :last="pageCount(patchHistory)" @page="patchPage = $event"></pagination-bar>
            </div>
          </panel-box>

          <panel-box id="season-dates" title="Season Dates" subtitle="Shown in your time zone." :divider="false" class="scroll-mt-4 min-w-0" data-faq-topic>
            <table class="min-w-0 w-full responsive-table rounded-table">
              <thead>
                <tr>
                  <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Season') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('Start Date') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('End Date') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in pageOf(seasonDates, seasonPage)" :key="row.id">
                  <td class="py-3 px-4 text-center whitespace-nowrap">{{ $t(':year Season :season', { year: row.year, season: row.season }) }}</td>
                  <td class="py-3 px-4 text-center"><format-date :input="row.start_date"></format-date></td>
                  <td class="py-3 px-4 text-center"><format-date v-if="row.end_date" :input="row.end_date"></format-date></td>
                </tr>
              </tbody>
            </table>
            <div class="flex flex-wrap items-center justify-between gap-4">
              <span class="text-sm text-subtext">{{ showing(seasonDates, seasonPage) }}</span>
              <pagination-bar v-if="pageCount(seasonDates) > 1" :current="seasonPage" :last="pageCount(seasonDates)" @page="seasonPage = $event"></pagination-bar>
            </div>
          </panel-box>
        </div>

        <!-- Match Awards: the first nine, then all of them on request -->
        <panel-box id="awards" title="Match Awards" subtitle="End-of-match awards and what each one is for." :divider="false" class="scroll-mt-4" data-faq-topic>
          <div class="grid sm:grid-cols-2 2xl:grid-cols-3 gap-3">
            <div v-for="award in (showAllAwards ? awards : awards.slice(0, 9))" :key="award.award_id + '-' + award.title" class="flex items-center gap-3 rounded-control bg-surface-1 p-3 min-w-0">
              <img :src="`/images/awards/${award.icon}_blue.png`" :alt="award.title" class="w-10 h-10 shrink-0" />
              <span class="flex flex-col min-w-0">
                <span class="font-semibold">{{ award.title }}</span>
                <span class="text-sm text-subtext">{{ award.description }}</span>
              </span>
            </div>
          </div>
          <button v-if="awards.length > 9" type="button" class="btn bg-blue hover:bg-lblue font-semibold w-full" @click="showAllAwards = !showAllAwards">
            {{ showAllAwards ? $t('Show fewer awards') : $t('Show all :count awards', { count: awards.length }) }}
          </button>
        </panel-box>

        <!-- Ask a Question -->
        <panel-box id="ask" title="Still have a question?" subtitle="Can't find what you're looking for? Send us a message and we'll get back to you." :divider="false" class="scroll-mt-4">
          <form @submit.prevent="submitQuestion" class="flex flex-col gap-6">
            <div class="grid sm:grid-cols-2 gap-4">
              <label class="flex flex-col gap-2">
                <span class="text-subtext">{{ $t('BattleTag') }}</span>
                <input v-model="formData.battletag" type="text" class="w-full bg-field text-white rounded-control px-4 h-12" placeholder="Name#1234" required />
              </label>
              <label class="flex flex-col gap-2">
                <span class="text-subtext">{{ $t('Email') }}</span>
                <input v-model="formData.email" type="email" class="w-full bg-field text-white rounded-control px-4 h-12" placeholder="you@example.com" required />
              </label>
            </div>
            <label class="flex flex-col gap-2">
              <span class="text-subtext">{{ $t('Your Question') }}</span>
              <textarea v-model="formData.message" class="w-full bg-field text-white rounded-control px-4 py-3 min-h-[9.875rem]" :placeholder="$t('What would you like to know?')" required></textarea>
            </label>

            <!-- Honeypot -->
            <input type="text" name="website" v-model="formData.website" style="display:none!important;visibility:hidden!important;position:absolute!important;left:-624.9375rem!important;" tabindex="-1" autocomplete="off">

            <div class="flex flex-wrap items-center justify-between gap-4">
              <span v-if="emailSent" class="text-lteal">{{ $t("Message sent! We'll be in touch.") }}</span>
              <span v-else></span>
              <button :disabled="isLoading" type="submit" class="btn bg-teal hover:bg-lteal font-semibold disabled:opacity-60">{{ isLoading ? $t('Sending...') : $t('Send question') }}</button>
            </div>
            <p v-if="sendError" class="bg-red/20 text-white rounded-control px-4 py-3">{{ sendError }}</p>
          </form>
        </panel-box>
      </div>
    </div>
  </div>
</template>

<script>
import { siteLocale } from '../utils/translate';
// A line under each topic's title, as in the redesign
const TOPIC_SUBTITLES = {
  'Getting Started': 'New to the site? Start here.',
  'Uploading Replays': 'Getting your games onto the site.',
  'MMR & Rankings': 'How MMR, tiers and recalculations work.',
  'Global Stats & Data': 'Where the numbers come from and how to read them.',
  'Filters': 'What each filter on the site does.',
  'Site Features': 'PreMatch, Friends & Foes, Replay Search and more.',
  'Drafter': 'Suggestions, pick rates and filters in the Drafter.',
  'Leaderboards': 'Requirements, HP Rating and seasons.',
  'Supporting Heroes Profile': 'Patreon, Analytics, API and open source.',
};
// Rows per page in the reference tables
const PER_PAGE = 8;
const RECAPTCHA_MESSAGE = "We couldn't verify this message with reCAPTCHA. Please allow reCAPTCHA (google.com) in your browser or blocker and try again, or email contact@heroesprofile.com directly.";

export default {
  name: 'FAQ',
  props: {
    recaptchaSiteKey: {
      type: String,
      default: ''
    },
    patchHistory: {
      type: Array,
      default: () => []
    },
    awards: {
      type: Array,
      default: () => []
    },
    seasonDates: {
      type: Array,
      default: () => []
    },
    // config/faq.php, shared with the help chat
    faqCategories: {
      type: Array,
      default: () => []
    },
    faqPages: {
      type: Array,
      default: () => []
    },
    // FaqMatcher's filler words, so search and the help chat agree
    faqStopwords: {
      type: Array,
      default: () => []
    },
  },
  data() {
    return {
      search: '',
      openItems: {},
      isLoading: false,
      emailSent: false,
      sendError: null,
      formData: {
        battletag: '',
        email: '',
        message: '',
        website: '',
        recaptcha_token: '',
      },
      categories: [...this.faqCategories],
      topicSubtitles: TOPIC_SUBTITLES,
      references: [
        { id: 'site-pages', title: 'Site Pages' },
        { id: 'patch-history', title: 'Patch & Season History' },
        { id: 'season-dates', title: 'Season Dates' },
        { id: 'awards', title: 'Match Awards' },
      ],
      // The section being read, marked in the sidebar
      activeTopic: null,
      sitePagesTab: null,
      sitePagesPage: 1,
      sitePagesSearch: '',
      patchPage: 1,
      seasonPage: 1,
      showAllAwards: false,
    };
  },
  mounted() {
    this.loadRecaptcha();
    this.openFromHash();
    window.addEventListener('scroll', this.onScroll, { passive: true });
    this.$nextTick(this.markActiveTopic);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.onScroll);
  },
  computed: {
    sitePagesSection() {
      return this.faqPages.find(section => section.name === this.sitePagesTab) || this.faqPages[0] || { name: '', items: [] };
    },
    // The open tab's pages, or every page matching the search (name, address or description)
    sitePagesRows() {
      const query = this.sitePagesSearch.trim().toLowerCase();
      if (!query) return this.sitePagesSection.items;
      return this.faqPages.flatMap(section => section.items)
        .filter(page => `${page.name} ${page.url} ${page.description}`.toLowerCase().includes(query));
    },
    // Every meaningful word has to appear, in any order: "how do I upload my replays" -> upload, replay
    filteredCategories() {
      if (!this.search.trim()) return this.categories;
      const stopwords = new Set(this.faqStopwords);
      const words = this.search.toLowerCase().split(/[^a-z0-9]+/)
        .filter(word => word.length > 1 && !stopwords.has(word))
        .map(word => word.length > 3 && word.endsWith('s') && !word.endsWith('ss') ? word.slice(0, -1) : word);
      // Only filler words: fall back to the whole phrase
      const terms = words.length ? words : [this.search.toLowerCase().trim()];

      return this.categories
        .map(cat => ({
          ...cat,
          items: cat.items.filter(item => {
            const text = (item.q + ' ' + item.a.replace(/<[^>]*>/g, ' ')).toLowerCase();
            return terms.every(term => text.includes(term));
          }),
        }))
        .filter(cat => cat.items.length > 0);
    },
  },
  methods: {
    openHelpChat() {
      window.dispatchEvent(new Event('open-help-chat'));
    },
    slug(text) {
      return 'topic-' + text.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    },
    pageCount(rows) {
      return Math.max(1, Math.ceil(rows.length / PER_PAGE));
    },
    pageOf(rows, page) {
      return rows.slice((page - 1) * PER_PAGE, page * PER_PAGE);
    },
    // "Showing 1–8 of 15 pages"
    showing(rows, page, noun) {
      const values = { from: rows.length ? (page - 1) * PER_PAGE + 1 : 0, to: Math.min(page * PER_PAGE, rows.length), total: rows.length };
      return noun === 'pages' ? this.$t('Showing :from–:to of :total pages', values) : this.$t('Showing :from–:to of :total', values);
    },
    onScroll() {
      if (this.scrollFrame) return;
      this.scrollFrame = requestAnimationFrame(() => {
        this.scrollFrame = null;
        this.markActiveTopic();
      });
    },
    // The last section whose top has scrolled past the top fifth of the window
    markActiveTopic() {
      const line = window.innerHeight / 5;
      let active = null;
      this.$el.querySelectorAll('[data-faq-topic]').forEach(section => {
        if (section.getBoundingClientRect().top <= line) {
          active = section.id;
        }
      });
      this.activeTopic = active || this.$el.querySelector('[data-faq-topic]')?.id || null;
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleDateString(siteLocale(), { year: 'numeric', month: '2-digit', day: '2-digit' });
    },
    // /FAQ#some-id opens that question and scrolls to it
    openFromHash() {
      const id = window.location.hash.slice(1);
      if (!id) return;
      for (const category of this.categories) {
        const index = category.items.findIndex(item => item.id === id);
        if (index !== -1) {
          this.openItems = { ...this.openItems, [`${category.name}-${index}`]: true };
          this.$nextTick(() => document.getElementById(id)?.scrollIntoView({ block: 'start' }));
          return;
        }
      }
    },
    toggle(catName, index) {
      const key = `${catName}-${index}`;
      this.openItems = { ...this.openItems, [key]: !this.openItems[key] };
    },
    isOpen(catName, index) {
      return !!this.openItems[`${catName}-${index}`];
    },
    loadRecaptcha() {
      if (!window.grecaptcha && this.recaptchaSiteKey) {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${this.recaptchaSiteKey}`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
      }
    },
    async getRecaptchaToken() {
      return new Promise((resolve) => {
        if (window.grecaptcha && window.grecaptcha.ready && this.recaptchaSiteKey) {
          window.grecaptcha.ready(() => {
            window.grecaptcha.execute(this.recaptchaSiteKey, { action: 'contact_form' })
              .then(resolve)
              .catch(() => resolve(null));
          });
        } else {
          resolve(null);
        }
      });
    },
    async submitQuestion() {
      this.isLoading = true;
      this.emailSent = false;
      this.sendError = null;
      try {
        const recaptchaToken = await this.getRecaptchaToken();
        this.formData.recaptcha_token = recaptchaToken;

        // reCAPTCHA is required; a blocked script means no token and the server would refuse.
        if (this.recaptchaSiteKey && !recaptchaToken) {
          this.sendError = RECAPTCHA_MESSAGE;
          return;
        }

        const response = await this.$axios.post('/api/v1/contact', {
          battletag: this.formData.battletag,
          email: this.formData.email,
          message: this.formData.message,
          website: this.formData.website,
          recaptcha_token: this.formData.recaptcha_token,
        });

        if (response.data === 'success') {
          this.emailSent = true;
          this.formData.battletag = '';
          this.formData.email = '';
          this.formData.message = '';
        }
      } catch (e) {
        if (e.response?.data?.error === 'recaptcha_failed') {
          this.sendError = RECAPTCHA_MESSAGE;
        }
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Links in an answer, in the redesign's teal */
.faq-answer :deep(a) {
  color: #00c0c0;
  font-weight: 600;
}
.faq-answer :deep(a:hover) {
  text-decoration: underline;
}
</style>
