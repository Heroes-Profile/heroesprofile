<template>
  <div>
        {{ this.text }}

    <div class="border border-gray-300 p-4 flex flex-wrap gap-0">
      <hero-box v-for="(item, index) in firstFiveData" :key="index" :hero="item.hero" :hovertext="item.hovertext"></hero-box>
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
    firstFiveData() {
      return this.data.slice(0, 5);
    }
  },
  watch: {
  },
  methods: {
  }
}
</script>