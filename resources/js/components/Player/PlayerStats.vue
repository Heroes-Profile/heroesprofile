<template>
  <div class="">
    <page-heading :infoText1="infoText" :heading="battletag +`(`+ regionsmap[region] + `)`"></page-heading>

    <single-select-filter :values="gameTypesWithAll" :text="'Game Type'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="'All'"></single-select-filter>
    <single-select-filter :values="seasonsWithAll" :text="'Season'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="'All'"></single-select-filter>
  

    <div v-if="data" class="">


      <div class="flex md:p-20 gap-10 ">
        <div class="flex-1 flex flex-wrap justify-end">
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
        <div>
          <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" size="large"></hero-image-wrapper>
        </div>

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

        <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Role'" class="flex justify-end " text="View all Roles"></custom-button>
      </div>

      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :playerlink="true" :text="'Most Played'" :data="data.heroes_three_most_played"></group-box>
            <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.heroes_three_highest_win_rate"></group-box>
            <group-box :playerlink="true" :text="'Latest Played'" :data="data.heroes_three_latest_played"></group-box>
          </div>
          <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Hero'" class="flex justify-end " text="View All Heroes"></custom-button>
        </div>
      </div>

      <div class="max-w-[1000px] mx-auto">
        <div class="grid grid-cols-4 items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Quick Match</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.qm_mmr_data ? data.qm_mmr_data.win_rate : 0 "></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.qm_mmr_data ? data.qm_mmr_data.rank_tier : ''"></stat-box>
          <stat-box :title="'MMR'" :value="data.qm_mmr_data ? data.qm_mmr_data.mmr : 0 "></stat-box>
        </div>

        <div class="grid grid-cols-4  items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Unranked Draft</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.ud_mmr_data ? data.ud_mmr_data.win_rate : 0 " color="teal"></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.ud_mmr_data ? data.ud_mmr_data.rank_tier : ''" color="teal"></stat-box>
          <stat-box :title="'MMR'" :value="data.ud_mmr_data ? data.ud_mmr_data.mmr : 0 " color="teal"></stat-box>
        </div>

        <div class=" grid grid-cols-4 items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Hero league</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.hl_mmr_data ? data.hl_mmr_data.win_rate : 0 " color="red"></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.hl_mmr_data ? data.hl_mmr_data.rank_tier : ''" color="red"></stat-box>
          <stat-box :title="'MMR'" :value="data.hl_mmr_data ? data.hl_mmr_data.mmr : 0 " color="red"></stat-box>
        </div>


        <div class="grid grid-cols-4 items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Team league</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.tl_mmr_data ? data.tl_mmr_data.win_rate : 0 " color="yellow"></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.tl_mmr_data ? data.tl_mmr_data.rank_tier : ''" color="yellow"></stat-box>
          <stat-box :title="'MMR'" :value="data.tl_mmr_data ? data.tl_mmr_data.mmr : 0 " color="yellow"></stat-box>
        </div>

        <div class="grid grid-cols-4 items-center gap-10 md:px-20 py-5 justify-center" >
         <h4 class="text-right"> Storm league</h4>
         <stat-bar-box :title="'Win Rate'" :value=" data.sl_mmr_data ? data.sl_mmr_data.win_rate : 0 " color="gray-dark"></stat-bar-box>
         <stat-box title="Rank Tier" :value="data.sl_mmr_data ? data.sl_mmr_data.rank_tier : ''" color="gray-dark"></stat-box>
         <stat-box :title="'MMR'" :value="data.sl_mmr_data ? data.sl_mmr_data.mmr : 0 " color="gray-dark"></stat-box>
       </div>

       <div class="grid grid-cols-4 items-center gap-10 md:px-20 py-5 justify-center" >
        <h4 class="text-right">ARAM</h4>
        <stat-bar-box :title="'Win Rate'" :value=" data.ar_mmr_data ? data.ar_mmr_data.win_rate : 0 "></stat-bar-box>
        <stat-box title="Rank Tier" :value="data.ar_mmr_data ? data.ar_mmr_data.rank_tier : ''"></stat-box>
        <stat-box :title="'MMR'" :value="data.ar_mmr_data ? data.ar_mmr_data.mmr : 0 "></stat-box>
      </div>
      <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/MMR'" class="flex justify-end " text="View MMR"></custom-button>

    </div>


    <div class="bg-lighten p-10 ">
      <div class=" max-w-[90em] ml-auto mr-auto">
        <h2 class="text-3xl font-bold py-5 text-center">Maps</h2>
        <div class="flex flex-wrap justify-center">
          <group-box :playerlink="true" :text="'Most Played'" :data="data.maps_three_most_played"></group-box>
          <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.maps_three_highest_win_rate"></group-box>
          <group-box :playerlink="true" :text="'Latest Played'" :data="data.maps_three_latest_played"></group-box>
        </div>
        <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Map'" class="flex justify-end " text="View All Maps"></custom-button>
      </div>
    </div>


    <div class="p-10 max-w-[90em] ml-auto mr-auto">
      <h2 class="text-3xl font-bold py-5">Party Size Win Rates</h2>

      <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-xs"><span>Solo</span><span>Total Games: {{ data.stack_one_wins + data.stack_one_losses }} </span></div>
        <stat-bar-box size="big" :value="data.stack_one_win_rate "></stat-bar-box>     
      </div>
      <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-xs"><span>Two Stack</span><span>Total Games: {{ data.stack_two_wins + data.stack_two_losses }} </span></div>
        <stat-bar-box size="big" :value="data.stack_two_win_rate " color="teal"></stat-bar-box>     
      </div>
      <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-xs"><span>Three Stack</span><span>Total Games: {{ data.stack_three_wins + data.stack_three_losses }} </span></div>
        <stat-bar-box size="big" :value="data.stack_three_win_rate " color="red"></stat-bar-box>     
      </div>
      <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-xs"><span>Four Stack</span><span>Total Games: {{ data.stack_four_wins + data.stack_four_losses }} </span></div>
        <stat-bar-box size="big" :value="data.stack_four_win_rate " color="yellow"></stat-bar-box>     
      </div>
      <div class="w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-xs"><span>Five Stack</span><span>Total Games: {{ data.stack_five_wins + data.stack_five_losses }} </span></div>
        <stat-bar-box size="big" :value="data.stack_five_win_rate "></stat-bar-box>     
      </div>
      
      

    </div>

    <div class="p-10 max-w-[90em] ml-auto mr-auto" v-if="data && data.matchData">
      <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>

      
      <game-summary-box v-for="(item, index) in data.matchData" :data="item"></game-summary-box>
      
      <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Match/History'" class="flex justify-end " text="View Match History"></custom-button>

    </div>
  </div>
  <div v-else>
    <loading-component :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
  </div>

</div>
</template>

<script>

  export default {
    name: 'PlayerStats',
    components: {
    },
    props: {
      settinghero: Object,
      filters: Object,
      battletag: String,
      blizzid: String, 
      region: String,
      season: Number,
      gametype: Array,
      regionsmap: Object,
    },
    data(){
      return {
        loading: false,
        data: null,
        infoText: "Profile data",
        modifiedgametype: null,
        modifiedseason: null,
        inputHero: null,

      }
    },
    created(){
      this.modifiedgametype = this.gametype;
      this.modifiedseason = this.season;
    },
    mounted() {
      if(this.settinghero){
        this.inputHero = this.settinghero;
      }else{
        this.inputHero = {
          name: "Auto Select",
          short_name: "autoselect3",
          icon: "autoselect3.jpg",
        };
      }

      this.getData();
    },
    computed: {
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
      async getData(){
        this.loading = true;
        try{
          const response = await this.$axios.post("/api/v1/player", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
          });
          this.data = response.data;
        }catch(error){
        //Do something here
        }
        this.loading = false;
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
    },

  }
</script>