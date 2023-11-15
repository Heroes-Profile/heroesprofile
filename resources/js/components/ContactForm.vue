<template>
  <div>
    <page-heading :infoText1="infoText1" heading="Contact"></page-heading>

    <div>
      <form @submit.prevent="submitForm">
        <label for="name">Battletag:</label>
        <input class="text-black" type="text" id="name" v-model="formData.battletag" required>

        <label for="email">Email:</label>
        <input class="text-black" type="email" id="email" v-model="formData.email" required>

        <label for="message">Message:</label>
        <textarea class="text-black" id="message" v-model="formData.message" required></textarea>

        <button type="submit">Submit</button>
      </form>
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

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      console.log(this.formData.name);
      try{
        const response = await this.$axios.post("/api/v1/contact", {
          battletag: this.formData.battletag,
          email: this.formData.email,
          message: this.formData.message,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        this.data = response.data[0];
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