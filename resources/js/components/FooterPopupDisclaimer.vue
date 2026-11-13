<template>
  <!-- Floats above the page until accepted, so it doesn't read as part of the footer -->
  <div v-if="popUpVar" class="fixed bottom-4 inset-x-4 z-40 flex justify-center pointer-events-none">
    <div ref="card" class="pointer-events-auto w-full max-w-[68.75rem] bg-surface-4 rounded-2xl border-2 border-red shadow-[0_0.5rem_2rem_rgba(0,0,0,0.7)] p-5 md:p-6 flex max-md:flex-col items-center gap-4 md:gap-8">
      <p class="text-sm md:text-base flex-1">
        {{ $t('Heroes Profile is funded by its developers. If you like the site, consider donating to our Patreon. even $5 helps. The data on this website relies on replays uploaded to Heroes Profile API. Make sure you') }} <a class="underline hover:text-header" href="/Upload">{{ $t('upload your data with the Heroes Profile Uploader') }}</a>. {{ $t('If you do not upload your replays, your data will not be accurate.') }}
        {{ $t('This site uses cookies in order to store your recent profile views. To learn more, view our') }} <a class="underline hover:text-header" href="/Privacy/Policy">{{ $t('Privacy Policy') }}</a>.
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
    // The help button sits above the banner: share its height as --disclaimer-height
    if (this.popUpVar && this.$refs.card && typeof ResizeObserver !== 'undefined') {
      this.resizeObserver = new ResizeObserver(() => this.setHeight());
      this.resizeObserver.observe(this.$refs.card);
    }
  },
  beforeUnmount() {
    this.resizeObserver?.disconnect();
  },
  computed: {
  },
  watch: {
  },
  methods: {
    setHeight() {
      const height = this.popUpVar && this.$refs.card ? this.$refs.card.offsetHeight + 16 : 0;
      document.documentElement.style.setProperty('--disclaimer-height', `${height}px`);
    },
    closePopup(){
      localStorage.setItem('popupDisclaimer', 'true');
      this.popUpVar = false;
      this.resizeObserver?.disconnect();
      this.setHeight();
    }
  }
}
</script>