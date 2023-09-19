<template>
  <div class=" w-auto inline-block m-1">
    <h2 class="bg-blue rounded-t  p-2 text-sm text-center uppercase"> {{ this.text }}</h2>
    <div class=" bg-black rounded-b  p-5 flex flex-wrap gap-5">
      <template v-for="(item, index) in data" :key="index">
        <hero-image-wrapper v-if="item.hero" :size="'big'" :hero="item.hero"></hero-image-wrapper>
        <map-image-wrapper v-if="item.game_map" :size="'big'" :map="item.game_map"></map-image-wrapper>
      </template>
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