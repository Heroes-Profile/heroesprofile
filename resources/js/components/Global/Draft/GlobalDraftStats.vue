<template>
  <div>
    <div class="grid gap-5 grid-cols-1">
      <page-heading :infoText1="infoText1" :infoText2="infoText2" :heading="selectedHero ? selectedHero.name + ' Draft Statistics' : 'Draft Statistics'"></page-heading>


      <!-- Should turn into a component for easy styling? -->
      <div class="flex flex-wrap gap-1" v-if="!selectedHero">
        <div v-for="hero in heroes" :key="hero.id">
          <round-image :hero="hero" @click="clickedHero(hero)"></round-image>
        </div>
      </div>

      <div v-else>
        <single-select-filter :values="filters.heroes" :text="'Hero'" :defaultValue="this.selectedHero.id.toString()" @input-changed="herochanged"></single-select-filter>


        <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :gametypedefault="gametypedefault"
        :includetimeframetype="true"
        :includetimeframe="true"
        :includeregion="true"
        :includeherolevel="true"
        :includegamemap="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
        >
      </filters>

      <div v-if="draftdata">
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Pick Order
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Pick/Ban Rate % at position
              </th>            
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Team Wins
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Team Losses
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Team Win Rate %
              </th>                 
            </tr>
          </thead>
          <tbody>
            <!-- ChatGPT code. this isnt working 100% -->
            <tr 
              v-for="row in draftdata" 
              :key="row.pick_number"
              :class="determinePickOrBan(row.pick_number).includes('Ban') ? 'bg-red' : ''"
            >
              <td class="py-2 px-3 border-b border-gray-200">
                {{ determinePickOrBan(row.pick_number) }}
              </td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else>
        <loading-component></loading-component>
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
    },
    data(){
      return {
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

      console.log(this.selectedHero);
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
          const response = await this.$axios.post("/api/v1/global/draft", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
          });

          this.draftdata = response.data;
        }catch(error){
        //console.log(error);
        }
      },
      clickedHero(hero){
        this.selectedHero = hero;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        this.getData();
      },
      herochanged(eventPayload){
        console.log(eventPayload.value)
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
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
     }

   }
 }
</script>