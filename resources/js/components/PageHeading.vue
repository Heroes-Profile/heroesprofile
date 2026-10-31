<template>
  <div class="max-w-[1920px] w-full mx-auto px-4 md:px-8 py-6 md:pt-0 flex items-end gap-6 md:gap-20 max-md:flex-wrap">
     <slot>
    </slot>


    <a v-if="headingImage" :href="headingImageUrl" target="_blank" class="flex items-center gap-4">
      <img :src="headingImage" alt="" class="w-33 h-20"/>
      <span v-if="battletag && !esport" class="text-lg uppercase">{{ battletag }}({{ regionstring }})</span>
      <span v-if="battletag && esport" class="text-lg uppercase">{{ battletag }}</span>
      <span class="text-2xl font-bold">{{ heading }}</span>
    </a>

    <h1 v-else class="max-md:w-full">
      <div v-if="battletag" class="flex items-center gap-3 flex-wrap">
        <a :href="`/Player/${battletag}/${blizzid}/${region}`" class="text-3xl md:text-4xl font-bold">{{ battletag }}</a>
        <region-tag :region="regionstring"></region-tag>
        <div class="flex items-center gap-1 text-[20px]">
          <icon-with-hover v-if="isOwner && !ownerHidden" size="small" icon="fas fa-crown" title="info" popupsize="small" style="color:gold">
            <div>
              <p class="max-sm:text-xs">Site Owner</p>
            </div>
          </icon-with-hover>
          <icon-with-hover v-else-if="isPatreon" size="small" icon="fas fa-star" title="info" popupsize="small" style="color:gold">
            <div>
              <p class="max-sm:text-xs">Patreon Subscriber</p>
            </div>
          </icon-with-hover>
          <void-eye-flair v-if="!esport && voidEye" class="md:-translate-y-[0.3em]" :force="true"></void-eye-flair>
        </div>
      </div>
      <slot name="aboveHeading"></slot>
      <div :class="['heading', battletag ? 'text-base text-gray-medium mt-1' : 'text-3xl md:text-4xl font-bold']">{{ heading }}</div>
    </h1>
    <div v-if="!hideText" class="text-gray-medium md:ml-auto md:max-w-[50%]">
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
import { hasVoidEye, ownerFlairHidden } from '../voidEye';

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
    if(this.infoTextTotalCharacters > 250 && window.innerWidth < 1024){
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
    voidEye() {
      return hasVoidEye(this.blizzid, this.region);
    },
    ownerHidden() {
      return this.isOwner && ownerFlairHidden();
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>