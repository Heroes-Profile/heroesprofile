<template>
  <div>
    <single-select-filter :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange" :defaultValue="hero"></single-select-filter>
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

        <div>
          <span><a :href="this.getTalentPageUrl()">View Talent Data</a></span>
        </div>


        <line-chart v-if="seasonWinRateDataArray" :data="seasonWinRateDataArray"></line-chart>

        <div>
          <span>Maps played on {{ heroname }}</span>

          <infobox :input="'Click a map to see more information and stats, or select all maps to view maps regardless of hero.'"></infobox>

          <div class="flex">
            <group-box :data="data.map_data_top_played.slice(0, 3)"></group-box>
            <group-box :data="data.map_data_top_win_rate.slice(0, 3)"></group-box>
            <group-box :data="data.map_data_top_latest_played.slice(0, 3)"></group-box>
          </div>

          <div class="flex">
            <map-image-wrapper v-for="(item, index) in data.map_data" :key="index" :map="item.game_map"></map-image-wrapper>
          </div>
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
      <div v-else>
        <loading-component></loading-component>
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
    hero: Number,
    battletag: String,
    blizzid: {
      type: [String, Number]
    },
    region: Number,
    regions: Object,
  },
  data(){
    return {
      inputhero: null,
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
      data: null,
    }
  },
  created(){
    this.inputhero = this.hero;
  },
  mounted() {
    this.getData();
  },
  computed: {
    heroname(){
      return this.filters.heroes.find(hero => hero.code === this.inputhero).name;
    },
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
          hero: this.hero,
          type: "single",
          page: "hero",
        });

        this.data = response.data[0];
      }catch(error){
        //Do something here
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
    getTalentPageUrl(){
      return "/Player/" + this.battletag + "/" + this.blizzid + "/" + this.region  + "/Talents/" + "/" + this.heroname;
    },
  }
}
</script>