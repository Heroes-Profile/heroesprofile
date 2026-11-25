<template>
  <!-- The footer's redesign credit: the same teal box as the ad-blocker notice at the top, for everyone until it expires
       (layouts/app.blade.php). The Discord icon copies the designer's username to paste into Add Friend -->
  <!-- The words alone are centred; the icon hangs off their right end so it doesn't pull them left -->
  <div class="bg-teal px-4 md:px-20 py-2 text-xs text-center">
    <span class="relative">
      {{ $t('Redesigned by :name', { name: name }) }}
      <button v-if="discord" type="button" class="absolute left-full top-1/2 -translate-y-1/2 ml-1.5 hover:text-surface transition-colors" :title="copied ? $t('Copied') : $t('Copy Discord username')" :aria-label="copied ? $t('Copied') : $t('Copy Discord username')" @click="copy">
        <i :class="copied ? 'fas fa-check' : 'fab fa-discord'"></i>
      </button>
    </span>
  </div>
</template>

<script>
export default {
  name: 'RedesignCredit',
  props: {
    name: { type: String, required: true },
    // Their Discord username, from config/team.php
    discord: { type: String, default: null },
  },
  data() {
    return {
      copied: false,
    };
  },
  methods: {
    async copy() {
      try {
        await navigator.clipboard.writeText(this.discord);
      } catch (error) {
        // The browser refused the clipboard
        return;
      }
      this.copied = true;
      clearTimeout(this.timer);
      this.timer = setTimeout(() => { this.copied = false; }, 2000);
    },
  },
};
</script>
