<template>
  <!-- Two handles on one track, for picking a start and an end; the part between them is teal -->
  <div class="relative h-6 select-none">
    <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-1.5 rounded-full bg-surface-3"></div>
    <div class="absolute top-1/2 -translate-y-1/2 h-1.5 rounded-full bg-teal" :style="{ left: percent(modelValue[0]) + '%', right: (100 - percent(modelValue[1])) + '%' }"></div>
    <input
      v-for="(value, handle) in modelValue"
      :key="handle"
      type="range"
      class="range-slider-input absolute inset-0 w-full appearance-none bg-transparent pointer-events-none"
      :min="min" :max="max" step="1"
      :value="value"
      :aria-label="$t(handle === 0 ? 'Start' : 'End')"
      @input="move(handle, Number($event.target.value), $event.target)"
    >
  </div>
</template>

<script>
export default {
  name: 'RangeSlider',
  props: {
    // [start, end]
    modelValue: { type: Array, required: true },
    min: { type: Number, default: 0 },
    max: { type: Number, required: true },
  },
  emits: ['update:modelValue'],
  methods: {
    percent(value) {
      return this.max > this.min ? ((value - this.min) / (this.max - this.min)) * 100 : 0;
    },
    // The handles can't cross: each stops at the other
    move(handle, value, input) {
      const next = [...this.modelValue];
      next[handle] = handle === 0 ? Math.min(value, next[1]) : Math.max(value, next[0]);
      input.value = next[handle];
      this.$emit('update:modelValue', next);
    },
  },
};
</script>

<style>
/* The site's dark-mode input style would paint the track white */
.range-slider-input {
  background: transparent !important;
}
/* Only the handles take the pointer, so the two inputs stacked on one track can both be dragged */
.range-slider-input::-webkit-slider-thumb {
  pointer-events: auto;
  appearance: none;
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 9999px;
  background: #fff;
  border: 3px solid #008b8b;
  cursor: grab;
}
.range-slider-input::-moz-range-thumb {
  pointer-events: auto;
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 9999px;
  background: #fff;
  border: 3px solid #008b8b;
  cursor: grab;
}
.range-slider-input::-webkit-slider-runnable-track { background: transparent; }
.range-slider-input::-moz-range-track { background: transparent; }
</style>
