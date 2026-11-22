<template>
  <api-form-card heading="Choose A New Password" :infoText1="infoText" :errors="errors">
    <form method="POST" action="/Api/Password/Reset" class="flex flex-col gap-2">
      <input type="hidden" name="_token" :value="csrf">
      <input type="hidden" name="token" :value="token">

      <label for="email">{{ $t('Email:') }}</label>
      <input class="form-control search-input text-black" type="email" id="email" name="email" v-model="emailValue" required>

      <label for="password">{{ $t('New Password:') }}</label>
      <input class="form-control search-input text-black" type="password" id="password" name="password" required autofocus>
      <span class="text-xs text-gray-medium">{{ $t('At least 8 characters.') }}</span>

      <label for="password_confirmation">{{ $t('Confirm New Password:') }}</label>
      <input class="form-control search-input text-black" type="password" id="password_confirmation" name="password_confirmation" required>

      <button type="submit" class="btn bg-blue hover:bg-lblue w-auto ml-auto my-2">{{ $t('Reset Password') }}</button>
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
