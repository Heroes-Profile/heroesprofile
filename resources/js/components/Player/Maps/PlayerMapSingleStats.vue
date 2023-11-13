<template>
  <div>
    <page-heading :infoText1="'Map data for ' + battletag + ' on ' + map" :heading="battletag +`(`+ regionsmap[region] + `)`"></page-heading>


      <single-select-filter :values="gameTypesWithAll" :text="'Game Type'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="'All'"></single-select-filter>
      <single-select-filter :values="seasonsWithAll" :text="'Season'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="'All'"></single-select-filter>

      <div v-if="data">
        <span>Wins = </span><span>{{ data.wins }}</span><br>
        <span>Losses = </span><span>{{ data.losses }}</span><br>
        <span>Win Rate = </span><span>{{ data.win_rate }}</span><br>
        <span>KDR = </span><span>{{ data.kdr }}</span><br>
        <span>KDA = </span><span>{{ data.kda }}</span><br>
        <span>Takedowns = </span><span>{{ data.sum_takedowns }}</span><br>
        <span>Kills = </span><span>{{ data.sum_kills }}</span><br>
        <span>Assists = </span><span>{{ data.sum_assists }}</span><br>
        <span>Games Played = </span><span>{{ data.games_played }}</span><br>
        <span>Deaths = </span><span>{{ data.sum_deaths }}</span><br>


        <span>QM MMR = </span><span>{{ data.qm_mmr_data ? data.qm_mmr_data.mmr : 0 }}</span><br>
        <span>QM MMR Tier = </span><span>{{ data.qm_mmr_data ? data.qm_mmr_data.rank_tier : "" }}</span><br>

        <span>UD MMR = </span><span>{{ data.ud_mmr_data ? data.ud_mmr_data.mmr : 0 }}</span><br>
        <span>UD MMR Tier = </span><span>{{ data.ud_mmr_data ? data.ud_mmr_data.rank_tier : "" }}</span><br>

        <span>HL MMR = </span><span>{{ data.hl_mmr_data ? data.hl_mmr_data.mmr : 0 }}</span><br>
        <span>HL MMR Tier = </span><span>{{ data.hl_mmr_data ? data.hl_mmr_data.rank_tier : "" }}</span><br>

        <span>TL MMR = </span><span>{{ data.tl_mmr_data ? data.tl_mmr_data.mmr : 0 }}</span><br>
        <span>TL MMR Tier = </span><span>{{ data.tl_mmr_data ? data.tl_mmr_data.rank_tier : "" }}</span><br>

        <span>SL MMR = </span><span>{{ data.sl_mmr_data ? data.sl_mmr_data.mmr : 0 }}</span><br>
        <span>SL MMR Tier = </span><span>{{ data.sl_mmr_data ? data.sl_mmr_data.rank_tier : "" }}</span><br>

        <span>AR MMR = </span><span>{{ data.ar_mmr_data ? data.ar_mmr_data.mmr : 0 }}</span><br>
        <span>AR MMR Tier = </span><span>{{ data.ar_mmr_data ? data.ar_mmr_data.rank_tier : "" }}</span><br>

        <line-chart v-if="seasonWinRateDataArray" :data="seasonWinRateDataArray" :dataAttribute="'win_rate'"></line-chart>

        <h1>Heroes played on {{ map }}</h1>
        <group-box :playerlink="true" :text="'Most Played'" :data="data.hero_data_top_played.slice(0, 3)"></group-box>
        <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.hero_data_top_win_rate.slice(0, 3)"></group-box>
        <group-box :playerlink="true" :text="'Latest Played'" :data="data.hero_data_top_latest_played.slice(0, 3)"></group-box>

        <div class="flex flex-wrap gap-1">
          <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Hero/' + item.hero.name" v-for="(item, index) in data.hero_data_all_heroes">
            <hero-image-wrapper :hero="item.hero">
              <image-hover-box :title="item.hero.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
            </hero-image-wrapper>
          </a>
        </div>


        <div>
          <h1>Party Size Win Rates</h1>
          solo: total games: {{ data.stack_size_one_total }} wins: {{ data.stack_size_one_wins }} losses: {{ data.stack_size_one_losses }} win rate: {{ data.stack_size_one_win_rate }}% <br>

          three-man: total games: {{ data.stack_size_three_total }} wins: {{ data.stack_size_three_wins }} losses: {{ data.stack_size_three_losses }} win rate: {{ data.stack_size_three_win_rate }}% <br>

          four-man: total games: {{ data.stack_size_four_total }} wins: {{ data.stack_size_four_wins }} losses: {{ data.stack_size_four_losses }} win rate: {{ data.stack_size_four_win_rate }}% <br>

          five-man: total games: {{ data.stack_size_five_total }} wins: {{ data.stack_size_five_wins }} losses: {{ data.stack_size_five_losses }} win rate: {{ data.stack_size_five_win_rate }}% <br>

        </div>

        <div v-if="data && data.latestGames">
          <h1>Most Recent matches</h1>

          <template v-for="(item, index) in data.latestGames">
            <div>{{ item.game_map.name }} | {{ item.game_type.name }} | {{ item.game_date }}</div>
            <game-summary-box :data="item"></game-summary-box>
          </template>
        </div>

      </div>
      <div v-else-if="isLoading">
        <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
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
    region: Number,
    regions: Object,
    map: String,
    regionsmap: Object,
  },
  data(){
    return {
      isLoading: false,
      cancelTokenSource: null,
      inputmap: null,
      modifiedgametype: null,
      modifiedseason: null,
      data: null,
    }
  },
  created(){
    this.inputmap = this.map;
  },
  mounted() {
    this.getData();
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
  },
  watch: {
  },
  methods: {
    async getData(type){
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
    handleDropdownClosed(){
      this.data = null;
      this.getData();
    },
  }
}
</script>