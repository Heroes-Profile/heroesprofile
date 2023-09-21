<template>
  <div>
    <div class="flex items-center mb-3">
      <input type="text" class="form-control search-input mr-3" :placeholder="labelText" :aria-label="labelText" aria-describedby="basic-addon2" v-model="userinput" @keyup.enter="clickedButton">
      <button v-if="buttonText" class="btn btn-outline-secondary" type="button" @click="clickedButton">{{ buttonText }}</button>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'SearchComponentNav',
    components: {
    },
    props: {
    },
    data(){
      return {
        type: "alt",
        buttonText: "Find Player",
        labelText: "Enter a battletag",
        userinput: "",
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
        window.location.href = '/battletag/searched/' + this.userinput + "/" + this.type;
      }
    }
  }
</script>
