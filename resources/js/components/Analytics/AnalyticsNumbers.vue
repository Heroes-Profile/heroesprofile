<template>
  <details class="bg-lighten rounded text-sm mt-2 mb-8 group">
    <summary class="px-4 py-2 flex justify-between items-center cursor-pointer list-none">
      <span>{{ label }}</span>
      <basil-icon name="arrow" class="rotate-90 transition-transform group-open:-rotate-90"></basil-icon>
    </summary>
    <div class="px-4 pb-4">
      <slot></slot>
    </div>
  </details>
</template>

<script>
// The exact numbers behind a chart, closed until asked for
export default {
  name: 'AnalyticsNumbers',
  props: {
    label: { type: String, default: 'Show the numbers' },
  },
};
</script>
