<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="''" :heading-image="'/images/EsportOther/' + this.series.icon" :heading-image-url="'/Esports/Other/' + this.series.name"></page-heading>
    
    <div class="flex flex-wrap justify-center gap-1 bg-teal rounded p-1 w-fit max-w-[calc(100%-2rem)] mx-auto mb-6 max-md:text-xs">
      <button type="button" :disabled="isLoading && activeButton !== 'teams'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'teams' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('teams')">Teams</button>

      <button type="button" :disabled="isLoading && activeButton !== 'players'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'players' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('players')">Players</button>

      <button type="button" :disabled="isLoading && activeButton !== 'overallHeroStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallHeroStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallHeroStats')">Overall Hero Stats</button>

      <button type="button" :disabled="isLoading && activeButton !== 'overallTalentStats'" :class="['px-4 py-2 rounded-control text-white', activeButton === 'overallTalentStats' ? 'bg-surface-1' : isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']" @click="setButtonActive('overallTalentStats')">Overall Talent Stats</button>
    </div>


    
    <div v-if="!activeButton">
      <div class="flex p-10 md:p-20 bg-surface rounded max-w-[93.75rem] mx-4 md:mx-auto flex-wrap justify-center">
        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-users" style="font-size: 6.25rem"></i>
          <h3>Teams</h3>
          <custom-button @click="setButtonActive('teams')" :text="'Teams'" :size="'big'" class="mt-10" :active="teamsClicked" :ignoreclick="true"></custom-button>
        </div>


        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-address-card" style="font-size: 6.25rem"></i>
          <h3>Players</h3>
          <custom-button @click="setButtonActive('players')" :text="'Players'" :size="'big'" class="mt-10" :active="playersClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fa-solid fa-chart-bar" style="font-size: 6.25rem"></i>
          <h3>Overall Hero Stats</h3>
          <custom-button @click="setButtonActive('overallHeroStats')" :text="'Overall Hero Stats'" :size="'big'" class="mt-10" :active="overallHeroStatsClicked" :ignoreclick="true"></custom-button>
        </div>

        <div class="text-center md:w-[15%] mb-15 mx-5">
          <i class="fas fa-chart-line" style="font-size: 6.25rem"></i>
          <h3>Overall Talent Stats</h3>
          <custom-button @click="setButtonActive('overallTalentStats')" :text="'Overall Talent Stats'" :size="'big'" class="mt-10" :active="overallTalentStatsClicked" :ignoreclick="true"></custom-button>
        </div>
      </div>

    </div>
    <div v-else>
      <div v-if="activeButton === 'teams'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: seasons },
            { text: 'Regions', values: regions },
            { text: 'Tournaments', values: tournaments },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>

        <div class="flex flex-wrap gap-2 max-w-[93.75rem] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search For Team'" :aria-label="'Search For Team'" v-model="userinput">
        </div>


        <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[25vw]   2xl:mx-auto  " style=" ">
          <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
            <thead>
              <tr>
                <th class="py-2 px-3 text-center text-sm leading-4 cursor-pointer">
                  Team
                </th>               
              </tr>
            </thead>
            <tbody class="text-center">
              <template v-for="(row, index) in filteredTeams">
                <tr>
                  <td>
                    <a class="group/bt inline-flex items-center" :href="'./' + series.name + '/Team/' + row"><span class="group-hover/bt:underline">{{ row }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeButton === 'players'">
        <div class="flex flex-wrap gap-2 max-w-[93.75rem] justify-center mx-auto items-center mb-10">
          <input type="text" class="form-control search-input mr-3" :placeholder="'Search for a player'" :aria-label="'Search for a player'" v-model="userinput" @keyup.enter="filter()">
          <custom-button @click="filter()" :text="'Search'" :size="'medium'" class="bg-teal ml-10 hover:bg-lteal" :ignoreclick="true"></custom-button>

        </div>


        <div v-if="battletagresponse" id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden   2xl:mx-auto  " style=" ">
          <table id="responsive-table" class="responsive-table  relative min-w-[30em]" ref="responsivetable">
            <thead>
              <tr>
                <th class="py-2 px-3 text-center text-sm leading-4 cursor-pointer">
                  Player
                </th>               
              </tr>
            </thead>
            <tbody class="text-center">
              <template v-for="(row, index) in battletagresponse">
                <tr>
                  <td>
                    <a class="group/bt inline-flex items-center" :href="'./' + series.name + '/Player/' + row.battletag + '/' + row.blizz_id"><span class="group-hover/bt:underline">{{ row.battletag }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
       
      </div>
      
      <div v-if="activeButton === 'overallHeroStats'">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Seasons', values: seasons },
            { text: 'Regions', values: regions },
            { text: 'Tournaments', values: tournaments },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
        <esports-hero-stats v-if="heroStatsData" :data="heroStatsData"></esports-hero-stats>
      </div>

      <div v-if="activeButton === 'overallTalentStats'">
        <div v-if="!selectedHero">
          <hero-selection :heroes="heroes"></hero-selection>
        </div>


        <div v-else>
          <div v-if="talentStatsData">
            <filters
              :filters="filters"
              :isLoading="isLoading"
              :onFilter="() => filter()"
              :onChange="handleInputChange"
              :extrafilters="[
                { text: 'Seasons', values: seasons },
                { text: 'Regions', values: regions },
                { text: 'Tournaments', values: tournaments },
              ]"
              :excludetimeframes="true"
              :excludegametype="true"
              :hideadvancedfilteringbutton="true"
            >
            </filters>

            
            <esports-talent-stats :talentdetaildata="talentStatsData.talentData" :talentbuilddata="talentStatsData.buildData" :talentimages="talentimages" :selectedHero="selectedHero"></esports-talent-stats>
          </div>

        </div>

      </div>



    </div>

    <div v-if="isLoading">
      <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <loading-component :compact="true" :overrideimage="'/Esports/Other/' + this.series.name"></loading-component>
        <!-- Teams: a one-column list of names -->
        <div v-if="activeButton === 'teams'" class="max-w-[31.25rem] w-full mx-auto">
          <table-skeleton :bare="true" :rows="12" :columns="['name']"></table-skeleton>
        </div>
        <!-- Players: a one-column list of names -->
        <div v-else-if="activeButton === 'players'" class="max-w-[31.25rem] w-full mx-auto">
          <table-skeleton :bare="true" :rows="12" :columns="['name']"></table-skeleton>
        </div>
        <!-- Overall Hero Stats: hero with name, then win rate, popularity, ban rate and games -->
        <div v-else-if="activeButton === 'overallHeroStats'" class="max-w-[62.5rem] w-full mx-auto">
          <table-skeleton :bare="true" :rows="12" :columns="['avatar', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </div>
        <!-- Overall Talent Stats: a block per talent level, then the builds table -->
        <div v-else-if="activeButton === 'overallTalentStats'" class="flex flex-col items-center">
          <div v-for="level in 3" :key="level" class="w-full bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-20"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="3" :columns="['hero', 'stat', 'stat', 'stat']"></table-skeleton>
          </div>
          <div class="w-full max-w-[50rem] bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-16"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
          </div>
        </div>
        <table-skeleton v-else></table-skeleton>
      </div>
    </div>
  </div>
</template>

<script>
const OTHER_TABS = ['teams', 'players', 'overallHeroStats', 'overallTalentStats'];

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
    urlparameters: [Object, Array],
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
    // ?tab= opens that tab straight away
    const tab = this.urlparameters?.tab;
    if (OTHER_TABS.includes(tab)) {
      this.setButtonActive(tab);
    }
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
      // One tab loads at a time; the others wait until it's done
      if (this.isLoading) {
        return;
      }
      this.activeButton = buttonName;
      // The open tab goes in the URL, so it can be linked to or reloaded
      const url = new URL(window.location.href);
      url.searchParams.set('tab', buttonName);
      history.replaceState(history.state, '', url);
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