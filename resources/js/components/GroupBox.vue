<template>
  <div class=" w-auto inline-block m-1">
    <h2 v-if="text && !hideheader" class="text-sm text-subtext mb-2">{{ $t(text) }}</h2>
    <h2 v-else-if="!hideheader" :class="[colorClass, 'rounded', 'px-3 py-1.5', 'mb-2', 'text-sm', 'text-center', 'font-semibold']">
      <a v-if="esport && esport != 'Other'" class="link" :href="`/Esports/${esport}/Team/${esportteamname}${ tournament ? '?tournament=' + tournament : ''}`">{{  esportteamname  }}</a>
      <a v-if="esport && esport == 'Other'" class="link" :href="`/Esports/${esport}/${series}/Team/${esportteamname}`">{{  esportteamname  }}</a>
      <span v-if="esport"> - </span>
      <span>{{ winnerloser }}</span>
    </h2>

    <div v-if="!headeronly" class="flex flex-wrap gap-3 max-md:gap-2">
      <template v-for="(item, index) in data" :key="index">

        <!-- Hero Section -->
        <a v-if="!esport && match && playerlink && item.hero && !item.check" class="cursor-pointer" @click="this.$redirectToProfile(item.battletag, item.blizz_id, item.region, false)" :href="`/Player/${item.battletag}/${item.blizz_id}/${item.region}`">
          <span>
            <hero-image-wrapper size="tile" :hero="item.hero" :award="item.match_award" :winner="winner" :hpowner="item.hp_owner" :party="item.party" :ispatreon="item.patreon_subscriber" :voideye="hasVoidEye(item.blizz_id, item.region)">
              <image-hover-box 
                :title="$heroName(item.hero)" 
                :played-by="item.battletag" 
                :paragraph-two="$t('Account Level') + ': ' + item.account_level"
                :paragraph-three="$t('HP Player MMR') + ': ' + item.player_mmr"
                :paragraph-four="$t('HP Hero MMR') + ': ' + item.hero_mmr"
                :paragraph-five="$t('HP Role MMR') + ': ' + item.role_mmr"
                :paragraph-six="$t('Hero Level') + ': ' + item.hero_level"
                :hpOwner="item.hp_owner ? $t('Heroes Profile Owner') : null"
              ></image-hover-box>
            </hero-image-wrapper>
          </span>
        </a>
        <div v-else-if="!esport && match && playerlink && item.hero && item.check">
          <hero-image-wrapper size="tile" :hero="item.hero">
            <image-hover-box 
              :title="$heroName(item.hero)" 
            ></image-hover-box>
          </hero-image-wrapper>
        </div>

        <a v-else-if="esport && esport != 'Other' && match && playerlink && item.hero" :href="'/Esports/' + esport + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name + (tournament ? '?tournament=' + tournament : '')" v-if="!item.check">
          <hero-image-wrapper size="tile" :hero="item.hero">
            <image-hover-box 
              :title="$heroName(item.hero)" 
              :paragraph-one="$t('Played by') + ': ' + item.battletag" 
              :paragraph-six="$t('Hero Level') + ': ' + item.hero_level"
            ></image-hover-box>
          </hero-image-wrapper>
        </a>

        <a v-else-if="esport && esport == 'Other' && match && playerlink && item.hero" :href="'/Esports/' + esport + '/' + series + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name">
          <hero-image-wrapper size="tile" :hero="item.hero">
            <image-hover-box 
              :title="$heroName(item.hero)" 
              :paragraph-one="$t('Played by') + ': ' + item.battletag" 
              :paragraph-six="$t('Hero Level') + ': ' + item.hero_level"
            ></image-hover-box>
          </hero-image-wrapper>
        </a>

        <a v-else-if="!esport && playerlink && item.hero" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name">
          <hero-image-wrapper size="tile" :hero="item.hero" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" link-text="View Hero">
            <image-hover-box :title="$heroName(item.hero)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
          </hero-image-wrapper>
        </a>

        <a v-else-if="esport && playerlink && item.hero" :href="item.link">
          <hero-image-wrapper size="tile" :hero="item.hero">
            <image-hover-box :title="$heroName(item.hero)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
          </hero-image-wrapper>
        </a>

        <hero-image-wrapper v-else-if="item.hero && useinputforhover" size="tile" :hero="item.hero">
          <image-hover-box :title="$heroName(item.hero)" :paragraph-one="item.inputhover"></image-hover-box>
        </hero-image-wrapper>


        <hero-image-wrapper v-else-if="item.hero" size="tile" :hero="item.hero">
          <image-hover-box :title="$heroName(item.hero)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
        </hero-image-wrapper>


        <!--Map Section -->
        <a v-if="!esport && playerlink && item.game_map" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name">
          <map-image-wrapper size="tile" :map="item.game_map" :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" link-text="View Map">
            <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
          </map-image-wrapper>
        </a>

        <a v-else-if="esport && playerlink && item.game_map" :href="item.link">
          <map-image-wrapper size="tile" :map="item.game_map">
            <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
          </map-image-wrapper>
        </a>

        <map-image-wrapper v-else-if="item.game_map && useinputforhover" size="tile" :map="item.game_map">
          <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="item.inputhover"></image-hover-box>
        </map-image-wrapper>

        <map-image-wrapper v-else-if="item.game_map" size="tile" :map="item.game_map">
          <image-hover-box :title="$mapName(item.game_map)" :paragraph-one="$t('Win Rate') + ': ' + item.win_rate + '%'" :paragraph-two="$t('Games Played') + ': ' + item.games_played"></image-hover-box>
        </map-image-wrapper>

      </template>
      <!-- Empty slots up to minTiles, the tile's size with a dashed edge; the info icon in the corner says why they're empty -->
      <div v-for="slot in Math.max(0, (minTiles || 0) - (data || []).length)" :key="'empty' + slot" class="relative w-16 h-16 rounded border-2 border-dashed border-gray-dark">
        <round-image class="!absolute top-0.5 right-0.5" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
          <p class="font-normal">{{ $t(emptyNote) }}</p>
        </round-image>
      </div>
    </div>
  </div>
</template>

<script>
  import { hasVoidEye } from '../voidEye';

  // Whole class names, so Tailwind sees them
  const colorClasses = {
    blue: 'bg-blue',
    teal: 'bg-teal',
    purple: 'bg-purple',
    red: 'bg-red',
    yellow: 'bg-yellow',
    'gray-dark': 'bg-gray-dark',
  };

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
      hideheader: Boolean,
      headeronly: Boolean,
      // Pad the group with empty slots to this many tiles, each with emptyNote behind an info icon
      minTiles: Number,
      emptyNote: { type: String, default: 'Not enough games yet.' },
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
      colorClass() {
        return colorClasses[this.color];
      },
    },
    watch: {
    },
    methods: {
      hasVoidEye,
    }
  }
</script>