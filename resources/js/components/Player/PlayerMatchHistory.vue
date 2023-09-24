<template>
  <div>
    Match History
  </div>
</template>

<script>
export default {
  name: 'PlayerMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: String, 
    region: String,
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
    console.log(this.filters);
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>