<template>
  <api-form-card heading="Choose A New Password" icon="fas fa-key" :infoText1="infoText" :errors="errors">
    <form method="POST" action="/Api/Password/Reset" class="flex flex-col gap-3">
      <input type="hidden" name="_token" :value="csrf">
      <input type="hidden" name="token" :value="token">

      <label class="text-subtext" for="email">{{ $t('Email:') }}</label>
      <input class="h-[3.125rem] rounded-control bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="email" id="email" name="email" v-model="emailValue" required>

      <label class="text-subtext" for="password">{{ $t('New Password:') }}</label>
      <input class="h-[3.125rem] rounded-control bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="password" id="password" name="password" required autofocus>
      <span class="text-sm text-subtext">{{ $t('At least 8 characters.') }}</span>

      <label class="text-subtext" for="password_confirmation">{{ $t('Confirm New Password:') }}</label>
      <input class="h-[3.125rem] rounded-control bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white px-4 w-full min-w-0 placeholder:text-subtext/70" type="password" id="password_confirmation" name="password_confirmation" required>

      <button type="submit" class="btn bg-teal hover:bg-lteal w-full text-lg font-semibold disabled:opacity-50 disabled:hover:bg-teal mt-2">{{ $t('Reset Password') }}</button>
    </form>
  </api-form-card>
</template>

<script>
export default {
  name: 'ApiResetPassword',
  components: {
  },
  props: {
    csrf: String,
    token: String,
    email: String,
    errors: {
      type: Array,
      default: () => [],
    },
  },
  data(){
    return {
      infoText: "Set a new password for your API account.",
      emailValue: this.email || '',
    }
  },
}
</script>
