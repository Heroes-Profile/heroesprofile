<template>
  <div>
    <page-heading  :heading="'Match History'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>

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
    <takeover-ad :patreon-user="patreonUser"></takeover-ad>

    <div v-if="data">
      <div>
        <ul class="pagination flex max-w-[1500px] mx-auto px-2 justify-between mb-2 text-sm">
          <li v-if="data.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !data.prev_page_url }">
            <a class="page-link" @click.prevent="getData(data.current_page - 1)" href="#">
              Previous
            </a>
          </li>
          <li v-if="data.current_page != data.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !data.next_page_url }">
            <a class="page-link" @click.prevent="getData(data.current_page + 1)" href="#">
              Next
            </a>
          </li>
        </ul>
      </div>

      <div id="table-container" ref="tablecontainer" class="w-auto   w-[100vw]   2xl:mx-auto  " style=" ">
      <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
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
              <a class="link" :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a>
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
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
            </td>
            <td>
              {{ row.winner }}
            </td>
            <td>

              <div class="flex gap-x-1 mx-2 items-center">
                <talent-image-wrapper v-if="row.level_one && row.level_one !=0" :talent="row.level_one" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_four && row.level_four !=0" :talent="row.level_four" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_seven && row.level_seven !=0" :talent="row.level_seven" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_ten && row.level_ten !=0" :talent="row.level_ten" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_thirteen && row.level_thirteen !=0" :talent="row.level_thirteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_sixteen && row.level_sixteen !=0" :talent="row.level_sixteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper v-if="row.level_twenty && row.level_twenty !=0" :talent="row.level_twenty" :size="'small'"></talent-image-wrapper>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
      <div>
        <ul class="pagination flex max-w-[1500px] mx-auto px-2 pt-2 justify-between mb-2 text-sm">
          <li v-if="data.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !data.prev_page_url }">
            <a class="page-link" @click.prevent="getData(data.current_page - 1)" href="#">
              Previous
            </a>
          </li>
          <li v-if="data.current_page != data.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !data.next_page_url }">
            <a class="page-link" @click.prevent="getData(data.current_page + 1)" href="#">
              Next
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
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
    playerloadsetting: {
      type: [String, Boolean]
    },
    battletag: String,
    blizzid: String, 
    region: String,
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    gametypedefault: Array,
  },
  data(){
    return {
      windowWidth: window.innerWidth,
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      data: null,
      role: null,
      hero: null,
      gamemap: null,
      season: null,
      sortKey: '',
      sortDir: 'desc',
      gametype: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData(1);
    }
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
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
        return true;
      }
      return false;
    },
  },
  watch: {
  },
  methods: {
    async getData(page){      
     if (this.isLoading || page < 1 || (this.data && page > this.data.last_page)) {
        return;
      }
    
      this.data = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$axios.post("/api/v1/player/match/history", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          game_type: this.gametype,
          role: this.role,
          hero: this.hero,
          game_map: this.gamemap,
          pagination_page: page,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            const newTableWidth = this.windowWidth /responsivetable.clientWidth;
            responsivetable.style.transformOrigin = 'top left';
            responsivetable.style.transform = `scale(${newTableWidth})`;
            const container = this.$refs.tablecontainer;
            container.style.height = (responsivetable.clientHeight * newTableWidth) + 'px';
          }
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
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