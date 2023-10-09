EsportsPlayerHeroStats<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="esport" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <single-select-filter :values="data.seasons" :text="'Seasons'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifiedseason"></single-select-filter>
      <single-select-filter :values="data.divisions" :text="'Divisions'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifieddivision"></single-select-filter>


       <div>
        <stat-box :title="'Wins'" :value="data.wins"></stat-box>
        <stat-box :title="'Losses'" :value="data.losses"></stat-box>
        <stat-bar-box :title="'Win Rate'" :value="data.win_rate"></stat-bar-box>         
        <stat-box :title="'KDR'" :value="data.kdr"></stat-box>
        <stat-box :title="'KDA'" :value="data.kda"></stat-box>
        <stat-box :title="'Takedowns'" :value="data.takedowns"></stat-box>
        <stat-box :title="'Kills'" :value="data.Kills"></stat-box>
        <stat-box :title="'Assists'" :value="data.assists"></stat-box>
        <stat-box :title="'# Games'" :value="data.total_games"></stat-box>
        <stat-box :title="'Deaths'" :value="data.deaths"></stat-box>
        <stat-box :title="'Time spent dead'" :value="data.time_spent_dead"></stat-box>
      </div>

      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Hero Enemies and Allies</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="'Top Heroes Lost Against'" :data="data.heroes_lost_against.slice(0,5)"></group-box>
            <group-box :useinputforhover="true" :text="'Top Heroes Won Against'" :data="data.heroes_won_against.slice(0,5)"></group-box>
          </div>
        </div>
      </div>


      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played (5+ games)'" :data="data.hero_top_three_most_played"></group-box>
            <group-box :text="'Highest Win Rate (5+ games)'" :data="data.hero_top_three_highest_win_rate"></group-box>
            <group-box :text="'Lowest Win Rate (5+ games)'" :data="data.hero_top_three_lowest_win_rate"></group-box>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <hero-image-wrapper v-for="(item, index) in data.heroes" :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
          </hero-image-wrapper>
        </div>
      </div>


      <div class="bg-lighten p-10">

        <div class="p-10 max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>
          <game-summary-box 
            v-for="(item, index) in data.matches" 
            :esport="true" 
            :esport-league="esport"
            :data="item"
          ></game-summary-box>
          <custom-button :href="`/Esports/${esport}/Player/${battletag}/${blizz_id}/Match/History`" class="flex justify-end " text="View Match History"></custom-button>
        </div>
      </div>


    </div>
    <div v-else>
      <loading-component :overrideimage="getLoadingImage()"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsPlayerMapStats',
  components: {
  },
  props: {
  	esport: String,
    division: String, 
    battletag: String,
    blizz_id: {
      type: [Number, String]
    },
    season: {
      type: [Number, String]
    },
    game_map: String,
  },
  data(){
    return {
      data: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedseason: null,
      modifieddivision: null,
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
      }
    },
    headingImageUrl(){
      if(this.esport == "NGS"){
        return "https://www.nexusgamingseries.org/"
      }else if(this.esport == "CCL"){
        return "Heroes of the Storm statistics and comparison for the Community Clash League"
      }
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `${this.team} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `${this.battletag} during season ${this.modifiedseason}`;
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
      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/single/player/map", {
          esport: this.esport,
          division: this.modifieddivision,
          battletag: this.battletag,
          blizz_id: this.blizz_id,
          season: this.modifiedseason,
          game_map: this.game_map,
        });
        this.data = response.data;
      }catch(error){
      //Do something here
      }
      this.loading = false;
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
        return "/images/NGS/600-600-ngs_large_header.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }
    },
  }
}
</script>