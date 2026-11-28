<template>
  <!-- Little lightning bolts that flash at random spots around the Site Owner avatar or crown. Place it inside a relative
       wrapper beside them; each spark starts on the edge of the icon itself (an <i> such as the crown, else the first
       element), measured when it fires, since the wrapper can be bigger than the icon. Nothing shows for reduced motion. -->
  <span ref="layer" class="absolute inset-0 pointer-events-none" aria-hidden="true">
    <svg
      v-for="spark in sparks"
      :key="spark.id"
      class="owner-spark absolute w-[0.875rem] h-[0.875rem] -mt-[0.4375rem] origin-left"
      :style="{ left: spark.x + 'px', top: spark.y + 'px', transform: `rotate(${spark.angle}deg)` }"
      viewBox="0 0 14 14"
      fill="none"
    >
      <path :d="spark.path" stroke="#bfeaff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </span>
</template>

<script>
// A bolt's zigzag, drawn from its left end (set on the edge, turned to point outward); a few shapes so they don't all look alike
const PATHS = [
  'M1 7 L5 5 L7 9 L10 6 L13 7',
  'M1 7 L4 9 L7 5 L9 8 L13 6',
  'M1 7 L5 8 L6 4 L10 7 L13 5',
];

export default {
  name: 'OwnerSparks',
  data() {
    return {
      sparks: [],
      timer: null,
      nextId: 0,
    };
  },
  mounted() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }
    this.schedule();
  },
  beforeUnmount() {
    clearTimeout(this.timer);
  },
  methods: {
    // Every 0.6 to 2 seconds, one spark at a random spot on the edge
    schedule() {
      this.timer = setTimeout(() => {
        this.spark();
        this.schedule();
      }, 600 + Math.random() * 1400);
    },
    spark() {
      const layer = this.$refs.layer;
      const wrapper = layer && layer.parentElement;
      const icon = wrapper && (wrapper.querySelector('i') || wrapper.firstElementChild);
      if (!icon || icon === layer) {
        return;
      }
      // The icon's box within the layer, in px
      const box = icon.getBoundingClientRect();
      const origin = layer.getBoundingClientRect();
      const left = box.left - origin.left;
      const top = box.top - origin.top;
      // A point on the icon's edge, and the way out from it, give or take
      const side = Math.floor(Math.random() * 4);
      const along = 0.1 + Math.random() * 0.8;
      const [x, y, out] = [
        [left + along * box.width, top, -90],
        [left + box.width, top + along * box.height, 0],
        [left + along * box.width, top + box.height, 90],
        [left, top + along * box.height, 180],
      ][side];
      const id = this.nextId++;
      this.sparks.push({ id, x, y, angle: out + (Math.random() * 50 - 25), path: PATHS[Math.floor(Math.random() * PATHS.length)] });
      // Gone once its flicker (app.css .owner-spark) has played
      setTimeout(() => {
        this.sparks = this.sparks.filter(spark => spark.id !== id);
      }, 450);
    },
  },
};
</script>
