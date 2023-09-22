<template>
  <div class=" w-auto inline-block m-1">
    <h2 class="bg-blue rounded-t  p-2 text-sm text-center uppercase"> {{ this.text }}</h2>
    <div class=" bg-black rounded-b  p-5 flex flex-wrap gap-5">
      <template v-for="(item, index) in data" :key="index">
        <div v-if="item.hero">
          <a v-if="item.blizz_id" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name">
            <hero-image-wrapper :hovertextstyleoverride="true" :size="'big'" :hero="item.hero">
              <div class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
                <h2>{{ item.hero.name }}</h2>
                <p>{{ item.hovertext }}</p>
              </div>
            </hero-image-wrapper>
          </a>
          <hero-image-wrapper v-else-if="type == 'Matchups'" :size="'big'" :hero="item.hero" :hovertextstyleoverride="true">
            <div class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
              <h2>{{ item.hero.name }}</h2>
              <p>{{ item.hovertext }}</p>
            </div>
          </hero-image-wrapper>
          <hero-image-wrapper v-else :size="'big'" :hero="item.hero"></hero-image-wrapper>
        </div>

        <div v-if="item.game_map">
          <a v-if="item.blizz_id" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name">
            <map-image-wrapper :hovertextstyleoverride="true" :size="'big'" :map="item.game_map">
              <div class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
                <h2>{{ item.game_map.name }}</h2>
                <p>{{ item.hovertext }}</p>
              </div>
            </map-image-wrapper>
          </a>
          <map-image-wrapper v-else :size="'big'" :map="item.game_map"></map-image-wrapper>
        </div>

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
    },
    computed: {
    },
    watch: {
    },
    methods: {
    }
  }
</script>