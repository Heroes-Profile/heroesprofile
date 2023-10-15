<template>
  <span>
    {{ formatDate(input) }}
  </span>
</template>

<script>
import moment from 'moment-timezone';

export default {
  name: 'FormatDate',
  components: {
  },
  props: {
    input: String,
  },
  data(){
    return {
      userTimezone: moment.tz.guess(), // Automatically detect user's timezone
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(this.userTimezone);

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
  }
}
</script>