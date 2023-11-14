<template>
  <div>
    <div class="grid gap-5 grid-cols-1">
      <page-heading :infoText1="infoText1" :infoText2="infoText2" :heading="selectedHero ? selectedHero.name + ' Draft Statistics' : 'Draft Statistics'">
        <hero-image-wrapper v-if="selectedHero" :hero="selectedHero" :size="'big'"></hero-image-wrapper>
      </page-heading>


      <div v-if="!selectedHero">
        <hero-selection :heroes="heroes"></hero-selection>
      </div>

      <div v-else>
        <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypedefault="gametypedefault"
        :includetimeframetype="true"
        :includetimeframe="true"
        :includeregion="true"
        :includeherolevel="true"
        :includegamemap="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
        :advancedfiltering="advancedfiltering"

        >
      </filters>

      <div class="max-w-[1500px] mx-auto flex justify-start mb-2"> 
      <span class="flex gap-4 mb-2"> {{ this.selectedHero.name }} {{ "Draft Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span>
      </div>
      <div v-if="draftdata">
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Pick Order
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Pick/Ban Rate % at position
              </th>            
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Team Wins
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Team Losses
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Team Win Rate %
              </th>                 
            </tr>
          </thead>
          <tbody>
            <!-- ChatGPT code. this isnt working 100% colors seem messed up-->
            <tr 
            v-for="row in draftdata" 
            :key="row.pick_number"
            :class="determinePickOrBan(row.pick_number).includes('Ban') ? 'bg-hred border-b border-gray-light' : ''"
            >
            <td class="py-2 px-3 ">
              {{ determinePickOrBan(row.pick_number) }}
            </td>
            <td class="py-2 px-3 ">{{ row.popularity.toFixed(2) }}</td>
            <td class="py-2 px-3 ">{{ row.wins.toLocaleString() }}</td>
            <td class="py-2 px-3 ">{{ row.losses.toLocaleString() }}</td>
            <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
  </div>

</div>
</div>
</template>

<script>
  export default {
    name: 'GlobalDraftStats',
    components: {
    },
    props: {
      filters: Object,
      inputhero: Object,
      heroes: Array,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
      defaultbuildtype: String,
      advancedfiltering: Boolean,
    },
    data(){
      return {
        isLoading: null,
        cancelTokenSource: null,
        infoText1: "Storm League Hero pick rates, ban rates, and pick order rate.",
        infoText2: "Teams win, losses, and win rate are based on where they pick a hero in the draft. So if a team bans Abathur at the first position of the draft, we are showing those teams wins and losses and win rates as well as when teams actually pick Abathur.",
        selectedHero: null,
        draftdata: null,

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
      }
    },
    created(){
      this.selectedHero = this.inputhero;
      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
      this.timeframetype = this.defaulttimeframetype;

      if(this.selectedHero){
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
    },
    watch: {
    },
    methods: {
      async getData(){
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/global/draft", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });

          this.draftdata = response.data;
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
      clickedHero(hero){
        this.selectedHero = hero;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        this.getData();
      },
      herochanged(eventPayload){
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;

        this.draftdata = null;
        this.getData();
      },
      determinePickOrBan(pick_number) {
        const mapping = {
         0: "Ban 1",
         1: "Ban 2",
         2: "Ban 3",
         3: "Ban 4",
         4: "Pick 1",
         5: "Pick 2",
         6: "Pick 3",
         7: "Pick 4",
         8: "Pick 5",
         9: "Ban 5",
         10: "Ban 6",
         11: "Pick 6",
         12: "Pick 7",
         13: "Pick 8",
         14: "Pick 9",
         15: "Pick 10",
       };
       
       return mapping[pick_number];
     },
     redirectChangeHero(){
      window.location.href = "/Global/Draft/General";
    },

  }
}
</script>