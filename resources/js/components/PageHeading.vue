<template>
  <div  class="flex py-0 px-10 md:px-20 items-center md:gap-20 text-center md:text-left bg-teal flex-col md:flex-row">
     <slot>
    </slot>

    
    <a v-if="headingImage" :href="headingImageUrl" target="_blank">
      <img :src="headingImage" alt="" class="w-33 h-20"/>
    </a>

   

    <h1 v-else class=" text-xl md:text-3xl">{{heading}}</h1>
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
    headingImage: String,
    headingImageUrl: String,
    infoText1: String,
    infoText2: String,
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