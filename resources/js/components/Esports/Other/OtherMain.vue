<template>
  <div>
    <esports-organizations :data="hgcSeries" :esport="'HGCMore'"></esports-organizations>
    <esports-organizations :data="withoutHGCSeries" :esport="'HGCMore'"></esports-organizations>
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
    console.log(this.series);
    console.log("hi");
    console.log("Filtered HGC Series:", this.hgcSeries);
  }
}
</script>
