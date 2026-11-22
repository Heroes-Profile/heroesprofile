<template>
  <!-- A full-width checkbox row: a teal box with a tick, then the label. Use with v-model -->
  <label class="flex items-center gap-3 rounded-control bg-surface-1 hover:bg-surface-3 transition-colors px-3 py-2.5 cursor-pointer select-none min-h-[3.5rem]">
    <input type="checkbox" class="sr-only peer" :checked="modelValue" @change="$emit('update:modelValue', $event.target.checked)">
    <span :class="['w-9 h-9 shrink-0 rounded-[0.375rem] flex items-center justify-center text-xl peer-focus-visible:ring-2 peer-focus-visible:ring-lteal', modelValue ? 'bg-teal' : 'bg-surface-3']">
      <basil-icon v-if="modelValue" name="check"></basil-icon>
    </span>
    <span class="text-lg font-semibold"><slot>{{ $t(text) }}</slot></span>
  </label>
</template>

<script>
export default {
  name: 'CheckRow',
  props: {
    modelValue: { type: Boolean, default: false },
    text: { type: String, default: '' },
  },
  emits: ['update:modelValue'],
};
</script>
