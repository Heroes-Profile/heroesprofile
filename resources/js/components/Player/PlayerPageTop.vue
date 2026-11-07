<template>
  <!-- A player page's heading with its filters: side by side on desktop, stacked on mobile -->
  <div class="flex max-md:flex-col max-w-[120rem] mx-auto">
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
