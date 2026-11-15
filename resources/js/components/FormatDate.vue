<template>
  <span>
    {{ formatDate(input) }}
  </span>
</template>

<script>
import { formatGameDate } from '../utils/formatGameDate';

export default {
  name: 'FormatDate',
  props: {
    input: String,
  },
  methods: {
    formatDate(dateString) {
      return formatGameDate(dateString);
    },
  }
}
</script>
