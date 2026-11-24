<template>
  <!-- Labels never break mid-label; a row too wide for the space wraps whole tabs instead -->
  <!-- vertical: a column on desktop; on a phone a row across the full width, tabs sharing it equally -->
  <div :class="vertical ? 'flex md:flex-col max-md:w-full' : 'flex flex-wrap justify-center'">
    <a
      v-for="(item, index) in options"
      :key="item.key"
      role="button"
      :class="[
        'transition-colors text-white inline-block py-2 px-4 max-sm:px-2 max-sm:text-xs whitespace-nowrap',
        {
          'rounded-l': index === 0 && !vertical,
          'rounded-r': index === options.length - 1 && !vertical,
          'md:rounded-t max-md:rounded-l': index === 0 && vertical,
          'md:rounded-b max-md:rounded-r': index === options.length - 1 && vertical,
          'text-center max-md:flex-1 md:py-3': vertical,
          'bg-blue hover:bg-lblue ring-inset ring-lblue ring-2': modelValue === item.key,
          'bg-gray-dark hover:bg-gray-light': modelValue !== item.key,
        }
      ]"
      @click="$emit('update:modelValue', item.key)"
    >
      {{ $t(item.label) }}
    </a>
  </div>
</template>

<script>
// A row of joined tab buttons, one selected. options: [{ key, label }]; use with v-model.
// vertical stacks them as a column on md and up, and stays a row on phones.
export default {
  name: 'MultiTabButton',
  props: {
    options: {
      type: Array,
      required: true,
    },
    modelValue: [String, Number],
    vertical: Boolean,
  },
  emits: ['update:modelValue'],
};
</script>
