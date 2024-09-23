<template>
  <div  class="flex py-0 px-10 md:px-20 items-center md:gap-20  md:text-left bg-teal flex-row max-md:py-2 max-md:mt-2 max-md:flex-wrap">
     <slot>
    </slot>

    
    <a v-if="headingImage" :href="headingImageUrl" target="_blank">
      <img :src="headingImage" alt="" class="w-33 h-20"/>
      <span v-if="battletag && !esport"  class="text-lg uppercase ">{{ battletag }}({{ regionstring  }}) </span> {{ heading }}
      <span v-if="battletag && esport"  class="text-lg uppercase ">{{ battletag }} </span> {{ heading }}
    </a>

   

    <h1 v-else class="text-xl md:text-3xl max-md:w-full">
      <div>
      <div class="flex items-center max-md:flex-col text-sm">
        <div v-if="isOwner" class="text-[20px] height-auto">
          <!-- Owner -->
          <icon-with-hover class="mt-2"  size="small"    icon="fas fa-crown"   title="info"  popupsize="small" style="color:gold">
                <slot>
                  <div>
                    <p class="max-sm:text-xs">Site Owner</p>
                  </div>
                </slot>
              </icon-with-hover>
         
        </div>
        <div v-else-if="isPatreon" class="text-[20px] height-auto">
         <!-- Patreon Subscriber -->
         <icon-with-hover class="md:mt-2"  size="small"    icon="fas fa-star"   title="info"  popupsize="small" style="color:gold">
                <slot>
                  <div>
                    <p class="max-sm:text-xs">Patreon Subscriber</p>
                  </div>
                </slot>
          </icon-with-hover>
          
        </div>

        <a v-if="battletag" :href="`/Player/${battletag}/${blizzid}/${region}`" class="text-lg link ">{{ battletag }}({{ regionstring  }}) </a>
        </div>
        <div class="heading">{{ heading }}</div>
      </div>
    </h1>
    <div v-if="!hideText" class=" ">
      <infobox :input="infoText1"></infobox>
      <infobox :input="infoText2"></infobox>
      <slot name="extraInfo"></slot>
    </div>
    <div class="ml-auto" v-else>
      <custom-button @click="hideText = !hideText" text="Read more" alt="Show Header Information" size="small" :ignoreclick="true"></custom-button>
    </div>
  </div>
</template>

<script>
import { stringify } from 'postcss';

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
    battletag: String,
    region: String,
    regionstring: String,
    blizzid: {
      type: [String, Number]
    },
    esport: String,
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