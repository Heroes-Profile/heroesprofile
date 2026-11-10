<template>
  <div class="flex items-center gap-4">
    <span class="font-bold whitespace-nowrap">Level {{ modelValue }}</span>
    <input
      type="range"
      class="w-full accent-teal"
      :min="min" :max="max" step="1"
      :value="modelValue"
      @input="$emit('update:modelValue', Number($event.target.value))"
    >
  </div>
</template>

<script>
export default {
  name: 'LevelSlider',
  props: {
    modelValue: {
      type: Number,
      default: 1,
    },
    min: {
      type: Number,
      default: 1,
    },
    max: {
      type: Number,
      default: 30,
    },
  },
  emits: ['update:modelValue'],
}
</script>
