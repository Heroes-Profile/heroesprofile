<template>
  <!-- Shown beside the header of the column the table is sorted by: down for descending, up for ascending -->
  <i v-if="column === sortKey" :class="['fa-solid text-xs ml-1.5', sortDir === 'asc' ? 'fa-chevron-up' : 'fa-chevron-down']" aria-hidden="true"></i>
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
