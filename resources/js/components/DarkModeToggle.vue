<template>
  <div class="inline-flex bg-teal rounded p-1 gap-1">
    <button
      v-for="side in ['left', 'right']"
      :key="side"
      type="button"
      :class="['w-8 h-8 rounded flex items-center justify-center transition-colors', isDark === (side === 'right') ? 'bg-[#1f2229] text-white' : 'text-[#1f2229] hover:bg-lteal']"
      :aria-label="side === 'right' ? 'Dark mode' : 'Light mode'"
      :aria-pressed="isDark === (side === 'right')"
      @click="setMode(side)"
    >
      <i :class="side === 'right' ? 'fa-solid fa-moon' : 'fa-solid fa-sun'"></i>
    </button>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

const COOKIE_OPTIONS = { expires: 365, path: '/' };

export default {
  name: 'DarkModeToggle',
  props: {
    darkmode: Boolean,
    authenticated: Boolean,
  },
  data() {
    return {
      isDark: this.darkmode,
    };
  },
  mounted() {
    // Logged in, the account setting is the source of truth; keep the cookie in step so it carries over after logout.
    if (this.authenticated) {
      Cookies.set('darkmode', this.darkmode ? '1' : '0', COOKIE_OPTIONS);
      Cookies.remove('darkmode_pending', { path: '/' });
    }
  },
  methods: {
    setMode(side) {
      const dark = side === 'right';
      this.isDark = dark;
      document.body.classList.toggle('dark-mode', dark);
      document.body.classList.toggle('light-mode', !dark);
      Cookies.set('darkmode', dark ? '1' : '0', COOKIE_OPTIONS);

      if (this.authenticated) {
        this.$axios.post('/api/v1/profile/save/settings', { darkmode: dark }).catch(() => {});
      } else {
        // Applied to the account on next login.
        Cookies.set('darkmode_pending', '1', COOKIE_OPTIONS);
      }
    },
  },
};
</script>
