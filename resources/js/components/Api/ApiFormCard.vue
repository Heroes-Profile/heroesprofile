<template>
  <!-- The API account pages' centred card: an icon, the title and intro, then the form; a line and the footer links under it -->
  <div :class="popup ? 'w-full' : 'max-w-[120rem] mx-auto px-4 md:px-8 py-10 md:py-16 flex justify-center'">
    <div class="relative w-full max-w-[40rem] mx-auto rounded bg-surface border-t-4 border-teal px-6 md:px-12 pt-10 md:pt-12 pb-10 flex flex-col items-center gap-7">
      <button v-if="popup" type="button" class="absolute right-4 top-4 w-9 h-9 flex items-center justify-center rounded-control hover:bg-surface-3 transition-colors" :aria-label="$t('Close')" @click="$emit('close')"><basil-icon name="cross" class="text-xl"></basil-icon></button>
      <span class="w-20 h-20 rounded-full bg-teal/15 flex items-center justify-center text-3xl"><i :class="icon"></i></span>
      <div class="flex flex-col items-center gap-2.5 text-center">
        <h1 class="text-3xl font-bold">{{ $t(heading) }}</h1>
        <p v-if="infoText1" class="text-subtext">{{ $t(infoText1) }}</p>
      </div>

      <div v-if="status" class="w-full rounded bg-teal/15 border-l-4 border-teal px-4 py-3 text-sm">
        {{ status }}
      </div>

      <div v-if="errors.length" class="w-full rounded bg-red/15 border-l-4 border-red px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
          <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
        </ul>
      </div>

      <div class="w-full">
        <slot></slot>
      </div>

      <template v-if="$slots.footer">
        <div class="w-full h-px bg-gray-dark"></div>
        <div class="flex flex-col items-center gap-1 text-sm text-subtext text-center">
          <slot name="footer"></slot>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiFormCard',
  components: {
  },
  props: {
    heading: String,
    infoText1: String,
    // Font Awesome classes for the icon above the title
    icon: { type: String, default: 'fas fa-user' },
    errors: {
      type: Array,
      default: () => [],
    },
    status: String,
    // Shown over a page (the API sign-in popup): no page spacing, and a close button
    popup: Boolean,
  },
  emits: ['close'],
}
</script>
