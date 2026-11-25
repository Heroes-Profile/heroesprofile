<template>
  <!-- Shown beside the header of the column the table is sorted by: down for descending, up for ascending.
       On desktop it takes no width and hangs past the label, like the header info icons, so sorting never resizes the
       columns. Phones' columns are too tight for that, so there it takes its own room instead of running off the edge -->
  <span v-if="column === sortKey" class="sort-indicator inline-block md:w-0 overflow-visible whitespace-nowrap"><basil-icon name="arrow" :class="['text-xs ml-1.5', sortDir === 'asc' ? '-rotate-90' : 'rotate-90']"></basil-icon></span>
</template>

<script>
export default {
  name: 'SortIndicator',
  props: {
    column: String,
    sortKey: String,
    sortDir: String,
  },
};
</script>
