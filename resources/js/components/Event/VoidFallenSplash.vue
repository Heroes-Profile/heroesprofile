<template>
  <div v-if="visible" class="void-fallen-splash fixed inset-0 z-[10000] flex flex-col items-center justify-center text-center text-white px-6">
    <img class="w-40 mb-6" src="/images/event/xalatath/xalatath-logo-stage-5.svg" alt="" />
    <h2 class="font-logo text-4xl md:text-6xl mb-4">The Nexus Has Fallen</h2>
    <p class="max-w-xl mb-8">Xal'atath's games have spread the Void across every battleground. Heroes Profile belongs to her now.</p>
    <custom-button :text="'Accept your fate'" :size="'big'" :ignoreclick="true" class="px-10" @click="dismiss"></custom-button>
  </div>
</template>

<script>
const STORAGE_KEY = 'voidFallenSplashSeen';

export default {
  name: 'VoidFallenSplash',
  data() {
    return {
      visible: false,
    };
  },
  created() {
    try {
      this.visible = !localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      this.visible = false;
    }
  },
  methods: {
    dismiss() {
      try {
        localStorage.setItem(STORAGE_KEY, '1');
      } catch (e) {
        // storage blocked; just hide for this page view
      }
      this.visible = false;
    },
  },
};
</script>
