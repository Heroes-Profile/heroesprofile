<template>
  <div>
    <div v-for="level in talentlevels">
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th :colspan="statfilter ? 5 : 4" class="text-center py-2 px-3 border-b border-gray-200">
              Level {{ level }}
            </th>
          </tr>
        </thead>
        <thead>
          <tr>
            <th @click="sortTable('sort', level)" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Talent
            </th>
            <th @click="sortTable('win_rate', level)" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate
            </th>
            <th @click="sortTable('popularity', level)" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity
            </th>                
            <th @click="sortTable('games_played', level)" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>     
            <th v-if="statfilter" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Avg {{ statfilter.charAt(0).toUpperCase() + statfilter.slice(1) }}
            </th>                            
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in talentdetaildata[level]" :key="row.talentInfo.talent_id">
            <td class="py-2 px-3 border-b border-gray-200">
              <div class="flex items-center">
                <talent-image-wrapper :talent="row.talentInfo"></talent-image-wrapper>
                <span class="ml-left px-3">{{ row.talentInfo.title }}</span>
              </div>
            </td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
            <td v-if="statfilter" class="py-2 px-3 border-b border-gray-200">{{ row.total_filter_type }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalTalentDetailsSection',
  components: {
  },
  props: {
    talentdetaildata: Object,
    statfilter: String,
    talentimages: Array,
  },
  data(){
    return {
      sortOrders: {},
      talentlevels: [1, 4, 7, 10, 13, 16, 20],
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    sortTable(columnKey, level) {
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
    },
  }
}
</script>