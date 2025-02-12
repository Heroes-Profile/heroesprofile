<template>
  <div>    
    <page-heading :infoText1="infoText1" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <table class="mt-10">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game ID
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game Date
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
              <a class="link" :href="`/Esports/${esport}/Match/Single/` + row.replayID">{{ row.replayID }}</a>
            </td>
            <td>
              {{ formatDate(row.game_date) }}
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
                <talent-image-wrapper :talent="row.level_one" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_four" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_seven" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_ten" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_thirteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_sixteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_twenty" :size="'small'"></talent-image-wrapper>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="getLoadingImage()"></loading-component>
    </div>
  </div>
</template>

<script>
import moment from 'moment-timezone';

export default {
  name: 'EsportsPlayerMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: String, 
    esport: String,
    season: Number,
    seriesimage: String,
    series: String,
  },
  data(){
    return {
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      data: null,
      gamemap: null,
      season: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedSeason: null,
    }
  },
  created(){
    this.modifiedSeason = this.season;
  },
  mounted() {
    this.getData(1);
  },
  computed: {
    headingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/600-600-ngs_large_header.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
    headingImageUrl(){
      if(this.esport == "NGS"){
        return "/Esports/NGS"
      }else if(this.esport == "CCL"){
        return "/Esports/CCL"
      }else if(this.esport == "MastersClash"){
        return "/Esports/MastersClash"
      }else if(this.esport == "Other"){
        return "/Esports/Other/" + this.series; 
      }
    },
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
    infoText1(){
      if(this.esport == "NGS"){
        return `Match History for ${this.battletag} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `Match History for ${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "MastersClash"){
        return `Match History for ${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "Other"){
        return `Match History for ${this.battletag} in series ${this.series}`;
      }
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

      
      var url = "/api/v1/esports/single/player/match/history";
      
      if(this.series){
        url = "/api/v1/esports/other/single/player/match/history";
      }

      try{
        const response = await this.$axios.post(url, {
          esport: this.esport,
          series: this.series,
          battletag: this.battletag,
          blizz_id: this.blizzid,
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
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
  }
}
</script>