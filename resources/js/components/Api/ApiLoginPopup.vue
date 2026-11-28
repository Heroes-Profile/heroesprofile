<template>
  <!-- Any link to /Api/Login on an API page opens the sign-in card over it, as login-popup does for Battle.net; the page itself still works when visited directly -->
  <modal-box v-if="open" container-class="" width="max-w-[40rem]" @close="open = false">
    <api-login :csrf="csrf" :errors="errors" :oldemail="oldemail" :popup="true" @close="open = false"></api-login>
  </modal-box>
</template>

<script>
const LOGIN_PATH = '/api/login';

export default {
  name: 'ApiLoginPopup',
  props: {
    csrf: String,
    // A failed sign-in from the popup comes back here with its errors, and reopens it
    errors: {
      type: Array,
      default: () => [],
    },
    oldemail: String,
    reopen: Boolean,
  },
  data() {
    return {
      open: this.reopen,
    };
  },
  mounted() {
    // Capture runs before the link's own handlers, so custom-button doesn't navigate either
    document.addEventListener('click', this.interceptLoginLink, true);
    document.addEventListener('keydown', this.handleEscape);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.interceptLoginLink, true);
    document.removeEventListener('keydown', this.handleEscape);
  },
  methods: {
    interceptLoginLink(event) {
      // New-tab clicks, and the login page itself, still go to the page
      if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
        return;
      }
      if (window.location.pathname.toLowerCase() === LOGIN_PATH) {
        return;
      }
      const link = event.target.closest?.('a[href]');
      if (!link) {
        return;
      }
      const url = new URL(link.getAttribute('href'), window.location.href);
      if (url.origin !== window.location.origin || url.pathname.toLowerCase() !== LOGIN_PATH) {
        return;
      }
      event.preventDefault();
      event.stopPropagation();
      this.open = true;
    },
    handleEscape(event) {
      if (event.key === 'Escape') {
        this.open = false;
      }
    },
  },
};
</script>
