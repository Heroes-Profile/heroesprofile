<template>
  <div>
    <page-heading :infoText1="'Map data for ' + battletag + ' on ' + map" :heading="'Map Stats: '+  map" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
            <map-image-wrapper :map="mapobject" :size="'big'"></map-image-wrapper>
    </page-heading>


    <div class="flex justify-center max-w-[1500px] mx-auto">
      <single-select-filter :values="gameTypesWithAll" :text="'Game Type'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="!modifiedgametype ? 'All' : modifiedgametype" :disabled="disableFilterInput"></single-select-filter>
      <single-select-filter :values="seasonsWithAll" :text="'Season'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="'All'" :disabled="disableFilterInput"></single-select-filter>
      <button :disabled="disableFilterInput" @click="applyFilter"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
          Filter
      </button>
    </div>

    <takeover-ad :patreon-user="patreonUser"></takeover-ad>

    <div v-if="data">
      <div class="flex md:p-20 gap-10 mx-auto justify-center items-center ">
        <div class="flex-1 flex flex-wrap justify-between max-w-[450px] w-full items-between mt-[1em]">
          <stat-box class="w-[48%]" :title="'Wins'" :value="data.wins.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Losses'" :value="data.losses.toLocaleString('en-US')"></stat-box>
          <stat-bar-box class="w-full mb-5" size="full" :title="'Win Rate'" :value="data.win_rate.toFixed(2)" color="teal"></stat-bar-box>       
          <stat-box class="w-[48%]" :title="'KDR'" :value="data.kdr" color="red"></stat-box>          
          <stat-box class="w-[48%]" :title="'KDA'" :value="data.kda" color="red"></stat-box>                  
        </div>
        <div class="my-auto">
          <round-image :image="`/images/maps/icon/${formatMapName(map)}`" :excludehover="true" size="large"></round-image>
        </div>

        <div class="flex flex-wrap max-w-[450px] text-left w-full items-between h-full justify-center mt-[1em]">
          <stat-box class="w-[48%]" :title="'Takedowns'" :value="data.sum_takedowns.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Kills'" :value="data.sum_kills.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%] mt-4 mb-4" :title="'Assists'" color="teal" :value="data.sum_assists.toLocaleString('en-US')"></stat-box>          
          <stat-box class="w-[48%] mt-4 mb-4" :title="'Deaths'" color="teal" :value="data.sum_deaths.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[100%]" :title="'# Games'" color="red" :value="data.games_played.toLocaleString('en-US')"></stat-box>
        </div>
      </div>

      <div class="bg-lighten p-10">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes played on {{ map }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :playerlink="true" :text="'Most Played'" :data="data.hero_data_top_played.slice(0, 3)" color="blue"></group-box>
            <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.hero_data_top_win_rate.slice(0, 3)" color="teal"></group-box>
            <group-box :playerlink="true" :text="'Latest Played'" :data="data.hero_data_top_latest_played.slice(0, 3)" color="yellow"></group-box>
          </div>
          <div class="flex flex-wrap mx-auto gap-2 pt-5">
            <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" v-for="(item, index) in data.hero_data_all_heroes">
              <hero-image-wrapper :hero="item.hero" size="big">
                <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played" ></image-hover-box>
              </hero-image-wrapper>
            </a>
          </div>
        </div>
      </div>

      <line-chart v-if="seasonWinRateDataArray" class="max-w-[1500px] mx-auto" :data="seasonWinRateDataArray" :dataAttribute="'win_rate'" :title="`${battletag} Win Rate Data Per Season on ${map}`"></line-chart>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="bg-lighten">
        <div class="flex justify-center max-w-[1500px] mx-auto items-center">
          <div class="p-10 max-w-[90em] ml-auto mr-auto">
            <h2 class="text-3xl font-bold py-5">Party Size Win Rates</h2>
            <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
              <div class="flex gap-10 text-s"><span>Solo</span><span>Total Games: {{ (data.stack_size_one_wins + data.stack_size_one_losses).toLocaleString('en-US') }} </span></div>
              <stat-bar-box size="big" :value="data.stack_size_one_win_rate.toFixed(2) "></stat-bar-box>     
            </div>
            <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
              <div class="flex gap-10 text-s"><span>Two Stack</span><span>Total Games: {{ (data.stack_size_two_wins + data.stack_size_two_losses).toLocaleString('en-US') }} </span></div>
              <stat-bar-box size="big" :value="data.stack_size_two_win_rate.toFixed(2) " color="teal"></stat-bar-box>     
            </div>
            <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
              <div class="flex gap-10 text-s"><span>Three Stack</span><span>Total Games: {{ (data.stack_size_three_wins + data.stack_size_three_losses).toLocaleString('en-US') }} </span></div>
              <stat-bar-box size="big" :value="data.stack_size_three_win_rate.toFixed(2) " color="red"></stat-bar-box>     
            </div>
            <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
              <div class="flex gap-10 text-s"><span>Four Stack</span><span>Total Games: {{ (data.stack_size_four_wins + data.stack_size_four_losses).toLocaleString('en-US') }} </span></div>
              <stat-bar-box size="big" :value="data.stack_size_four_win_rate.toFixed(2) " color="yellow"></stat-bar-box>     
            </div>
            <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
              <div class="flex gap-10 text-s"><span>Five Stack</span><span>Total Games: {{ (data.stack_size_five_wins + data.stack_size_five_losses).toLocaleString('en-US') }} </span></div>
              <stat-bar-box size="big" :value="data.stack_size_five_win_rate.toFixed(2) "></stat-bar-box>     
            </div>
          </div>
          <dynamic-square-ad :patreon-user="patreonUser" :index="1"></dynamic-square-ad>
        </div>
      </div>

        <div class="">
          <div class="p-10 max-w-[90em] ml-auto mr-auto" v-if="data && data.latestGames">
            <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>
            <game-summary-box v-for="(item, index) in data.latestGames" :data="item"></game-summary-box>
            <div class="max-w-[1500px] mx-auto text-right my-2">
              <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Match/History/?game_map=' + map" class="" text="View Match History"></custom-button>
            </div>
          </div>
        </div>

      </div>
      <div v-else-if="typeof data === 'undefined'"  class="flex items-center justify-center">
        No data 
      </div>
      <div v-else-if="isLoading">
        <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
      </div>

  </div>
</template>

<script>
export default {
  name: 'PlayerHeroSingleStats',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    playerloadsetting: {
      type: [String, Boolean]
    },
    region: Number,
    regions: Object,
    map: String,
    regionsmap: Object,
    mapobject: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    gametypedefault: Array,

  },
  data(){
    return {
      isLoading: false,
      cancelTokenSource: null,
      inputmap: null,
      modifiedgametype: null,
      modifiedseason: null,
      data: null,
      disableFilterInput: null,
    }
  },
  created(){
    this.inputmap = this.map;
    if(this.gametypedefault && this.gametypedefault.length > 0){
      this.modifiedgametype = this.gametypedefault[0];
    }
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData();
    }
  },
  computed: {
    seasonWinRateDataArray() {
      return this.data && this.data.season_win_rate_data ? Object.values(this.data.season_win_rate_data) : null;
    },
    gameTypesWithAll() {
      const newValue = { code: 'All', name: 'All' };
      const updatedList = [...this.filters.game_types_full];
      updatedList.unshift(newValue);
      return updatedList;
    },
    seasonsWithAll() {
      const newValue = { code: 'All', name: 'All' };
      const updatedList = [...this.filters.seasons];
      updatedList.unshift(newValue);
      return updatedList;
    },
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
        return true;
      }
      return false;
    },
  },
  watch: {
  },
  methods: {
    async getData(){
      this.disableFilterInput = true;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/player/maps/single", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          region: this.region,
          game_type: this.modifiedgametype,
          season: this.modifiedseason,
          type: "single",
          page: "map",
          game_map: this.map,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        this.data = response.data[0];
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.disableFilterInput = false;
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    getRegionName(regionID){
      return this.regions[regionID];
    },
    handleInputChange(eventPayload) {
      if(eventPayload.field == "Game Type"){
        if(eventPayload.value == "All"){
          this.modifiedgametype = null;
        }else{
          this.modifiedgametype = eventPayload.value;
        }
      }

      if(eventPayload.field == "Season"){
        if(eventPayload.value == "All"){
          this.modifiedseason = null;
        }else{
          this.modifiedseason = eventPayload.value;
        }
      }
    },
    applyFilter(){
      if(!this.isLoading){
        this.data = null;
        this.getData();
      }
    },
    formatMapName(map) {
      return `bg_${map.toLowerCase().replace(/\s+/g, '_')}.jpg`;
    },
  }
}
</script>