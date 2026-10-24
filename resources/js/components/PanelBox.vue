<template>
  <div :class="[inner ? 'bg-surface-1' : 'bg-surface', 'rounded-lg p-4 md:p-6 flex flex-col gap-4 min-w-0']">
    <template v-if="title">
      <div class="flex flex-wrap justify-between items-baseline gap-x-4 gap-y-1">
        <h3 class="text-2xl font-bold flex items-center gap-2">{{ title }}<slot name="title"></slot></h3>
        <a v-if="linkHref" class="underline text-header whitespace-nowrap" :href="linkHref">{{ linkText }}</a>
      </div>
      <div v-if="divider" class="h-px bg-gray-dark"></div>
    </template>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: 'PanelBox',
  props: {
    title: String,
    linkText: String,
    linkHref: String,
    // A card inside another panel, one shade lighter
    inner: Boolean,
    divider: {
      type: Boolean,
      default: true,
    },
  },
};
</script>
