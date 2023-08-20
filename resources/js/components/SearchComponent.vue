<template>
  <div>
    <div class="input-group mb-3">
      <input type="text" class="form-control" placeholder="Enter your battletag" aria-label="Enter your battletag" aria-describedby="basic-addon2" v-model="userinput">
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" @click="clickedButton">Show My Stats</button>
      </div>
    </div>


    <h1>Response = {{ this.battletagresponse }}</h1>
  </div>
</template>

<script>
export default {
  name: 'SearchComponent',
  components: {
  },
  props: {
  },
  data(){
    return {
      loading: false,
      error: false,
      userinput: "",
      battletagresponse: "no data",
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
    clickedButton(){
      this.isLoading = true;
      let loader = this.$loading.show({
        // Optional parameters
        container: true ? null : this.$refs.formContainer,
        canCancel: false,
        backgroundColor: '#000000',
        color: '#ffffff'
      });

      window.Vue.axios.post("/api/battletag/search", {
        userinput: this.userinput,
      })

      .then(response => {
        this.battletagresponse = response.data;
        loader.hide();
      })
      .catch(error => {
        this.errored = error;
        loader.hide();
      })
      .finally(() => this.loading = false)
    },
  }
}
</script>
