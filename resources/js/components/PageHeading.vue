<template>
  <div  class="flex py-0 px-10 md:px-20 items-center md:gap-20 text-center md:text-left bg-teal flex-col md:flex-row max-md:pb-4 max-md:mt-2">
     <slot>
    </slot>

    
    <a v-if="headingImage" :href="headingImageUrl" target="_blank">
      <img :src="headingImage" alt="" class="w-33 h-20"/>
    </a>

   

    <h1 v-else class="text-xl md:text-3xl">
      <div class="flex items-center">
        <div class="" v-if="isPatreon">
         <!-- Patreon Subscriber -->
          <i class="fas fa-star" style="color:gold"></i>
        </div>
        {{ heading }}
      </div>
    </h1>
    <div v-if="!hideText">
      <infobox :input="infoText1"></infobox>
      <infobox :input="infoText2"></infobox>
    </div>
    <div v-else>
      <custom-button @click="hideText = !hideText" text="Show Header Information" alt="Show Header Information" size="small" :ignoreclick="true">Show Header Information</custom-button>
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
    isPatreon: false,
  },
  data(){
    return {
      hideText: false,
    }
  },
  created(){
    if(this.infoTextTotalCharacters > 250 && window.innerWidth < 768){
      this.hideText = true;
    }
  },
  mounted() {
  },
  computed: {
    infoTextTotalCharacters() {
      const combinedText = (this.infoText1 || '') + (this.infoText2 || '');
      return combinedText.length;
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>