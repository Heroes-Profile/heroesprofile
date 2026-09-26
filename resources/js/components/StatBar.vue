<template>
  <div class="relative grid overflow-hidden variable-background rounded ring-inset ring-[1px] variable-ring">
    <span class="invisible" aria-hidden="true">&nbsp;</span>
    <span ref="fill" :class="fillClasses" :style="{ width }" aria-hidden="true"></span>
    <span ref="textMeasure" class="absolute invisible flex items-center whitespace-nowrap px-2 tabular-nums" aria-hidden="true">
      {{ mainText }}<span :class="suffixClass">{{ suffix }}</span>
    </span>
    <span v-if="textFitsInside"
      class="absolute inset-y-0 left-0 z-10 flex items-center px-2 text-white whitespace-nowrap tabular-nums">
      {{ mainText }}<span :class="suffixClass">{{ suffix }}</span>
    </span>
    <span v-else class="absolute inset-y-0 z-10 flex items-center pl-1 variable-text whitespace-nowrap tabular-nums"
      :style="{ left: width }">
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
    divider: { type: Boolean, default: true },
  },
  data() {
    return {
      textFitsInside: false,
      resizeObserver: null,
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
      return this.displayText !== null && this.displayText !== undefined
        ? String(this.displayText)
        : String(this.value);
    },
    fillClasses() {
      return [
        'absolute inset-y-0 left-0 rounded-l',
        colorClasses[this.color] || colorClasses.blue,
        this.numericValue === 100 ? 'rounded-r' : '',
        this.divider && this.numericValue > 0 && this.numericValue < 100
          ? 'border-r-2 border-black'
          : '',
      ];
    },
  },
  mounted() {
    this.$nextTick(this.updateTextPlacement);

    this.resizeObserver = new ResizeObserver(() => {
      this.updateTextPlacement();
    });
    this.resizeObserver.observe(this.$refs.fill);
    this.resizeObserver.observe(this.$refs.textMeasure);
  },
  beforeUnmount() {
    this.resizeObserver?.disconnect();
  },
  methods: {
    updateTextPlacement() {
      const fill = this.$refs.fill;
      const text = this.$refs.textMeasure;
      if (!fill || !text) return;

      this.textFitsInside = fill.clientWidth >= text.offsetWidth;
    },
  },
};
</script>
