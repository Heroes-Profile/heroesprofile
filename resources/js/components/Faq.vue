<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'Frequently asked questions about Heroes Profile. Can\'t find your answer? Ask us below.'" -->
    <page-heading :heading="'FAQ'"></page-heading>

    <div class="mx-auto max-w-[62.5rem] px-4 mt-6">

      <!-- Search -->
      <div class="mb-8">
        <div class="relative">
          <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-teal text-sm"></i>
          <input
            v-model="search"
            type="text"
            :placeholder="$t('Search FAQ')"
            :aria-label="$t('Search FAQ')"
            class="w-full bg-field text-white rounded-control pl-9 pr-3 py-2 text-sm"
          />
        </div>
      </div>

      <!-- No results -->
      <div v-if="filteredCategories.length === 0" class="text-center text-subtext py-10">
        {{ $t('No results found for ":search". Try a different keyword or ask us below.', { search: search }) }}
      </div>

      <!-- FAQ Categories -->
      <div v-for="category in filteredCategories" :key="category.name" class="mb-8">
        <panel-box :title="category.name">
        <div class="overflow-hidden -mx-4 md:-mx-6 -mb-4 md:-mb-6">
          <div
            v-for="(item, index) in category.items"
            :key="index"
            :id="item.id"
            class="border-b border-gray-dark last:border-b-0 scroll-mt-24"
          >
            <button
              @click="toggle(category.name, index)"
              class="w-full text-left px-6 py-4 flex justify-between items-center hover:bg-white/5 transition-colors"
            >
              <span class="font-medium pr-4">{{ item.q }}</span>
              <span class="shrink-0 text-teal text-xl leading-none">
                {{ isOpen(category.name, index) ? '−' : '+' }}
              </span>
            </button>
            <div v-if="isOpen(category.name, index)" class="px-6 pb-5 text-soft leading-relaxed">
              <div v-html="item.a"></div>
            </div>
          </div>
        </div>
        </panel-box>
      </div>

      <!-- Site Pages: config/faq.php, shared with the help chat -->
      <div id="site-pages" class="mt-10 mb-8">
        <panel-box :title="$t('Site Pages')">
        <div class="flex flex-col gap-6">
          <div v-for="section in faqPages" :key="section.name">
            <h3 class="font-bold mb-1">{{ section.name }}</h3>
            <p v-if="section.note" class="text-sm text-soft mb-2">{{ section.note }}</p>
            <scroll-hint-container :fit="true">
            <table class="patch-history-table" style="border-collapse:collapse; font-size:0.875rem; width:100%;">
              <thead>
                <tr>
                  <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('Page') }}</th>
                  <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('URL') }}</th>
                  <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('What it does') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="page in section.items" :key="page.url">
                  <td style="padding:0.1875rem 0.5rem; white-space:nowrap;">{{ page.name }}</td>
                  <td style="padding:0.1875rem 0.5rem; white-space:nowrap;">
                    <a v-if="!page.url.includes('{')" :href="page.url" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ page.url }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                    <span v-else>{{ page.url }}</span>
                    <div v-if="page.hero_url" class="text-xs text-soft">{{ $t('One hero:') }} <a :href="page.hero_url.replace('{hero}', 'Abathur')" class="underline">{{ page.hero_url.replace('{hero}', 'Abathur') }}</a></div>
                  </td>
                  <td style="padding:0.1875rem 0.5rem;">{{ page.description }}</td>
                </tr>
              </tbody>
            </table>
            </scroll-hint-container>
          </div>
        </div>
        </panel-box>
      </div>

      <!-- Patch & Season History -->
      <div id="patch-history" class="mt-10 mb-8">
        <panel-box :title="$t('Patch & Season History')">
        <div>
          <scroll-hint-container :fit="true">
          <table class="patch-history-table" style="border-collapse:collapse; font-size:0.875rem; width:100%;">
            <thead>
              <tr>
                <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('Season') }}</th>
                <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('Game Version') }}</th>
                <th style="padding:0.25rem 0.5rem; text-align:left; white-space:nowrap;">{{ $t('Start Date') }}</th>
                <th style="padding:0.25rem 0.5rem; text-align:left; white-space:nowrap;">{{ $t('End Date') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in patchHistory" :key="i">
                <td style="padding:0.1875rem 0.5rem;">{{ row.season }}</td>
                <td style="padding:0.1875rem 0.5rem;">
                  <a v-if="row.patch_notes_url" :href="row.patch_notes_url" target="_blank" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ row.game_version }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                  <span v-else>{{ row.game_version }}</span>
                </td>
                <td style="padding:0.1875rem 0.5rem;">{{ formatDate(row.start_date) }}</td>
                <td style="padding:0.1875rem 0.5rem;">{{ row.end_date ? formatDate(row.end_date) : $t('Present') }}</td>
              </tr>
            </tbody>
          </table>
          </scroll-hint-container>
        </div>
        </panel-box>
      </div>

      <!-- Season Dates -->
      <div id="season-dates" class="mt-10 mb-8">
        <panel-box :title="$t('Season Dates')">
        <div>
          <p class="text-sm text-soft mb-3">{{ $t('Shown in your time zone.') }}</p>
          <scroll-hint-container :fit="true">
          <table class="patch-history-table" style="border-collapse:collapse; font-size:0.875rem; width:100%;">
            <thead>
              <tr>
                <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('Season') }}</th>
                <th style="padding:0.25rem 0.5rem; text-align:left; white-space:nowrap;">{{ $t('Start Date') }}</th>
                <th style="padding:0.25rem 0.5rem; text-align:left; white-space:nowrap;">{{ $t('End Date') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in seasonDates" :key="row.id">
                <td style="padding:0.1875rem 0.5rem;">{{ $t(':year Season :season', { year: row.year, season: row.season }) }}</td>
                <td style="padding:0.1875rem 0.5rem;"><format-date :input="row.start_date"></format-date></td>
                <td style="padding:0.1875rem 0.5rem;"><format-date v-if="row.end_date" :input="row.end_date"></format-date></td>
              </tr>
            </tbody>
          </table>
          </scroll-hint-container>
        </div>
        </panel-box>
      </div>

      <!-- Match Awards -->
      <div id="awards" class="mt-10 mb-8">
        <panel-box :title="$t('Match Awards')">
        <div>
          <scroll-hint-container :fit="true">
          <table class="patch-history-table" style="border-collapse:collapse; font-size:0.875rem; width:100%;">
            <thead>
              <tr>
                <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('Award') }}</th>
                <th style="padding:0.25rem 0.5rem; text-align:left;">{{ $t('Description') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="award in awards" :key="award.award_id + '-' + award.title">
                <td style="padding:0.1875rem 0.5rem; white-space:nowrap;">
                  <div class="flex items-center gap-2">
                    <img :src="`/images/awards/${award.icon}_blue.png`" :alt="award.title" class="w-8 h-8" />{{ award.title }}
                  </div>
                </td>
                <td style="padding:0.1875rem 0.5rem;">{{ award.description }}</td>
              </tr>
            </tbody>
          </table>
          </scroll-hint-container>
        </div>
        </panel-box>
      </div>

      <!-- Ask a Question -->
      <div class="mt-12 mb-8">
        <panel-box :title="$t('Still have a question?')">
        <div>
          <p class="text-soft mb-4">
            {{ $t("Can't find what you're looking for? Send us a message and we'll get back to you. You can also email us directly at") }}
            <a href="mailto:Zemill@heroesprofile.com" class="link">Zemill@heroesprofile.com</a>.
          </p>

          <form @submit.prevent="submitQuestion" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block mb-1 text-sm text-soft">{{ $t('BattleTag') }}</label>
                <input
                  v-model="formData.battletag"
                  type="text"
                  class="form-control search-input w-full text-black"
                  required
                />
              </div>
              <div>
                <label class="block mb-1 text-sm text-soft">{{ $t('Email') }}</label>
                <input
                  v-model="formData.email"
                  type="email"
                  class="form-control search-input w-full text-black"
                  required
                />
              </div>
            </div>
            <div>
              <label class="block mb-1 text-sm text-soft">{{ $t('Your Question') }}</label>
              <textarea
                v-model="formData.message"
                class="form-control search-input w-full text-black"
                rows="4"
                required
              ></textarea>
            </div>

            <!-- Honeypot -->
            <input type="text" name="website" v-model="formData.website" style="display:none!important;visibility:hidden!important;position:absolute!important;left:-624.9375rem!important;" tabindex="-1" autocomplete="off">

            <div class="flex items-center gap-4">
              <button
                :disabled="isLoading"
                type="submit"
                class="btn bg-blue hover:bg-lblue px-6"
              >
                {{ $t(isLoading ? $t('Sending...') : $t('Send Question')) }}
              </button>
              <span v-if="emailSent" class="text-teal">{{ $t("Message sent! We'll be in touch.") }}</span>
            </div>
            <p v-if="sendError" class="bg-red p-3 rounded">{{ sendError }}</p>
          </form>
        </div>
        </panel-box>
      </div>

    </div>
  </div>
</template>

<script>
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
    };
  },
  mounted() {
    this.loadRecaptcha();
    this.openFromHash();
  },
  computed: {
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
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: '2-digit', day: '2-digit' });
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
.patch-history-table {
  min-width: 0 !important;
}
</style>
