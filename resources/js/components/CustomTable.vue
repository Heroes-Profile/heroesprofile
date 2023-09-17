<template>
  <div class="max-w-full  md:px-20 overflow-scroll md:overflow-auto max-w-full h-[50vh] md:h-auto">
    <table >
      <thead>
        <tr>
          <th 
            v-for="column in columns" 
            :key="column.value" 
            :class="['py-2', 'px-3', 'border-b', 'border-gray-200', 'text-left', 'text-sm', 'leading-4', 'text-gray-500', 'tracking-wider', column.sortable ? 'cursor-pointer' : '']"
            @click="column.sortable ? sortTable(column.value) : null"
            >
              {{ column.text }}
          </th>        
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in sortedData" :key="row.rank">
          <td v-for="column in columns" :key="column.value" class="">
            <div v-if="column.value === 'battletag'">
              <a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row[column.value] }}</a>
            </div>
            <div v-else-if="column.value === 'most_played_hero'">
              <div class="flex gap-x-2 items-center">
                <round-box-small :hero="row.most_played_hero"></round-box-small>
                {{ row.hero_build_games_played }}
              </div>
            </div>
            <div v-else>
              {{ row[column.value] }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'CustomTable',
  components: {
  },
  props: {
    columns: Array,
    data: Array,
  },
  data(){
    return {
      sortKey: '',
      sortDir: 'asc',

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
        this.sortDir = 'asc';
      }
      this.sortKey = key;
    },
  }
}
</script>