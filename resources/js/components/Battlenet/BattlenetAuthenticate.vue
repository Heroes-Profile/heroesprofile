<template>
  <!-- The login card: the /Authenticate/Battlenet page shows it on its own, login-popup shows it over the page -->
  <div :class="['relative mx-auto w-full max-w-[45rem] bg-surface border-t-4 border-teal rounded-[1.75rem] p-12 max-md:px-5 max-md:py-8 flex flex-col items-center gap-8', { 'mt-[7.5rem] max-md:mt-10': !closable }]">
    <button v-if="closable && !redirecting" type="button" class="absolute right-6 top-6 max-md:right-4 max-md:top-4 w-9 h-9 flex items-center justify-center rounded hover:bg-surface-3 transition-colors" :aria-label="$t('Close')" @click="$emit('close')"><basil-icon name="cross" class="text-2xl"></basil-icon></button>

    <div class="flex flex-col items-center gap-3 text-center">
      <div class="w-[5.5rem] h-[5.5rem] rounded-full bg-teal/15 flex items-center justify-center">
        <img class="w-[3.36rem]" src="/images/logo/heroesprofilelogo.png" :alt="$t('Heroes Profile Logo')">
      </div>
      <h1 class="text-[2.25rem] max-md:text-[1.75rem] font-bold leading-tight">{{ $t('Log in to Heroes Profile') }}</h1>
      <p class="text-lg text-subtext">{{ $t('To access your profile, log in through Battle.net.') }}</p>
    </div>

    <div class="w-full flex flex-col gap-3">
      <div class="flex items-end gap-4 max-sm:flex-col max-sm:items-stretch">
        <div class="flex-1 min-w-0 flex flex-col gap-2">
          <span class="text-lg text-subtext">{{ $t('Region') }}</span>
          <single-select-filter :values="regions" :text="'Region'" :compact="true" :hidelabel="true" :nosearch="true" placeholder="Choose your region" @input-changed="handleInputChange"></single-select-filter>
        </div>
        <!-- Battle.net can take a moment to open, so the button and the line below show it's on its way -->
        <custom-button class="text-center" :href="'/redirect/authenticate/battlenet'" :text="'Login with Battle.net'" :alt="'Login with Battle.net'" :color="'blue'" :disabled="!selectedRegion" :loading="redirecting" @click="selectedRegion && setRedirecting(true)"></custom-button>
      </div>
      <p v-if="redirecting" class="flex items-center gap-2 text-sm text-lteal" role="status"><img class="logo-spin h-5 w-auto shrink-0" src="/images/logo/heroesprofilelogo.png" alt="">{{ $t('Logging in with Battle.net...') }}</p>
      <p v-else class="flex items-center gap-2 text-sm text-subtext"><basil-icon name="info"></basil-icon>{{ $t('Choose the region your account plays on.') }}</p>
    </div>

    <div class="w-full h-px bg-gray-dark"></div>

    <div class="w-full bg-surface-2 rounded-2xl p-5 flex items-start gap-4">
      <span class="w-10 h-10 shrink-0 rounded-[0.625rem] bg-teal/20 flex items-center justify-center"><i class="fas fa-shield-halved text-lg"></i></span>
      <div class="flex flex-col gap-1 min-w-0">
        <p class="font-semibold">{{ $t('Secure login with Battle.net OAuth') }}</p>
        <p class="text-sm text-subtext">{{ $t("Heroes Profile never sees your password. Battle.net OAuth is Blizzard's login service: you sign in on Battle.net, and it gives Heroes Profile limited access to your account without sharing your credentials.") }}</p>
      </div>
    </div>
  </div>
</template>

<script>
  import Cookies from 'js-cookie';

  // Battle.net region ids, as the login callback reads them from the cookie
  const REGION_IDS = { NA: 1, EU: 2, KR: 3, CN: 5 };
  // What each region covers, beside its code in the dropdown
  const REGION_NAMES = { NA: 'Americas', EU: 'Europe', KR: 'Korea · Taiwan', CN: 'China' };

  export default {
    name: 'BattlenetAuthenticate',
    props: {
      // Shown in the popup, with a close button
      closable: Boolean,
    },
    // redirecting: the popup holding it can't be closed while it's on its way to Battle.net
    emits: ['close', 'redirecting'],
    data(){
      return {
        selectedRegion: null,
        // Set once the button is clicked, while the browser heads to Battle.net
        redirecting: false,
      }
    },
    // Coming back with the browser's Back button can restore the page as it was left, still "logging in"
    mounted() {
      window.addEventListener('pageshow', this.resetRedirect);
    },
    beforeUnmount() {
      window.removeEventListener('pageshow', this.resetRedirect);
    },
    computed: {
      regions() {
        return Object.keys(REGION_IDS).map(code => ({ code, badge: code, name: REGION_NAMES[code] }));
      },
    },
    methods: {
      resetRedirect(event) {
        if (event.persisted) this.setRedirecting(false);
      },
      setRedirecting(value) {
        this.redirecting = value;
        this.$emit('redirecting', value);
      },
      handleInputChange(value) {
        if (!value || !value.value) {
          this.selectedRegion = null;
          return;
        }
        this.selectedRegion = value.value;
        this.saveCookieData();
      },
      saveCookieData(){
        const regionInt = REGION_IDS[this.selectedRegion];

        if (regionInt === undefined) {
          console.error('Invalid region selected');
          return;
        }
        Cookies.set('battlenet_region', regionInt.toString(), { sameSite: 'Lax', path: '/', expires: 90 });
      }
    }
  }
</script>
