<template>
  <!-- Sent: the "Check your email" card, with a resend once the server allows another; otherwise the email form -->
  <api-form-card v-if="sent" heading="Check your email" icon="fas fa-envelope-open" :infoText1="sentText" :errors="errors">
    <div class="flex flex-col gap-4">
      <div class="flex flex-col gap-2 rounded bg-surface-1 px-5 py-4 text-sm">
        <span class="flex items-center gap-2.5 font-semibold"><basil-icon name="info" class="text-lg shrink-0"></basil-icon>{{ $t("Didn't get it?") }}</span>
        <span class="flex items-start gap-2.5 text-subtext"><span class="shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full bg-teal"></span>{{ $t('Check your spam or promotions folder.') }}</span>
        <span class="flex items-start gap-2.5 text-subtext"><span class="shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full bg-teal"></span>{{ $t("Make sure it's the email you registered the API account with.") }}</span>
      </div>
      <form method="POST" action="/Api/Password/Forgot" class="flex flex-col items-center gap-2" @submit="rememberEmail">
        <input type="hidden" name="_token" :value="csrf">
        <input type="hidden" name="email" :value="sentTo">
        <button type="submit" class="btn bg-teal hover:bg-lteal w-full text-lg font-semibold disabled:opacity-50 disabled:hover:bg-teal" :disabled="!sentTo || resendIn > 0">{{ $t('Resend link') }}</button>
        <span v-if="sentTo && resendIn > 0" class="text-xs text-subtext">{{ $t('You can resend in :time', { time: resendClock }) }}</span>
      </form>
    </div>

    <template #footer>
      <a href="/Api/Login" class="font-semibold text-teal hover:underline"><basil-icon name="arrow-left" class="text-xs mr-2"></basil-icon>{{ $t('Back to sign in') }}</a>
      <span>{{ $t('No API account yet?') }} <a href="/Api/Register" class="font-semibold text-teal hover:underline">{{ $t('Register') }}</a></span>
    </template>
  </api-form-card>

  <api-form-card v-else heading="Reset your password" icon="fas fa-envelope" :infoText1="infoText" :errors="errors">
    <form method="POST" action="/Api/Password/Forgot" class="flex flex-col gap-4" @submit="rememberEmail">
      <input type="hidden" name="_token" :value="csrf">

      <label class="flex flex-col gap-2 text-subtext">{{ $t('Email') }}
        <input class="h-[3.125rem] rounded-control bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="email" name="email" v-model="email" placeholder="name@example.com" required autofocus>
      </label>

      <button type="submit" class="btn bg-teal hover:bg-lteal w-full text-lg font-semibold disabled:opacity-50 disabled:hover:bg-teal mt-2">{{ $t('Send reset link') }}</button>
    </form>

    <template #footer>
      <a href="/Api/Login" class="font-semibold text-teal hover:underline"><basil-icon name="arrow-left" class="text-xs mr-2"></basil-icon>{{ $t('Back to sign in') }}</a>
      <span>{{ $t('No API account yet?') }} <a href="/Api/Register" class="font-semibold text-teal hover:underline">{{ $t('Register') }}</a></span>
    </template>
  </api-form-card>
</template>

<script>
// The address the link went to, kept for the sent card and its resend; the server doesn't say, so the same answer shows for any email
const SENT_EMAIL_KEY = 'apiResetEmail';
// The reset broker allows one link a minute (config/auth.php, passwords.api_accounts.throttle)
const RESEND_SECONDS = 60;

export default {
  name: 'ApiForgotPassword',
  components: {
  },
  props: {
    csrf: String,
    errors: {
      type: Array,
      default: () => [],
    },
    status: String,
    oldemail: String,
  },
  data(){
    let sentTo = null;
    try {
      sentTo = sessionStorage.getItem(SENT_EMAIL_KEY);
    } catch (error) {
      // Storage blocked: the card shows without the address or a resend
    }
    return {
      infoText: "Enter the email on your API account and we will send you a link to set a new password.",
      email: this.oldemail || '',
      sentTo,
      resendIn: RESEND_SECONDS,
      timer: null,
    }
  },
  computed: {
    sent() {
      return !!this.status && !this.errors.length;
    },
    sentText() {
      return this.sentTo
        ? this.$t("If an API account exists for :email, we've sent a link to set a new password. The link works for 60 minutes.", { email: this.sentTo })
        : this.$t("If an API account exists for that email, we've sent a link to set a new password. The link works for 60 minutes.");
    },
    resendClock() {
      return Math.floor(this.resendIn / 60) + ':' + String(this.resendIn % 60).padStart(2, '0');
    },
  },
  mounted() {
    if (this.sent) {
      this.timer = setInterval(() => {
        this.resendIn = Math.max(0, this.resendIn - 1);
        if (!this.resendIn) clearInterval(this.timer);
      }, 1000);
    }
  },
  beforeUnmount() {
    clearInterval(this.timer);
  },
  methods: {
    rememberEmail(event) {
      const email = new FormData(event.target).get('email');
      try {
        sessionStorage.setItem(SENT_EMAIL_KEY, email);
      } catch (error) {
        // Storage blocked: the sent card shows without the address
      }
    },
  },
}
</script>
