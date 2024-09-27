<template>
  <div>
    <page-heading :infoText1="hero + ' stats for ' + battletag" :heading="'Hero Stats: '+hero" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
      <hero-image-wrapper :hero="heroobject" :size="'big'"></hero-image-wrapper>
    </page-heading>

    <div class="flex justify-center max-w-[1500px] mx-auto flex-wrap max-md:flex-col max-md:items-center">
      <single-select-filter :values="gameTypesWithAll" :text="'Game Type'" @input-changed="handleInputChange" :trackclosure="true" :defaultValue="!modifiedgametype ? 'All' : modifiedgametype" :disabled="disableFilterInput"></single-select-filter>
      <single-select-filter :values="seasonsWithAll" :text="'Season'" @input-changed="handleInputChange" :trackclosure="true" :defaultValue="'All'" :disabled="disableFilterInput"></single-select-filter>
      <single-select-filter :values="gameMapWithAll" :text="'Game Map'" @input-changed="handleInputChange" :trackclosure="true" :defaultValue="'All'" :disabled="disableFilterInput"></single-select-filter>
      <button :disabled="disableFilterInput" @click="applyFilter"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disableFilterInput, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disableFilterInput}">
          Filter
      </button>
    </div>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    
    <div v-if="data">
      <div class="flex md:p-20 gap-10 mx-auto justify-center items-between  max-md:flex-col max-md:items-center">
        <div class="flex-1 flex flex-wrap justify-between max-w-[400px] w-full items-between mt-[1em] max-md:order-1">
          <stat-box class="w-[48%]" :title="'Wins'" :value="data.wins.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Losses'" :value="data.losses.toLocaleString('en-US')"></stat-box>
          <div class="w-full mx-auto text-center">
            <stat-bar-box class="w-full mb-5" size="full" :title="'Win Rate'" :value="data.win_rate.toFixed(2)" color="teal"></stat-bar-box> 
          </div>      
          <stat-box class="w-[48%]" :title="'KDR'" :value="data.kdr" color="red"></stat-box>          
          <stat-box class="w-[48%]" :title="'KDA'" :value="data.kda" color="red"></stat-box>                  
        </div>
        <div class="my-auto">
          <hero-image-wrapper :rectangle="true" :hero="heroobject" :title="heroobject.name" size="large"></hero-image-wrapper>
        </div>

        <div class="flex flex-wrap max-w-[450px] text-left w-full items-between h-full justify-center mt-[1em]">
          <stat-box class="w-[48%] flex-1" :title="'Takedowns'" :value="data.sum_takedowns.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%] " :title="'Kills'" :value="data.sum_kills.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%] mt-4 mb-4" :title="'Assists'" color="teal" :value="data.sum_assists.toLocaleString('en-US')"></stat-box>
          
          <stat-box class="w-[48%] mt-4 mb-4" :title="'Deaths'" color="teal" :value="data.sum_deaths.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[100%]" :title="'# Games'" color="red" :value="data.games_played.toLocaleString('en-US')"></stat-box>
        </div>

      </div>


      <div class="max-w-[1500px] mx-auto text-right mb-2">
        <custom-button :href="this.getTalentPageUrl()" class="flex-1 " text="View Talent Data"></custom-button>
      </div>
      
      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps played on {{ hero }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :playerlink="true" :text="'Most Played'" :data="data.map_data_top_played.slice(0, 3)" color="blue"></group-box>
            <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.map_data_top_win_rate.slice(0, 3)" color="teal"></group-box>
            <group-box :playerlink="true" :text="'Latest Played'" :data="data.map_data_top_latest_played.slice(0, 3)" color="yellow"></group-box>
          </div>
          <div class="flex flex-wrap mx-auto gap-2 pt-5 justify-center">
            <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + item.region + '/Map/' + item.game_map.name" v-for="(item, index) in data.map_data" :key="index">
              <map-image-wrapper :map="item.game_map" size="big">
                <image-hover-box :title="item.game_map.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + item.games_played"></image-hover-box>
              </map-image-wrapper>
            </a>
          </div>
        </div>
      </div>

      <line-chart v-if="seasonWinRateDataArray" class="max-w-[1500px] mx-auto" :data="seasonWinRateDataArray" :dataAttribute="'win_rate'" :title="`${battletag} Win Rate Data Per Season with ${heroobject.name}`"></line-chart>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="bg-lighten">
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
    </div>
      </div>
      <div class="flex justify-center max-w-[1500px] mx-auto items-center max-md:flex-col">
    <div class="p-10 max-w-[90em] ml-auto mr-auto">
        <h2 class="text-3xl font-bold py-5">Party Size Win Rates</h2>
        <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
          <div class="flex gap-10 text-s"><span>Solo</span><span>Total Games: {{ (data.stack_size_one_wins + data.stack_size_one_losses).toLocaleString('en-US') }} </span></div>
          <stat-bar-box size="big" :value="data.stack_size_one_win_rate.toFixed(2) "></stat-bar-box>     
        </div>
        <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
          <div class="flex gap-10 text-s"><span>Two Stack</span><span>Total Games: {{ (data.stack_size_two_wins + data.stack_size_two_losses).toLocaleString('en-US') }} </span></div>
          <stat-bar-box size="big" :value="data.stack_size_two_win_rate.toFixed(2) " color="teal"></stat-bar-box>     
        </div>
        <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
          <div class="flex gap-10 text-s"><span>Three Stack</span><span>Total Games: {{ (data.stack_size_three_wins + data.stack_size_three_losses).toLocaleString('en-US') }} </span></div>
          <stat-bar-box size="big" :value="data.stack_size_three_win_rate.toFixed(2) " color="red"></stat-bar-box>     
        </div>
        <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
          <div class="flex gap-10 text-s"><span>Four Stack</span><span>Total Games: {{ (data.stack_size_four_wins + data.stack_size_four_losses).toLocaleString('en-US') }} </span></div>
          <stat-bar-box size="big" :value="data.stack_size_four_win_rate.toFixed(2) " color="yellow"></stat-bar-box>     
        </div>
        <div class="md:w-[1000px] items-center gap-10 md:px-20 py-5 justify-center" >
          <div class="flex gap-10 text-s"><span>Five Stack</span><span>Total Games: {{ (data.stack_size_five_wins + data.stack_size_five_losses).toLocaleString('en-US') }} </span></div>
          <stat-bar-box size="big" :value="data.stack_size_five_win_rate.toFixed(2) "></stat-bar-box>     
        </div>
        
        </div>
        <dynamic-square-ad :patreon-user="patreonUser" :index="2"></dynamic-square-ad>
      </div>

      <div class="bg-lighten">
        <div class="p-10 max-w-[90em] ml-auto mr-auto" v-if="data && data.latestGames">
          <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>
          <game-summary-box v-for="(item, index) in data.latestGames" :data="item"></game-summary-box>
          <div class="max-w-[1500px] mx-auto text-right my-2">
            <custom-button :href="'/Player/' + this.battletag + '/' + this.blizzid + '/' + this.region + '/Match/History/?hero=' + heroobject.name" class="" text="View Match History"></custom-button>
          </div>
        </div>
      </div>

      <div class="max-w-[1500px] mx-auto">
        <h2 class="text-3xl font-bold py-5">Advanced Stats</h2>
        <table class="max-md:text-xs max-w-full" v-for="(section, sectionIndex) in sections" :key="sectionIndex">
          <thead>
            <tr>
              <td class="teal">{{ section.title }}</td>
              <td>Total</td>
              <td>Average</td>
              <td>Maximum</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rowIndex) in section.rows" :key="rowIndex">
              <td width="25%" style="max-width:25%">{{ row.label }}</td>
              <td width="25%">{{ formatValue(row.key, this.data["sum_" + row.key])}}</td>
              <td width="25%">{{ formatValue(row.key, this.data["avg_" + row.key]) }}</td>
              <td width="25%">{{ formatValue(row.key, this.data["max_" + row.key]) }}</td>
            </tr>
          </tbody>
        </table>
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
      playerloadsetting: {
        type: [String, Boolean]
      },
      hero: String,
      heroobject: Object,
      battletag: String,
      blizzid: {
        type: [String, Number]
      },
      region: Number,
      regionsmap: Object,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        modifiedgametype: null,
        modifiedseason: null,
        modifiedgamemap: null,
        data: null,
        disableFilterInput: null,
        sections: [
          {
            title: 'Combat',
            rows: [
              { label: 'Kills', key: 'kills' },
              { label: 'Assists', key: 'assists' },
              { label: 'Takedowns', key: 'takedowns' },
              { label: 'Deaths', key: 'deaths' },
            ],
          },
          {
            title: 'Player',
            rows: [
              { label: 'Regeneration Globes', key: 'regen_globes' },
              { label: 'Hero Damage', key: 'hero_damage' },
              { label: 'Physical Damage Done', key: 'physical_damage' },
              { label: 'Spell Damage Done', key: 'spell_damage' },
              { label: 'Damage Taken', key: 'damage_taken' },
              { label: 'Time Spent Dead', key: 'time_spent_dead' },
              { label: 'Enemy Silence Duration', key: 'silencing_enemies' },
              { label: 'Enemy Rooted Duration', key: 'rooting_enemies' },
              { label: 'Enemy Stunned Duration', key: 'stunning_enemies' },
              { label: 'Escapes', key: 'escapes' },
              { label: 'Vengeances', key: 'vengeance' },
              { label: 'Outnumbered Deaths', key: 'outnumbered_deaths' },
            ],
          },
          {
            title: 'Siege',
            rows: [
              { label: 'Siege Damage', key: 'siege_damage' },
              { label: 'Structure Damage', key: 'structure_damage' },
              { label: 'Minion Damage', key: 'minion_damage' },
              { label: 'Lane Merc. Damage', key: 'creep_damage' },
              { label: 'Summon Damage', key: 'summon_damage' },
            ],
          },
          {
            title: 'Macro',
            rows: [
              { label: 'Experience Contribution', key: 'experience_contribution' },
              { label: 'Merc. Camp Captures', key: 'merc_camp_captures' },
              { label: 'Watch Tower Captures', key: 'watch_tower_captures' },
              { label: 'Team Exp.', key: 'meta_experience' },
              { label: 'Game Length', key: 'game_length' },
            ],
          },
          {
            title: 'Team Fight',
            rows: [
              { label: 'Teamfight Damage Taken', key: 'teamfight_damage_taken' },
              { label: 'Teamfight Hero Damage', key: 'teamfight_hero_damage' },
              { label: 'Teamfight Escapes', key: 'teamfight_escapes' },
              { label: 'Teamfight Healing', key: 'teamfight_healing' },
            ],
          },
          {
            title: 'Defense/ Healing',
            rows: [
              { label: 'Healing', key: 'healing' },
              { label: 'Self Healing', key: 'self_healing' },
              { label: 'Clutch Heals', key: 'clutch_heals' },
              { label: 'Ally Protection', key: 'protection_allies' },
              { label: 'Crowd Control Enemies', key: 'time_cc_enemy_heroes' },
            ],
          },
          {
            title: 'Other',
            rows: [
              { label: 'Town Kills', key: 'town_kills' },
              { label: 'Kill Streak', key: 'highest_kill_streak' },
              { label: 'Multikills', key: 'multikill' },
            ],
          },
        ],

      }
    },
    created(){
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
      gameMapWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.game_maps];
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
          const response = await this.$axios.post("/api/v1/player/heroes/single", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            game_map: this.modifiedgamemap,
            hero: this.hero,
            type: "single",
            page: "hero",
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

        if(eventPayload.field == "Game Map"){
          if(eventPayload.value == "All"){
            this.modifiedgamemap = null;
          }else{
            this.modifiedgamemap = eventPayload.value;
          }
        }

      },
      applyFilter(){
        if(!this.isLoading){
          this.data = null;
          this.getData();
        }
      },
      getTalentPageUrl(){
        return "/Player/" + this.battletag + "/" + this.blizzid + "/" + this.region  + "/Talents/" + this.hero;
      },
      formatValue(key, value){
        var returnValue = null;

        if(!value){
          returnValue = 0;
        }else if(value < 1000){
          returnValue = value.toFixed(2);
        }else{
          returnValue = Math.round(value).toLocaleString('en-US');
        }

        if (key == "game_length") {
          let valueInSeconds = value;
          const days = Math.floor(valueInSeconds / (24 * 3600));
          valueInSeconds %= 24 * 3600;
          const hours = Math.floor(valueInSeconds / 3600);
          valueInSeconds %= 3600;
          const minutes = Math.floor(valueInSeconds / 60);
          const secs = Math.floor(valueInSeconds % 60);

          let returnValue = "";

          if (days > 0) {
            returnValue += `${days} days, `;
          }
          if (hours > 0) {
            returnValue += `${hours} hours, `;
          }
          if (minutes > 0) {
            returnValue += `${minutes} minutes, `;
          }
          returnValue += `${secs} seconds`;

          return returnValue;
        }
        return returnValue;
      },
    }
  }
</script>