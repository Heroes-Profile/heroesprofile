<template>
  <div class=" w-auto inline-block m-1">
       <h2 class="bg-blue rounded-t  p-2 text-sm text-center uppercase"> {{ this.text }}</h2>

    <div class=" bg-black rounded-b  p-5 flex flex-wrap gap-5">

      <round-box-small v-for="(item, index) in data" :key="index" :obj="item.hero !== undefined ? item.hero : (item.game_map !== undefined ? item.game_map : item.talent)" :hovertext="item.hovertext" size="big"></round-box-small>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GroupBox',
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
    console.log('this.data', this.data);
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>