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

          :timeframetypeinput="timeframetype"
          :timeframeinput="timeframe"
          :regioninput="region"
          :herolevelinput="herolevel"
          :gamemapinput="gamemap"
          :playerrankinput="playerrank"
          :herorankinput="herorank"
          :rolerankinput="rolerank"

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
        <takeover-ad :patreon-user="patreonUser"></takeover-ad>

   
        <div v-if="draftdata">
          <div class="max-w-[1500px] mx-auto flex justify-start mb-2"> 
            <span class="flex gap-4 mb-2 mx-2"> {{ this.selectedHero.name }} {{ "Draft Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span>
          </div>
          <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw]  max-sm:text-xs 2xl:mx-auto  " style=" ">
            <table id="responsive-table" class="responsive-table  relative max-sm:text-xs" ref="responsivetable">
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
                <tr 
                  v-for="row in draftdata" 
                  :key="row.pick_number"
                  :class="determinePickOrBan(row.pick_number).includes('Ban') ? 'bg-hred border-b border-gray-light' : ''"
                  >
                  <td class="py-2 px-3 ">
                    {{ determinePickOrBan(row.pick_number) }}
                  </td>
                  <td class="py-2 px-3 ">{{ row.popularity.toFixed(2) }}</td>
                  <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
                  <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
                  <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
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
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        windowWidth: window.innerWidth,
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

      if(this.urlparameters){
        this.setURLParameters();
      }
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


        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
        queryString += `&game_type=${this.gametype}`;

        if(this.region){
          queryString += `&region=${this.region}`;
        }
        
        if(this.herolevel){
          queryString += `&hero_level=${this.herolevel}`;
        }

        if(this.gamemap){
          queryString += `&game_map=${this.gamemap}`;
        }

        if(this.playerrank){
          queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
        }

        if(this.herorank){
          queryString += `&hero_league_tier=${this.convertRankIDtoName(this.herorank)}`;
        }

        if(this.rolerank){
          queryString += `&role_league_tier=${this.convertRankIDtoName(this.rolerank)}`;
        }

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);



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
      window.location.href = "/Global/Draft/";
    },
    convertRankIDtoName(rankIDs) {
      return rankIDs.map(rankID => this.filters.rank_tiers.find(tier => tier.code == rankID).name);
    },
    setURLParameters(){
      if(this.urlparameters["timeframe_type"]){
        this.timeframetype = this.urlparameters["timeframe_type"];
      }
      
      if(this.urlparameters["timeframe"]){
        this.timeframe = this.urlparameters["timeframe"].split(',');
      }

      if(this.urlparameters["game_type"]){
        this.gametype = this.urlparameters["game_type"].split(',');
      }

      if(this.urlparameters["region"]){
        this.region = this.urlparameters["region"].split(',');
      }
      
      if(this.urlparameters["hero_level"]){
        this.herolevel = this.urlparameters["hero_level"].split(',');
      }

      if(this.urlparameters["game_map"]){
        this.gamemap = this.urlparameters["game_map"].split(',');
      }

      if (this.urlparameters["league_tier"]) {
        this.playerrank = this.urlparameters["league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
      }

      if (this.urlparameters["hero_league_tier"]) {
        this.herorank = this.urlparameters["hero_league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
      }

      if (this.urlparameters["role_league_tier"]) {
        this.rolerank = this.urlparameters["role_league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
      }
    },
  }
}
</script>