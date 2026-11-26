<template>
  <!-- A small on/off pill; a row of them is a multi-select. Use with v-model -->
  <button type="button" :aria-pressed="modelValue"
    :class="['px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors select-none', modelValue ? 'bg-teal hover:bg-lteal text-white' : 'bg-surface-1 hover:bg-surface-3 text-subtext']"
    @click="$emit('update:modelValue', !modelValue)">
    <slot>{{ $t(text) }}</slot>
  </button>
</template>

<script>
export default {
  name: 'ToggleChip',
  props: {
    modelValue: { type: Boolean, default: false },
    text: { type: String, default: '' },
  },
  emits: ['update:modelValue'],
};
</script>
