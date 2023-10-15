<template>
  <div>
    <page-heading :infoText1="'Match History'" :heading="battletag +`(`+ regionsmap[region] + `)`"></page-heading>

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
      :hideadvancedfilteringbutton="true"
      >
    </filters>

    <div v-if="data">

      Pagination works.  So cool
      <div>
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: !data.prev_page_url }">
            <a class="page-link" @click.prevent="getData(data.current_page - 1)" href="#">
              Previous
            </a>
          </li>
          <li class="page-item" :class="{ disabled: !data.next_page_url }">
            <a class="page-link" @click.prevent="getData(data.current_page + 1)" href="#">
              Next
            </a>
          </li>
        </ul>
      </div>


      <table class="">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game ID
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Date
            </th>            
            <th @click="sortTable('game_type_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Type
            </th>
            <th @click="sortTable('game_map')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Map
            </th>
            <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Hero
            </th>    
            <th @click="sortTable('winner')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Winner
            </th>    
            <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
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
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ row.game_type.name }}
            </td>
            <td>
              {{ row.game_map }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1">
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
import moment from 'moment-timezone';

export default {
  name: 'PlayerMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: String, 
    region: String,
    regionsmap: Object,
  },
  data(){
    return {
      userTimezone: moment.tz.guess(),
      isLoading: false,
      data: null,
      role: null,
      hero: null,
      gamemap: null,
      season: null,
      sortKey: '',
      sortDir: 'desc',
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
    }
  },
  created(){
  },
  mounted() {
    this.getData(1);
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
    },
  },
  watch: {
  },
  methods: {
    async getData(page){
     if (this.isLoading || page < 1 || (this.data && page > this.data.last_page)) {
        return;
      }
    

      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/player/match/history", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          battletag: this.battletag,
          game_type: this.gametype,
          role: this.role,
          hero: this.hero,
          game_map: this.gamemap,
          pagination_page: page,
        });
        this.data = response.data;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    filterData(filteredData){
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.season = filteredData.single.Season ? filteredData.single.Season : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;


      this.data = null;
      this.getData(1);
    },
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
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
  }
}
</script>