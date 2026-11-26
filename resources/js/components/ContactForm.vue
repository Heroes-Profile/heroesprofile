<template>
  <div>
    <page-heading heading="Contact" subheading="Get in touch with Heroes Profile"></page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 grid xl:grid-cols-[1188fr_700fr] gap-6 xl:gap-8 items-start">
      <panel-box title="Send us a message" subtitle="Questions, feedback or a problem with your profile. We reply by email." :divider="false">
        <form @submit.prevent="submitForm" class="flex flex-col gap-6">
          <div class="grid sm:grid-cols-2 gap-4">
            <label class="flex flex-col gap-2">
              <span class="text-subtext">{{ $t('BattleTag') }}*</span>
              <input class="w-full bg-field text-white rounded px-4 h-12" type="text" id="name" v-model="formData.battletag" placeholder="Name#1234" required>
            </label>
            <label class="flex flex-col gap-2">
              <span class="text-subtext">{{ $t('Email') }}*</span>
              <input class="w-full bg-field text-white rounded px-4 h-12" type="email" id="email" v-model="formData.email" placeholder="you@example.com" required>
            </label>
          </div>
          <label class="flex flex-col gap-2">
            <span class="text-subtext">{{ $t('Message') }}*</span>
            <textarea class="w-full bg-field text-white rounded px-4 py-3 min-h-[9.875rem]" id="message" v-model="formData.message" :placeholder="$t('Tell us what happened, which page it was on and your region if it\'s about a profile.')" required></textarea>
          </label>

          <!-- Honeypot field - hidden from users but visible to bots -->
          <input type="text" name="website" v-model="formData.website" style="display: none !important; visibility: hidden !important; position: absolute !important; left: -624.9375rem !important;" tabindex="-1" autocomplete="off">

          <div class="flex flex-wrap items-center justify-between gap-4">
            <span class="flex items-center gap-2 text-sm text-subtext"><basil-icon name="info"></basil-icon>{{ $t('All fields are required.') }}</span>
            <button :disabled="isLoading" type="submit" class="btn bg-teal hover:bg-lteal font-semibold disabled:opacity-60">{{ $t('Send message') }}</button>
          </div>

          <p v-if="emailSent" class="bg-teal/20 text-white rounded px-4 py-3">{{ $t('Email Sent Successfully!') }}</p>
          <p v-if="sendError" class="bg-red/20 text-white rounded px-4 py-3">{{ sendError }}</p>
        </form>
      </panel-box>

      <panel-box title="Other ways to reach us" subtitle="For bugs and feature ideas, GitHub is the fastest way." :divider="false">
        <div class="flex flex-col gap-3">
          <a v-for="link in otherWays" :key="link.title" :href="link.href" :target="link.external ? '_blank' : null" :rel="link.external ? 'noopener noreferrer' : null"
            class="group/bt flex items-center gap-4 rounded-control bg-surface-1 hover:bg-surface-3 transition-colors p-4">
            <span :class="['w-12 h-12 shrink-0 rounded flex items-center justify-center text-xl', link.iconClass]"><i :class="link.icon"></i></span>
            <span class="flex flex-col min-w-0">
              <span class="font-semibold">{{ $t(link.title) }}</span>
              <span class="text-sm text-subtext truncate">{{ link.external ? $t(link.subtitle) : link.subtitle }}</span>
            </span>
            <basil-icon name="arrow-small" class="ml-auto text-sm"></basil-icon>
          </a>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
const RECAPTCHA_MESSAGE = "We couldn't verify this message with reCAPTCHA. Please allow reCAPTCHA (google.com) in your browser or blocker and try again, or email contact@heroesprofile.com directly.";

export default {
  name: 'ContactForm',
  components: {
  },
  props: {
    recaptchaSiteKey: {
      type: String,
      default: ''
    }
  },
  data(){
    return {
      cancelTokenSource: null,
      isLoading: null,
      formData: {
        battletag: '',
        email: '',
        message: '',
        website: '', // Honeypot field - should remain empty
        recaptcha_token: '', // reCAPTCHA token
      },
      // The same addresses the nav's Help and Donate menus use
      otherWays: [
        { title: 'Email', subtitle: 'Zemill@heroesprofile.com', href: 'mailto:zemill@heroesprofile.com', icon: 'fas fa-at', iconClass: 'bg-teal/20 text-lteal' },
        { title: 'Report a bug', subtitle: 'Open an issue on GitHub', href: 'https://github.com/Heroes-Profile/heroesprofile/issues/new?assignees=&labels=&projects=&template=bug_report.md', icon: 'fas fa-bullhorn', iconClass: 'bg-red/20 text-lred', external: true },
        { title: 'Request a feature', subtitle: 'Start a discussion on GitHub', href: 'https://github.com/Heroes-Profile/heroesprofile/discussions/new?category=ideas', icon: 'fas fa-square-plus', iconClass: 'bg-blue/60 text-[#5aa9ff]', external: true },
        { title: 'Patreon', subtitle: 'Support the site and remove ads', href: 'https://www.patreon.com/c/heroesprofile', icon: 'fas fa-heart', iconClass: 'bg-red/20 text-lred', external: true },
      ],
      emailSent: false,
      sendError: null,
    }
  },
  created(){
  },
  mounted() {
    this.loadRecaptcha();
  },
  computed: {
  },
  watch: {
  },
  methods: {
    loadRecaptcha() {
      // Load reCAPTCHA script if not already loaded
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
              .then((token) => {
                resolve(token);
              })
              .catch(() => {
                resolve(null);
              });
          });
        } else {
          resolve(null);
        }
      });
    },

    async submitForm() {
      this.isLoading = true;
      this.emailSent = false;
      this.sendError = null;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        // Get reCAPTCHA token
        const recaptchaToken = await this.getRecaptchaToken();
        this.formData.recaptcha_token = recaptchaToken;

        // reCAPTCHA is required; a blocked script means no token and the server would refuse.
        if (this.recaptchaSiteKey && !recaptchaToken) {
          this.sendError = RECAPTCHA_MESSAGE;
          return;
        }

        const response = await this.$axios.post("/api/v1/contact", {
          battletag: this.formData.battletag,
          email: this.formData.email,
          message: this.formData.message,
          website: this.formData.website, // Honeypot field
          recaptcha_token: this.formData.recaptcha_token, // reCAPTCHA token
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        this.data = response.data;

        if(this.data == "success"){
          this.emailSent = true;
        }
      }catch(error){
        if (error.response?.data?.error === 'recaptcha_failed') {
          this.sendError = RECAPTCHA_MESSAGE;
        }
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
  }
}
</script>