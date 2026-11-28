<template>
  <!-- A player page's heading with its filters: side by side on desktop, stacked on mobile -->
  <!-- The heading is centred on the filter card, as in the redesign -->
  <div class="flex max-md:flex-col md:items-center md:gap-12 md:pt-[2.75rem] max-w-[120rem] mx-auto">
    <div class="shrink-0">
      <slot name="heading"></slot>
    </div>
    <div class="w-full min-w-0">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerPageTop',
};
</script>
