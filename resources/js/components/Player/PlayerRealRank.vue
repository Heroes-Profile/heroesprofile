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
      <real-rank-badge :rank="data.current" :width="200" label="below" label-class="text-2xl font-bold mt-2"></real-rank-badge>
      <div class="text-sm opacity-80">Last seen {{ formatDay(data.current.last_game_date) }}, read in {{ data.current.read_count.toLocaleString('en-US') }} {{ data.current.read_count == 1 ? 'game' : 'games' }}</div>
    </div>

    <div v-if="data.periods && data.periods.length > 1" class="mt-6">
      <h3 class="text-xl font-bold text-center">{{ data.season ? data.season : 'This season' }}</h3>
      <div class="flex flex-wrap justify-center gap-6 mt-3">
        <div v-for="(period, index) in data.periods" :key="index" class="flex flex-col items-center text-center w-[130px]">
          <real-rank-badge :rank="period" :width="80" label="below" label-class="font-bold"></real-rank-badge>
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
    formatDay(dateString){
      return moment.tz(dateString, 'UTC').tz(moment.tz.guess()).format('MMM D, YYYY');
    },
  }
}
</script>
