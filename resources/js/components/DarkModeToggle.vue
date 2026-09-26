<template>
  <tab-button small tab1text="Light" tab2text="Dark" :ignoreclick="true" :overridedefaultside="darkmode ? 'right' : 'left'" @tab-click="setMode"></tab-button>
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
