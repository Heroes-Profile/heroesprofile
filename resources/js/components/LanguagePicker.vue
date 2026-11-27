<template>
  <!-- Phones: mr-1.5 makes room for the EN badge that overhangs the right edge -->
  <div class="relative group flex md:ml-4 max-md:mr-1.5">
    <div :data-battletag="menuKey" class="mobile-secondary-nav-open flex cursor-pointer">
      <span class="relative flex w-11 h-11 bg-teal rounded" :aria-label="$t('Language') + ': ' + locales[current]">
        <span class="w-full h-full rounded flex items-center justify-center text-surface hover:bg-lteal transition-colors">
          <i class="fa-solid fa-globe text-xl"></i>
        </span>
        <span class="absolute -top-1.5 -right-1.5 px-1 rounded bg-surface text-white text-[0.625rem] font-bold leading-[0.875rem]" aria-hidden="true">{{ shortCode }}</span>
      </span>
    </div>
    <!-- md:top-full: this wrapper is flex, so unlike the user menu's the panel would start level with the button, over it -->
    <div :data-battletag="menuKey" class="nav-dropdown nav-dropdown-secondary-nav md:!left-auto md:!right-0 md:top-full max-md:fixed max-md:top-0 max-md:w-full max-md:h-full max-md:bg-gray-dark">
      <div class="nav-dropdown-inner-wrapper">
        <div class="mobile-secondary-nav-name flex justify-between items-center md:hidden p-3 pl-4 bg-teal">{{ $t('Language') }} <button class="close-secondary-nav bg-blue rounded px-3 py-1 min-w-[2.75rem] min-h-[2.5rem] text-lg" :aria-label="$t('Close menu')">x</button></div>
        <a
          v-for="(label, code) in locales"
          :key="code"
          href="#"
          :lang="code"
          :class="['flex items-center justify-between gap-3', code === current ? 'bg-teal' : '']"
          :aria-current="code === current ? 'true' : null"
          @click.prevent="setLocale(code)"
        >{{ label }}<img v-if="switching === code" class="logo-spin h-4 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" /></a>
      </div>
    </div>
    <!-- Blocks the page while it reloads into the new language, same layering as ModalBox -->
    <Teleport to="body">
      <div v-if="switching" class="fixed inset-0 z-[9998] h-[100dvh] flex flex-col items-center justify-center gap-4 text-white" style="background: radial-gradient(circle at center, rgba(0, 0, 0, 0.97) 0%, rgba(0, 0, 0, 0.9) 35%, rgba(0, 0, 0, 0.8) 100%);" role="status" aria-live="polite">
        <img class="logo-spin h-16 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
        <p class="text-xl font-bold">{{ $t('Changing language to :language...', { language: locales[switching] }) }}</p>
      </div>
    </Teleport>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

const COOKIE_OPTIONS = { expires: 365, path: '/' };

export default {
  name: 'LanguagePicker',
  props: {
    locale: { type: String, default: 'en' },
    locales: { type: Object, required: true },
    authenticated: Boolean,
    // Pairs the button with its panel (MobileNavHack); each copy of the picker needs its own
    menuKey: { type: String, default: 'nav-language-menu' },
  },
  computed: {
    // Two letters for the badge; the region where two locales share a language (es-MX shows MX, zh-CN CN, zh-TW TW)
    shortCode() {
      const [language, region] = this.current.split('-');
      const shared = ['es', 'zh'].includes(language);
      return (shared && region ? region : language).toUpperCase();
    },
  },
  data() {
    return {
      current: this.locale,
      // The picked language while the page reloads into it
      switching: null,
    };
  },
  mounted() {
    // Logged in, the account setting wins; keep the cookie in step so it carries over after logout.
    if (this.authenticated) {
      Cookies.set('locale', this.locale, COOKIE_OPTIONS);
    }
  },
  methods: {
    async setLocale(code) {
      if (code === this.current || this.switching) {
        return;
      }

      this.current = code;
      this.switching = code;
      Cookies.set('locale', code, COOKIE_OPTIONS);

      if (this.authenticated) {
        await this.$axios.post('/api/v1/profile/save/settings', { locale: code }).catch(() => {});
      }

      window.location.reload();
    },
  },
};
</script>
