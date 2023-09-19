<template>
  <div class="">
  
      <multi-select-filter :values="filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="gametype"></multi-select-filter>
      <single-select-filter :values="filters.seasons" :text="'Season'" @input-changed="handleInputChange"></single-select-filter>
     

      <div v-if="data" class="">

        <div class="flex md:p-20 gap-10">
          <div class="flex-1 flex flex-wrap">
            <stat-box :title="'Wins'" :value="data.wins"></stat-box>
            <stat-box :title="'Losses'" :value="data.losses"></stat-box>
            <stat-box :title="'First to Ten Wins'" :value="data.first_to_ten_wins"></stat-box>
            <stat-box :title="'First to Ten Losses'" :value="data.first_to_ten_losses"></stat-box>
            <stat-bar-box :title="'First to Ten Win Rate'" :value="data.first_to_ten_win_rate"></stat-bar-box>

            <stat-box :title="'Second to Ten Wins'" :value="data.second_to_ten_wins"></stat-box>
            <stat-box :title="'Second to Ten Losses'" :value="data.second_to_ten_losses"></stat-box>
            <stat-bar-box :title="'Second to Ten Win Rate'" :value="data.second_to_ten_win_rate"></stat-bar-box>         
            
            <stat-box :title="'KDR'" :value="data.kdr"></stat-box>          
            <stat-box :title="'KDA'" :value="data.kda"></stat-box>          

            <stat-box :title="'Account Level'" :value="data.account_level"></stat-box>          
          </div>
          <div>player image goes here</div>

          <div class="flex-1 flex flex-wrap ">
            <stat-box :title="'MVP'" :value="data.mvp_rate"></stat-box>       
            <stat-box :title="'Total Time Played'" :value="data.total_time_played"></stat-box>       
            <stat-box :title="'AVG. Time on Fire'" :value="data.average_time_on_fire"></stat-box>       

            <stat-bar-box :title="'Win Rate'" :value="data.win_rate"></stat-bar-box>       
            <stat-bar-box :title="'Bruiser Win Rate'" :value="data.bruiser_win_rate"></stat-bar-box>       
            <stat-bar-box :title="'Support Win Rate'" :value="data.support_win_rate"></stat-bar-box>       
            <stat-bar-box :title="'Ranged Assassin Win Rate'" :value="data.ranged_assassin_win_rate"></stat-bar-box>       
            <stat-bar-box :title="'Melee Assassin Win Rate'" :value="data.melee_assassin_win_rate"></stat-bar-box>       
            <stat-bar-box :title="'Healer Win Rate'" :value="data.healer_win_rate"></stat-bar-box>       
            <stat-bar-box :title="'Tank Win Rate'" :value="data.tank_win_rate"></stat-bar-box>       

          </div>
        </div>

        <div class="bg-lighten p-10 text-center">
          <h2 class="flex-1 text-3xl font-bold"> Heroes </h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played'" :data="data.heroes_three_most_played"></group-box>
            <group-box :text="'Highest Win Rate'" :data="data.heroes_three_highest_win_rate"></group-box>
            <group-box :text="'Latest Played'" :data="data.heroes_three_latest_played"></group-box>
          </div>
        </div>

        <div>
          <div class="flex items-center gap-10 md:px-20 py-5 justify-center" >
            Quick Match
            <stat-bar-box :title="'Win Rate'" :value=" data.qm_mmr_data ? data.qm_mmr_data.win_rate : 0 "></stat-bar-box>
            <stat-box title="Rank Tier" :value="data.qm_mmr_data ? data.qm_mmr_data.rank_tier : ''"></stat-box>
            <stat-box :title="'MMR'" :value="data.qm_mmr_data ? data.qm_mmr_data.mmr : 0 "></stat-box>
          </div>

          <div class="flex items-center gap-10 md:px-20 py-5 justify-center" >
            Unranked Draft
            <stat-bar-box :title="'Win Rate'" :value=" data.ud_mmr_data ? data.ud_mmr_data.win_rate : 0 "></stat-bar-box>
            <stat-box title="Rank Tier" :value="data.ud_mmr_data ? data.ud_mmr_data.rank_tier : ''"></stat-box>
            <stat-box :title="'MMR'" :value="data.ud_mmr_data ? data.ud_mmr_data.mmr : 0 "></stat-box>
          </div>

          <div class="flex items-center gap-10 md:px-20 py-5 justify-center" >
            Hero league
            <stat-bar-box :title="'Win Rate'" :value=" data.hl_mmr_data ? data.hl_mmr_data.win_rate : 0 "></stat-bar-box>
            <stat-box title="Rank Tier" :value="data.hl_mmr_data ? data.hl_mmr_data.rank_tier : ''"></stat-box>
            <stat-box :title="'MMR'" :value="data.hl_mmr_data ? data.hl_mmr_data.mmr : 0 "></stat-box>
          </div>


          <div class="flex items-center gap-10 md:px-20 py-5 justify-center" >
            Team league
            <stat-bar-box :title="'Win Rate'" :value=" data.tl_mmr_data ? data.tl_mmr_data.win_rate : 0 "></stat-bar-box>
            <stat-box title="Rank Tier" :value="data.tl_mmr_data ? data.tl_mmr_data.rank_tier : ''"></stat-box>
            <stat-box :title="'MMR'" :value="data.tl_mmr_data ? data.tl_mmr_data.mmr : 0 "></stat-box>
          </div>

          <div class="flex items-center gap-10 md:px-20 py-5 justify-center" >
            Storm league
            <stat-bar-box :title="'Win Rate'" :value=" data.sl_mmr_data ? data.sl_mmr_data.win_rate : 0 "></stat-bar-box>
            <stat-box title="Rank Tier" :value="data.sl_mmr_data ? data.sl_mmr_data.rank_tier : ''"></stat-box>
            <stat-box :title="'MMR'" :value="data.sl_mmr_data ? data.sl_mmr_data.mmr : 0 "></stat-box>
          </div>

          <div class="flex items-center gap-10 md:px-20 py-5 justify-center" >
            ARAM
            <stat-bar-box :title="'Win Rate'" :value=" data.ar_mmr_data ? data.ar_mmr_data.win_rate : 0 "></stat-bar-box>
            <stat-box title="Rank Tier" :value="data.ar_mmr_data ? data.ar_mmr_data.rank_tier : ''"></stat-box>
            <stat-box :title="'MMR'" :value="data.ar_mmr_data ? data.ar_mmr_data.mmr : 0 "></stat-box>
          </div>
        </div>

      

         <div class="bg-lighten p-10 text-center">
          <h2 class="flex-1 text-3xl font-bold"> Maps </h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played'" :data="data.maps_three_most_played"></group-box>
            <group-box :text="'Highest Win Rate'" :data="data.maps_three_highest_win_rate"></group-box>
            <group-box :text="'Latest Played'" :data="data.maps_three_latest_played"></group-box>
          </div>
        </div>


        <div>
          <h2>Party Size Win Rates</h2>
          solo: total games: {{ data.stack_one_total }} wins: {{ data.stack_one_wins }} losses: {{ data.stack_one_losses }} win rate: {{ data.stack_one_win_rate }}% <br>

          three-man: total games: {{ data.stack_three_total }} wins: {{ data.stack_three_wins }} losses: {{ data.stack_three_losses }} win rate: {{ data.stack_three_win_rate }}% <br>

          four-man: total games: {{ data.stack_four_total }} wins: {{ data.stack_four_wins }} losses: {{ data.stack_four_losses }} win rate: {{ data.stack_four_win_rate }}% <br>

          five-man: total games: {{ data.stack_five_total }} wins: {{ data.stack_five_wins }} losses: {{ data.stack_five_losses }} win rate: {{ data.stack_five_win_rate }}% <br>

        </div>

        <div class="p-10 max-w-[90em] ml-auto mr-auto" v-if="data && data.matchData">
          <h2>Most Recent matches</h2>

                   
            <game-summary-box v-for="(item, index) in data.matchData" :data="item" :caption="`${item.game_map.name} | ${item.game_type.name} | ${item.game_date}`"></game-summary-box>
          
        </div>
      </div>


  </div>
</template>

<script>
export default {
  name: 'PlayerStats',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: String, 
    region: String,
  },
  data(){
    return {
      data: null,
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],

    }
  },
  created(){
    this.getData();
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/player", {
          blizz_id: this.blizzid,
          region: this.region,
          game_type: "all",
          season: "all",
        });
        this.data = response.data; 
      }catch(error){
        console.log(error);
      }
    },
    handleInputChange(eventPayload) {
    },
  },

}
</script>