<template>
  <div class="flex justify-center">
    <a
      v-for="(item, index) in options"
      :key="item.key"
      role="button"
      :class="[
        'transition-colors text-white inline-block py-2 px-4 max-sm:px-2 max-sm:text-xs',
        {
          'rounded-l': index === 0,
          'rounded-r': index === options.length - 1,
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
export default {
  name: 'MultiTabButton',
  props: {
    options: {
      type: Array,
      required: true,
    },
    modelValue: [String, Number],
  },
  emits: ['update:modelValue'],
};
</script>
