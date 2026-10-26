<template>
  <div>
    <p v-if="data.games.length === 0" class="text-center">No season games match these filters.</p>

    <div v-if="data.games.length" class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Games'" :value="data.games.length.toLocaleString('en-US')" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Wins - Losses'" :value="wins + ' - ' + (data.games.length - wins)" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Win Rate'" :value="share(wins, data.games.length)" color="teal"></stat-box>
    </div>

    <!-- Same place and size as the Talent Builder's layout toggle -->
    <div v-else class="flex justify-end max-w-[1500px] mx-auto mb-2">
      <span class="flex items-center gap-4">
        <tab-button :tab1text="'Table'" :tab2text="'Compact'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
      </span>
    </div>

    <!-- Same cards and table as Player Match History, chosen by the same setting and toggle -->
    <div v-if="data.games.length && compact">
      <div class="p-10 max-md:px-4 max-w-[90em] ml-auto mr-auto">
        <div v-for="game in data.games" :key="game.replayID">
          <game-summary-box :data="game"></game-summary-box>
          <div class="flex justify-end mt-1">
            <custom-button
              v-if="isProcessed(game) && replayLink(game.replayID)"
              :href="replayLink(game.replayID)"
              :text="'Watch Replay'"
              :alt="'Watch this game in the replay viewer'"
              size="small"
            ></custom-button>
            <custom-button
              v-else-if="!sample"
              :text="processButtonText(game)"
              :alt="'Process this game'"
              size="small"
              color="teal"
              :loading="isQueued(game)"
              :disabled="isQueued(game)"
              :ignoreclick="true"
              @click="process(game)"
            ></custom-button>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="data.games.length">
      <div id="table-container" ref="tablecontainer" class="w-auto   w-[100vw]   2xl:mx-auto  " style=" ">
        <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
          <thead>
            <tr>
              <th>Game ID</th>
              <th>Game Date</th>
              <th>Game Type</th>
              <th>Game Map</th>
              <th>Hero</th>
              <th>Result</th>
              <th>Player MMR</th>
              <th>Talents</th>
              <th>Replay</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in data.games" :key="row.replayID">
              <td>
                <a v-if="replayLink(row.replayID)" class="link" :href="replayLink(row.replayID)">{{ row.replayID }}</a>
                <template v-else>{{ row.replayID }}</template>
              </td>
              <td>
                {{ formatDate(row.game_date) }}
              </td>
              <td>
                {{ row.game_type ? row.game_type.name : '' }}
              </td>
              <td>
                {{ row.game_map ? row.game_map.name : '' }}
              </td>
              <td class="py-2 px-3  flex items-center gap-1">
                <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
              </td>
              <td>
                <span :class="row.winner == 1 ? 'text-teal' : 'text-red'">{{ row.winner == 1 ? 'Win' : 'Loss' }}</span>
              </td>
              <td>
                {{ row.player_mmr.toLocaleString('en-US') }}
                <span v-if="row.player_change !== null" class="text-sm" :class="row.player_change >= 0 ? 'text-teal' : 'text-red'">({{ (row.player_change >= 0 ? '+' : '') + Number(row.player_change).toFixed(2) }})</span>
              </td>
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
              <td>
                <custom-button
                  v-if="isProcessed(row) && replayLink(row.replayID)"
                  :href="replayLink(row.replayID)"
                  :text="'Watch'"
                  :alt="'Watch this game in the replay viewer'"
                  size="small"
                ></custom-button>
                <custom-button
                  v-else-if="!sample"
                  :text="processButtonText(row)"
                  :alt="'Process this game'"
                  size="small"
                  color="teal"
                  :loading="isQueued(row)"
                  :disabled="isQueued(row)"
                  :ignoreclick="true"
                  @click="process(row)"
                ></custom-button>
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
import analytics from '../../utils/analyticsFormat.js';

export default {
  name: 'AnalyticsGames',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
    playermatchtablestyle: {
      type: [String, Boolean, Number],
    },
    authenticated: Boolean,
    // The public sample: nothing to process, and only its one game has a replay
    sample: Boolean,
    replayLink: { type: Function, default: replayId => '/Analytics/Match/Single/' + replayId },
  },
  data() {
    return {
      // replayID: 'queued' while a Process request runs, 'ready' once it's done, 'error' if it failed
      processState: {},
      processTimer: null,
      windowWidth: window.innerWidth,
      compact: this.playermatchtablestyle == 1,
    };
  },
  computed: {
    wins() {
      return this.data.games.filter(g => g.winner == 1).length;
    },
  },
  mounted() {
    this.$nextTick(() => this.scaleTable());
  },
  beforeUnmount() {
    clearInterval(this.processTimer);
  },
  methods: {
    isProcessed(game) {
      return game.processed || this.processState[game.replayID] === 'ready';
    },
    isQueued(game) {
      return this.processState[game.replayID] === 'queued';
    },
    processButtonText(game) {
      const state = this.processState[game.replayID];
      return state === 'queued' ? 'Processing' : state === 'error' ? 'Try again' : 'Process';
    },
    async process(game) {
      this.processState = { ...this.processState, [game.replayID]: 'queued' };
      try {
        await this.$axios.post('/Analytics/Match/Single/' + game.replayID + '/Process');
        this.pollProcessing();
      } catch (e) {
        this.processState = { ...this.processState, [game.replayID]: 'error' };
      }
    },
    // One timer for every game being processed; stops when none are left
    pollProcessing() {
      if (this.processTimer) return;
      this.processTimer = setInterval(async () => {
        const queued = Object.keys(this.processState).filter(id => this.processState[id] === 'queued');
        if (!queued.length) {
          clearInterval(this.processTimer);
          this.processTimer = null;
          return;
        }
        for (const id of queued) {
          try {
            const response = await this.$axios.get('/Analytics/Match/Single/' + id + '/Status');
            if (response.data.ready) {
              this.processState = { ...this.processState, [id]: 'ready' };
            }
          } catch (e) {
            // Keep waiting; the next round tries again
          }
        }
      }, 5000);
    },
    // Like the Talent Builder's layout toggle: a cookie for everyone, and the account setting when logged in
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
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
  },
};
</script>
