<template>
  
  <div class="search-component flex items-stretch mt-auto py-4 ">
    <input type="text" class="form-control variable-text rounded-l p-2" :placeholder="labelText" :aria-label="labelText" aria-describedby="basic-addon2" v-model="userinput" @keyup.enter="clickedButton">
    <button
      v-if="buttonText"
      class="btn btn-outline-secondary bg-teal hover:bg-lteal rounded-r p-2"
      type="button"
      :disabled="!isInputValid"
      @click="clickedButton"
    >
      {{ buttonText }}
    </button>
  </div>
  
</template>

<script>
  export default {
    name: 'SearchComponent',
    components: {
    },
    props: {
      type: String,

    },
    data(){
      return {
        userinput: "",
        buttonText: "Find Player",
        labelText: "Enter a battletag"
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
      isInputValid() {
        return this.userinput && this.userinput.trim().length > 0;
      }
    },
    watch: {
    },
    methods: {
      clickedButton(){
        if (!this.isInputValid) {
          return;
        }
        window.location.href = '/battletag/searched/' + encodeURIComponent(this.userinput.trim()) + "/" + this.type;
      }
    }
  }
</script>
