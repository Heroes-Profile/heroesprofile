<template>
  <div v-if="data" class="mx-4 text-sm">
    <div class="flex justify-center mb-4">
      <a
        v-for="(item, index) in modes"
        :key="item.key"
        role="button"
        :class="[
          'transition-colors text-white inline-block py-2 px-4 max-sm:px-2 max-sm:text-xs',
          {
            'rounded-l': index === 0,
            'rounded-r': index === modes.length - 1,
            'bg-blue hover:bg-lblue ring-inset ring-lblue ring-2': mode === item.key,
            'bg-gray-dark hover:bg-gray-light': mode !== item.key,
          }
        ]"
        @click="selectMode(item.key)"
      >
        {{ item.label }}
      </a>
    </div>

    <div v-if="mmrSplit" class="max-w-[1500px] mx-auto mb-4">
      <div class="flex justify-between items-baseline mb-1">
        <span class="text-xs">Avg. {{ modeShort }} MMR <span class="text-base font-bold">{{ mmrSplit.teamOne }}</span></span>
        <span class="text-xs text-gray-medium">{{ mmrSplit.lead }}</span>
        <span class="text-xs"><span class="text-base font-bold">{{ mmrSplit.teamTwo }}</span> Avg. {{ modeShort }} MMR</span>
      </div>
      <div class="flex h-2 rounded overflow-hidden">
        <div :class="'bg-' + teamColors[0]" :style="{ width: mmrSplit.percent + '%' }"></div>
        <div :class="['flex-1', 'bg-' + teamColors[1]]"></div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 max-w-[1500px] mx-auto">
      <div v-for="team in [0, 1]" :key="team" class="min-w-0">
        <h2 :class="['bg-' + teamColors[team], 'rounded-t', 'p-2', 'text-sm', 'text-center', 'uppercase']">Team {{ team + 1 }}</h2>

        <div v-if="data[team]" class="bg-black rounded-b">
          <div class="flex flex-wrap justify-center p-2">
            <stat-box class="flex-1" :title="'Avg. ' + modeShort + ' HP MMR'" :value="data[team]['average_' + mode + '_mmr'] === null ? '—' : data[team]['average_' + mode + '_mmr'] + '|' + data[team]['average_' + mode + '_rank']" :color="teamColors[team]"></stat-box>
            <stat-box class="flex-1" :title="'Avg. Account Level'" :value="data[team].average_account_level" :color="teamColors[team]"></stat-box>
            <stat-box class="flex-1" :title="'Best ' + modeShort + ' HP Rank'" :value="data[team]['highest_' + mode + '_mmr_battletag'] || '—'" :color="teamColors[team]"></stat-box>
          </div>

          <prematch-player-row
            v-for="(player, index) in data[team].players"
            :key="index"
            :player="player"
            :mode="mode"
            :modelabel="modeLabel"
            :color="teamColors[team]"
            :expanded="expanded === team + '-' + index"
            :recentloading="recentLoading"
            @toggle="toggle(team + '-' + index)"
          ></prematch-player-row>
        </div>
      </div>
    </div>

    <p class="text-xs text-gray-medium text-center mt-4">Faded players have fewer than 25 games in this mode. Squares are their last 10 games, newest first.</p>
  </div>
  <div v-else-if="isLoading">
    <loading-component></loading-component>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'Prematch',
  props: {
    prematchid: Number,
  },
  data(){
    return {
      isLoading: false,
      recentLoading: false,
      data: null,
      mode: 'qm',
      // Set once the viewer picks a mode themselves; the game's own mode no longer overrides it.
      modePicked: false,
      modePollTimer: null,
      modePollsLeft: 60,
      expanded: null,
      modes: [
        { key: 'qm', label: 'Quick Match', short: 'QM' },
        { key: 'sl', label: 'Storm League', short: 'SL' },
        { key: 'ar', label: 'ARAM', short: 'ARAM' },
      ],
      teamColors: ['teal', 'blue'],
    }
  },
  created(){
    const savedMode = Cookies.get('prematchMode');
    if (this.modes.some(item => item.key === savedMode)) {
      this.mode = savedMode;
    }
    this.getData();
  },
  beforeUnmount() {
    clearTimeout(this.modePollTimer);
  },
  computed: {
    modeLabel() {
      return this.modes.find(item => item.key === this.mode).label;
    },
    modeShort() {
      return this.modes.find(item => item.key === this.mode).short;
    },
    mmrSplit() {
      if (!this.data[0] || !this.data[1]) {
        return null;
      }
      const teamOne = this.data[0]['average_' + this.mode + '_mmr'];
      const teamTwo = this.data[1]['average_' + this.mode + '_mmr'];
      if (teamOne === null || teamTwo === null) {
        return null;
      }
      return {
        teamOne,
        teamTwo,
        lead: teamOne === teamTwo ? 'Even' : `Team ${teamOne > teamTwo ? 1 : 2} +${Math.abs(teamOne - teamTwo)}`,
        // A 300 MMR gap fills the bar for the leading team.
        percent: 50 + Math.max(-1, Math.min(1, (teamOne - teamTwo) / 300)) * 50,
      };
    },
  },
  methods: {
    async getData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/prematch", {
          prematchid: this.prematchid,
        });
        this.data = response.data;
      }catch(error){
      //Do something here
      }finally {
        this.isLoading = false;
      }
      if (this.data) {
        this.getRecentGames();
        this.pollGameMode();
      }
    },
    // The page opens at the loading screen, before the game's mode is known. The uploader
    // sends it once the game starts, so check every few seconds for a while.
    async pollGameMode(){
      if (this.modePicked) {
        return;
      }
      try{
        const response = await this.$axios.post("/api/v1/prematch/mode", {
          prematchid: this.prematchid,
        });
        const gameMode = response.data.mode;
        if (gameMode) {
          if (!this.modePicked && this.modes.some(item => item.key === gameMode)) {
            this.mode = gameMode;
          }
          return;
        }
      }catch(error){
      // Try again on the next round
      }
      if (--this.modePollsLeft > 0) {
        this.modePollTimer = setTimeout(this.pollGameMode, 5000);
      }
    },
    selectMode(key) {
      this.mode = key;
      this.modePicked = true;
      clearTimeout(this.modePollTimer);
      Cookies.set('prematchMode', key, { expires: 365, path: '/' });
    },
    async getRecentGames(){
      this.recentLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/prematch/recent", {
          prematchid: this.prematchid,
        });
        Object.values(this.data).forEach(team => {
          team.players.forEach(player => {
            if (player.blizz_id) {
              player.recent_games = response.data[player.blizz_id + '|' + player.region];
            }
          });
        });
      }catch(error){
      // No squares if this fails
      }finally {
        this.recentLoading = false;
      }
    },
    toggle(key) {
      this.expanded = this.expanded === key ? null : key;
    },
  }
}
</script>
