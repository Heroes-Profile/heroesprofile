<template>
  <div  class="flex py-0 px-10 md:px-20 items-center md:gap-20  md:text-left bg-teal flex-row max-md:py-2 max-md:mt-2 max-md:flex-wrap">
     <slot>
    </slot>

    
    <a v-if="headingImage" :href="headingImageUrl" target="_blank">
      <img :src="headingImage" alt="" class="w-33 h-20"/>
    </a>

   

    <h1 v-else class="text-xl md:text-3xl max-md:w-full">
      <div class="flex items-center">
        <div v-if="isOwner">
          <!-- Owner -->
          <i class="fas fa-crown text" style="color:gold;"></i>
        </div>
        <div v-else-if="isPatreon" class="">
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
    <div class="ml-auto" v-else>
      <custom-button @click="hideText = !hideText" text="Read more" alt="Show Header Information" size="small" :ignoreclick="true"></custom-button>
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
    isPatreon: Boolean,
    isOwner: Boolean,
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