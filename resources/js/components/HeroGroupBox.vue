<template>
  <div>
        {{ this.text }}

    <div class="border border-gray-300 p-4 flex flex-wrap gap-0">
      CHANGE THIS TO group-box
      <round-image v-for="(item, index) in data" :key="index" :obj="item.hero" :hovertext="item.hovertext"></round-image>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeroGroupBox',
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
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>