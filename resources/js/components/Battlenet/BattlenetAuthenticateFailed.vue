<template>
  <div>
    <div class="flex items-end my-20 justify-center">
      <h1>
        Log in Failed.
      </h1>
      <custom-button class="m-2" :href="'/Authenticate/Battlenet'" :text="'Try and log in again'" :alt="'Try and log in again'" :size="'medium'" :color="'blue'"></custom-button>
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
      selectedRegion: null,
      filters: {
        type: Object,
        required: true
      },
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
  }
  }
</script>