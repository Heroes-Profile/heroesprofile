<template>
  <div>
    <page-heading :infoText1="'Role data for ' + battletag + ' on ' + role" :heading="battletag +`(`+ regionsmap[region] + `)`"></page-heading>

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


      <div>
        <h1>Heroes played on {{ role }}</h1>
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


      </div>


      <div>
        <h1>Maps played on {{ role }}</h1>
        <group-box :playerlink="true" :text="'Most Played'" :data="data.map_data_top_played.slice(0, 3)"></group-box>
        <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.map_data_top_win_rate.slice(0, 3)"></group-box>
        <group-box :playerlink="true" :text="'Latest Played'" :data="data.map_data_top_latest_played.slice(0, 3)"></group-box>

        <div class="flex flex-wrap gap-1">
          <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" v-for="(item, index) in data.map_data">
            <map-image-wrapper :map="item.game_map">
              <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
            </map-image-wrapper>
          </a>
        </div>


      </div>

      <div>
        <div v-if="data && data.latestGames">
          <h1>Most Recent matches</h1>

          <template v-for="(item, index) in data.latestGames">
            <div>{{ item.game_map.name }} | {{ item.game_type.name }} | {{ item.game_date }}</div>
            <game-summary-box :data="item"></game-summary-box>
          </template>
        </div>

      </div>
    </div>
    <div v-else>
      <loading-component :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PlayerRoleSingleStats',
    components: {
    },
    props: {
      filters: Object,
      role: String,
      battletag: String,
      blizzid: {
        type: [String, Number]
      },
      region: Number,
      regionsmap: Object
    },
    data(){
      return {
        inputrole: null,
        data: null,
        modifiedgametype: null,
        modifiedseason: null,
      }
    },
    created(){
      this.inputrole = this.hero;
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
        try{
          const response = await this.$axios.post("/api/v1/player/role/single", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            type: "single",
            page: "role",
            role: this.role,
          });

          this.data = response.data[0];
        }catch(error){
          //Do something here
        }
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