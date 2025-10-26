<template>
  <div>
    <page-heading :infoText1="infoText1" heading="Contact"></page-heading>

    <div>
      <div class="mx-auto max-w-[1500px] bg-gray-200 p-8 text-center rounded-lg">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Contact Form Temporarily Disabled</h3>
        <p class="text-gray-600 mb-4">The contact form is currently unavailable. Please use the email address below for inquiries:</p>
        <p class="text-blue-600 font-medium">ZEMILL@heroesprofile.com</p>
      </div>

      <!-- Disabled form (hidden but kept for potential future re-enabling) -->
      <form @submit.prevent="submitForm" class="flex flex-col max-w-[1500px] mx-auto p-4 gap-2" style="display: none;">
        <label for="name">Battletag:</label>
        <input class="form-control search-input mr-3 text-black" type="text" id="name" v-model="formData.battletag" required>

        <label for="email">Email:</label>
        <input class="form-control search-input mr-3 text-black" type="email" id="email" v-model="formData.email" required>

        <label for="message">Message:</label>
        <textarea class="form-control search-input mr-3 text-black" id="message" v-model="formData.message" required></textarea>

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
  },
  data(){
    return {
      cancelTokenSource: null,
      isLoading: null,
      formData: {
        battletag: '',
        email: '',
        message: '',
      },
      infoText1: "If you find an issue on the website, or have general questions, please contact us directly at ZEMILL@heroesprofile.com",
      emailSent: false,
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async submitForm() {
      this.isLoading = true;
      this.emailSent = false;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$axios.post("/api/v1/contact", {
          battletag: this.formData.battletag,
          email: this.formData.email,
          message: this.formData.message,
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