<template>
  <div>
    <div class="input-group mb-3">
      <input type="text" class="form-control" placeholder="Enter your battletag" aria-label="Enter your battletag" aria-describedby="basic-addon2" v-model="userinput">
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" @click="clickedButton">Search For Player</button>
      </div>
    </div>


    <h1>Response = {{ this.battletagresponse }}</h1>
  </div>
</template>

<script>
export default {
  name: 'SearchPlayer',
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
    async clickedButton(){
      try{
        const response = await this.$axios.post("/api/v1/battletag/search", {
          userinput: this.userinput,
        });

        this.battletagresponse = response.data;
      this.$emit('onDataReturn', this.battletagresponse);

      }catch(error){
        console.log(error);
        //this.error = error;
        this.battletagresponse = "Invalid input: '%', '?' and ' ' are invalid inputs";
      }
    },
  }
}
</script>