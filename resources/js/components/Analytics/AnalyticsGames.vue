<template>
  <div>
    <p v-if="data.games.length === 0" class="text-center">No processed games match these filters.</p>

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
              :href="'/Analytics/Match/Single/' + game.replayID"
              :text="'Watch Replay'"
              :alt="'Watch this game in the replay viewer'"
              size="small"
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
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Game ID</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Game Date</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Game Type</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Game Map</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Hero</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Winner</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Talents</th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">Replay</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in data.games" :key="row.replayID">
              <td>
                <a class="link" :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a>
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
                {{ row.winner == 1 ? 'True' : 'False' }}
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
                  :href="'/Analytics/Match/Single/' + row.replayID"
                  :text="'Watch'"
                  :alt="'Watch this game in the replay viewer'"
                  size="small"
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

export default {
  name: 'AnalyticsGames',
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
    playermatchtablestyle: {
      type: [String, Boolean, Number],
    },
    authenticated: Boolean,
  },
  data() {
    return {
      windowWidth: window.innerWidth,
      compact: this.playermatchtablestyle == 1,
    };
  },
  mounted() {
    this.$nextTick(() => this.scaleTable());
  },
  methods: {
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
