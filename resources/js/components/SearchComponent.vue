<template>
  
  <div class="search-component mt-auto py-4">
    <div class="flex items-stretch">
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
    <p v-if="errorMessage" class="text-red text-sm mt-1">{{ errorMessage }}</p>
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
        labelText: "Enter a battletag",
        prohibitedCharacters: ['*', '?', '%', '\'', '"', '(', ')', ';', '<', '>', '=', '|', '\\'],
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
      isInputValid() {
        return this.userinput && this.userinput.trim().length > 0 && !this.hasProhibitedCharacters;
      },
      hasProhibitedCharacters() {
        if (!this.userinput) return false;
        return this.prohibitedCharacters.some(char => this.userinput.includes(char));
      },
      foundProhibitedCharacters() {
        if (!this.userinput) return [];
        return this.prohibitedCharacters.filter(char => this.userinput.includes(char));
      },
      errorMessage() {
        if (!this.userinput || this.userinput.trim().length === 0) return '';
        if (this.hasProhibitedCharacters) {
          const chars = this.foundProhibitedCharacters.map(c => `"${c}"`).join(', ');
          return `The following characters are not allowed: ${chars}`;
        }
        return '';
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
