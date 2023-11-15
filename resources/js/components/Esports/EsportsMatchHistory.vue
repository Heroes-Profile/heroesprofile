<template>
  <div>
    <page-heading :infoText1="'Temp'"></page-heading>

    <div v-if="data">
      <table class="">
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
              {{ row.game_map }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="isLoadingImageUrl"></loading-component>
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
    esport: String,
    defaultseason: Number,
    filters: Object,
    division: String,
  },
  data(){
    return {
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',
    }
  },
  created(){
  },
  mounted() {
    if(this.esport == "NGS"){
      this.getNGSDivisionData();
    }
  },
  computed: {
    isLoadingImageUrl(){
     if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }
    },
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
    async getNGSDivisionData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/esport/division/match/history", {
          division: this.division,
          season: this.defaultseason,
        });
        this.data = response.data;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
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