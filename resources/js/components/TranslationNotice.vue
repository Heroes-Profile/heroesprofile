<template>
  <!-- Shown once per browser, the first time a page is in a language other than English -->
  <modal-box v-if="show" :title="$t('About this translation')" width="max-w-[40rem]">
    <p class="mb-3">{{ $t('Hero, map, talent and ability names come from the game itself, so they match what you see in Heroes of the Storm.') }}</p>
    <p class="mb-3">{{ $t('The rest of the site was translated by machine and may contain mistakes. If you spot one, please let us know so we can fix it.') }}</p>
    <a :href="issueUrl" target="_blank" class="link">{{ $t('Suggest a better translation on GitHub') }}</a>

    <template #footer>
      <custom-button class="ml-auto" color="teal" text="Got it" :ignoreclick="true" @click="close"></custom-button>
    </template>
  </modal-box>
</template>

<script>
// Bump the key when the wording changes enough that everyone should see it again
const STORAGE_KEY = 'translationNoticeV1';

export default {
  name: 'TranslationNotice',
  props: {
    locale: { type: String, required: true },
  },
  data() {
    let seen = false;
    try {
      seen = localStorage.getItem(STORAGE_KEY) === 'true';
    } catch (e) {
      // Storage blocked: it shows once per page load instead
    }

    return { show: this.locale !== 'en' && !seen };
  },
  computed: {
    issueUrl() {
      const title = encodeURIComponent(`Translation fix (${this.locale}): `);
      return `https://github.com/Heroes-Profile/heroesprofile/issues/new?labels=translation&title=${title}`;
    },
  },
  methods: {
    close() {
      try {
        localStorage.setItem(STORAGE_KEY, 'true');
      } catch (e) {
        // Storage blocked: it shows again next visit
      }
      this.show = false;
    },
  },
};
</script>
