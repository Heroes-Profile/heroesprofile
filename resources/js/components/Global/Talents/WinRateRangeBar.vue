<template>
  <!-- A talent's worst-to-best build range on its tier's shared scale, with a tick at its own win rate -->
  <div class="min-w-[12rem]">
    <div class="relative h-6">
      <span class="absolute inset-y-0 -inset-x-3 rounded bg-darken"></span>
      <span v-for="tick in ticks" :key="tick" class="absolute inset-y-0 border-l border-dashed border-gray-medium" :style="{ left: position(tick) }"></span>
      <span :class="['absolute top-1/2 -translate-y-1/2 h-3 rounded-full', highlight ? 'bg-teal' : 'bg-gray-medium']" :style="{ left: position(worst), width: `calc(${position(best)} - ${position(worst)})` }"></span>
      <span v-if="other != null" class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 h-3 w-3 rounded-full bg-gray-dark border-2 border-gray-medium" :style="{ left: position(other) }"></span>
      <span class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 h-4 w-4 rounded-full bg-red border-2 border-white" :style="{ left: position(worst) }"></span>
      <span class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 h-4 w-4 rounded-full bg-lteal border-2 border-white" :style="{ left: position(best) }"></span>
      <span class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 h-5 w-0.5 bg-white" :style="{ left: position(value) }"></span>
    </div>
    <div class="relative h-4 mt-1 text-xs text-gray-medium">
      <span class="absolute -translate-x-1/2 whitespace-nowrap" :style="{ left: position(value) }">Avg {{ value.toFixed(2) }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WinRateRangeBar',
  props: {
    worst: Number,
    best: Number,
    value: Number,
    other: Number,
    min: Number,
    max: Number,
    highlight: Boolean,
  },
  computed: {
    ticks() {
      const ticks = [];
      for (let tick = this.min; tick <= this.max; tick += 5) {
        ticks.push(tick);
      }
      return ticks;
    },
  },
  methods: {
    position(rate) {
      const share = (Math.min(Math.max(rate, this.min), this.max) - this.min) / (this.max - this.min);
      return `${share * 100}%`;
    },
  },
};
</script>
