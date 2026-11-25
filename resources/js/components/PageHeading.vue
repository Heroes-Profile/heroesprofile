<template>
  <div :class="['max-w-[120rem] w-full mx-auto px-4 py-6 md:pt-0 flex items-end gap-6 md:gap-20 max-md:flex-wrap max-md:justify-center', hasInfo ? 'md:px-8' : 'md:pl-8 md:pr-0']">
    <!-- A portrait beside the title (Talents, Draft, Matchups, hero pages) sits 24px from it, not the 80px gap kept for the filters -->
    <div v-if="$slots.default" :class="['shrink-0 md:-mr-14 md:self-center', { 'max-md:hidden': portraitDesktopOnly }]">
      <slot></slot>
    </div>


    <!-- An esports player or team gets the profile's heading, with the league logo in the region tag's place -->
    <a v-if="headingImage && !(battletag && esport)" :href="headingImageUrl" target="_blank" class="flex items-center gap-4">
      <img :src="headingImage" :alt="heading || ''" class="h-20 object-contain"/>
      <span v-if="battletag && !esport" class="text-lg uppercase">{{ battletag }}({{ regionstring }})</span>
      <span v-if="battletag && esport" class="text-lg uppercase">{{ battletag }}</span>
      <!-- The page's h1, beside the logo -->
      <h1 v-if="heading" class="text-2xl font-bold">{{ $t(heading) }}</h1>
    </a>

    <h1 v-else class="max-md:w-full max-md:text-center">
      <!-- BattleTag, then its flair, then the region tag, which sits level with the top of the BattleTag (lined up with
           the filters' top) -->
      <div v-if="battletag" class="flex items-center gap-3 flex-wrap md:flex-nowrap max-md:justify-center">
        <span v-if="esport" class="text-3xl md:text-6xl font-bold">{{ battletag }}</span>
        <a v-else :href="`/Player/${battletag}/${blizzid}/${region}`" class="group/bt inline-flex"><span class="text-3xl md:text-6xl font-bold group-hover/bt:underline">{{ battletag }}</span></a>
        <!-- Each flair in a box its own size, centred on the BattleTag (flair-icon, app.css) -->
        <span v-if="(isOwner && !ownerHidden) || isPatreon || (!esport && voidEye)" class="flex items-center gap-2.5 text-[1.25rem]">
          <span v-if="isOwner && !ownerHidden" class="relative inline-flex">
          <icon-with-hover class="owner-crown flair-icon" size="small" icon="fas fa-crown" title="info" popupsize="small">
            <div>
              <p class="max-sm:text-xs">{{ $t('Site Owner') }}</p>
            </div>
          </icon-with-hover>
          <owner-sparks class="owner-sparks-small"></owner-sparks>
          </span>
          <icon-with-hover v-if="isPatreon" class="flair-icon text-gold" size="small" icon="fas fa-star" title="info" popupsize="small">
            <div>
              <p class="max-sm:text-xs">{{ $t('Patreon Subscriber') }}</p>
            </div>
          </icon-with-hover>
          <void-eye-flair v-if="!esport && voidEye" class="flair-icon" :force="true"></void-eye-flair>
        </span>
        <a v-if="esport && headingImage" :href="headingImageUrl" class="self-start shrink-0"><img :src="headingImage" :alt="esport" class="h-9 w-auto max-w-[6rem] object-contain"/></a>
        <region-tag v-else class="self-start" :region="regionstring" :large="true"></region-tag>
      </div>
      <slot name="aboveHeading"></slot>
      <div :class="['heading flex items-center gap-3 max-md:justify-center', battletag ? 'text-base md:text-2xl uppercase text-gray-medium mt-1' : 'text-4xl md:text-6xl font-bold']"><!-- A page can swap the title for its own (the Talents hero switcher) --><slot name="headingText">{{ $t(heading) }}</slot><!-- An info icon sits raised at the top of the title, like a superscript --><span v-if="$slots.headingInfo" class="page-title-info self-start flex"><slot name="headingInfo"></slot></span></div>
      <!-- A short uppercase line under a page title, as under a BattleTag -->
      <!-- scope ("Global") leads the subtext, so every global page says whose numbers they are -->
      <div v-if="(subheading || $slots.subheading) && !battletag" class="text-base md:text-2xl uppercase text-gray-medium mt-2 max-md:text-center"><template v-if="scope">{{ $t(scope) }} · </template><slot name="subheading">{{ $t(subheading) }}</slot></div>
    </h1>
    <div v-if="hasInfo && !hideText" class="text-gray-medium md:ml-auto md:max-w-[50%]">
      <infobox :input="infoText1"></infobox>
      <infobox :input="infoText2"></infobox>
      <slot name="extraInfo"></slot>
    </div>
    <div class="ml-auto" v-else-if="hasInfo">
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
    subheading: String,
    // Put before the subtext with a dot: 'Global' on the global stats pages
    scope: String,
    // The portrait only on desktop; on a phone the title stands alone
    portraitDesktopOnly: Boolean,
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
    hasInfo() {
      return !!(this.infoText1 || this.infoText2 || this.$slots.extraInfo);
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