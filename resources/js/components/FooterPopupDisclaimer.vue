<template>
  <!-- Floats above the page until accepted, so it doesn't read as part of the footer -->
  <div v-if="popUpVar" class="fixed bottom-4 inset-x-4 z-40 flex justify-center pointer-events-none">
    <div class="pointer-events-auto w-full max-w-[1100px] bg-surface-4 rounded-2xl border-2 border-red shadow-[0_8px_32px_rgba(0,0,0,0.7)] p-5 md:p-6 flex max-md:flex-col items-center gap-4 md:gap-8">
      <p class="text-sm md:text-base flex-1">
        Heroes Profile is funded by its developers. If you like the site, consider donating to our Patreon. even $5 helps. The data on this website relies on replays uploaded to Heroes Profile API. Make sure you <a class="underline hover:text-header" href="/Upload">upload your data with the Heroes Profile Uploader</a>. If you do not upload your replays, your data will not be accurate.
        This site uses cookies in order to store your recent profile views. To learn more, view our <a class="underline hover:text-header" href="/Privacy/Policy">Privacy Policy</a>.
      </p>
      <custom-button class="shrink-0 max-md:w-full text-center" color="red" @click="closePopup" text="Accept and close" alt="Accept and close" :ignoreclick="true"></custom-button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FooterPopupDisclaimer',
  components: {
  },
  props: {
  },
  data(){
    return {
      popUpVar: true,
    }
  },
  created(){
    try {
      if (typeof localStorage !== 'undefined' && localStorage !== null) {
        if (localStorage.getItem('popupDisclaimer')) {
            this.popUpVar = false;
        }
      } else {
        this.popUpVar = false;
      }
    } catch (error) {
      this.popUpVar = false;
    }

      
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    closePopup(){
      localStorage.setItem('popupDisclaimer', 'true');
      this.popUpVar = false;
    }
  }
}
</script>