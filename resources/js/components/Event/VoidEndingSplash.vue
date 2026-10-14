<template>
  <div v-if="visible" class="void-fallen-splash fixed inset-0 z-[10000] flex flex-col items-center justify-center text-center text-white px-6">
    <img class="w-40 mb-6 opacity-80" src="/images/event/xalatath/xalatath-logo-stage-0.svg" alt="" />
    <h2 class="font-logo text-4xl md:text-6xl mb-4">The Void Recedes... For Now</h2>
    <p class="max-w-xl mb-8">Xal'atath's whispers have gone quiet and the Nexus is whole again. She left plenty behind, though.</p>
    <div class="flex flex-wrap gap-4 justify-center">
      <a href="/Event/Xalatath" class="inline-block bg-[#5b21b6] hover:bg-[#7b3fe4] text-white text-lg px-10 py-4 rounded" @click="remember">See what she left behind</a>
      <button type="button" class="text-lg px-10 py-4 underline opacity-75 hover:opacity-100" @click="dismiss">Close</button>
    </div>
  </div>
</template>

<script>
const STORAGE_KEY = 'voidEndingSplashSeen';

export default {
  name: 'VoidEndingSplash',
  data() {
    return {
      visible: false,
    };
  },
  created() {
    try {
      this.visible = !localStorage.getItem(STORAGE_KEY) && window.location.pathname !== '/Event/Xalatath';
    } catch (e) {
      this.visible = false;
    }
  },
  methods: {
    remember() {
      try {
        localStorage.setItem(STORAGE_KEY, '1');
      } catch (e) {
        // storage blocked; it may show again next page
      }
    },
    dismiss() {
      this.remember();
      this.visible = false;
    },
  },
};
</script>
