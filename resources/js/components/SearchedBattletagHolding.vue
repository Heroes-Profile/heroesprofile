<template>
  <dynamic-banner-ad :patreon-user="patreonUser" :index="1"></dynamic-banner-ad>
  <div>
    <div class="max-w-[1500px] mx-auto mt-10 w-full" v-if="battletagresponse">
      <div class="flex flex-col items-center justify-center " v-if="battletagresponse.length > 1">
        <h2 class="text-xl mb-4 ">Results</h2>
        <div 
          class="bg-blue hover:bg-lblue p-4 rounded mb-4 w-[500px] flex flex-col items-center cursor-pointer" 
          v-for="(item, index) in battletagresponse" 
          :key="index" 
          @click="redirectToProfile(item.battletag, item.blizz_id, item.region)"
        >
          <div>{{ item.battletagShort }} ({{ item.regionName }})</div>
          <div>{{ item.latest_game }}</div>
          <div>Games Played: {{ item.totalGamesPlayed }}</div>
          <div v-if="item.latestMap">{{ item.latestMap.name }}</div>
          <div v-if="item.latestHero">
            <hero-image-wrapper :hero="item.latestHero"></hero-image-wrapper>
          </div>
        </div>
      </div>

      <div v-else-if="battletagresponse.length == 1">
        Account found, loading Profile...
      </div>

        <div class="flex justify-center items-center flex-col" v-else>
          <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'"></search-component>
        <div class="rounded bg-red text-white p-4 mb-4">No battletag found for {{ userinput }}
          Try Again?
        </div>
      </div>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
    </div>
  </div>
  <dynamic-banner-ad :patreon-user="patreonUser" :index="1"></dynamic-banner-ad>

</template>

<script>

export default {
  name: 'SearchedBattletagHolding',
  components: {
  },
  props: {
    userinput: String,
    type: String,
    patreonUser: Boolean,
  },
  data(){
    return {
      battletagresponse: null,
      cancelTokenSource: null,
      isLoading: true,
    }
  },
  created(){
  },
  mounted() {
    this.getData();
  },
  computed: {
    isBattletagReponseValid(){
      return this.battletagresponse[0] && this.battletagresponse[0].battletag && this.battletagresponse[0].blizz_id !== undefined && this.battletagresponse[0].region !== undefined;
    }
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

      try{
        const response = await this.$axios.post("/api/v1/battletag/search", {
          userinput: this.userinput,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.battletagresponse = response.data;
        if(this.isBattletagReponseValid) {
          if(this.battletagresponse.length == 1){
            this.redirectToProfile(this.battletagresponse[0].battletag, this.battletagresponse[0].blizz_id, this.battletagresponse[0].region);
          }
        } else {
          //Do something here
        }
      }catch(error){
        this.battletagresponse = "Invalid input: '%', '?' and ' ' are invalid inputs";
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
    redirectToProfile(battletag, blizz_id, region) {
      this.isLoading = true;
      this.$redirectToProfile(battletag.split('#')[0], blizz_id, region);
    }
  }
}
</script>