<template>
  <div>
    All Heroes
    as played by
    <span><a :href="`/Player/${battletag}/${blizzid}/${region}`" target="_blank">{{ battletag }}</a></span>

    <infobox :input="infoText"></infobox>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :gametypedefault="gametype"
      :minimumgamesdefault="'0'"
      :includehero="true"
      :includerole="true"
      :includegamemap="true"
      :includegametype="true"
      :includeseason="true"
      :includeminimumgames="true"

      >
    </filters>

    <div>
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th @click="sortTable('name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>    
            <th @click="sortTable('win_ratr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate
            </th>
            <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>  
            <th @click="sortTable('qm_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              QM MMR
            </th>   
            <th @click="sortTable('ud_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              UD MMR
            </th>
            <th @click="sortTable('hl_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              HL MMR
            </th>           
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sortedData" :key="row.id">
            <td class="py-2 px-3 border-b border-gray-200"><hero-box-small :hero="row"></hero-box-small>{{ row.name }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.qm_mmr }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.ud_mmr }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.hl_mmr }}</td>
          </tr>
        </tbody>
      </table>
    </div>


  </div>
</template>

<script>
export default {
  name: 'PlayerHeroesAllStats',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    battletag: String,
    blizzid: String, 
    region: String,
  },
  data(){
    return {
      infoText: "Select a hero below to view detailed stats for that hero. Use the search box above to filter the list of heroes. Or scroll down to the advanced section for table view.",
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
      data: null,
      sortKey: '',
      sortDir: 'asc',
    }
  },
  created(){
  },
  mounted() {

    this.getData();
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
    async getData(type){
      try{
        const response = await this.$axios.post("/api/v1/player/heroes/all", {
          blizz_id: this.blizzid,
          region: this.region,
          game_type: this.gametype,
        });
        this.data = response.data;

      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
      //this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;

      this.getData();
    },
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