<template>
  <div>
    CHANGE THIS TO group-box
        {{ this.text }}

    <div class="border border-gray-300 p-4 flex flex-wrap gap-0">
      <round-box-small v-for="(item, index) in data" :key="index" :obj="item.game_map" :type="'map'" :hovertext="'I am not sure if this is the right way to do it'"></round-box-small>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MapGroupBox',
  components: {
  },
  props: {
    text: String,
    data: Array,
  },
  data(){
    return {
    }
  },
  created(){
    console.log('map-data', this.data);
  },
  mounted() {
    //console.log(this.data);
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>