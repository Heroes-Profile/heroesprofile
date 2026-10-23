<template>
  <div v-if="open" class="flex justify-center px-4 md:px-8 mt-2 mb-4 text-sm">
    <div class="bg-red rounded px-4 py-3 flex items-center gap-4 w-fit max-w-[1600px] max-md:hidden">
      <i class="fa-solid fa-bell"></i>
      <div v-html="alert"></div>
      <button type="button" class="ml-2 hover:opacity-75" aria-label="Close" @click="close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <details class="group bg-red rounded px-4 py-3 w-full md:hidden">
      <summary class="cursor-pointer list-none flex items-center gap-2">
        <i class="fa-solid fa-bell"></i>
        <span>Site update. <span class="link group-open:hidden">Read more</span></span>
        <button type="button" class="ml-auto hover:opacity-75" aria-label="Close" @click.prevent="close"><i class="fa-solid fa-xmark"></i></button>
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
