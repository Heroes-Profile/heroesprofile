<template>
  <div class="flex-1 flex flex-col">

    <div v-if="showPopup" class="modal">
      <new-user-popup @popupclosed="showPopup = false"></new-user-popup>
    </div>


    <div class="text-center pt-10 pb-8 px-4">
      <h1 class="flex items-center justify-center font-logo text-4xl md:text-8xl normal-case">
        Heroes
        <img class="w-[1.667em] mx-[0.333em]" src="/images/logo/heroesprofilelogo.png" alt=""/>
        Profile
      </h1>

      <div ref="heroSearch" class="flex justify-center mt-10">
        <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'" class="text-lg"></search-component>
      </div>
    </div>


    <div class="flex flex-wrap justify-center gap-x-10 gap-y-16 max-w-[1920px] mx-auto px-4 md:px-8 pb-8">

      <div :class="cardClasses">
        <i class="fa-solid fa-globe text-[80px]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">Global Stats</h3>
        <p class="text-sm text-left mb-8">Variety of Heroes of the Storm stats, including global stats for heroes, talent stats, overall stats, builds, compositions, draft, matchups, and more.</p>
        <div class="flex gap-4 mt-auto">
          <custom-button :href="'/Global/Hero'" :text="'Hero Stats'" :alt="'Hero Stats'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
          <custom-button :href="'/Global/Talents'" :text="'Talent Stats'" :alt="'Talent Stats'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>

      <div :class="cardClasses">
        <i class="fas fa-list-ol text-[80px]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">Leaderboards</h3>
        <p class="text-sm text-left mb-8">View leaderboards based on Player, Hero, or Role using Heroes Profile Rating, real in-game Storm League ranks, and the Match Prediction game. Get talent builds, and navigate directly to player's profiles.</p>
        <div class="flex mt-auto">
          <custom-button :href="'/Global/Leaderboard'" :text="'View Leaderboards'" :alt="'View Leaderboards'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>

      <div :class="cardClasses">
        <i class="fa-solid fa-database text-[80px]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">Heroes Profile API</h3>
        <p class="text-sm text-left mb-8">Heroes Profile API provides access to parsed Heroes of the Storm data from HeroesProfile.com, including player stats, match details, and performance insights.</p>
        <div class="flex mt-auto">
          <custom-button :href="'/Api'" :text="'API'" :alt="'API'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>

      <div :class="cardClasses">
        <i class="fas fa-address-card text-[80px]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">Player Profile</h3>
        <p class="text-sm text-left mb-8">See all player stats in one place.  See data for individual maps or heroes played, match history, real in-game Storm League rank and comparisons all from within a streamlined profile.</p>
        <div class="flex mt-auto">
          <custom-button :text="'Find Player'" :alt="'Find Player'" :color="'blue'" :ignoreclick="true" class="flex-1 font-semibold" @click="focusSearch"></custom-button>
        </div>
      </div>

      <div :class="cardClasses">
        <i class="fas fa-chart-line text-[80px]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">Player Analytics</h3>
        <p class="text-sm text-left mb-8">Deep analysis of your season replays: where and how you die, time spent, performance ahead or behind, habit coaching, and a replay viewer for every game. Pre-alpha for Patreon supporters.</p>
        <div class="flex mt-auto">
          <custom-button v-if="analyticsAllowed" :href="'/Analytics'" :text="'Open Analytics'" :alt="'Open Analytics'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
          <custom-button v-else :href="'/Analytics'" :text="'Learn More'" :alt="'Learn more about Analytics'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>
    </div>


    <div class="mt-auto bg-surface-2 text-sm px-4 md:px-8 py-6">
      <div class="max-w-[1856px] mx-auto space-y-2">
        <p>Heroes Profile uses data from Heroes Profile API.  Heroes Profile API uploads are in open Heroes of the Storm replay database with user uploaded replay data.
          Currently, Heroes Profile has pulled {{ getValueLocal(maxreplayid) }} replays up to and including data from patch
        {{ latestpatch }} and date/time <format-date :input="latestgamedate"></format-date> and incorporated them into our dataset.</p>
        <p>For more information on Heroes Profile API navigate to <a class="underline" href="/Api">heroesprofile.com/Api</a></p>
      </div>
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';


  export default {
    name: 'MainPage',
    components: {
    },
    props: {
      user: Object,
      maxreplayid: Number,
      latestpatch: String,
      latestgamedate: String,
      // Whether the signed-in player can open Analytics (Patreon supporters)
      analyticsAllowed: Boolean,
    },
    data(){
      return {
        showPopup: true,
      }
    },
    created(){
      Cookies.remove('additional-battletags');

      if (typeof localStorage !== 'undefined' && localStorage !== null) {
        if (localStorage.getItem('newUserPopup')) {
          this.showPopup = false;
        }
      } else {
        this.showPopup = false;
      }
    },
    mounted() {
    },
    computed: {
      cardClasses() {
        return 'flex flex-col text-center w-full md:w-[calc(50%-1.25rem)] xl:w-[calc(33.333%-1.75rem)] 2xl:w-[calc(20%-2rem)]';
      },
    },
    watch: {
    },
    methods: {
      focusSearch() {
        const input = this.$refs.heroSearch.querySelector('input');
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        input.focus({ preventScroll: true });
      },
      getValueLocal(value){
        return value ? value.toLocaleString('en-US') : "";
      },
    }
  }
</script>