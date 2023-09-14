<template>
  <div>
        {{ this.text }}

    <div class="border border-gray-300 p-4 flex flex-wrap gap-0">
      <map-box-small v-for="(item, index) in data" :key="index" :map="item.game_map" :hovertext="'I am not sure if this is the right way to do it'"></map-box-small>
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