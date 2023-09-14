<template>
  <div>
        {{ this.text }}

    <div class="border border-gray-300 p-4 flex flex-wrap gap-0">
      <hero-box-small v-for="(item, index) in data" :key="index" :hero="item.hero" :hovertext="item.hovertext"></hero-box-small>
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