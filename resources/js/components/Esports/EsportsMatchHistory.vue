<template>
  <div>    
    <page-heading :infoText1="`${esport == 'HeroesInternational' ? 'Heroes International' : esport} ${team} Match History`" :heading="esport == 'HeroesInternational' ? 'Heroes International' : esport" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <table class="mt-10 max-md:text-xs">
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
            <th @click="sortTable('enemy')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Opponent
            </th> 
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a class="link" :href="esport != 'Other' ? `/Esports/${esport}/Match/Single/` + row.replayID : `/Esports/${esport}/${series}/Match/Single/` + row.replayID">{{ row.replayID }}</a>
            </td>
            <td>
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ row.game_map }}
            </td>
            <td>
              {{ row.enemy }}
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
  name: 'EsportsMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    esport: String,
    series: String,
    season: {
      type: [String, Number]
    },
    type: String,
    team: String,
    division: String,
    tournament: String,
    seriesimage: String,

  },
  data(){
    return {
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      data: null,
      gamemap: null,
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

      var url = "";

      var url = "/api/v1/esports/team/match/history";
      
      if(this.series){
        url = "/api/v1/esports/other/team/match/history";
      }


      try{
        const response = await this.$axios.post(url, {
          esport: this.esport,
          series: this.series,
          season: this.modifiedSeason,
          division: this.division,
          tournament: this.tournament,
          pagination_page: page,
          team: this.team,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data;

        console.log(this.data);

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
      }
    },
  }
}
</script>