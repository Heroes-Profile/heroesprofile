<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'NGS'" :heading-image="'/images/NGS/600-600-ngs_large_header.png'" :heading-image-url="'/Esports/NGS'"></page-heading>

    <div class="flex flex-wrap gap-2">
      <single-select-filter :values="filters.ngs_seasons" :text="'Seasons'" @input-changed="handleInputChange" :defaultValue="defaultseason"></single-select-filter>
      <custom-button :disabled="isLoading" @click="filter()" :text="'Filter'" :size="'big'" class="mt-10" :ignoreclick="true"></custom-button>
    </div>

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
            <th @click="sortTable('team_0_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Team 1
            </th>
            <th @click="sortTable('team_1_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Team 2
            </th>
            <th @click="sortTable('round')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Round
            </th>
            <th @click="sortTable('game')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Game
            </th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">
              Heroes
            </th>                      
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a class="link" :href="'/Esports/NGS/Match/Single/' + row.replayID">{{ row.replayID }}</a>
            </td>
            <td>
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ row.game_map.name }}
            </td>
            <td>
              <a class="link" :href="`/Esports/${esport}/Team/${row.team_0_name}?season=${modifiedseason}&division=${division}`" >{{ row.team_0_name }}</a>
            </td>
            <td>
              <a class="link" :href="`/Esports/${esport}/Team/${row.team_1_name}?season=${modifiedseason}&division=${division}`" >{{ row.team_1_name }}</a>
            </td>
            <td>
              {{ row.round }}
            </td>
            <td>
              {{ row.game }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1">
              <div  v-for="(hero, heroIndex) in row.heroes" :key="heroIndex">
                <hero-image-wrapper v-if="hero" :hero="hero.hero"></hero-image-wrapper>
              </div>
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
      seasons: null,
      modifiedseason: null,
      infoText1: `Division ${this.division} Match History for season ${this.modifiedseason}`,
    }
  },
  created(){
  },
  mounted() {
    this.modifiedseason = this.defaultseason;
    this.infoText1 = `Division ${this.division} Match History for season ${this.modifiedseason}`;
    this.getData();
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
    async getData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/esport/division/match/history", {
          division: this.division,
          season: this.modifiedseason,
        });
        this.data = response.data.matches;
        this.seasons = response.data.seasons;
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
    handleInputChange(eventPayload){
      if(eventPayload.field == "Seasons"){
            this.modifiedseason = eventPayload.value;
        }
    },
    filter(){
      let newURL = `/Esports/NGS/Division/${this.division}/Match/History`;
      if (this.modifiedseason) {
        newURL += `?season=${this.modifiedseason}`;
      }
      history.pushState({}, "", newURL);
      this.data = null;
      this.getData();
    },
  }
}
</script>