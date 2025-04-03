<template>
  <div class=" w-auto inline-block m-1">
    <h2 v-if="text" :class="['bg-' + color, 'rounded-t', 'p-2', 'text-sm', 'text-center', 'uppercase']">{{ text }}</h2>
    <h2 v-else :class="['bg-' + color, 'rounded-t', 'p-2', 'text-sm', 'text-center', 'uppercase']">
      <a v-if="esport && esport != 'Other'" class="link" :href="`/Esports/${esport}/Team/${esportteamname}`">{{  esportteamname  }}</a>
      <a v-if="esport && esport == 'Other'" class="link" :href="`/Esports/${esport}/${series}/Team/${esportteamname}`">{{  esportteamname  }}</a>
      <span v-if="esport"> - </span>
      <span>{{ winnerloser }}</span>
    </h2>

    <div class=" bg-black rounded-b  p-5 flex flex-wrap gap-5 justify-center max-md:gap-2 max-md:p-2">
      <template v-for="(item, index) in data" :key="index">

        <!-- Hero Section -->
        <a v-if="!esport && match && playerlink && item.hero && !item.check" class="link cursor-pointer" @click="this.$redirectToProfile(item.battletag, item.blizz_id, item.region, false)" :href="`/Player/${item.battletag}/${item.blizz_id}/${item.region}`">
          <span>
            <hero-image-wrapper :size="'big'" :hero="item.hero" :award="item.match_award" :winner="winner" :hpowner="item.hp_owner" :party="item.party" :ispatreon="item.patreon_subscriber" popupsize="large">
              <image-hover-box 
                :title="item.hero.name" 
                :paragraph-one="`Played by : <b>${item.battletag}</b>`" 
                :paragraph-two="`Account Level: ${item.account_level}`"
                :paragraph-three="`Player MMR: ${item.player_mmr}`"
                :paragraph-four="`Hero MMR: ${item.hero_mmr}`"
                :paragraph-five="`Role MMR: ${item.role_mmr}`"
                :paragraph-six="`Hero Level: ${item.hero_level}`"
                :hpOwner="item.hp_owner ? 'Heroes Profile Owner' : null"
              ></image-hover-box>
            </hero-image-wrapper>
          </span>
        </a>
        <div v-else-if="!esport && match && playerlink && item.hero && item.check">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box 
              :title="item.hero.name" 
            ></image-hover-box>
          </hero-image-wrapper>
        </div>

        <a v-else-if="esport && esport != 'Other' && match && playerlink && item.hero" :href="'/Esports/' + esport + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name + (tournament ? '?tournament=' + tournament : '')" v-if="!item.check">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box 
              :title="item.hero.name" 
              :paragraph-one="`Played by ${item.battletag}`" 
              :paragraph-six="`Hero Level: ${item.hero_level}`"
            ></image-hover-box>
          </hero-image-wrapper>
        </a>

        <a v-else-if="esport && esport == 'Other' && match && playerlink && item.hero" :href="'/Esports/' + esport + '/' + series + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box 
              :title="item.hero.name" 
              :paragraph-one="`Played by ${item.battletag}`" 
              :paragraph-six="`Hero Level: ${item.hero_level}`"
            ></image-hover-box>
          </hero-image-wrapper>
        </a>

        <a v-else-if="!esport && playerlink && item.hero" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
          </hero-image-wrapper>
        </a>

        <a v-else-if="esport && playerlink && item.hero" :href="item.link">
          <hero-image-wrapper :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
          </hero-image-wrapper>
        </a>

        <hero-image-wrapper v-else-if="item.hero && useinputforhover" :size="'big'" :hero="item.hero">
          <image-hover-box :title="item.hero.name" :paragraph-one="item.inputhover"></image-hover-box>
        </hero-image-wrapper>


        <hero-image-wrapper v-else-if="item.hero" :size="'big'" :hero="item.hero">
          <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
        </hero-image-wrapper>


        <!--Map Section -->
        <a v-if="!esport && playerlink && item.game_map" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name">
          <map-image-wrapper :size="'big'" :map="item.game_map">
            <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
          </map-image-wrapper>
        </a>

        <a v-else-if="esport && playerlink && item.game_map" :href="item.link">
          <map-image-wrapper :size="'big'" :map="item.game_map">
            <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
          </map-image-wrapper>
        </a>

        <map-image-wrapper v-else-if="item.game_map && useinputforhover" :size="'big'" :map="item.game_map">
          <image-hover-box :title="item.game_map.name" :paragraph-one="item.inputhover"></image-hover-box>
        </map-image-wrapper>

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
      winnerloser: String,
      esportteamname: String,
      data: Array,
      playerlink: Boolean,
      type: String,
      match: Boolean,
      useinputforhover: Boolean,
      esport: String,
      color: String,
      winner: Boolean,
      popupsize: String,
      showpopup: true,
      series: String,
      tournament: String,
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