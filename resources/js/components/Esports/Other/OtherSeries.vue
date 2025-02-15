<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="''" :heading-image="'/images/EsportOther/' + this.series.icon" :heading-image-url="'/Esports/Other/' + this.series.name"></page-heading>
    
    <div class="flex flex-1 mx-auto justify-center mb-4 w-full bg-blue">
      <div class="border-r border-white">
        <custom-button @click="setButtonActive('teams')" :text="'Teams'" :size="'big'" class="rounded-none " :color="activeButton === 'teams' ? 'lblue' : ''" :active="teamsClicked" :ignoreclick="true"></custom-button>
      </div>

      <div class="border-r border-white">
        <custom-button @click="setButtonActive('players')" :text="'Players'" :size="'big'" class="rounded-none" :color="activeButton === 'Players' ? 'lblue' : ''" :active="playersClicked" :ignoreclick="true"></custom-button>
      </div>

      <div class="border-r border-white">
        <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="rounded-none" :active="overallHeroStatsClicked" :color="activeButton === 'overallHeroStats' ? 'lblue' : ''" :ignoreclick="true"></custom-button>
      </div>

      <div class="">
        <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="rounded-none" :active="overallTalentStatsClicked" :color="activeButton === 'overallTalentStats' ? 'lblue' : ''" :ignoreclick="true"></custom-button>
      </div>
    </div>


    
    <div v-if="!activeButton">
      <div class="flex   p-20 bg-lighten flex-wrap justify-center">
        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-users" style="font-size: 100px"></i>
          <h3>Teams</h3>
          <custom-button @click="setButtonActive('teams')" :text="'Teams'" :size="'big'" class="mt-10" :active="teamsClicked" :ignoreclick="true"></custom-button>
        </div>


        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-address-card" style="font-size: 100px"></i>
          <h3>Players</h3>
          <custom-button @click="setButtonActive('players')" :text="'Players'" :size="'big'" class="mt-10" :active="playersClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fa-solid fa-chart-bar" style="font-size: 100px"></i>
          <h3>Overall Hero Stats</h3>
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="mt-10" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-chart-line" style="font-size: 100px"></i>
          <h3>Overall Talent Stats</h3>
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="mt-10" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>
      </div>

    </div>
    <div v-else>
      <div v-if="activeButton === 'teams'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="seasons" :text="'Seasons'" @input-changed="handleInputChange"></single-select-filter> 
          <single-select-filter :values="regions" :text="'Regions'" @input-changed="handleInputChange"></single-select-filter> 
          <single-select-filter :values="tournaments" :text="'Tournaments'" @input-changed="handleInputChange"></single-select-filter>
          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>

        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search For Team'" :aria-label="'Search For Team'" v-model="userinput">
        </div>


        <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[25vw]   2xl:mx-auto  " style=" ">
          <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
            <thead>
              <tr>
                <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                  Team
                </th>               
              </tr>
            </thead>
            <tbody>
              <template v-for="(row, index) in filteredTeams">
                <tr>
                  <td>
                    <a class="link" :href="'./' + series.name + '/Team/' + row">{{ row }}</a>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeButton === 'players'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search for a player'" :aria-label="'Search for a player'" v-model="userinput" @keyup.enter="filter()">
          <custom-button @click="filter()" :text="'Search'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2  hover:bg-lteal" :ignoreclick="true"></custom-button>

        </div>


        <div v-if="battletagresponse" id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden   2xl:mx-auto  " style=" ">
          <table id="responsive-table" class="responsive-table  relative min-w-[30em]" ref="responsivetable">
            <thead>
              <tr>
                <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                  Player
                </th>               
              </tr>
            </thead>
            <tbody>
              <template v-for="(row, index) in battletagresponse">
                <tr>
                  <td>
                    <a class="link" :href="'./' + series.name + '/Player/' + row.battletag + '/' + row.blizz_id">{{ row.battletag }}</a>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
       
      </div>
      
      <div v-if="activeButton === 'overallHeroStats'">
        <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
          <single-select-filter :values="seasons" :text="'Seasons'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="regions" :text="'Regions'" @input-changed="handleInputChange"></single-select-filter>
          <single-select-filter :values="tournaments" :text="'Tournaments'" @input-changed="handleInputChange"></single-select-filter>




          <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
        </div>
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
            <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
              <single-select-filter :values="seasons" :text="'Seasons'" @input-changed="handleInputChange"></single-select-filter>
              <single-select-filter :values="regions" :text="'Regions'" @input-changed="handleInputChange"></single-select-filter>
              <single-select-filter :values="tournaments" :text="'Tournaments'" @input-changed="handleInputChange"></single-select-filter>

              <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>
            </div>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>



    </div>

    <div v-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="'/Esports/Other/' + this.series.name"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OtherSeries',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    series: Array,
    seasons: Array,
    regions: Array,
    tournaments: Array,
    heroes: Object,
    talentimages: Object,
  },
  data(){
    return {
      infoText1: "Heroes of the Storm statistics and comparison for " + this.series.name,
      activeButton: null,
      teamsData: null,
      isLoading: false,
      season: null,
      region: null,
      tournament: null,
      userinput: '',
      battletagresponse: null,
      heroStatsData: null,
      selectedHero: null,
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    filteredTeams() {
      if(this.teamsData){
        return this.teamsData.filter((team) => {
          return team.toLowerCase().includes(this.userinput.toLowerCase());
        });
      }
      return null;
    },

    teamsClicked() {
      return this.activeButton === 'teams';
    },
    matchesClicked() {
      return this.activeButton === 'matches';
    },
    playersClicked() {
      return this.activeButton === 'players';
    },
    overallHeroStatsClicked() {
      return this.activeButton === 'overallHeroStats';
    },
    overallTalentStatsClicked() {
      return this.activeButton === 'overallTalentStats';
    },
  },
  watch: {
  },
  methods: {
    async getTeamsData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/other/teams", {
          series: this.series.name,
          season: this.season,
          region: this.region,
          tournament: this.tournament,
        }, 
        {
        });
        this.teamsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },

    async searchedPlayer(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      


      try{
        const response = await this.$axios.post(`/api/v1/esports/other/${this.series}/player/search`, {
          userinput: this.userinput,
          series: this.series.name
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.battletagresponse = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },

    async getHeroStats(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post(`/api/v1/esports/other/${this.series.name}/hero/stats`, {
          season: this.season,
          esport: "Other",
          series: this.series.name,
          region: this.region,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.heroStatsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    async getTalentStats(){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post(`/api/v1/esports/other/${this.series.name}/hero/talents/stats`, {
          season: this.season,
          hero: this.selectedHero.name,
          esport: "Other",
          series: this.series.name,
          region: this.region,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.talentStatsData = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },

    setButtonActive(buttonName) {
      this.activeButton = buttonName;
      this.selectedHero = null;
      if(this.activeButton === 'teams'){
        this.teamsData = null;
        this.getTeamsData();

      }else if(this.activeButton === 'recentMatches'){
        this.recentMatchesData = null;
        this.getRecentMatches(1);
      }else if(this.activeButton === 'overallHeroStats'){
        this.heroStatsData = null;
        this.getHeroStats();
      }else if(this.activeButton === 'overallTalentStats' && this.selectedHero){
        this.talentStatsData = null;
        this.getTalentStats();
      }
    },

    handleInputChange(eventPayload) {
      if(eventPayload.field == "Seasons"){
        this.season = eventPayload.value;
      }else if(eventPayload.field == "Regions"){
        this.region = eventPayload.value;
      }else if(eventPayload.field == "Tournaments"){
        this.tournament = eventPayload.value;
      }
    },

    filter(){
      if(this.activeButton === 'teams'){
      this.teamsData = null;
        this.getTeamsData();
      }else if(this.activeButton === 'recentMatches'){
        this.recentMatchesData = null;
        this.getRecentMatches(1);
      }else if(this.activeButton === 'players'){
        this.searchedPlayer();
      }else if(this.activeButton === 'overallHeroStats'){
        this.heroStatsData = null;
        this.getHeroStats();
      }else if(this.activeButton === 'overallTalentStats' && this.selectedHero){
        this.talentStatsData = null;
        this.getTalentStats();
      }
    },
    clickedHero(hero){
      this.selectedHero = hero;
      this.getTalentStats();
    },
  }
}
</script>