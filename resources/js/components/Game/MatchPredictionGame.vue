<template>
  <div>
    <div class="flex justify-center max-w-[1500px] mx-auto">
     

          <!-- All Game Type Single -->
          <single-select-filter
            :values="gametypes" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></single-select-filter>


      <button :disabled="disableFilterInput" @click="applyFilter"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
          Start
      </button>
    </div>

    <div v-if="isLoading">
      <loading-component></loading-component>
    </div>
    <div v-if="data">


      <div v-if="!disableWinnerSelect">
        <button :disabled="disableWinnerSelect" @click="chooseWinner(0)"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
          {{ "Team 1 Winner" }}
        </button>

        <button :disabled="disableWinnerSelect" @click="chooseWinner(1)"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
          {{ "Team 2 Winner" }}
        </button>
      </div>
      





      <div>
        <group-box :playerlink="true" :text="'Team 1 Picks'" :data="teamZeroPicks" color="teal"></group-box>
      </div>
      <div v-if="data.draftData">
        <group-box :playerlink="true" :text="'Team 1 Bans'" :data="data.draftData[0].bans" color="teal"></group-box>
      </div>




      <div>
        <group-box :playerlink="true" :text="'Team 2 Picks'" :data="teamOnePicks" color="teal"></group-box>
      </div>
      <div v-if="data.draftData">
        <group-box :playerlink="true" :text="'Team 2 Bans'" :data="data.draftData[1].bans" color="teal"></group-box>
      </div>
  


      <div v-if="userchoiceresult">

        {{ userchoiceresult.data }}
        See replay <a target="_blank" class="link" :href="'/Match/Single/' + userchoiceresult.replayID">{{ userchoiceresult.replayID }}</a> for more information

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
  },
  data(){
    return {
      gametype: null,
      isLoading: false,
      data: null,
      fingerprint: null,
      userchoiceresult: null,
      disableWinnerSelect: false,
    }
  },
  created(){
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
      this.getData();
    },
    handleInputChange(eventPayload) {
      this.gametype = eventPayload.value;
    },
    async chooseWinner(team){      
      this.isLoading = true;
      this.disableWinnerSelect = true;
      try{
        const response = await this.$axios.post("/api/v1/match/prediction/game/choose/winner", {
          team: team, 
          fingerprint: this.data.fingerprint, 
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        
        //console.log(response.data);
        this.userchoiceresult = response.data;
        this.isLoading = false;
      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    }
  }
}
</script>