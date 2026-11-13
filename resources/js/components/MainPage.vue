<template>
  <div class="flex-1 flex flex-col">

    <div v-if="showPopup" class="modal">
      <new-user-popup @popupclosed="showPopup = false"></new-user-popup>
    </div>


    <!-- Short windows (laptops at 125-150% scaling): smaller logo, icons and gaps so the cards stay on the first screen -->
    <div class="flex-[1_1_0] min-h-12 md:[@media(max-height:700px)]:min-h-6 max-md:hidden"></div>

    <div class="text-center px-4 max-md:hidden">
      <!-- Equal-width words put the hexagon on the page's centre line; PROFILE is the wider word -->
      <h1 class="flex items-center justify-center font-logo text-4xl md:text-8xl md:[@media(max-height:700px)]:text-6xl normal-case">
        <span class="flex-1 basis-0 text-right whitespace-nowrap">Heroes</span>
        <img class="w-[1.667em] mx-[0.333em] shrink-0" src="/images/logo/heroesprofilelogo.png" alt=""/>
        <span class="flex-1 basis-0 text-left whitespace-nowrap">Profile</span>
      </h1>
      <!-- Count rolls up at the last day's average upload rate; no requests after page load -->
      <!-- Digits shift by % of their 0-9 strip, not em: the root font size scales with the window, and an em shift would roll on resize -->
      <div v-if="tickerCount" class="mt-6 md:[@media(max-height:700px)]:mt-3 flex flex-col items-center gap-1">
        <p class="text-2xl font-bold">
          <span class="inline-flex tabular-nums leading-none align-baseline" :aria-label="tickerCount.toLocaleString('en-US')">
            <template v-for="(char, index) in tickerChars" :key="tickerChars.length - index">
              <span v-if="/\d/.test(char)" class="inline-block h-[1em] overflow-hidden" aria-hidden="true">
                <span class="flex flex-col transition-transform duration-500 ease-out" :style="{ transform: `translateY(-${char * 10}%)` }">
                  <span v-for="digit in 10" :key="digit" class="h-[1em]">{{ digit - 1 }}</span>
                </span>
              </span>
              <span v-else aria-hidden="true">{{ char }}</span>
            </template>
          </span>
          replays uploaded
        </p>
        <template v-if="latestgamedate">
          <p v-if="parsingStale" class="bg-red rounded px-4 py-2 text-sm flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i>Replay parsing is currently offline. Latest game played {{ latestGameAgo }}.</p>
          <p v-else class="text-sm text-subtext">Latest game played {{ latestGameAgo }}</p>
        </template>
      </div>

         </div>

    <!-- Wider than the gaps inside each group, so the logo and the cards read as two groups -->
    <div class="flex-[1.5_1_0] min-h-28 md:[@media(max-height:700px)]:min-h-10 max-md:min-h-0"></div>

    <!-- md+: one row of cards that shrink to fit; below md the two wrappers keep the mobile order -->
    <div class="flex flex-wrap md:flex-nowrap justify-center gap-x-10 md:gap-x-6 2xl:gap-x-10 gap-y-16 max-w-[120rem] mx-auto px-4 md:px-8 max-md:pt-8">
      <div class="flex flex-wrap justify-center gap-x-10 gap-y-16 max-w-[120rem] md:contents">
      <div :class="cardClasses">
        <i class="fas fa-address-card text-[5rem] md:[@media(max-height:700px)]:text-[3.5rem]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">{{ $t('Player Profiles') }}</h3>
        <p class="text-sm text-left mb-8">{{ $t('See all player stats in one place.  See data for individual maps or heroes played, match history, real in-game Storm League rank and comparisons all from within a streamlined profile.') }}</p>
        <div class="flex mt-auto">
          <custom-button :text="'Find Player'" :alt="'Find Player'" :color="'teal'" :ignoreclick="true" class="flex-1 font-semibold" @click="focusSearch"></custom-button>
        </div>
      </div>
      <div :class="cardClasses">
        <i class="fas fa-chart-line text-[5rem] md:[@media(max-height:700px)]:text-[3.5rem]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">{{ $t('Player Analytics') }}</h3>
        <p class="text-sm text-left mb-8">{{ $t('Deep analysis of your season replays: where and how you die, time spent, performance ahead or behind, habit coaching, and a replay viewer for every game. Pre-alpha: try it free, then keep it with Patreon or any paid API plan.') }}</p>
        <div class="flex mt-auto">
          <custom-button v-if="analyticsAllowed" :href="'/Analytics'" :text="'Open Analytics'" :alt="'Open Analytics'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
          <custom-button v-else :href="'/Analytics'" :text="'Try It Free'" :alt="'Try Player Analytics free'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>
      <div :class="cardClasses" class="max-md:order-first">
        <i class="fa-solid fa-globe text-[5rem] md:[@media(max-height:700px)]:text-[3.5rem]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">{{ $t('Global Stats') }}</h3>
        <p class="text-sm text-left mb-8">{{ $t('Variety of Heroes of the Storm stats, including global stats for heroes, talent stats, overall stats, builds, compositions, draft, matchups, and more.') }}</p>
        <div class="flex flex-wrap gap-4 mt-auto">
          <custom-button :href="'/Global/Hero'" :text="'Hero Stats'" :alt="'Hero Stats'" :color="'teal'" class="grow basis-auto font-semibold"></custom-button>
          <custom-button :href="'/Global/Talents'" :text="'Talent Stats'" :alt="'Talent Stats'" :color="'teal'" class="grow basis-auto font-semibold"></custom-button>
        </div>
      </div>
</div>
<div class="flex flex-wrap justify-center gap-x-10 gap-y-16 max-w-[120rem] md:contents">
      <div :class="cardClasses">
        <i class="fa-solid fa-grip text-[5rem] md:[@media(max-height:700px)]:text-[3.5rem]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">{{ $t('Drafter') }}</h3>
        <p class="text-sm text-left mb-8">{{ $t('Get ban and pick suggestions at every step of a Storm League draft, based on how often heroes are taken at that point, their win rates and bans, and the team compositions they fit into.') }}</p>
        <div class="flex mt-auto">
          <custom-button :href="'/Drafter'" :text="'Drafter'" :alt="'Drafter'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>
      <div :class="cardClasses" class="max-md:order-first">
        <i class="fas fa-list-ol text-[5rem] md:[@media(max-height:700px)]:text-[3.5rem]"></i>
        <h3 class="text-3xl font-semibold mt-6 mb-4">{{ $t('Leaderboards') }}</h3>
        <p class="text-sm text-left mb-8">{{ $t("View leaderboards based on Player, Hero, or Role using Heroes Profile Rating, real in-game Storm League ranks, and the Match Prediction game. Get talent builds, and navigate directly to player's profiles.") }}</p>
        <div class="flex mt-auto">
          <custom-button :href="'/Global/Leaderboard'" :text="'View Leaderboards'" :alt="'View Leaderboards'" :color="'teal'" class="flex-1 font-semibold"></custom-button>
        </div>
      </div>
    </div>

</div>
    <div class="h-[8.75rem] md:[@media(max-height:700px)]:h-12 shrink-0 max-md:h-12"></div>

    <div>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="0" :mobile-override="false"></dynamic-banner-ad>
    </div>
    <div class="bg-surface-2 text-sm px-4 md:px-8 py-6">
      <div class="max-w-[116rem] mx-auto space-y-2">
        <p>{{ $t('Heroes Profile uses data from the Heroes Profile API, an open Heroes of the Storm replay database built from user-uploaded replays.') }}
          So far, Heroes Profile has pulled {{ getValueLocal(maxreplayid) }} replays into its dataset, up to and including patch
        {{ latestpatch }} and games played through <format-date :input="latestgamedate"></format-date>.</p>
        <p>For more information on the Heroes Profile API, visit <a class="underline" href="/Api">heroesprofile.com/Api</a>.</p>
      </div>
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';
import moment from 'moment-timezone';


  export default {
    name: 'MainPage',
    components: {
    },
    props: {
      user: Object,
      patreonUser: Boolean,
      maxreplayid: Number,
      latestpatch: String,
      latestgamedate: String,
      // { count, rate }: rate is replays per second
      replayTicker: Object,
      // GlobalDataService::PARSING_STALE_MINUTES
      parsingStaleMinutes: {
        type: Number,
        default: 60,
      },
      // Whether the signed-in player can open Analytics (trial, Patreon or a paid API plan)
      analyticsAllowed: Boolean,
    },
    data(){
      return {
        showPopup: true,
        tickerStart: Date.now(),
        now: Date.now(),
        tickerTimer: null,
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
      this.tickerTimer = setInterval(() => { this.now = Date.now(); }, 1000);
    },
    beforeUnmount() {
      clearInterval(this.tickerTimer);
    },
    computed: {
      tickerCount() {
        if (!this.replayTicker?.count) {
          return null;
        }
        // Parsing offline: hold at the real count
        if (this.latestgamedate && this.parsingStale) {
          return this.maxreplayid || this.replayTicker.count;
        }
        const rate = this.replayTicker.rate || 0;
        return this.replayTicker.count + Math.floor(rate * (this.now - this.tickerStart) / 1000);
      },
      tickerChars() {
        return this.tickerCount.toLocaleString('en-US').split('');
      },
      // No new game in that long means the parser has likely stopped
      parsingStale() {
        return this.now - moment.tz(this.latestgamedate, 'Atlantic/Reykjavik').valueOf() > this.parsingStaleMinutes * 60 * 1000;
      },
      latestGameAgo() {
        // Recomputes with the ticker clock
        this.now;
        return moment.tz(this.latestgamedate, 'Atlantic/Reykjavik').fromNow();
      },
      cardClasses() {
        return 'flex flex-col text-center w-[21.0625rem] md:w-auto md:flex-1 md:min-w-0 md:max-w-[21.0625rem]';
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