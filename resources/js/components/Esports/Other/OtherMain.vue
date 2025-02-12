<template>
  <div>
    <esports-organizations :data="hgcSeries" :esport="'Other'"></esports-organizations>
    <esports-organizations :data="withoutHGCSeries" :esport="'Other'"></esports-organizations>
  </div>
</template>

<script>
export default {
  name: 'OtherMain',
  components: {
  },
  props: {
    series: Array,
  },
  computed: {
    hgcSeries() {
      return this.series.filter(item => item.name === "HGC");
    },
    withoutHGCSeries(){
      return this.series.filter(item => item.name !== "HGC");
    },
  },
  mounted() {
  }
}
</script>
