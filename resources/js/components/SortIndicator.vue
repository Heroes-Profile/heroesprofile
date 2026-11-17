<template>
  <!-- Shown beside the header of the column the table is sorted by: down for descending, up for ascending -->
  <basil-icon v-if="column === sortKey" name="arrow" :class="['text-xs ml-1.5', sortDir === 'asc' ? '-rotate-90' : 'rotate-90']"></basil-icon>
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
