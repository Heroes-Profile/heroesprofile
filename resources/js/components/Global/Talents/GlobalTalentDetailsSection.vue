<template>
  <div>
    <div id="table-container" ref="tablecontainer" class="w-auto  max-md:overflow-hidden w-[100vw] max-sm:text-xs  2xl:mx-auto  " style=" ">
    <div id="resizeTable" ref="scaledtables" class="max-md:inline-grid">
    <div v-for="level in talentlevels" ref="resizeTable">
      <table :id="'responsivetable'+level" class="responsive-table  relative max-md:w-full" :ref="'responsivetable'+level">
        <thead>
          <tr>
            <th :colspan="statfilter ? 5 : 4" class="text-center py-2 px-3 ">
              Level {{ level }}
            </th>
          </tr>
        </thead>
        <thead>
          <tr>
            <th @click="sortTable('sort', level)" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Talent
            </th>
            <th @click="sortTable('win_rate', level)" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate %
            </th>
            <th @click="sortTable('popularity', level)" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity %
            </th>                
            <th @click="sortTable('games_played', level)" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>     
            <th v-if="statfilter && statfilter != 'win_rate'" @click="sortTable('total_filter_type', level)" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              {{ statFilterLabel(statfilter) }}
            </th>                            
          </tr>
        </thead>
        <tbody>
          <template v-for="row in talentdetaildata[level]" :key="row.talentInfo.talent_id">
          <!-- Rows with build data open a breakdown underneath -->
          <tr :class="{ 'cursor-pointer': row.build_range }" @click="toggleRow(level, row)">
            <td class="py-2 px-3  w-[35%]">
              <div class="flex items-center">
                <talent-image-wrapper :talent="row.talentInfo"></talent-image-wrapper>
                <span class="ml-left px-3">{{ row.talentInfo.title }}</span>
                <i v-if="row.build_range" :class="['fa-solid fa-chevron-down ml-auto text-xs transition-transform', { 'rotate-180': isExpanded(level, row) }]"></i>
              </div>
            </td>
            <td class="py-2 px-3  ">{{ row.win_rate.toFixed(2) }}</td>
            <td class="py-2 px-3  ">{{ row.popularity.toFixed(2) }}</td>
            <td class="py-2 px-3  ">{{ row.games_played.toLocaleString('en-US') }}</td>
            <td v-if="statfilter && statfilter != 'win_rate'" class="py-2 px-3 ">{{ row.total_filter_type }}</td>
          </tr>
          <tr v-if="isExpanded(level, row)">
            <td :colspan="statfilter && statfilter != 'win_rate' ? 5 : 4" class="py-3 px-3">
              <!-- Zero width so the breakdown wraps to the table instead of widening it -->
              <div class="w-0 min-w-full">
                <div class="flex items-center gap-6 max-md:flex-col max-md:items-stretch">
                  <div class="md:w-1/4 text-center">
                    <div class="text-xs">Worst build</div>
                    <div>{{ row.build_range.worst.win_rate.toFixed(2) }}%</div>
                    <div class="flex flex-wrap gap-1 justify-center mt-1">
                      <talent-image-wrapper v-for="talent in row.build_range.worst.talents" :key="talent.level" :talent="talentInfos[talent.talent_id]" size="small"></talent-image-wrapper>
                    </div>
                  </div>
                  <div class="flex-1 flex flex-col gap-1">
                    <div class="relative h-4 text-xs">
                      <span v-for="tick in scaleTicks(level)" :key="tick" class="absolute -translate-x-1/2 text-gray-medium" :style="{ left: scalePosition(level, tick) }">{{ tick }}%</span>
                    </div>
                    <win-rate-range-bar :worst="row.build_range.worst.win_rate" :best="row.build_range.best.win_rate" :value="row.win_rate" :other="row.build_range.other?.win_rate" :min="scale(level).min" :max="scale(level).max" :highlight="row.talentInfo.talent_id === topTalent(level)"></win-rate-range-bar>
                  </div>
                  <div class="md:w-1/4 text-center">
                    <div class="text-xs">Best build</div>
                    <div>{{ row.build_range.best.win_rate.toFixed(2) }}%</div>
                    <div class="flex flex-wrap gap-1 justify-center mt-1">
                      <talent-image-wrapper v-for="talent in row.build_range.best.talents" :key="talent.level" :talent="talentInfos[talent.talent_id]" size="small"></talent-image-wrapper>
                    </div>
                  </div>
                </div>
                <div class="text-xs mt-2 space-y-1">
                  <p>{{ row.build_range.games_played.toLocaleString('en-US') }} games across {{ row.build_range.combinations }} combinations of {{ pairText(level) }}.</p>
                  <p v-if="row.build_range.low_sample" class="text-yellow">Not enough games: {{ row.build_range.combinations_kept }} of these have {{ row.build_range.min_games }}+ games, so the worst and best build use every combination and may not be reliable.</p>
                  <p v-else>{{ row.build_range.combinations_kept }} have {{ row.build_range.min_games }}+ games and count toward the worst and best build.</p>
                  <template v-if="row.build_range.other">
                    <p>The grey dot is the other {{ row.build_range.combinations - row.build_range.combinations_kept }} combined: {{ row.build_range.other.games_played.toLocaleString('en-US') }} games at {{ row.build_range.other.win_rate.toFixed(2) }}.</p>
                    <p>Each of those has too few games to count on its own.</p>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 pt-1">
                      <div v-for="build in row.build_range.other.builds" :key="build.talents.map(talent => talent.talent_id).join('-')" class="flex items-center gap-2">
                        <talent-image-wrapper v-for="talent in build.talents" :key="talent.level" :talent="talentInfos[talent.talent_id]" size="small"></talent-image-wrapper>
                        <span>{{ build.win_rate.toFixed(2) }}% ({{ build.games_played }} games)</span>
                      </div>
                    </div>
                    <p v-if="otherUnlisted(row.build_range) > 0">and {{ otherUnlisted(row.build_range) }} more</p>
                  </template>
                </div>
              </div>
            </td>
          </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
  </div>
  </div>
</template>

<script>
import { statFilterLabel } from '../../../utils/statFilterLabel';
import { scaleTableToFit } from '../../../utils/scaleTable';

export default {
  name: 'GlobalTalentDetailsSection',
  components: {
  },
  props: {
    esport: String,
    talentdetaildata: Object,
    statfilter: String,
    talentimages: Array,
  },
  data(){
    return {
      windowWidth: window.innerWidth,
      tablewidth: null,
      sortOrders: {},
      talentlevels: [1, 4, 7, 10, 13, 16, 20],
      // Open breakdowns, keyed 'level-talent_id'; any number can be open at once
      expandedRows: {},
      // Matches BUILD_RANGE_PAIRS in GlobalTalentStatsController
      buildPairs: { 1: [4, 7], 4: [1, 7], 7: [1, 4], 10: [1, 4, 7], 13: [10, 16], 16: [13, 20], 20: [1, 4, 7, 10, 13, 16] },
    }
  },
  created(){
   
  },
  mounted() {
    this.$nextTick(() => {
      if (window.matchMedia('(max-width: 767px)').matches) {
        this.tablewidth = scaleTableToFit(this.$refs.scaledtables, this.$refs.tablecontainer);
      }
    });
  },
  computed: {
    // Every talent on the page by id, for the build icons in the breakdown
    talentInfos() {
      const infos = {};
      Object.values(this.talentdetaildata || {}).forEach(rows => (rows || []).forEach(row => {
        infos[row.talentInfo.talent_id] = row.talentInfo;
      }));
      return infos;
    },
  },
  watch: {
  },
  methods: {
    statFilterLabel,
    toggleRow(level, row) {
      if (!row.build_range) {
        return;
      }
      const key = level + '-' + row.talentInfo.talent_id;
      this.expandedRows = { ...this.expandedRows, [key]: !this.expandedRows[key] };
    },
    isExpanded(level, row) {
      return !!row.build_range && !!this.expandedRows[level + '-' + row.talentInfo.talent_id];
    },
    otherUnlisted(range) {
      return range.combinations - range.combinations_kept - range.other.builds.length;
    },
    pairText(level) {
      const pairs = this.buildPairs[level];
      return pairs.length === 6 ? 'the rest of the build' : 'the level ' + pairs.join(', ').replace(/, (\d+)$/, ' and $1') + ' talents';
    },
    // The tier's scale runs to the 5% marks either side of its widest build range, shared by every row
    scale(level) {
      const rows = (this.talentdetaildata[level] || []).filter(row => row.build_range);
      const low = Math.min(...rows.map(row => Math.min(row.build_range.worst.win_rate, row.win_rate, row.build_range.other?.win_rate ?? Infinity)));
      const high = Math.max(...rows.map(row => Math.max(row.build_range.best.win_rate, row.win_rate, row.build_range.other?.win_rate ?? -Infinity)));
      const min = Math.floor(low / 5) * 5;
      return { min, max: Math.max(Math.ceil(high / 5) * 5, min + 5) };
    },
    scaleTicks(level) {
      const { min, max } = this.scale(level);
      const ticks = [];
      for (let tick = min; tick <= max; tick += 5) {
        ticks.push(tick);
      }
      return ticks;
    },
    scalePosition(level, rate) {
      const { min, max } = this.scale(level);
      return `${(rate - min) / (max - min) * 100}%`;
    },
    // The tier's highest win rate gets the teal bar
    topTalent(level) {
      const rows = this.talentdetaildata[level] || [];
      return rows.reduce((top, row) => (!top || row.win_rate > top.win_rate ? row : top), null)?.talentInfo.talent_id;
    },
    sortTable(columnKey, level) {
      if(!this.esport){
        if (!this.sortOrders[level]) {
          this.sortOrders = { ...this.sortOrders, [level]: {} };
        }
        const currentOrder = this.sortOrders[level][columnKey] || 'asc';
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        this.sortOrders[level][columnKey] = newOrder;
        this.talentdetaildata[level].sort((a, b) => {
          if (a[columnKey] < b[columnKey]) return newOrder === 'asc' ? -1 : 1;
          if (a[columnKey] > b[columnKey]) return newOrder === 'asc' ? 1 : -1;
          return 0;
        });
      }

    },
  }
}
</script>