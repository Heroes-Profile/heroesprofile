<template>
  <!-- A player's name link with their flairs beside it. The chevron floats past the name on hover and the flairs slide over, so the name never moves. -->
  <span class="inline-flex items-center">
    <a :href="href" :target="newTab ? '_blank' : null" :rel="newTab ? 'noopener' : null" class="peer/name group/bt relative inline-flex items-center" @click="redirect && $redirectToProfile(battletag, blizzId, region, false)">
      <span class="group-hover/bt:underline">{{ battletag }}</span>
      <i class="fas fa-chevron-right text-xs absolute left-full ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i>
    </a>
    <span class="inline-flex items-center ml-1 transition-transform peer-hover/name:translate-x-4">
      <icon-with-hover v-if="hpOwner" class="[&>span]:h-full" size="small" icon="fas fa-crown" title="info" popupsize="small" style="color:gold">
        <p class="max-sm:text-xs">Site Owner</p>
      </icon-with-hover>
      <icon-with-hover v-else-if="patreon" class="[&>span]:h-full" size="small" icon="fas fa-star" title="info" popupsize="small" style="color:gold">
        <p class="max-sm:text-xs">Patreon Subscriber</p>
      </icon-with-hover>
      <void-eye-flair v-if="voidEye" class="[&>span]:h-full" :blizz-id="blizzId" :region="region"></void-eye-flair>
    </span>
  </span>
</template>

<script>
export default {
  name: 'PlayerName',
  props: {
    battletag: String,
    blizzId: [String, Number],
    region: [String, Number],
    // Defaults to their profile
    link: String,
    // Use the site's profile redirect on click (normal player pages)
    redirect: Boolean,
    newTab: Boolean,
    hpOwner: Boolean,
    patreon: Boolean,
    // Esports rows have no void eye
    voidEye: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    href() {
      return this.link ?? `/Player/${this.battletag}/${this.blizzId}/${this.region}`;
    },
  },
};
</script>
