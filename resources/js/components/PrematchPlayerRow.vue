<template>
  <div :class="['border-t border-lighten', { 'opacity-50': lowSample && !expanded }]">
    <div v-if="player.hidden" class="p-3 text-left text-gray-medium italic">Hidden</div>

    <template v-else>
      <div class="flex items-center gap-3 p-2 cursor-pointer hover:bg-lighten" role="button" @click="$emit('toggle')">
        <div class="flex gap-1 shrink-0">
          <template v-for="(heroEntry, i) in player.top_heroes" :key="i">
            <hero-image-wrapper :size="'small'" :hero="heroEntry.hero">
              <image-hover-box :title="heroEntry.hero.name" :paragraph-one="'Games Played: ' + heroEntry.count"></image-hover-box>
            </hero-image-wrapper>
          </template>
        </div>

        <div class="flex-1 min-w-0 text-left">
          <a class="link text-lteal block truncate" :href="profileLink" @click.stop="this.$redirectToProfile(player.battletag, player.blizz_id, player.region, false)">{{ player.battletag }}</a>
          <div class="text-xs text-gray-medium">Lvl {{ player.account_level }}</div>
          <div class="flex gap-[2px] mt-1">
            <span v-for="game in games" :key="game.replayID" :class="['inline-block w-2 h-2 rounded-sm', game.winner ? 'bg-teal' : 'bg-red']"></span>
          </div>
        </div>

        <stat-box class="shrink-0 w-[13em] !min-w-[13em]" :title="gamesPlayed + ' games'" :value="mmr === null ? '—' : mmr + '|' + rank" :color="lowSample ? 'gray-dark' : color"></stat-box>
        <i :class="['fas text-xs text-gray-medium', expanded ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
      </div>

      <div v-if="expanded" class="bg-lighten p-3 text-left">
        <h4 class="text-xs uppercase text-gray-medium mb-2">
          Last {{ games.length }} {{ modelabel }} games<span v-if="games.length"> · {{ wins }}–{{ games.length - wins }}</span>
        </h4>
        <div v-if="!games.length" class="text-gray-medium">No games</div>
        <div v-for="game in games" :key="game.replayID" class="flex items-center gap-2 py-1 border-t border-lighten">
          <hero-image-wrapper v-if="game.hero" :size="'small'" :hero="game.hero"></hero-image-wrapper>
          <span class="flex-1 min-w-0 truncate">{{ game.hero ? game.hero.name : '' }} <span class="text-gray-medium">· {{ game.game_map ? game.game_map.name : '' }}</span></span>
          <span :class="game.winner ? 'text-lteal' : 'text-lred'">{{ game.winner ? 'Win' : 'Loss' }}</span>
          <span class="text-xs text-gray-medium min-w-[9em] text-right whitespace-nowrap">{{ timeAgo(game.game_date) }}</span>
        </div>

        <div class="flex justify-between gap-2 mt-3 text-xs">
          <span class="text-gray-medium">{{ player.last_played ? 'Last played ' + timeAgo(player.last_played) : '' }}</span>
          <a class="link text-lteal" :href="profileLink" @click="this.$redirectToProfile(player.battletag, player.blizz_id, player.region, false)">View Profile</a>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import moment from 'moment-timezone';

export default {
  name: 'PrematchPlayerRow',
  props: {
    player: Object,
    mode: String,
    modelabel: String,
    color: String,
    expanded: Boolean,
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
    wins() {
      return this.games.filter(game => game.winner).length;
    },
    profileLink() {
      return `/Player/${this.player.battletag}/${this.player.blizz_id}/${this.player.region}`;
    },
  },
  methods: {
    timeAgo(dateString) {
      return moment.tz(dateString, 'Atlantic/Reykjavik').fromNow();
    },
  },
}
</script>
