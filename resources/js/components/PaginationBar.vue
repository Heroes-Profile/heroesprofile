<template>
  <!-- A teal pill like tab-button: chevrons at the ends, the current page on a dark chip -->
  <nav class="inline-flex items-center gap-1 bg-teal rounded p-1 text-white" aria-label="Pages">
    <button type="button" class="w-9 h-9 rounded-control hover:bg-lteal disabled:opacity-40 disabled:hover:bg-transparent" :disabled="current <= 1" aria-label="Previous page" @click="$emit('page', current - 1)"><i class="fa-solid fa-chevron-left"></i></button>
    <template v-for="(page, index) in pages" :key="index">
      <!-- A gap: click it to type any page number -->
      <template v-if="page === null">
        <input v-if="jumpIndex === index" :ref="setJumpInput" v-model="jumpValue" type="number" min="1" :max="last" class="w-14 h-9 rounded-control !bg-surface-1 text-white text-center focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none" aria-label="Go to page" @keydown.enter.prevent="jump" @keydown.esc="jumpIndex = null" @blur="jumpIndex = null"/>
        <button v-else type="button" class="min-w-[2.25rem] h-9 px-1 rounded-control hover:bg-lteal" title="Go to page" aria-label="Go to page" @click="openJump(index)">…</button>
      </template>
      <button v-else type="button" :class="['min-w-[2.25rem] h-9 px-2 rounded-control', page === current ? 'bg-surface-1' : 'hover:bg-lteal']" :aria-current="page === current ? 'page' : null" @click="page !== current && $emit('page', page)">{{ page }}</button>
    </template>
    <button type="button" class="w-9 h-9 rounded-control hover:bg-lteal disabled:opacity-40 disabled:hover:bg-transparent" :disabled="current >= last" aria-label="Next page" @click="$emit('page', current + 1)"><i class="fa-solid fa-chevron-right"></i></button>
  </nav>
</template>

<script>
export default {
  name: 'PaginationBar',
  emits: ['page'],
  props: {
    current: { type: Number, required: true },
    last: { type: Number, required: true },
  },
  data() {
    return {
      jumpIndex: null,
      jumpValue: '',
    };
  },
  computed: {
    // The first and last page, two either side of the current one, null for each gap
    pages() {
      const shown = new Set([1, this.last]);
      for (let page = this.current - 2; page <= this.current + 2; page++) {
        if (page >= 1 && page <= this.last) {
          shown.add(page);
        }
      }
      const sorted = [...shown].sort((a, b) => a - b);
      return sorted.flatMap((page, index) => (index && page - sorted[index - 1] > 1 ? [null, page] : [page]));
    },
  },
  methods: {
    openJump(index) {
      this.jumpValue = '';
      this.jumpIndex = index;
    },
    setJumpInput(input) {
      input?.focus();
    },
    // Out-of-range or empty input is ignored
    jump() {
      const page = parseInt(this.jumpValue, 10);
      this.jumpIndex = null;
      if (page >= 1 && page <= this.last && page !== this.current) {
        this.$emit('page', page);
      }
    },
  },
};
</script>
