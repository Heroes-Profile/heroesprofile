<template>
  <div>
    <single-select-filter :values="this.filters.role" :text="'Role'" @input-changed="handleInputChange"  :defaultValue="role"></single-select-filter>
    as played by {{ battletag }}({{ getRegionName(region) }})



    <multi-select-filter :values="filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="gametype"></multi-select-filter>
    <single-select-filter :values="filters.seasons" :text="'Season'" @input-changed="handleInputChange"></single-select-filter>


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


      <line-chart v-if="seasonWinRateDataArray" :data="seasonWinRateDataArray"></line-chart>


      <div>
        <h1>Heroes played on {{ role }}</h1>


        <group-box :text="'Most Played'" :data="data.hero_data_top_played.slice(0, 3)"></group-box>
        <group-box :text="'Highest Win Rate'" :data="data.hero_data_top_win_rate.slice(0, 3)"></group-box>

        <div class="flex flex-wrap gap-1">
          <template v-for="(item, index) in data.hero_data_all_heroes">
            <round-box-small :hero="item.hero"></round-box-small>
          </template>
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
      regions: Object,
    },
    data(){
      return {
        inputrole: null,
        gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
        data: null,
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
      }
    },
    watch: {
    },
    methods: {
      async getData(type){
        try{
          const response = await this.$axios.post("/api/v1/player/heroes/single", {
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            type: "single",
            page: "role",
            role: this.role,
          });

          this.data = response.data[0];
        }catch(error){
          console.log(error);
        }
      },
      getRegionName(regionID){
        return this.regions[regionID];
      },
      handleInputChange(eventPayload) {
        if (eventPayload.field === "Heroes") {
          this.inputhero = eventPayload.value;

        //Might have to url encode this...who knows
          history.pushState(null, null, this.heroname);
        }
      },
    }
  }
</script>