<template>
  <!-- The API sign-in card: the /Api/Login page, and api-login-popup over any API page -->
  <api-form-card heading="Sign in" icon="fas fa-right-to-bracket" :infoText1="infoText" :errors="errors" :status="status" :popup="popup" @close="$emit('close')">
    <form method="POST" action="/Api/Login" class="flex flex-col gap-4" @submit="rememberReturn">
      <input type="hidden" name="_token" :value="csrf">
      <!-- A failed sign-in from the popup comes back to its page, which reopens the popup from this -->
      <input v-if="popup" type="hidden" name="login_popup" value="1">

      <label class="flex flex-col gap-2 text-subtext">{{ $t('Email') }}
        <input class="h-[3.125rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="email" name="email" v-model="email" placeholder="name@example.com" required autofocus>
      </label>

      <label class="flex flex-col gap-2 text-subtext">{{ $t('Password') }}
        <input class="h-[3.125rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="password" name="password" required>
      </label>

      <label class="flex items-center gap-3 rounded bg-surface-1 px-4 py-3 text-sm cursor-pointer">
        <input type="checkbox" name="remember" value="1" class="form-checkbox shrink-0">
        {{ $t('Remember me') }}
      </label>

      <button type="submit" class="btn bg-teal hover:bg-lteal w-full text-lg font-semibold mt-2">{{ $t('Sign in') }}</button>
    </form>

    <template #footer>
      <a href="/Api/Password/Forgot" class="font-semibold text-teal hover:underline">{{ $t('Forgot your password?') }}</a>
      <span>{{ $t('No API account yet?') }} <a href="/Api/Register" class="font-semibold text-teal hover:underline">{{ $t('Register') }}</a></span>
    </template>
  </api-form-card>
</template>

<script>
// The account section to open after signing in (/Api/Login#twitch, from the Twitch page's Set it up); ApiAccount reads it
export const RETURN_SECTION_KEY = 'apiLoginReturnSection';

export default {
  name: 'ApiLogin',
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
    // Inside api-login-popup
    popup: Boolean,
  },
  emits: ['close'],
  data(){
    return {
      infoText: "Sign in to your Heroes Profile API account. This is separate from a Heroes Profile site login. If you already have an API key, these are the same credentials you use today.",
      email: this.oldemail || '',
    }
  },
  methods: {
    rememberReturn() {
      // Only the page's own hash; a popup's page hash belongs to that page
      const section = this.popup ? '' : window.location.hash.slice(1);
      try {
        if (/^[\w-]+$/.test(section)) {
          sessionStorage.setItem(RETURN_SECTION_KEY, section);
        } else {
          sessionStorage.removeItem(RETURN_SECTION_KEY);
        }
      } catch (error) {
        // Storage blocked: sign-in still lands on the account page
      }
    },
  },
}
</script>
