<template>
  <div class="text-center">
    <div class="input-group mb-3">
      <input type="text" class="form-control search-input" placeholder="Enter a battletag" aria-label="Enter a battletag" aria-describedby="basic-addon2" v-model="userinput"  @keyup.enter="clickedPlayerSearch
      ">
      <div class="input-group-append">
        <custom-button class="btn btn-outline-secondary" text="Search For Player" :ignoreclick="true" :disabled="isLoading" @click="clickedPlayerSearch">{{ "Search For Player" }}</custom-button>
      </div>
    </div>

    <div v-if="battletagresponse && battletagresponse.length > 1">
        <div 
          class="bg-blue p-4 rounded mb-4 w-[500px] flex flex-col items-center cursor-pointer" 
          v-for="(item, index) in battletagresponse" 
          :key="index" 
          @click="setBattletag(item)"
        >
          <div>{{ item.battletagShort }} ({{ item.regionName }})</div>
          <div>{{ item.latest_game }}</div>
          <div>Games Played: {{ item.totalGamesPlayed }}</div>
          <div v-if="item.latestMap">{{ item.latestMap.name }}</div>
          <div>
            <hero-image-wrapper :hero="item.latestHero"></hero-image-wrapper>
          </div>
        </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'HeroOrLeagueChoiceBox',
    components: {
    },
    props: {
      index: Number,
    },
    data(){
      return {
        isLoading: false,
        userinput: null,
        battletagresponse: null,
        cancelTokenSource: null,
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
    },
    watch: {
    },
    methods: {
      async clickedPlayerSearch(){        
        this.$emit('data-loading');
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

          if(this.battletagresponse.length == 1){
            this.setBattletag(this.battletagresponse[0]);
          }
        }catch(error){
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.$emit('data-loading-finished');
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      setBattletag(item){
        let blizz_id = item.blizz_id;
        let battletag = item.battletag;
        let battletag_short = item.battletagShort;
        let region = item.region;
        let region_name = item.regionName;

        const payload = { blizz_id, battletag, battletag_short, region, region_name };
        this.$emit('onDataReturn', this.index, payload);
      },
    }
  }
</script>