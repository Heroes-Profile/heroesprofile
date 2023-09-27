<template>
  <div class=" w-auto inline-block m-1">
    <h2 class="bg-blue rounded-t  p-2 text-sm text-center uppercase"> {{ this.text }}</h2>
    <div class=" bg-black rounded-b  p-5 flex flex-wrap gap-5">
      <template v-for="(item, index) in data" :key="index">

        <hero-image-wrapper  v-if="item.hero" :size="'big'" :hero="item.hero">
          <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
        </hero-image-wrapper>

        <map-image-wrapper v-if="item.game_map" :size="'big'" :map="item.game_map">
          <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
        </map-image-wrapper>

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
      playerlink: Boolean,
      type: String,
    },
    data(){
      return {
      }
    },
    created(){
    },
    mounted() {
      console.log(this.data);
    },
    computed: {
    },
    watch: {
    },
    methods: {
    }
  }
</script>