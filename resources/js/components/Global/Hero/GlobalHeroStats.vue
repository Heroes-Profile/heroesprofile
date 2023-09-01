GlobalHeroStats<template>
  <div>
    <h1>Global Hero Statistics</h1>
    <infobox :input="infoText"></infobox>

    <filters :onFilter="filterData" :filters="filters"></filters>

    <div v-if="this.data">
     <table class="min-w-full bg-white">
        <thead>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Avg
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_win_rate }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ "&#177;" }}{{ this.data.average_confidence_interval }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_positive_win_rate_change }}{{ "|" }}{{ this.data.average_negative_win_rate_change }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_popularity }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_pick_rate }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_ban_rate }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_positive_influence }}{{ "|" }}{{ this.data.average_negative_influence }}
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            {{ this.data.average_games_played }}
          </th>
        </thead>
        <thead>
          <tr>
            <th @click="sortTable('name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate
            </th>
            <th @click="sortTable('confidence_interval')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Confidence
            </th>
            <th @click="sortTable('win_rate_change')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate Change
            </th>
            <th @click="sortTable('popularity')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity
            </th>
            <th @click="sortTable('pick_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Pick Rate
            </th>   
            <th @click="sortTable('ban_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Ban Rate
            </th>    
            <th @click="sortTable('influence')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Influence
            </th>                  
            <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>                                      
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sortedData" :key="row.hero_id">
            <td class="py-2 px-3 border-b border-gray-200">{{ row.name }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ "&#177;" }}{{ row.confidence_interval }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate_change }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.pick_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.ban_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.influence }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalHeroStats',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    }
  },
  data(){
    return {
    	infoText: "Hero win rates based on differing increments, stat types, game type, or Rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'asc',
      data: [],
    }
  },
  created(){
    console.log(this.filters)
  	this.getData();
  },
  mounted() {
  },
  computed: {
    sortedData() {
      if (!this.sortKey) return this.data.data;
      return this.data.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    }
    
  },
  watch: {
  },
  methods: {
  	async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/hero", {
          timeframe: "2.55.3.90670",
          gameType: 5,
        });

        this.data = response.data;
      }catch(error){
      }
    },
    filterData(filteredData){
      //Might be able to remove this later
      console.log("Filtered data = " + filteredData);
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'asc';
      }
      this.sortKey = key;
    }
  }
}
</script>