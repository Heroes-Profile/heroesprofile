<template>
  <api-form-card heading="Sign In" :infoText1="infoText" :errors="errors" :status="status">
    <form method="POST" action="/Api/Login" class="flex flex-col gap-2">
      <input type="hidden" name="_token" :value="csrf">

      <label for="email">Email:</label>
      <input class="form-control search-input text-black" type="email" id="email" name="email" v-model="email" required autofocus>

      <label for="password">Password:</label>
      <input class="form-control search-input text-black" type="password" id="password" name="password" required>

      <label class="flex items-center gap-2 my-2">
        <input type="checkbox" name="remember" value="1">
        Remember me
      </label>

      <button type="submit" class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4 w-auto ml-auto my-2">Sign In</button>
    </form>

    <div class="text-sm space-y-1">
      <div><a href="/Api/Password/Forgot" class="underline hover:text-lteal">Forgot your password?</a></div>
      <div>No account? <a href="/Api/Register" class="underline hover:text-lteal">Register</a></div>
    </div>
  </api-form-card>
</template>

<script>
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
  },
  data(){
    return {
      infoText: "Sign in to your Heroes Profile API account. This is separate from a Heroes Profile site login. If you already have an API key, these are the same credentials you use today.",
      email: this.oldemail || '',
    }
  },
}
</script>
