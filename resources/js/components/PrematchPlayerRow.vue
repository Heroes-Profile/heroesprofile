<template>
  <div :class="['border-t border-surface-3', { 'bg-surface-1': expanded }]">
    <div v-if="player.hidden" class="px-6 py-3.5 text-left text-subtext italic">{{ $t('Hidden') }}</div>

    <template v-else>
      <div :class="['flex items-center gap-3 md:gap-4 px-4 md:px-6 py-3.5 cursor-pointer hover:bg-surface-1', { 'opacity-50': lowSample && !expanded }]" role="button" @click="$emit('toggle')">
        <div class="flex shrink-0">
          <span v-for="(heroEntry, i) in player.top_heroes" :key="i" :class="['rounded-full ring-2 ring-surface', { '-ml-2.5 max-sm:hidden': i > 0 }]">
            <hero-image-wrapper size="small" :hero="heroEntry.hero">
              <image-hover-box :title="$heroName(heroEntry.hero)" :paragraph-one="$t('Games Played') + ': ' + heroEntry.count"></image-hover-box>
            </hero-image-wrapper>
          </span>
        </div>

        <div class="flex-1 min-w-0 flex flex-col gap-0.5 text-left">
          <div class="flex items-center gap-2 min-w-0">
            <a class="group/bt inline-flex items-center min-w-0 text-lg font-semibold" :href="profileLink" @click.stop="$redirectToProfile(player.battletag, player.blizz_id, player.region, false)"><span class="truncate group-hover/bt:underline">{{ player.battletag }}</span><basil-icon name="arrow-small" class="shrink-0 text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a>
            <span v-if="party" :class="['ml-auto shrink-0 rounded-md px-1.5 text-[0.625rem] font-bold uppercase', partyClass]">{{ $t('Party') }}</span>
          </div>
          <div class="flex items-center gap-2 min-w-0">
            <span class="text-sm text-subtext truncate">{{ $t('Lvl :level', { level: player.account_level }) }} · {{ $t(':count games', { count: gamesPlayed }) }}</span>
            <span v-if="lowSample" class="shrink-0 rounded-md bg-surface-3 px-1.5 text-[0.625rem] font-bold uppercase text-subtext">{{ $t('Few games') }}</span>
          </div>
          <span class="flex gap-1 mt-1 md:hidden"><span v-for="(square, i) in squares" :key="i" :class="['w-2.5 h-2.5 rounded-[3px]', square, { 'animate-pulse': recentloading }]"></span></span>
        </div>

        <span class="flex gap-1 w-[12.5rem] shrink-0 max-md:hidden"><span v-for="(square, i) in squares" :key="i" :class="['w-3.5 h-3.5 rounded-[3px]', square, { 'animate-pulse': recentloading }]"></span></span>

        <span class="flex items-center gap-3 md:w-[13.75rem] shrink-0">
          <span class="text-xl font-bold">{{ mmr === null ? '—' : mmr }}</span>
          <rank-tier-pill v-if="mmr !== null" :small="true" :tier="rank" class="max-sm:hidden"></rank-tier-pill>
        </span>
        <basil-icon name="arrow-small" :class="['shrink-0 text-subtext', expanded ? '-rotate-90' : 'rotate-90']"></basil-icon>
      </div>

      <div v-if="expanded" class="flex flex-col gap-1.5 px-4 md:px-6 pb-5 text-left">
        <template v-if="recentloading">
          <skeleton-box class="h-3 w-48 mb-1"></skeleton-box>
          <skeleton-box v-for="n in 5" :key="n" class="h-[3.25rem] w-full !rounded-control"></skeleton-box>
        </template>
        <template v-else>
          <div class="flex items-center justify-between gap-3 pb-1">
            <h4 class="text-[0.6875rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Last :games :modelabel games', { games: games.length, modelabel: modelabel }) }}</h4>
            <span v-if="games.length" :class="['text-sm font-semibold', wins >= games.length - wins ? 'text-win' : 'text-loss']">{{ $t(':wins W · :losses L', { wins: wins, losses: games.length - wins }) }}</span>
          </div>
          <div v-if="!games.length" class="text-subtext">{{ $t('No games') }}</div>
        </template>
        <div v-for="game in games" :key="game.replayID" class="flex items-center gap-3 rounded-control bg-surface pl-2.5 pr-3.5 py-1.5">
          <span :class="['self-stretch w-1 rounded-full shrink-0', game.winner ? 'bg-[#4dcc8c]' : 'bg-loss']"></span>
          <hero-image-wrapper v-if="game.hero" size="small" :hero="game.hero" :excludehover="true"></hero-image-wrapper>
          <span class="flex-1 min-w-0 truncate"><span class="font-semibold">{{ game.hero ? $heroName(game.hero) : '' }}</span> <span class="text-subtext">· {{ game.game_map ? $mapName(game.game_map) : '' }}</span></span>
          <span :class="['text-sm font-bold', game.winner ? 'text-win' : 'text-loss']">{{ game.winner ? $t('Win') : $t('Loss') }}</span>
          <span class="w-[6.25rem] shrink-0 text-right text-sm text-subtext whitespace-nowrap max-sm:hidden">{{ timeAgo(game.game_date) }}</span>
        </div>

        <div class="flex items-center justify-between gap-2 pt-1.5 text-sm">
          <span class="text-subtext">{{ player.last_played ? $t('Last played :time', { time: timeAgo(player.last_played) }) : '' }}</span>
          <a class="group/bt inline-flex items-center gap-1.5 font-semibold text-teal" :href="profileLink" @click="$redirectToProfile(player.battletag, player.blizz_id, player.region, false)"><span class="group-hover/bt:underline">{{ $t('View Profile') }}</span><basil-icon name="arrow-small" class="text-xs"></basil-icon></a>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { timeAgo as relativeTime } from '../utils/formatGameDate';

// The party icons' colours, as whole class names so Tailwind sees them
const partyClasses = {
  red: 'bg-[#b22122] text-white',
  blue: 'bg-[#1f5faf] text-white',
  teal: 'bg-[#72e6ef] text-black',
  orange: 'bg-[#af591e] text-white',
  brown: 'bg-[#cc7569] text-white',
  purple: 'bg-[#9b2aed] text-white',
};

export default {
  name: 'PrematchPlayerRow',
  props: {
    player: Object,
    mode: String,
    modelabel: String,
    expanded: Boolean,
    recentloading: Boolean,
  },
  emits: ['toggle'],
  computed: {
    mmr() {
      return this.player[this.mode + '_mmr'];
    },
    rank() {
      return this.player[this.mode + '_rank'];
    },
    gamesPlayed() {
      return this.player[this.mode + '_games_played'] || 0;
    },
    lowSample() {
      return !this.player.hidden && this.gamesPlayed < 25;
    },
    games() {
      return this.player.recent_games ? this.player.recent_games[this.mode] : [];
    },
    party() {
      return this.player.recent_games ? this.player.recent_games.party : null;
    },
    // Ten squares, newest first; grey where there's no game
    squares() {
      const recent = this.games.slice(0, 10).map(game => game.winner ? 'bg-[#4dcc8c]' : 'bg-loss');
      return [...recent, ...Array(10 - recent.length).fill('bg-surface-3')];
    },
    partyClass() {
      return partyClasses[this.party] || 'bg-quest/20 text-quest';
    },
    wins() {
      return this.games.filter(game => game.winner).length;
    },
    profileLink() {
      return `/Player/${this.player.battletag}/${this.player.blizz_id}/${this.player.region}`;
    },
  },
  methods: {
    timeAgo(dateString) {
      return relativeTime(dateString);
    },
  },
}
</script>
