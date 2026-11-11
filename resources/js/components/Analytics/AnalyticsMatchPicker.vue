<template>
  <!-- Laid out like the Match History page: pager with the Table / Compact toggle, then the table or the cards.
       A whole row (or card) picks that match. -->
  <div>
    <ul class="pagination flex justify-between mb-2 text-sm">
      <li v-if="page > 1" class="page-item underline underline-offset-4 mr-auto">
        <a class="page-link" @click.prevent="page--" href="#">Previous</a>
      </li>
      <!-- Left of Next, as on Match History -->
      <li class="ml-auto max-md:mx-auto flex items-center gap-4">
        <tab-button :tab1text="'Table'" :tab2text="'Compact'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
        <span v-if="page < pages" class="page-item underline underline-offset-4">
          <a class="page-link" @click.prevent="page++" href="#">Next</a>
        </span>
      </li>
    </ul>

    <div v-if="compact">
      <div class="max-md:px-4 max-w-[90em] ml-auto mr-auto">
        <!-- The card links to the match page; here it picks the match instead -->
        <div v-for="game in shown" :key="game.replayID" class="cursor-pointer" :class="{ 'opacity-60': game.replayID === selected }" @click.capture.prevent.stop="$emit('select', game.replayID)">
          <game-summary-box :data="game"></game-summary-box>
        </div>
      </div>
    </div>

    <div v-else>
      <div ref="tablecontainer" class="w-full">
        <table class="min-w-0 w-full responsive-table relative" ref="responsivetable">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game ID</th>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game Date</th>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game Type</th>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game Map</th>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Hero</th>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Winner</th>
              <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Talents</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in shown"
              :key="row.replayID"
              class="cursor-pointer"
              :class="{ 'font-bold': row.replayID === selected }"
              @click="$emit('select', row.replayID)"
            >
              <td>{{ row.replayID }}</td>
              <td>{{ formatDate(row.game_date) }}</td>
              <td>{{ row.game_type ? row.game_type.name : '' }}</td>
              <td>{{ row.game_map ? row.game_map.name : '' }}</td>
              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
              </td>
              <td>{{ row.winner == 1 ? 'True' : 'False' }}</td>
              <td>
                <div class="flex gap-x-1 mx-2 items-center">
                  <talent-image-wrapper v-if="row.level_one && row.level_one !=0" :talent="row.level_one" :size="'small'"></talent-image-wrapper>
                  <talent-image-wrapper v-if="row.level_four && row.level_four !=0" :talent="row.level_four" :size="'small'"></talent-image-wrapper>
                  <talent-image-wrapper v-if="row.level_seven && row.level_seven !=0" :talent="row.level_seven" :size="'small'"></talent-image-wrapper>
                  <talent-image-wrapper v-if="row.level_ten && row.level_ten !=0" :talent="row.level_ten" :size="'small'"></talent-image-wrapper>
                  <talent-image-wrapper v-if="row.level_thirteen && row.level_thirteen !=0" :talent="row.level_thirteen" :size="'small'"></talent-image-wrapper>
                  <talent-image-wrapper v-if="row.level_sixteen && row.level_sixteen !=0" :talent="row.level_sixteen" :size="'small'"></talent-image-wrapper>
                  <talent-image-wrapper v-if="row.level_twenty && row.level_twenty !=0" :talent="row.level_twenty" :size="'small'"></talent-image-wrapper>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../utils/scaleTable';
import moment from 'moment-timezone';
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
      windowWidth: window.innerWidth,
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
  watch: {
    page() {
      this.$nextTick(() => this.scaleTable());
    },
  },
  mounted() {
    this.$nextTick(() => this.scaleTable());
  },
  methods: {
    // Same as Match History: a cookie for everyone, and the account setting when logged in
    setStyle(side) {
      this.compact = side === 'right';
      Cookies.set('playerhistorytable', this.compact ? '1' : '0', { expires: 365, path: '/' });
      if (this.authenticated) {
        this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.compact }).catch(() => {});
      }
      this.$nextTick(() => this.scaleTable());
    },
    // Narrow screens scale the table to fit, as on Match History
    scaleTable() {
      const responsivetable = this.$refs.responsivetable;
      if (responsivetable && this.windowWidth < 1500) {
        scaleTableToFit(responsivetable, this.$refs.tablecontainer);
      }
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik');
      return originalDate.clone().tz(moment.tz.guess()).format('MM/DD/YYYY h:mm:ss a');
    },
  },
};
</script>
