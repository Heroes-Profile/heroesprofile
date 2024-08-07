<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'Match Prediction Game'">
      <template #extraInfo>
        <p class="text-sm max-md:text-sm">
          How good are you at predicting the winner of a game based on the team picks? Select your game mode, then select which team you believe won. Results are based off actual game data. 
        </p>
      </template>
    </page-heading>


    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>



    <div v-if="!user"  class="max-w-[1000px] mx-auto">
      <div class="bg-gray-dark p-4">
        Log in to track your prediction stats and rank on the leaderboard
      </div>
    </div>
    
    <div v-else class="max-w-[1000px] mx-auto">
      
      <div v-if="practicemode" class="bg-red p-4" >PRACTICE MODE ({{ 10 - totalGamesPlayedPractice }} practices left)</div>
      <div v-else>
        <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase"> {{ truncatedBattletag }}</h2>
        <div class="bg-gray-dark p-4">
          <span ><stat-bar-box class="w-full" size="full" :title="'Quick Match Prediction Rate (Out of '+getPredictionGames(1)+' Games)'" :value="getPredictionRate(1)"></stat-bar-box> </span>
          <span ><stat-bar-box color="teal" class="w-full" size="full" :title="'Storm League Prediction Rate (Out of '+getPredictionGames(5)+' Games)'" :value="getPredictionRate(5)"></stat-bar-box> </span>
          <span ><stat-bar-box color="red" class="w-full" size="full" :title="'ARAM Prediction Rate (Out of '+getPredictionGames(6)+' Games)'" :value="getPredictionRate(6)"></stat-bar-box></span>
          </div>
        </div>

    </div>

    <div class="max-w-[1000px] mx-auto">
      <div class="bg-gray-dark p-4">
        Leaderboards can be found at <a class="link" href="/Global/Leaderboard" target="_blank">Leaderboards</a>
      </div>
    </div>


    <div class="flex justify-center max-w-[1500px] mx-auto">
     

          <!-- All Game Type Single -->
          <single-select-filter
            :values="gametypes" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></single-select-filter>


      <button :disabled="disableFilterInput" @click="applyFilter"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
          Next Match
      </button>

    </div>

    <div v-if="isLoading">
      <loading-component></loading-component>
    </div>



    <div v-if="data" class="">

      <div class="w-full max-w-[1000px] bg-blue rounded flex justify-between gap-2 mx-auto p-4 mb-4">
        <span>{{ data.replayData.game_map.name }}</span>
        <span>HP Rank: {{ data.rank }}</span>
        <span>Region: {{ data.replayData.regionString }}</span>
        <span>{{ data.replayData.timeFormat }}</span>
      </div>


      <p class="mx-auto text-center max-w-[1500px] py-2">Who won the match?</p>

      <div class="flex max-w-[1000px] mx-auto justify-center gap-10">

        <div class="flex flex-col align-center border-2 rounded-md border-white " >
          <div class="min-h-[4em] ">
            <button v-if="!disableWinnerSelect" :disabled="disableWinnerSelect" @click="chooseWinner(0)"  :class="{'bg-blue rounded text-white  px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto w-full max-md:mt-10 mx-auto': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
              {{ "Select Team 1 Winner" }}
            </button>
            <div v-else>
              <div class="rounded-md bg-teal w-full text-center px-4 py-2" v-if="userchoiceteam == 0 && userchoiceresult && userchoiceresult.data == 1">CORRECT!</div>
              <div class="rounded-md bg-red w-full text-center px-4 py-2" v-if="userchoiceteam == 0 && userchoiceresult && userchoiceresult.data == 0">WRONG</div>
            </div>
          </div>
          <div class="px-2" v-if="data.draftData">
            <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase">Team 1 Bans</h2>
            <div class="flex justify-center gap-5 p-4 max-md:flex-col">
              <template v-for="(item, index) in data.draftData[0].bans" :key="index">
                <hero-image-wrapper v-if="item.hero" :hero="item.hero" size="big" ></hero-image-wrapper >
              </template>
            </div>
          </div>
          <div class="px-2">
            <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase">Team 1 Picks</h2>
            <div class="flex justify-center gap-5 p-4 max-md:flex-col">
              <template v-for="(item, index) in teamZeroPicks" :key="index">
                <hero-image-wrapper v-if="item.hero" :hero="item.hero" size="big" ></hero-image-wrapper >
              </template>
            </div>
          </div>
        </div>
        
        <div class="flex flex-col align-center justify-center  border-2 rounded-md border-white ">
          <div class="min-h-[4em]">
            <button v-if="!disableWinnerSelect" :disabled="disableWinnerSelect" @click="chooseWinner(1)"  :class="{'bg-blue rounded text-white px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
              {{ "Select Team 2 Winner" }}
            </button>
            <div v-else>
              <div class="rounded-md bg-teal w-full text-center px-4 py-2" v-if="userchoiceteam == 1 && userchoiceresult && userchoiceresult.data == 1">CORRECT!</div>
              <div class="rounded-md bg-red w-full text-center px-4 py-2" v-if="userchoiceteam == 1 && userchoiceresult && userchoiceresult.data == 0">WRONG</div>
            </div>
          </div>
          <div class="px-2" v-if="data.draftData">
            <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase">Team 2 Bans</h2>
            <div class="flex justify-center gap-5 p-4 max-md:flex-col">
            <template v-for="(item, index) in data.draftData[1].bans" :key="index">
              
              <hero-image-wrapper :hero="item.hero" size="big" ></hero-image-wrapper >
            </template>
          </div>
        </div>
        
        <div class="px-2">
          <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase">Team 2 Picks</h2>
          <div class="flex justify-center gap-5 p-4 max-md:flex-col">
            <template v-for="(item, index) in teamOnePicks" :key="index">
              <hero-image-wrapper :hero="item.hero" size="big" ></hero-image-wrapper >
            </template>
          </div>
        </div>
      </div>
    </div>

   
    <div v-if="userchoiceresult" class="max-w-[1000px] text-center w-full mx-auto mt-2 bg-teal rounded-md p-4">
      See replay <a target="_blank" class="link" :href="'/Match/Single/' + userchoiceresult.replayID">{{ userchoiceresult.replayID }}</a> for more information
    </div>

    <div class="p-10  max-w-[1000px] mx-auto">
      <h2 class="text-3xl font-bold py-5">Talents to Level 10</h2>
      <div class="flex flex-wrap gap-20 justify-around">
        <div class="">
          <div class="w-full mb-10" v-for="(item, index) in data.playerData[0]" :key="index">
            <div class="flex items-center gap-5">
              <hero-image-wrapper class="mr-5" :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <div class="flex items-center gap-2 mb-2">
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_one"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_four"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_seven"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_ten"></talent-image-wrapper>
              </div>
            </div>
          </div>
        </div>

        
        <div class="">
          <div class="w-full mb-10" v-for="(item, index) in data.playerData[1]" :key="index">
            <div class="flex items-center gap-5">
              <hero-image-wrapper class="mr-5" :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <div class="flex items-center gap-2 mb-2">
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_one"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_four"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_seven"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talent.level_ten"></talent-image-wrapper>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>

  </div>
  </div>
</template>

<script>
export default {
  name: 'MatchPredictionGame',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    gametypes: Array,
    user: Object,
    predictionstats: Object,
    predictionstatspractice: Object,
    patreonUser: Boolean,
    season: Number,
  },
  data(){
    return {
      gametype: null,
      isLoading: false,
      data: null,
      fingerprint: null,
      userchoiceresult: null,
      disableWinnerSelect: false,
      userchoiceteam: null,
      predictionstatsupdated: null,
      totalGamesPlayedPractice: null,
      practicemode: null,
    }
  },
  created(){
    this.predictionstatsupdated = this.predictionstats;
    this.totalGamesPlayedPractice = this.predictionstatspractice ? this.predictionstatspractice.reduce((total, stat) => total + stat.games_played, 0): 0;
    this.practicemode = this.predictionstatspractice ? this.predictionstatspractice.reduce((total, stat) => total + stat.games_played, 0) >= 10 ? false : true : true;
  },
  mounted() {
  },
  computed: {
    disableFilterInput(){
      if(this.gametype == null || this.gametype == "" || this.isLoading){
        return true;
      }
      return false;
    },
    teamZeroPicks(){
      if (this.data.draftData?.[0]?.picks) {
        return this.data.draftData[0].picks;
      }
      return this.data.playerData[0];
    },
    teamOnePicks(){
      if (this.data.draftData?.[1]?.picks) {
        return this.data.draftData[1].picks;
      }
      return this.data.playerData[1];
    },
    truncatedBattletag(){
      return this.user.battletag.split('#')[0];
    },
  },
  watch: {
  },
  methods: {
    async getData(){
      this.isLoading = true;
      this.data = null;
      try{
        const response = await this.$axios.post("/api/v1/match/prediction/game", {
          gametype: this.gametype, 
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        
        this.data = response.data;

        this.isLoading = false;
      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },
    applyFilter(){
      this.userchoiceresult = null;
      this.disableWinnerSelect = false;
      this.userchoiceteam = null;
      this.getData();
    },
    handleInputChange(eventPayload) {
      this.gametype = eventPayload.value;
    },
    async chooseWinner(team){   
      this.userchoiceteam = team;   
      this.isLoading = true;
      this.disableWinnerSelect = true;
      try{
        const response = await this.$axios.post("/api/v1/match/prediction/game/choose/winner", {
          team: team, 
          fingerprint: this.data.fingerprint, 
          gametype: this.gametype,
          user: this.user,
          practicemode: this.practicemode,
          practicemodegamesplayed: this.totalGamesPlayedPractice,
          season: this.season,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        
        this.userchoiceresult = response.data;

        
        if(this.practicemode){
          this.practicemode = response.data.predictionstats.reduce((total, stat) => total + stat.games_played, 0) >= 10 || response.data.predictionstats.length == 0 ? false : true;
          this.totalGamesPlayedPractice = response.data.predictionstats.reduce((total, stat) => total + stat.games_played, 0);
        }else{
          this.predictionstatsupdated = response.data.predictionstats;
        }
        this.isLoading = false;
      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },
    getPredictionRate(gameType) {
      const stat = this.predictionstatsupdated.find(stat => stat.game_type === gameType);
      return stat ? stat.win_rate.toFixed(2) : 0; 
    },
    getPredictionGames(gameType){
      const stat = this.predictionstatsupdated.find(stat => stat.game_type === gameType);
      return stat ? stat.games_played : 0; 
    },
  }
}
</script>