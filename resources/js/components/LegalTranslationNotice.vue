<template>
  <!-- On a translated Terms or Privacy page: the English text is the one that counts -->
  <div v-if="translated" class="bg-surface-1 border-l-4 border-yellow rounded-control px-4 py-3 text-sm mb-6">
    {{ $t('This is a translation, provided for convenience. The English version is the official one, and it governs if the two differ or anything is mistranslated.') }}
  </div>
</template>

<script>
import { siteLocale } from '../utils/translate';

export default {
  name: 'LegalTranslationNotice',
  computed: {
    // siteLocale() is only set when the page is in a language other than English
    translated() {
      return !!siteLocale();
    },
  },
};
</script>
