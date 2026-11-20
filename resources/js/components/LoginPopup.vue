<template>
  <!-- Any link to the login page opens this instead; the page itself still works when visited directly -->
  <modal-box v-if="open" container-class="" width="max-w-[45rem]" @close="close">
    <battlenet-authenticate :closable="true" @close="close" @redirecting="redirecting = $event"></battlenet-authenticate>
  </modal-box>
</template>

<script>
const LOGIN_PATH = '/authenticate/battlenet';

export default {
  name: 'LoginPopup',
  data() {
    return {
      open: false,
      // Logging in: the popup stays open until the browser reaches Battle.net
      redirecting: false,
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
      // New-tab clicks still go to the page
      if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
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
        this.close();
      }
    },
    close() {
      if (!this.redirecting) {
        this.open = false;
      }
    },
  },
};
</script>
