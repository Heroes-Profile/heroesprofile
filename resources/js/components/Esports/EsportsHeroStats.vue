<template>
  <!-- In a card at the page's width, as on the global pages -->
  <div class="max-w-[120rem] mx-auto px-4 md:px-8">
   <panel-box>
    <!-- Shrinks to fit a phone's card instead of running past it -->
    <div ref="tablecontainer" class="overflow-clip">
    <table ref="table" class="min-w-0 w-full rounded-table max-md:text-xs">
      <thead>
        <tr>
          <th @click="sortTable('hero_id')" class="py-2 px-3 text-left text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'hero_id' }">
            {{ $t('Hero') }}<sort-indicator :column="'hero_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('win_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate' }">
            {{ $t('Win Rate %') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('popularity')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'popularity' }">
            {{ $t('Popularity %') }}<sort-indicator :column="'popularity'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('ban_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'ban_rate' }">
            {{ $t('Ban Rate %') }}<sort-indicator :column="'ban_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>            
          <th @click="sortTable('games_played')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played' }">
            {{ $t('Games Played') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>                       
        </tr>
      </thead>
      <tbody class="text-center">
        <tr v-for="(row, index) in sortedData" :key="index">
          <!-- Hero names left-aligned, as on the global tier list -->
          <td class="py-2 px-3"><div class="flex items-center"><hero-image-wrapper :hero="row.hero" :includehover="false"></hero-image-wrapper><span class="hidden md:block ml-2">{{ $heroName(row.hero) }}</span></div></td>
          <td>{{ row.win_rate.toFixed(2) }}</td>
          <td>{{ row.popularity.toFixed(2) }}</td>
          <td>{{ row.ban_rate.toFixed(2) }}</td>
          <td>{{ row.games_played.toLocaleString('en-US') }}</td>
        </tr>
      </tbody>
    </table>
    </div>
   </panel-box>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../utils/scaleTable';
export default {
  name: 'EsportsHeroStats',
  components: {
  },
  props: {
    data: Array,
  },
  data(){
    return {
      sortKey: '',
      sortDir: 'desc',
    }
  },
  created(){
  },
  mounted() {
    scaleTableToFit(this.$refs.table, this.$refs.tablecontainer);
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
  }
}
</script>