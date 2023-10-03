<template>
  <div class=" w-auto inline-block m-1">
    <h2 class="bg-blue rounded-t  p-2 text-sm text-center uppercase"> {{ this.text }}</h2>
    <div class=" bg-black rounded-b  p-5 flex flex-wrap gap-5">
      <template v-for="(item, index) in data" :key="index">


       <a v-if="match && playerlink && item.hero" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box 
              :title="item.hero.name" 
              :paragraph-one="`Played by ${item.battletag}`" 
              :paragraph-two="`Account Level: ${item.account_level}`"
              :paragraph-three="`Player MMR: ${item.player_mmr}`"
              :paragraph-four="`Hero MMR: ${item.hero_mmr}`"
              :paragraph-five="`Role MMR: ${item.role_mmr}`"
              :paragraph-six="`Hero Level: ${item.hero_level}`"
            ></image-hover-box>
          </hero-image-wrapper>
        </a>



        <a v-else-if="playerlink && item.hero" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
          </hero-image-wrapper>
        </a>

        <hero-image-wrapper v-else-if="item.hero" :size="'big'" :hero="item.hero">
          <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
        </hero-image-wrapper>


        <a v-if="playerlink && item.game_map" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name">
          <map-image-wrapper :size="'big'" :map="item.game_map">
            <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
          </map-image-wrapper>
        </a>
        <map-image-wrapper v-else-if="item.game_map" :size="'big'" :map="item.game_map">
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
      match: Boolean,
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