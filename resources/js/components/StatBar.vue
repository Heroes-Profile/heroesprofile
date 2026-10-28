<template>
  <div class="flex items-center gap-4 min-w-0">
    <div :class="['flex-1 min-w-0 rounded bg-surface-3 overflow-hidden', size === 'big' ? 'h-[30px]' : 'h-4']" aria-hidden="true">
      <div :class="['h-full', colorClasses[color] || colorClasses.blue]" :style="{ width }"></div>
    </div>
    <span :class="['shrink-0 font-semibold text-header whitespace-nowrap tabular-nums', { 'text-2xl': size === 'big' }]">
      {{ mainText }}<span :class="suffixClass">{{ suffix }}</span>
    </span>
  </div>
</template>

<script>
const colorClasses = {
  blue: 'bg-blue',
  teal: 'bg-teal',
  purple: 'bg-purple',
  red: 'bg-red',
  yellow: 'bg-yellow',
  'gray-dark': 'bg-gray-dark',
};

export default {
  name: 'StatBar',
  props: {
    value: { type: [Number, String], required: true },
    displayText: { type: [Number, String], default: null },
    suffix: { type: String, default: '' },
    suffixClass: { type: String, default: '' },
    color: {
      type: String,
      default: 'blue',
      validator(value) {
        return Object.hasOwn(colorClasses, value);
      },
    },
    // 'big' is the 30px bar, for a headline stat
    size: { type: String, default: null },
  },
  data() {
    return {
      colorClasses,
    };
  },
  computed: {
    numericValue() {
      const value = Number(this.value);
      return Number.isFinite(value)
        ? Math.min(100, Math.max(0, value))
        : 0;
    },
    width() {
      return `${this.numericValue}%`;
    },
    mainText() {
      if (this.displayText !== null && this.displayText !== undefined) {
        return String(this.displayText);
      }
      // Two whole digits for a percentage, so 1.00% reads 01.00% and lines up with 58.47%
      const text = String(this.value);
      return this.suffix === '%' && /^\d(\.|$)/.test(text) ? '0' + text : text;
    },
  },
};
</script>
