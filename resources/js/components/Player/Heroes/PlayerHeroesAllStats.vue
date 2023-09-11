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
      :includegametypefull="true"
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
            <th v-if="showGameTypeColumn('qm')" @click="sortTable('qm_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              QM MMR
            </th>   
            <th v-if="showGameTypeColumn('ud')" @click="sortTable('ud_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              UD MMR
            </th>
            <th v-if="showGameTypeColumn('hl')" @click="sortTable('hl_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              HL MMR
            </th>      
            <th v-if="showGameTypeColumn('tl')" @click="sortTable('tl_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              TL MMR
            </th>       
            <th v-if="showGameTypeColumn('sl')" @click="sortTable('sl_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              SL MMR
            </th>    
            <th v-if="showGameTypeColumn('ar')" @click="sortTable('ar_mmr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              AR MMR
            </th>  
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sortedData" :key="row.id">
            <td class="py-2 px-3 border-b border-gray-200"><a :href="getPlayerHeroPageUrl()"><hero-box-small :hero="row"></hero-box-small>{{ row.name }}</a></td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
            <td v-if="showGameTypeColumn('qm')" class="py-2 px-3 border-b border-gray-200">{{ row.qm_mmr }}</td>
            <td v-if="showGameTypeColumn('ud')" class="py-2 px-3 border-b border-gray-200">{{ row.ud_mmr }}</td>
            <td v-if="showGameTypeColumn('hl')" class="py-2 px-3 border-b border-gray-200">{{ row.hl_mmr }}</td>
            <td v-if="showGameTypeColumn('tl')" class="py-2 px-3 border-b border-gray-200">{{ row.tl_mmr }}</td>
            <td v-if="showGameTypeColumn('sl')" class="py-2 px-3 border-b border-gray-200">{{ row.sl_mmr }}</td>
            <td v-if="showGameTypeColumn('ar')" class="py-2 px-3 border-b border-gray-200">{{ row.ar_mmr }}</td>
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
      role: null,
      hero: null,
      minimumgames: 0,
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
          hero: this.hero,
          role: this.role,
          minimumgames: this.minimumgames,
        });
        this.data = response.data;

      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      this.data = [];
      
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
    showGameTypeColumn(game_type){
      return this.gametype.includes(game_type);
    },
    getPlayerHeroPageUrl(){
      return "/Player/Hero/Single/" + this.battletag + "/" + this.blizzid + "/" + this.region;
    }

  }
}
</script>