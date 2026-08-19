<template>
  <api-form-card heading="Reset Your Password" :infoText1="infoText" :errors="errors" :status="status">
    <form method="POST" action="/Api/Password/Forgot" class="flex flex-col gap-2">
      <input type="hidden" name="_token" :value="csrf">

      <label for="email">Email:</label>
      <input class="form-control search-input text-black" type="email" id="email" name="email" v-model="email" required autofocus>

      <button type="submit" class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4 w-auto ml-auto my-2">Send Reset Link</button>
    </form>

    <div class="text-sm">
      <a href="/Api/Login" class="underline hover:text-lteal">Back to sign in</a>
    </div>
  </api-form-card>
</template>

<script>
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
    return {
      infoText: "Enter the email on your API account and we will send you a link to set a new password.",
      email: this.oldemail || '',
    }
  },
}
</script>
