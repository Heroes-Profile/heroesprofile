<template>
  <api-form-card heading="Create An Account" :infoText1="infoText" :errors="errors">
    <form method="POST" action="/Api/Register" class="flex flex-col gap-2">
      <input type="hidden" name="_token" :value="csrf">

      <label for="name">Name:</label>
      <input class="form-control search-input text-black" type="text" id="name" name="name" v-model="name" required autofocus>

      <label for="email">Email:</label>
      <input class="form-control search-input text-black" type="email" id="email" name="email" v-model="email" required>

      <label for="password">Password:</label>
      <input class="form-control search-input text-black" type="password" id="password" name="password" required>
      <span class="text-xs text-gray-medium">At least 8 characters.</span>

      <label for="password_confirmation">Confirm Password:</label>
      <input class="form-control search-input text-black" type="password" id="password_confirmation" name="password_confirmation" required>

      <button type="submit" class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4 w-auto ml-auto my-2">Create Account</button>
    </form>

    <div class="text-sm">
      Already have an account? <a href="/Api/Login" class="underline hover:text-lteal">Sign in</a>
    </div>
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
    }
  },
}
</script>
