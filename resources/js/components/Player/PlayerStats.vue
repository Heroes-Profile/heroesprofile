<template>
  <div class="">
    <page-heading :infoText1="infoText" :heading="'Profile'" :battletag="battletag +`(`+ regionsmap[region] + `)`" :isPatreon="isPatreon" :isOwner="isOwner">
    </page-heading>
    <div class="flex justify-center max-w-[1500px] mx-auto">
      <single-select-filter :values="gameTypesWithAll" :text="'Game Type'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="!modifiedgametype ? 'All' : modifiedgametype" :disabled="disableFilterInput"></single-select-filter>
      <single-select-filter :values="seasonsWithAll" :text="'Season'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="'All'" :disabled="disableFilterInput"></single-select-filter>
    </div>

    <takeover-ad :patreon-user="patreonUser"></takeover-ad>
    
    <div v-if="data == ''" class="flex md:p-20 gap-10 mx-auto justify-center items-between ">
      <div class="flex items-center">
        <span>No data for this player and filters</span>
      </div>
    </div>
    <div v-else-if="data" class="">


      <div class="flex md:p-20 gap-10 mx-auto justify-center items-between  max-md:flex-col max-md:items-center">
        <div class="flex-1 flex flex-wrap justify-between max-w-[400px] w-full items-between mt-[1em] max-md:order-1">
          <stat-box class="w-[48%]" :title="'Wins'" :value="data.wins.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Losses'" :value="data.losses.toLocaleString('en-US')"></stat-box>

          
          <div class="w-full mx-auto text-center">
            <stat-bar-box class="w-full " size="full" :title="'First to Ten Win Rate'" :value="data.first_to_ten_win_rate.toFixed(2)" color="teal"></stat-bar-box>
          </div>
         
           <div class="w-full mx-auto text-center">
            <stat-bar-box class="w-full" size="full" :title="'Second to Ten Win Rate'" :value="data.second_to_ten_win_rate.toFixed(2)" color="red"></stat-bar-box>         
           </div>
          <stat-box class="w-[48%]" :title="'KDR'" :value="data.kdr" color="yellow"></stat-box>          
          <stat-box class="w-[48%]" :title="'KDA'" :value="data.kda" color="yellow"></stat-box>          

          <stat-box class="w-full" :title="'Account Level'" :value="data.account_level.toLocaleString('en-US')"></stat-box>          
        </div>
        <div class="my-auto">
          <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" size="large"></hero-image-wrapper>
        </div>

        <div class="flex flex-col max-w-[400px] text-left w-full items-between max-md:order-2 ">
          <stat-bar-box class="w-full" size="full" :title="'Win Rate'" :value="data.win_rate.toFixed(2)"></stat-bar-box>       
          <stat-bar-box class="w-full" size="full" :title="'Bruiser Win Rate'" :value="data.bruiser_win_rate.toFixed(2)" color="teal"></stat-bar-box>       
          <stat-bar-box class="w-full" size="full" :title="'Support Win Rate'" :value="data.support_win_rate.toFixed(2)" color="red"></stat-bar-box>       
          <stat-bar-box class="w-full" size="full" :title="'Ranged Assassin Win Rate'" :value="data.ranged_assassin_win_rate.toFixed(2)" color="yellow"></stat-bar-box>       
          <stat-bar-box class="w-full" size="full" :title="'Melee Assassin Win Rate'" :value="data.melee_assassin_win_rate.toFixed(2)"></stat-bar-box>       
          <stat-bar-box class="w-full" size="full" :title="'Healer Win Rate'" :value="data.healer_win_rate.toFixed(2)" color="teal"></stat-bar-box>       
          <stat-bar-box class="w-full" size="full" :title="'Tank Win Rate'" :value="data.tank_win_rate.toFixed(2)" color="red"></stat-bar-box>       
        </div>
      </div>

      <div class="flex mx-auto justify-center max-w-[1500px] max-md:flex-col max-md:p-4">
        <stat-box :title="'MVP%'" :value="data.mvp_rate.toFixed(2)"></stat-box>  <stat-box :title="'Total Time Played'" :value="data.total_time_played"></stat-box>  <stat-box :title="'AVG. Time on Fire'" :value="data.average_time_on_fire"></stat-box>      
      </div>
      <div class="max-w-[1500px] mx-auto text-right mb-2 max-md:text-center">
        <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Role'" class="flex-1 " text="View all Roles"></custom-button>
      </div>

      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :playerlink="true" :text="'Most Played'" :data="data.heroes_three_most_played" color="blue"></group-box>
            <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.heroes_three_highest_win_rate" color="teal"></group-box>
            <group-box :playerlink="true" :text="'Latest Played'" :data="data.heroes_three_latest_played" color="yellow"></group-box>
          </div>

        </div>
        <div class="max-w-[1500px] mx-auto text-right mb-2">
          <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Hero'" class=" " text="View All Heroes"></custom-button>
        </div>
      </div>
      <div class="flex justify-center max-w-[1500px] mx-auto items-center max-md:flex-col">
      <div class="max-w-[1500px] mx-auto">
        <div class="grid grid-cols-4 max-md:grid-cols-2 items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Quick Match</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.qm_mmr_data ? data.qm_mmr_data.win_rate.toFixed(2) : 0 "></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.qm_mmr_data ? data.qm_mmr_data.rank_tier : ''"></stat-box>
          <stat-box :title="'MMR'" :value="data.qm_mmr_data ? data.qm_mmr_data.mmr.toLocaleString('en-US') : 0 "></stat-box>
        </div>

        <div class="grid grid-cols-4 max-md:grid-cols-2   items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Unranked Draft</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.ud_mmr_data ? data.ud_mmr_data.win_rate.toFixed(2) : 0 " color="teal"></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.ud_mmr_data ? data.ud_mmr_data.rank_tier : ''" color="teal"></stat-box>
          <stat-box :title="'MMR'" :value="data.ud_mmr_data ? data.ud_mmr_data.mmr.toLocaleString('en-US') : 0 " color="teal"></stat-box>
        </div>

        <div class=" grid grid-cols-4 max-md:grid-cols-2  items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Hero league</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.hl_mmr_data ? data.hl_mmr_data.win_rate.toFixed(2) : 0 " color="red"></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.hl_mmr_data ? data.hl_mmr_data.rank_tier : ''" color="red"></stat-box>
          <stat-box :title="'MMR'" :value="data.hl_mmr_data ? data.hl_mmr_data.mmr.toLocaleString('en-US') : 0 " color="red"></stat-box>
        </div>


        <div class="grid grid-cols-4 max-md:grid-cols-2  items-center gap-10 md:px-20 py-5 justify-center" >
          <h4 class="text-right">Team league</h4>
          <stat-bar-box :title="'Win Rate'" :value=" data.tl_mmr_data ? data.tl_mmr_data.win_rate.toFixed(2) : 0 " color="yellow"></stat-bar-box>
          <stat-box title="Rank Tier" :value="data.tl_mmr_data ? data.tl_mmr_data.rank_tier : ''" color="yellow"></stat-box>
          <stat-box :title="'MMR'" :value="data.tl_mmr_data ? data.tl_mmr_data.mmr.toLocaleString('en-US') : 0 " color="yellow"></stat-box>
        </div>

        <div class="grid grid-cols-4  max-md:grid-cols-2  items-center gap-10 md:px-20 py-5 justify-center" >
         <h4 class="text-right"> Storm league</h4>
         <stat-bar-box :title="'Win Rate'" :value=" data.sl_mmr_data ? data.sl_mmr_data.win_rate.toFixed(2) : 0 " color="gray-dark"></stat-bar-box>
         <stat-box title="Rank Tier" :value="data.sl_mmr_data ? data.sl_mmr_data.rank_tier : ''" color="gray-dark"></stat-box>
         <stat-box :title="'MMR'" :value="data.sl_mmr_data ? data.sl_mmr_data.mmr.toLocaleString('en-US') : 0 " color="gray-dark"></stat-box>
       </div>

       <div class="grid grid-cols-4 max-md:grid-cols-2  items-center gap-10 md:px-20 py-5 justify-center" >
        <h4 class="text-right">ARAM</h4>
        <stat-bar-box :title="'Win Rate'" :value=" data.ar_mmr_data ? data.ar_mmr_data.win_rate.toFixed(2) : 0 "></stat-bar-box>
        <stat-box title="Rank Tier" :value="data.ar_mmr_data ? data.ar_mmr_data.rank_tier : ''"></stat-box>
        <stat-box :title="'MMR'" :value="data.ar_mmr_data ? data.ar_mmr_data.mmr.toLocaleString('en-US') : 0 "></stat-box>
      </div>

     

     
      </div>
    
      <dynamic-square-ad :patreon-user="patreonUser" :index="1"></dynamic-square-ad>
    </div>  <div class="max-w-[1500px] mx-auto  text-right my-2">
        <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/MMR'" class=" " text="View MMR Breakdown"></custom-button>
      </div>


    <div class="bg-lighten p-10 ">
      <div class=" max-w-[90em] ml-auto mr-auto">
        <h2 class="text-3xl font-bold py-5 text-center">Maps</h2>
        <div class="flex flex-wrap justify-center">
          <group-box :playerlink="true" :text="'Most Played'" :data="data.maps_three_most_played" color="blue"></group-box>
          <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.maps_three_highest_win_rate" color="teal"></group-box>
          <group-box :playerlink="true" :text="'Latest Played'" :data="data.maps_three_latest_played" color="yellow"></group-box>
        </div>
        <div class="max-w-[1500px] mx-auto text-right my-2">
        <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Map'" class=" " text="View All Maps"></custom-button>
      </div>
      </div>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>
    </div>

    <div class="flex justify-center max-w-[1500px] mx-auto items-center max-md:flex-col">
    <div class="p-10 max-w-[90em] ml-auto mr-auto">
      <h2 class="text-3xl font-bold py-5">Party Size Win Rates</h2>

      <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-s"><span>Solo</span><span>Total Games: {{ (data.stack_one_wins + data.stack_one_losses).toLocaleString('en-US') }} </span></div>
        <stat-bar-box size="big" :value="data.stack_one_win_rate.toFixed(2) "></stat-bar-box>     
      </div>
      <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-s"><span>Two Stack</span><span>Total Games: {{ (data.stack_two_wins + data.stack_two_losses).toLocaleString('en-US') }} </span></div>
        <stat-bar-box size="big" :value="data.stack_two_win_rate.toFixed(2) " color="teal"></stat-bar-box>     
      </div>
      <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-s"><span>Three Stack</span><span>Total Games: {{ (data.stack_three_wins + data.stack_three_losses).toLocaleString('en-US') }} </span></div>
        <stat-bar-box size="big" :value="data.stack_three_win_rate.toFixed(2) " color="red"></stat-bar-box>     
      </div>
      <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-s"><span>Four Stack</span><span>Total Games: {{ (data.stack_four_wins + data.stack_four_losses).toLocaleString('en-US') }} </span></div>
        <stat-bar-box size="big" :value="data.stack_four_win_rate.toFixed(2) " color="yellow"></stat-bar-box>     
      </div>
      <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
        <div class="flex gap-10 text-s"><span>Five Stack</span><span>Total Games: {{ (data.stack_five_wins + data.stack_five_losses).toLocaleString('en-US') }} </span></div>
        <stat-bar-box size="big" :value="data.stack_five_win_rate.toFixed(2) "></stat-bar-box>     
      </div>
     

    </div>
    <dynamic-square-ad :patreon-user="patreonUser" :index="2"></dynamic-square-ad>
    </div>

    <div class="bg-lighten">
      <div class="p-10 max-w-[90em] ml-auto mr-auto" v-if="data && data.matchData">
        <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>

        
        <game-summary-box v-for="(item, index) in data.matchData" :data="item"></game-summary-box>
        <div class="max-w-[1500px] mx-auto text-right my-2">
        <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Match/History'" class="" text="View Match History"></custom-button>
      </div>
      </div>
    </div>

    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
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
      regionsmap: Object,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,

    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        data: null,
        infoText: "Profile data",
        modifiedgametype: null,
        modifiedseason: null,
        inputHero: null,
        disableFilterInput: null,
      }
    },
    created(){
      this.modifiedseason = this.season;

      if(this.gametypedefault && this.gametypedefault.length > 0){
        this.modifiedgametype = this.gametypedefault[0];
      }
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
      isOwner(){
        if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
          return true;
        }
        return false;
      }
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
          const response = await this.$axios.post("/api/v1/player", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });
          this.data = response.data;
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
        if(!this.isLoading){
          this.data = null;
          this.getData();
        }
      },
    },

  }
</script>