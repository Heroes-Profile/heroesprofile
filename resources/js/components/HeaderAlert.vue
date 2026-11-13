<template>
  <!-- Its own row under the nav; gone entirely once closed -->
  <div v-if="open" class="flex justify-center px-4 md:px-8 pb-4 max-md:pt-2 text-sm">
    <div class="bg-red rounded px-4 py-3 flex items-center gap-4 w-fit max-w-[100rem] shadow-lg max-md:hidden">
      <i class="fa-solid fa-bell"></i>
      <div v-html="alert"></div>
      <button type="button" class="ml-2 hover:opacity-75" :aria-label="$t('Close')" @click="close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <details class="group bg-red rounded px-4 py-3 w-full shadow-lg md:hidden">
      <summary class="cursor-pointer list-none flex items-center gap-2">
        <i class="fa-solid fa-bell"></i>
        <span>Site update. <span class="link group-open:hidden">{{ $t('Read more') }}</span></span>
        <button type="button" class="ml-auto hover:opacity-75" :aria-label="$t('Close')" @click.prevent="close"><i class="fa-solid fa-xmark"></i></button>
      </summary>
      <div v-html="alert"></div>
    </details>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'HeaderAlert',
  props: {
    // Admin-written HTML from header_alert, so it can carry links.
    alert: String,
    dismissKey: String,
  },
  data() {
    return {
      open: true,
    };
  },
  methods: {
    close() {
      this.open = false;
      Cookies.set('header_alert_dismissed', this.dismissKey, { expires: 365, path: '/' });
    },
  },
};
</script>
