<template>
  <div>



    <table class="">
      <thead>
        <tr>
          <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Game ID
          </th>
          <th @click="sortTable('team_0_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Team 1
          </th>
          <th @click="sortTable('team_1_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Team 2
          </th>
          <th>
            Game
          </th>
          <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Game Date
          </th>            
          <th @click="sortTable('game_map')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Game Map
          </th>
          <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Heroes
          </th>                        
        </tr>
      </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a :href="`/Esports/${esport}/Match/Single/${row.replayID}`">{{ row.replayID }}</a>
            </td>
            <td>
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
            <td class="py-2 px-3  flex items-center gap-1">
              <template v-for="(hero, heroIndex) in row.heroes">
                <hero-image-wrapper v-if="hero" :hero="hero" :key="heroIndex"></hero-image-wrapper>
              </template>
            </td>
          </tr>
        </tbody>
    </table>
  </div>
</template>

<script>
import moment from 'moment-timezone';

export default {
  name: 'EsportsRecentMatches',
  components: {
  },
  props: {
    data: Array,
    esport: String,
  },
  data(){
    return {
      userTimezone: moment.tz.guess(),
      sortKey: '',
      sortDir: 'desc',
    }
  },
  created(){
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
  }
}
</script>