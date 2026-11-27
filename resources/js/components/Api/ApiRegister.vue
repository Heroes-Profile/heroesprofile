<template>
  <api-form-card heading="Create an account" icon="fas fa-plus" :infoText1="infoText" :errors="errors">
    <form method="POST" action="/Api/Register" class="flex flex-col gap-4">
      <input type="hidden" name="_token" :value="csrf">

      <label class="flex flex-col gap-2 text-subtext">{{ $t('Name') }}
        <input class="h-[3.125rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="text" id="name" name="name" v-model="name" required autofocus>
      </label>

      <label class="flex flex-col gap-2 text-subtext">{{ $t('Email') }}
        <input class="h-[3.125rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="email" id="email" name="email" v-model="email" placeholder="name@example.com" required>
      </label>

      <div class="grid sm:grid-cols-2 gap-4">
        <label class="flex flex-col gap-2 text-subtext">{{ $t('Password') }}
          <input class="h-[3.125rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="password" id="password" name="password" required>
        </label>
        <label class="flex flex-col gap-2 text-subtext">{{ $t('Confirm password') }}
          <input class="h-[3.125rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="password" id="password_confirmation" name="password_confirmation" required>
        </label>
      </div>
      <span class="-mt-1 text-sm text-subtext">{{ $t('At least 8 characters.') }}</span>

      <label class="flex items-center gap-3 rounded bg-surface-1 px-4 py-3 text-sm cursor-pointer">
        <input type="checkbox" id="terms" name="terms" value="1" class="form-checkbox shrink-0" v-model="agreed" required>
        <span>
          {{ $t('I agree to the') }}
          <a href="/Api/Terms" target="_blank" class="font-semibold text-teal link">{{ $t('Terms of Service') }}</a>
          {{ $t('and') }}
          <a href="/Api/Privacy" target="_blank" class="font-semibold text-teal link">{{ $t('Privacy Policy') }}</a>
        </span>
      </label>

      <button type="submit" class="btn bg-teal hover:bg-lteal w-full text-lg font-semibold disabled:opacity-50 disabled:hover:bg-teal mt-2" :disabled="!agreed">{{ $t('Create account') }}</button>
    </form>

    <template #footer>
      <span>{{ $t('Already have an account?') }} <a href="/Api/Login" class="font-semibold text-teal link">{{ $t('Sign in') }}</a></span>
    </template>
  </api-form-card>
</template>

<script>
export default {
  name: 'ApiRegister',
  components: {
  },
  props: {
    csrf: String,
    errors: {
      type: Array,
      default: () => [],
    },
    oldname: String,
    oldemail: String,
  },
  data(){
    return {
      infoText: "Create an account to generate an API key. Your account works on both the current API site and the new one.",
      name: this.oldname || '',
      email: this.oldemail || '',
      // The button waits for the terms box
      agreed: false,
    }
  },
}
</script>
