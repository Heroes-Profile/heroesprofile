<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="esport == 'HeroesInternational' ? 'Heroes International' : esport" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <div class="flex justify-center max-w-[1500px] mx-auto">
        <single-select-filter :values="data.seasons" :text="'Seasons'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifiedseason"></single-select-filter>
        <single-select-filter v-if="esport == 'NGS'" :values="data.divisions" :text="'Divisions'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifieddivision"></single-select-filter>
      </div>
   


      <div class="flex md:p-20 gap-10 mx-auto justify-center items-between ">
        <div class="flex-1 flex flex-wrap justify-between max-w-[400px] w-full items-between mt-[1em]">
          <stat-box class="w-[48%]" :title="'Wins'" :value="data.wins.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Losses'" :value="data.losses.toLocaleString('en-US')"></stat-box>
          <stat-bar-box class="w-full" size="full" :title="'Win Rate'" :value="data.win_rate.toFixed(2)"></stat-bar-box>
          <stat-box class="w-[48%]" :title="'KDR'" :value="data.kdr" color="yellow"></stat-box>          
          <stat-box class="w-[48%]" :title="'KDA'" :value="data.kda" color="yellow"></stat-box>          
        </div>
        <div class="my-auto">
          <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" size="large"></hero-image-wrapper>
        </div>
        <div class="flex-1 flex flex-wrap justify-between max-w-[400px] w-full items-between mt-[1em]">
          <stat-box class="w-[48%]" :title="'Takedowns'" :value="data.takedowns.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Kills'" :value="data.kills.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-full" :title="'Total Time spent dead'" :value="data.time_spent_dead"></stat-box>
          <stat-box class="w-[48%]" :title="'Assists'" :value="data.assists" color="teal"></stat-box>          
          <stat-box class="w-[48%]" :title="'Deaths'" :value="data.deaths" color="teal"></stat-box>          
        </div>
      </div>



      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes played by {{ battletag }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :esport="esport" :playerlink="true" :text="'Most Played'" :data="data.hero_top_three_most_played" color="blue"></group-box>
            <group-box :esport="esport" :playerlink="true" :text="'Highest Win Rate'" :data="data.hero_top_three_highest_win_rate" color="teal"></group-box>
            <group-box :esport="esport" :playerlink="true" :text="'Lowest Win Rate'" :data="data.hero_top_three_lowest_win_rate" color="yellow"></group-box>
          </div>
        </div>



        <div class="flex flex-wrap gap-2 justify-center mt-10 mb-10">
          <a :href="item.link" v-for="(item, index) in data.heroes" >
            <hero-image-wrapper :size="'big'" :hero="item.hero">
              <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
            </hero-image-wrapper>
          </a>
        </div>


        <table class="">
          <thead>
            <tr>
              <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Hero
              </th>
              <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Win Rate %
              </th>            
              <th @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Played
              </th>              
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in sortedData" :key="index">
              <td>
                <a :href="row.link">
                  <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}
                </a>
              </td>
              <td>
                {{ row.win_rate }}
              </td>
              <td>
                {{ row.games_played }}
              </td>
            </tr>
          </tbody>
        </table>




      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>


      <div>
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps played by {{ battletag }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :esport="esport" :playerlink="true" :text="'Most Played'" :data="data.map_top_three_most_played" color="blue"></group-box>
            <group-box :esport="esport" :playerlink="true" :text="'Highest Win Rate'" :data="data.map_top_three_highest_win_rate" color="teal"></group-box>
            <group-box :esport="esport" :playerlink="true" :text="'Lowest Win Rate'" :data="data.map_top_three_lowest_win_rate" color="yellow"></group-box>
          </div>
        </div>
      </div>


      <div class="bg-lighten p-10">

        <div class="p-10 max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>
          <game-summary-box 
            v-for="(item, index) in data.matches" 
            :esport="true" 
            :esport-league="esport"
            :esport-series="series"
            :data="item"
          ></game-summary-box>
          <div class="max-w-[1500px] mx-auto flex justify-end mt-4">
          <custom-button :href="`/Esports/${esport}/Player/${battletag}/${blizz_id}/Match/History`" class="flex justify-end " text="View Match History"></custom-button>
        </div>
        </div>
      </div>


    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="getLoadingImage()"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsPlayerStats',
  components: {
  },
  props: {
  	esport: String,
    series: String,
    division: String, 
    battletag: String,
    blizz_id: {
      type: [Number, String]
    },
    season: {
      type: [Number, String]
    },
    tournament: String,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedseason: null,
      modifieddivision: null,
      cancelTokenSource: null,
      inputHero: {
          name: "Auto Select",
          short_name: "autoselect3",
          icon: "autoselect3.jpg",
      },
    }
  },
  created(){
    this.modifiedseason = this.season;
    this.modifieddivision = this.division;
  },
  mounted() {
    this.getData();
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
    isLoadingImageUrl(){
     if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `${this.battletag} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "Masters Clash"){
        return `${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "Other"){
        return `${this.team} in series ${this.series}`;
      }
    },
    sortedData() {
      if (!this.sortKey) return this.data.heroes;
      return this.data.heroes.slice().sort((a, b) => {
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

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();


      var url = "/api/v1/esports/single/player";
      
      if(this.series){
        url = "/api/v1/esports/other/single/player";
      }

      try{
        const response = await this.$axios.post(url, {
          esport: this.esport,
          series: this.series,
          division: this.modifieddivision,
          battletag: this.battletag,
          blizz_id: this.blizz_id,
          season: this.modifiedseason,
          tournament: this.tournament,
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
    handleInputChange(eventPayload){
        if(eventPayload.field == "Seasons"){
            this.modifiedseason = eventPayload.value;
        }

        if(eventPayload.field == "Divisions"){
          this.modifieddivision = eventPayload.value;
        }

      let newURL = `/Esports/${this.esport}/Player/${this.battletag}/${this.blizz_id}`;
      if (this.modifiedseason && this.modifieddivision) {
        newURL += `?season=${this.modifiedseason}&division=${this.modifieddivision}`;
      } else if (this.modifiedseason) {
        newURL += `?season=${this.modifiedseason}`;
      } else if (this.modifieddivision) {
        newURL += `?division=${this.modifieddivision}`;
      }

      // Update the browser's URL without reloading the page
      history.pushState({}, "", newURL);
    },
    handleDropdownClosed(){
      this.data = null;
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
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }
    },
  }
}
</script>