<template>
  <div>
    <table class="max-w-[1000px] md:min-w-[1000px] max-md:text-xs">
      <thead>
        <tr>
          <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Hero
          </th>
          <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate %
          </th>
          <th @click="sortTable('popularity')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Popularity %
          </th>
          <th @click="sortTable('ban_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Ban Rate %
          </th>            
          <th @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played
          </th>                       
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in sortedData" :key="index">
          <td class="py-2 px-3 flex items-center  max-md:justify-center gap-1"><hero-image-wrapper :hero="row.hero" :includehover="false"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span></td>
          <td>{{ row.win_rate.toFixed(2) }}</td>
          <td>{{ row.popularity.toFixed(2) }}</td>
          <td>{{ row.ban_rate.toFixed(2) }}</td>
          <td>{{ row.games_played.toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
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