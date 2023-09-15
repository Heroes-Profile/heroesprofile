<template>
  <div  class="flex py-10 px-10 md:px-20 items-center md:gap-20 text-center md:text-left bg-teal flex-col md:flex-row">
    <h1 class=" text-xl md:text-3xl">{{heading}}</h1>
    <div>
      <infobox :input="infoText1"></infobox>
      <infobox :input="infoText2"></infobox>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PageHeading',
  components: {
  },
  props: {
    heading: String,
    infoText1: String,
    infoText2: String
  },
  data(){
    return {
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