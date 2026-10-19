<template>
  <div v-if="data" class="max-w-[1500px] mx-auto px-4">
    <h2 class="text-3xl font-bold py-5 text-center">Storm League Rank
      <round-image class="mt-2" size="small" icon="fas fa-info" title="info" popupsize="large">
        <slot>
          <div>
            <p class="max-sm:text-xs">This is the player's real in-game Storm League rank, not an estimate. Blizzard doesn't publish ranks, so we read them off the game itself.</p>
            <p class="max-sm:text-xs mt-2">The "Windows, with rank reading" version of the Heroes Profile uploader has an opt-in "Read ranks from the loading screen" setting. When it's on, the uploader looks at the loading screen of each Storm League game (only the game window, only while the loading screen is up), reads every player's rank badge, and sends those ranks to us along with the game's replay.</p>
            <p class="max-sm:text-xs mt-2">We only keep a rank for a player who is actually in that replay, and only when the uploader is confident the badge belongs to them. Each time the same rank is read again we count it, so "read in 12 games" means twelve separate loading screens showed that rank. A new rank, or a new season, starts a new entry.</p>
            <p class="max-sm:text-xs mt-2">A rank only appears once someone with the setting on has played a game with this player, so it can be out of date. Ranks are as read and can occasionally be wrong.</p>
          </div>
        </slot>
      </round-image>
    </h2>

    <div v-if="data.current" class="flex flex-col items-center">
      <div class="relative" :style="{ width: badgeWidth(data.current.league, 200) + 'px' }">
        <img v-if="badgeImage(data.current)" :src="badgeImage(data.current)" :alt="rankName(data.current)" class="w-full">
        <span v-if="data.current.league == 'grandmaster' && data.current.ladder_position" class="absolute left-0 right-0 text-center font-bold text-white leading-none" :style="ladderStyle(badgeWidth('grandmaster', 200), data.current.ladder_position)">#{{ data.current.ladder_position }}</span>
      </div>
      <div class="text-2xl font-bold mt-2">{{ rankName(data.current) }}</div>
      <div class="text-sm opacity-80">Last seen {{ formatDay(data.current.last_game_date) }}, read in {{ data.current.read_count.toLocaleString('en-US') }} {{ data.current.read_count == 1 ? 'game' : 'games' }}</div>
    </div>

    <div v-if="data.periods && data.periods.length > 1" class="mt-6">
      <h3 class="text-xl font-bold text-center">{{ data.season ? data.season : 'This season' }}</h3>
      <div class="flex flex-wrap justify-center gap-6 mt-3">
        <div v-for="(period, index) in data.periods" :key="index" class="flex flex-col items-center text-center w-[130px]">
          <div class="relative" :style="{ width: badgeWidth(period.league, 80) + 'px' }">
            <img v-if="badgeImage(period)" :src="badgeImage(period)" :alt="rankName(period)" class="w-full">
            <span v-if="period.league == 'grandmaster' && period.ladder_position" class="absolute left-0 right-0 text-center font-bold text-white leading-none" :style="ladderStyle(badgeWidth('grandmaster', 80), period.ladder_position)">#{{ period.ladder_position }}</span>
          </div>
          <div class="font-bold">{{ rankName(period) }}</div>
          <div class="text-xs opacity-80">{{ formatDay(period.first_game_date) }} - {{ formatDay(period.last_game_date) }}</div>
          <div class="text-xs opacity-80">{{ period.read_count.toLocaleString('en-US') }} {{ period.read_count == 1 ? 'game' : 'games' }}</div>
        </div>
      </div>
    </div>

    <div v-if="!data.current" class="text-center">No in-game rank has been read for this player yet.</div>

    <details class="bg-lighten rounded-lg text-sm max-w-[800px] mx-auto mt-8 group">
      <summary class="px-6 py-4 flex justify-between items-center cursor-pointer list-none">
        <h3 class="text-lg">How to get your rank on Heroes Profile</h3>
        <i class="fas fa-chevron-down transition-transform group-open:rotate-180"></i>
      </summary>
      <div class="px-6 pb-6 space-y-4">
        <ol class="list-decimal list-inside space-y-1">
          <li>Install the <strong>Windows, with rank reading</strong> version of the Heroes Profile uploader.</li>
          <li>In the uploader's Settings, turn on <strong>Read ranks from the loading screen</strong>.</li>
          <li>Play Storm League. Once a game's replay is uploaded, the ranks read from its loading screen show up here, for you and for everyone else in that game.</li>
        </ol>
        <p>Rank reading is Windows only for now, and off until you turn it on.
          <a class="link" href="/Upload/Uploader#rank-reading">How rank reading works</a>.
        </p>
        <custom-button href="/Upload" text="Get the uploader" alt="Download the Heroes Profile replay uploader" color="teal"></custom-button>
      </div>
    </details>
  </div>
</template>

<script>
import moment from 'moment-timezone';

// Image widths in public/images/ranks: league badges are 350 wide, Master 380, Grandmaster 420, all at the game's scale
const IMAGE_WIDTHS = { master: 380, grandmaster: 420 };

export default {
  name: 'PlayerRealRank',
  props: {
    blizzid: [String, Number],
    region: [String, Number],
  },
  data(){
    return {
      data: null,
    }
  },
  mounted() {
    this.getData();
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/player/realrank", {
          blizz_id: this.blizzid,
          region: this.region,
        });
        this.data = response.data;
      }catch(error){
        //Do something here
      }
    },
    badgeImage(rank){
      if(rank.league == 'unranked' || !rank.league){
        return null;
      }
      if(rank.league == 'master' || rank.league == 'grandmaster'){
        return '/images/ranks/' + rank.league + '.png';
      }
      return '/images/ranks/' + rank.league + '_' + rank.division + '.png';
    },
    // Keeps every badge at the game's relative scale: a league badge is `width` wide, Master and Grandmaster a little larger
    badgeWidth(league, width){
      return Math.round(width * (IMAGE_WIDTHS[league] || 350) / 350);
    },
    // The ladder number sits between the "Grand Master" label and the crown's tip.
    // Longer numbers get a smaller font so "#999" still fits inside the disc (about half the badge's width).
    ladderStyle(width, position){
      const height = width * 440 / 420;
      const characters = String(position).length + 1;
      const fontSize = Math.min(height * 0.19, width * 0.44 / (0.62 * characters));
      return { top: (height * 0.36 + (height * 0.19 - fontSize) / 2) + 'px', fontSize: fontSize + 'px', textShadow: '0 0 6px rgba(110, 180, 255, 0.9)' };
    },
    rankName(rank){
      if(rank.league == 'unranked'){
        return 'Unranked';
      }
      if(rank.league == 'grandmaster'){
        return 'Grandmaster' + (rank.ladder_position ? ' #' + rank.ladder_position : '');
      }
      if(rank.league == 'master'){
        return 'Master';
      }
      return rank.league.charAt(0).toUpperCase() + rank.league.slice(1) + ' ' + rank.division;
    },
    formatDay(dateString){
      return moment.tz(dateString, 'UTC').tz(moment.tz.guess()).format('MMM D, YYYY');
    },
  }
}
</script>
