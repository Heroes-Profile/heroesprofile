<template>
  <!-- Laid out like the Match History page: Compact / Expanded toggle and pager, then the rows or the cards.
       A whole row (or card) picks that match. -->
  <div>
    <!-- `compact` means the game cards, labelled Expanded, as on Match History -->
    <div class="flex flex-wrap justify-end items-center gap-4">
      <tab-button :tab1text="'Compact'" :tab2text="'Expanded'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
      <pagination-bar :current="page" :last="pages" @page="setPage"></pagination-bar>
    </div>
    <div class="py-4">
      <panel-box>
        <!-- The rows link to the match page; here they pick the match instead -->
        <div v-if="compact">
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="game in shown" :key="game.replayID" :data="game" class="cursor-pointer" :class="{ 'opacity-60': game.replayID === selected }" @click.capture.prevent.stop="$emit('select', game.replayID)"></game-summary-box>
        </div>
        <!-- Wide rows scroll sideways on narrow screens -->
        <div v-else class="overflow-x-auto">
          <div class="md:min-w-max w-full">
            <game-summary-header :compact="true"></game-summary-header>
            <game-summary-box v-for="game in shown" :key="game.replayID" :compact="true" :data="game" class="cursor-pointer" :class="{ 'opacity-60': game.replayID === selected }" @click.capture.prevent.stop="$emit('select', game.replayID)"></game-summary-box>
          </div>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';

const PER_PAGE = 10;

// The Analytics pages' match picker: the player's processed matches, 10 to a page, shown like Match History
export default {
  name: 'AnalyticsMatchPicker',
  props: {
    games: Array,
    selected: { type: Number, default: null },
    playermatchtablestyle: { type: [String, Boolean, Number] },
    authenticated: Boolean,
  },
  emits: ['select'],
  data() {
    return {
      page: 1,
      compact: this.playermatchtablestyle == 1,
    };
  },
  computed: {
    pages() {
      return Math.ceil(this.games.length / PER_PAGE);
    },
    shown() {
      return this.games.slice((this.page - 1) * PER_PAGE, this.page * PER_PAGE);
    },
  },
  methods: {
    // Same as Match History: a cookie for everyone, and the account setting when logged in
    setStyle(side) {
      this.compact = side === 'right';
      Cookies.set('playerhistorytable', this.compact ? '1' : '0', { expires: 365, path: '/' });
      if (this.authenticated) {
        this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.compact }).catch(() => {});
      }
    },
    setPage(page) {
      if (page >= 1 && page <= this.pages) {
        this.page = page;
      }
    },
  },
};
</script>
