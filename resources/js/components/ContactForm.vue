<template>
  <div>
    <page-heading :infoText1="infoText1" heading="Contact"></page-heading>

    <div>
      <form @submit.prevent="submitForm" class="flex flex-col max-w-[1500px] mx-auto p-4 gap-2">
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
      infoText1: "If you find an issue on the website, or have general questions, please contact us directly at ZEMILL@heroesprofile.com, or fill out the form below",
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