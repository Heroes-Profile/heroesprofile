<template>
  <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" ">
    <table id="responsive-table" class="responsive-table  relative  " ref="responsivetable">
      <thead>
        <tr>
          <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Match ID<sort-indicator :column="'replayID'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('team_0_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer ">
            Team 1<sort-indicator :column="'team_0_name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('team_1_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Team 2<sort-indicator :column="'team_1_name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th>
            Game
          </th>
          <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Game Date<sort-indicator :column="'game_date'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>            
          <th @click="sortTable('game_map')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Game Map<sort-indicator :column="'game_map'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Heroes<sort-indicator :column="'hero_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>                        
        </tr>
      </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td v-if="tournament">
              <a class="link" :href="`/Esports/${getUrlValue(esport)}/Match/Single/${row.replayID}/?tournament=${tournament}`">{{ row.replayID }}</a>
            </td>
            <td v-else>
              <a class="link" :href="`/Esports/${getUrlValue(esport)}/Match/Single/${row.replayID}`">{{ row.replayID }}</a>
            </td>
            <td class="">
              {{ row.team_0_name ? row.team_0_name : row.team_0_id }}
            </td>
            <td>
              {{ row.team_1_name ? row.team_1_name : row.team_1_id }}
            </td>
            <td>
              Game {{ row.game }} Round {{ row.round }}
            </td>
            <td>
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ row.game_map.name }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1 max-md:flex-wrap max-md:w-[300px] max-md:justify-around">
              <template v-for="(hero, heroIndex) in row.heroes">
                <hero-image-wrapper v-if="hero" :hero="hero" :key="heroIndex" class="max-md:w-[45%]"></hero-image-wrapper>
              </template>
            </td>
          </tr>
        </tbody>
    </table>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../utils/scaleTable';
import moment from 'moment-timezone';

export default {
  name: 'EsportsRecentMatches',
  components: {
  },
  props: {
    data: Array,
    esport: String,
    tournament: String,
  },
  data(){
    return {
      windowWidth: window.innerWidth,
      userTimezone: moment.tz.guess(),
      sortKey: '',
      sortDir: 'desc',
    }
  },
  created(){
    this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
  },
  mounted() {
  },
  computed: {
    sortedData() {
      if (!this.sortKey) return this.data;
      return this.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
  },
  watch: {
  },
  methods: {
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(this.userTimezone);

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
    getUrlValue(esport){
      if(esport == "hi" || esport == "hi_nc"){
        return "HeroesInternational";
      }

      return esport;
    }
  }
}
</script>