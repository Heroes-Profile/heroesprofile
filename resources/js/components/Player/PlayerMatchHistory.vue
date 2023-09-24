<template>
  <div>
    Match History for
    <span><a :href="`/Player/${battletag}/${blizzid}/${region}`" target="_blank">{{ battletag }}</a></span>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :minimumgamesdefault="'0'"
      :includehero="true"
      :includerole="true"
      :includegametypefull="true"
      :includeseason="true"
      :includegamemap="true"
      :includegamedate="true"
      >
    </filters>

    <div v-if="data">
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game ID
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Date
            </th>            
            <th @click="sortTable('game_type_id')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Type
            </th>
            <th @click="sortTable('game_map')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Map
            </th>
            <th @click="sortTable('hero_id')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>    
            <th @click="sortTable('winner')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Winner
            </th>    
            <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Talents
            </th>                   
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a>
            </td>
            <td>
              {{ row.game_date }}
            </td>
            <td>
              {{ row.game_type.name }}
            </td>
            <td>
              {{ row.game_map }}
            </td>
            <td class="py-2 px-3 border-b border-gray-200 flex items-center gap-1">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}
            </td>
            <td>
              {{ row.winner }}
            </td>
            <td>

              <div class="flex gap-x-1 mx-2 items-center">
                <talent-image-wrapper v-if="row.level_one" :talent="row.level_one" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_four" :talent="row.level_four" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_seven" :talent="row.level_seven" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_ten" :talent="row.level_ten" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_thirteen" :talent="row.level_thirteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_sixteen" :talent="row.level_sixteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_twenty" :talent="row.level_twenty" :size="'small'"></talent-image-wrapper>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <loading-component></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: String, 
    region: String,
    gametypedefault: Array,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      dataType: 'Table',
      sortKey: '',
      sortDir: 'desc',
      gametype: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
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
    async getData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/player/match/history", {
          blizz_id: this.blizzid,
          region: this.region,
          battletag: this.battletag,
          game_type: this.gametype,
        });
        this.data = response.data;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    filterData(filteredData){
      /*
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      */
      this.getData();
    },
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