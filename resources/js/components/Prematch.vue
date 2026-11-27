<template>
  <div v-if="data" class="w-full max-w-[120rem] mx-auto px-4 md:px-8 pt-10 pb-10 flex flex-col gap-8">
    <div class="flex flex-wrap items-end justify-between gap-6">
      <div class="flex flex-col gap-2 min-w-0">
        <h1 class="text-4xl md:text-6xl font-bold">{{ $t('Prematch') }}</h1>
        <p class="text-base md:text-2xl uppercase text-subtext">{{ $t("Who you're playing with and against") }}</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <tab-button :dark="true" :tabs="modes.map(item => ({ key: item.key, text: item.label }))" :overridedefaultside="mode" :ignoreclick="true" @tab-click="selectMode"></tab-button>
        <!-- How to read the rows: floats over the page, like Choose columns -->
        <div :class="['relative', { 'z-50': legendOpen }]">
          <div v-if="legendOpen" class="fixed inset-0 bg-black/50" @click="legendOpen = false"></div>
          <custom-button class="relative" @click="legendOpen = !legendOpen" text="How to read this" alt="How to read this" :ignoreclick="true"></custom-button>
          <div v-if="legendOpen" class="md:absolute md:right-0 md:top-full md:mt-3 max-md:fixed max-md:inset-x-4 max-md:top-1/2 max-md:-translate-y-1/2 max-md:max-h-[85dvh] max-md:overflow-y-auto z-50 md:w-[34rem] bg-surface-4 border border-surface-3 rounded shadow-2xl p-5 flex flex-col gap-3 text-sm text-subtext">
            <div class="flex justify-between gap-4">
              <h3 class="flex items-center gap-2 text-base font-semibold text-header"><basil-icon name="info" class="text-lg"></basil-icon>{{ $t('How to read this') }}</h3>
              <button type="button" class="w-9 h-9 -mt-1.5 -mr-1.5 flex items-center justify-center rounded hover:bg-surface-3 transition-colors" :aria-label="$t('Close')" @click="legendOpen = false"><basil-icon name="cross" class="text-xl"></basil-icon></button>
            </div>
            <p class="flex items-center gap-3"><span class="flex gap-1 shrink-0"><span class="w-3.5 h-3.5 rounded-[3px] bg-[#4dcc8c]"></span><span class="w-3.5 h-3.5 rounded-[3px] bg-loss"></span><span class="w-3.5 h-3.5 rounded-[3px] bg-[#4dcc8c]"></span></span>{{ $t('Squares are the last 10 games in this mode, newest first.') }}</p>
            <p class="flex items-center gap-3"><span class="flex gap-1 shrink-0"><span class="rounded-md bg-[#b22122] px-1.5 text-[0.625rem] font-bold uppercase text-white">{{ $t('Party') }}</span><span class="rounded-md bg-[#1f5faf] px-1.5 text-[0.625rem] font-bold uppercase text-white">{{ $t('Party') }}</span></span>{{ $t('Players who partied together in their recent QM, SL or ARAM games. Same colour, same party.') }}</p>
            <p class="flex items-center gap-3"><span class="shrink-0 rounded-md bg-surface-3 px-1.5 text-[0.625rem] font-bold uppercase text-subtext">{{ $t('Few games') }}</span>{{ $t('Faded players have fewer than 25 games in this mode.') }}</p>
            <p class="flex items-center gap-3"><span class="flex shrink-0"><span v-for="(heroEntry, i) in legendHeroes" :key="i" :class="['rounded-full ring-2 ring-surface-4', { '-ml-2.5': i > 0 }]"><hero-image-wrapper size="small" :hero="heroEntry.hero" :excludehover="true"></hero-image-wrapper></span></span>{{ $t("Each player's three most played heroes.") }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Which team is ahead on average MMR -->
    <div v-if="mmrSplit" class="rounded-panel bg-surface px-5 md:px-8 py-5 flex flex-col gap-3">
      <div class="flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2.5">
          <rank-tier-pill :small="true" :tier="data[0]['average_' + mode + '_rank']"></rank-tier-pill>
          <div class="flex flex-col leading-snug">
            <span class="text-sm font-medium text-subtext">{{ $t('Team 1') }} · {{ $t('Avg. :modeshort MMR', { modeshort: modeShort }) }}</span>
            <span class="text-2xl font-bold">{{ mmrSplit.teamOne }}</span>
          </div>
        </div>
        <span :class="['mx-auto order-last md:order-none max-md:w-full max-md:text-center rounded-lg px-3.5 py-1.5 font-semibold', mmrSplit.leader === 0 ? 'bg-teal/20' : mmrSplit.leader === 1 ? 'bg-loss/20' : 'bg-surface-3']">{{ mmrSplit.lead }}</span>
        <div class="flex items-center gap-2.5 ml-auto md:ml-0">
          <div class="flex flex-col items-end leading-snug">
            <span class="text-sm font-medium text-subtext">{{ $t('Team 2') }} · {{ $t('Avg. :modeshort MMR', { modeshort: modeShort }) }}</span>
            <span class="text-2xl font-bold">{{ mmrSplit.teamTwo }}</span>
          </div>
          <rank-tier-pill :small="true" :tier="data[1]['average_' + mode + '_rank']"></rank-tier-pill>
        </div>
      </div>
      <div class="flex gap-1">
        <div class="h-2.5 rounded-full bg-teal" :style="{ width: mmrSplit.percent + '%' }"></div>
        <div class="h-2.5 rounded-full bg-loss flex-1"></div>
      </div>
    </div>

    <div class="grid xl:grid-cols-2 gap-6 items-start">
      <div v-for="team in [0, 1]" :key="team" class="flex flex-col gap-6 min-w-0">
        <div class="rounded-panel bg-surface overflow-hidden">
          <div :class="['flex items-center gap-3 px-6 py-4', team === 0 ? 'bg-teal/20' : 'bg-loss/20']">
            <span :class="['w-3 h-3 rounded-full', team === 0 ? 'bg-teal' : 'bg-loss']"></span>
            <h2 class="text-2xl font-bold">{{ $t('Team :team', { team: team + 1 }) }}</h2>
          </div>

          <template v-if="data[team]">
            <div class="grid sm:grid-cols-3 gap-3 px-6 pt-5 pb-3">
              <stat-box class="!m-0 !min-w-0" :title="$t('Avg. :modeshort HP MMR', { modeshort: modeShort })" :value="data[team]['average_' + mode + '_mmr'] ?? '—'" :value-tier="data[team]['average_' + mode + '_mmr'] === null ? null : data[team]['average_' + mode + '_rank']"></stat-box>
              <stat-box class="!m-0 !min-w-0" :title="$t('Avg. Account Level')" :value="data[team].average_account_level"></stat-box>
              <stat-box class="!m-0 !min-w-0" :title="$t('Best :modeshort HP Rank', { modeshort: modeShort })" :value="data[team]['highest_' + mode + '_mmr_battletag'] || '—'"></stat-box>
            </div>

            <div class="flex gap-4 px-6 pt-3 pb-2 text-[0.6875rem] font-bold uppercase tracking-wider text-subtext max-md:hidden">
              <span class="flex-1">{{ $t('Player') }}</span>
              <span class="w-[12.5rem]">{{ $t('Last 10 games') }}</span>
              <span class="w-[13.75rem]">{{ $t(':modeshort HP MMR', { modeshort: modeShort }) }}</span>
              <span class="w-4"></span>
            </div>

            <prematch-player-row
              v-for="player in partyOrder(team)"
              :key="slot(team, player)"
              :player="player"
              :mode="mode"
              :modelabel="modeLabel"
              :expanded="expanded === team + '-' + slot(team, player)"
              :recentloading="recentLoading"
              @toggle="toggle(team + '-' + slot(team, player))"
            ></prematch-player-row>
          </template>
        </div>

      </div>
    </div>
  </div>
  <!-- The load failed: the error page's card in place of the data -->
  <div v-else-if="loadError" class="w-full max-w-[120rem] mx-auto px-4 md:px-8 pt-10 pb-10">
    <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
  </div>
  <prematch-skeleton v-else-if="isLoading"></prematch-skeleton>
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
      loadError: null,
      isLoading: false,
      recentLoading: false,
      data: null,
      mode: 'qm',
      // Set once the viewer picks a mode themselves; the game's own mode no longer overrides it.
      modePicked: false,
      modePollTimer: null,
      modePollsLeft: 60,
      expanded: null,
      legendOpen: false,
      modes: [
        { key: 'qm', label: 'Quick Match', short: 'QM' },
        { key: 'sl', label: 'Storm League', short: 'SL' },
        { key: 'ar', label: 'ARAM', short: 'ARAM' },
      ],
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
    // Any player's three heroes, for the legend
    legendHeroes() {
      const players = [...(this.data[0]?.players || []), ...(this.data[1]?.players || [])];
      return players.find(player => !player.hidden && player.top_heroes?.length)?.top_heroes.slice(0, 3) || [];
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
        leader: teamOne === teamTwo ? null : (teamOne > teamTwo ? 0 : 1),
        lead: teamOne === teamTwo ? this.$t('Teams are even') : this.$t('Team :team is ahead by :mmr MMR', { team: teamOne > teamTwo ? 1 : 2, mmr: Math.abs(teamOne - teamTwo) }),
        // A 300 MMR gap fills the bar for the leading team.
        percent: 50 + Math.max(-1, Math.min(1, (teamOne - teamTwo) / 300)) * 50,
      };
    },
  },
  methods: {
    async getData(){
      this.loadError = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/prematch", {
          prematchid: this.prematchid,
        });
        this.data = response.data;
      }catch(error){
      this.loadError = this.$loadError(error, () => this.getData());
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
    // Party members next to each other, in order of the first one's slot
    partyOrder(team) {
      const players = this.data[team].players;
      const partyOf = player => player.recent_games?.party;
      const order = [];
      players.forEach(player => {
        if (order.includes(player)) {
          return;
        }
        order.push(...(partyOf(player) ? players.filter(other => partyOf(other) === partyOf(player)) : [player]));
      });
      return order;
    },
    slot(team, player) {
      return this.data[team].players.indexOf(player);
    },
    toggle(key) {
      this.expanded = this.expanded === key ? null : key;
    },
  }
}
</script>
