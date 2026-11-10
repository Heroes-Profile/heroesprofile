<template>
  <!-- Labels never break mid-label; a row too wide for the space wraps whole tabs instead -->
  <div :class="vertical ? 'flex md:flex-col max-md:flex-wrap max-md:justify-center' : 'flex flex-wrap justify-center'">
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
          'text-center': vertical,
          'bg-blue hover:bg-lblue ring-inset ring-lblue ring-2': modelValue === item.key,
          'bg-gray-dark hover:bg-gray-light': modelValue !== item.key,
        }
      ]"
      @click="$emit('update:modelValue', item.key)"
    >
      {{ item.label }}
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
