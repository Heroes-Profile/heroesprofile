<template>
  <!-- Previous on the left, Next on the right; page numbers between them unless the slot fills that spot -->
  <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-4 text-sm">
    <div>
      <custom-button v-if="current > 1" size="small" color="teal" alt="Previous page" :ignoreclick="true" @click="$emit('page', current - 1)"><i class="fa-solid fa-arrow-left mr-2"></i>Previous</custom-button>
    </div>
    <slot>
      <div class="flex flex-wrap justify-center gap-1">
        <template v-for="(page, index) in pages" :key="index">
          <span v-if="page === null" class="px-1 text-subtext">…</span>
          <button v-else type="button" :class="['min-w-[2rem] h-8 px-2 rounded', page === current ? 'bg-teal text-white' : 'text-subtext hover:text-white hover:bg-surface-3']" :aria-current="page === current ? 'page' : null" @click="page !== current && $emit('page', page)">{{ page }}</button>
        </template>
      </div>
    </slot>
    <div class="justify-self-end">
      <custom-button v-if="current < last" size="small" color="teal" alt="Next page" :ignoreclick="true" @click="$emit('page', current + 1)">Next<i class="fa-solid fa-arrow-right ml-2"></i></custom-button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaginationBar',
  emits: ['page'],
  props: {
    current: { type: Number, required: true },
    last: { type: Number, required: true },
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
};
</script>
