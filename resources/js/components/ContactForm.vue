<template>
  <div>
    <page-heading :infoText1="infoText1" heading="Contact"></page-heading>

    <div>
      <!-- Contact form -->
      <form @submit.prevent="submitForm" class="flex flex-col max-w-[1500px] mx-auto p-4 gap-2">
        <label for="name">Battletag:</label>
        <input class="form-control search-input mr-3 text-black" type="text" id="name" v-model="formData.battletag" required>

        <label for="email">Email:</label>
        <input class="form-control search-input mr-3 text-black" type="email" id="email" v-model="formData.email" required>

        <label for="message">Message:</label>
        <textarea class="form-control search-input mr-3 text-black" id="message" v-model="formData.message" required></textarea>

        <!-- Honeypot field - hidden from users but visible to bots -->
        <input type="text" name="website" v-model="formData.website" style="display: none !important; visibility: hidden !important; position: absolute !important; left: -9999px !important;" tabindex="-1" autocomplete="off">

        <button :disabled="isLoading" type="submit" class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4 w-auto ml-auto mr-2 my-2">Submit</button>
      </form>
      <div v-if="emailSent" class="mx-auto max-w-[1500px] bg-teal p-4 text-center">
        <p>Email Sent Successfully!</p>
      </div>

    </div>

  </div>
</template>

<script>
export default {
  name: 'ExampleComponent',
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
      infoText1: "If you find an issue on the website, or have general questions, please use the contact form below or email us directly at ZEMILL@heroesprofile.com",
      emailSent: false,
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

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        // Get reCAPTCHA token
        const recaptchaToken = await this.getRecaptchaToken();
        this.formData.recaptcha_token = recaptchaToken;

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
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
  }
}
</script>