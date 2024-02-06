<template>
  <div>
    <div class="flex flex-col items-center my-20 justify-center " > 
      <h1 class="pt-40 pb-20 text-black w-[10em] text-center font-bold" style="background-image:url('/images/miscellaneous/flames.png'); background-size:10em; background-position:center; background-repeat:no-repeat;">
        Log in Failed.
      </h1>
      <custom-button class="m-2" :href="'/Authenticate/Battlenet'" :text="'Try and log in again'" :alt="'Try and log in again'" :size="'medium'" :color="'red'"></custom-button>
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