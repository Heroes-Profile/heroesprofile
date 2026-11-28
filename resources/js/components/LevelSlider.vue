<template>
  <!-- Level and its value, then the slider in a field between 1 and 30 -->
  <div class="flex flex-col gap-2">
    <span class="flex items-baseline justify-between gap-2"><span class="text-subtext">{{ $t('Level') }}</span><span class="text-xl font-bold text-white">{{ modelValue }}</span></span>
    <span class="h-[3.125rem] rounded bg-surface-3 px-4 flex items-center gap-4 text-sm font-medium text-subtext">
      {{ min }}
      <span class="relative flex-1 h-[1.375rem]">
        <span class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-2 rounded-[0.25rem] bg-surface-4"></span>
        <!-- Ends under the thumb's centre: the 22px thumb travels the track less its own width -->
        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-2 rounded-l-[0.25rem] bg-teal" :style="{ width: `calc(${percent}% + ${11 - percent * 0.22}px)` }"></span>
        <input
          type="range"
          class="level-slider-input absolute inset-0 w-full appearance-none bg-transparent cursor-pointer"
          :min="min" :max="max" step="1"
          :value="modelValue"
          :aria-label="$t('Level')"
          @input="$emit('update:modelValue', Number($event.target.value))"
        >
      </span>
      {{ max }}
    </span>
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
  computed: {
    percent() {
      return this.max > this.min ? ((this.modelValue - this.min) / (this.max - this.min)) * 100 : 0;
    },
  },
}
</script>

<style>
/* The site's dark-mode input style would paint the track white; the track is drawn under the input */
.level-slider-input {
  background: transparent !important;
}
.level-slider-input::-webkit-slider-runnable-track { background: transparent; }
.level-slider-input::-moz-range-track { background: transparent; }
.level-slider-input::-webkit-slider-thumb {
  appearance: none;
  width: 1.375rem;
  height: 1.375rem;
  border-radius: 9999px;
  background: #008b8b;
  border: 3px solid #fff;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}
.level-slider-input::-moz-range-thumb {
  width: 1.375rem;
  height: 1.375rem;
  border-radius: 9999px;
  background: #008b8b;
  border: 3px solid #fff;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}
</style>
