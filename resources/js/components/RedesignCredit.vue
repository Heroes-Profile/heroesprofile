<template>
  <!-- The footer's redesign credit: the same teal box as the ad-blocker notice at the top, for everyone until it expires
       (layouts/app.blade.php). The words link to the redesign page; the name and the Discord icon copy the designer's
       username to paste into Add Friend -->
  <!-- The words alone are centred; the icon hangs off their right end so it doesn't pull them left -->
  <div class="bg-teal px-4 md:px-20 py-2 text-xs text-center">
    <span class="relative">
      <!-- The translation is split around the name, so the name can sit anywhere in the sentence -->
      <template v-for="(part, index) in parts" :key="index">
        <button v-if="part === null" type="button" class="hover:underline" :title="copyLabel" @click="copy">{{ name }}</button>
        <!-- On hover a chevron appears right after the words, pushing the name along -->
        <template v-else-if="part.trim()">{{ /^\s/.test(part) ? ' ' : '' }}<a href="/Redesign/2026" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ part.trim() }}</span><basil-icon name="arrow-small" class="hidden group-hover/bt:inline-block text-[0.625rem] ml-1"></basil-icon></a>{{ /\s$/.test(part) ? ' ' : '' }}</template>
      </template>
      <button v-if="discord" type="button" class="absolute left-full top-1/2 -translate-y-1/2 ml-1.5 hover:text-surface transition-colors" :title="copyLabel" :aria-label="copyLabel" @click="copy">
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
  computed: {
    // The sentence's words around the name; null is where the name goes
    parts() {
      const marker = '\u0000';
      const [before, after = ''] = this.$t('Redesigned by :name', { name: marker }).split(marker);
      return [before, null, after];
    },
    copyLabel() {
      return this.copied ? this.$t('Copied') : this.$t('Copy Discord username');
    },
  },
  methods: {
    async copy() {
      if (!this.discord) {
        return;
      }
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
