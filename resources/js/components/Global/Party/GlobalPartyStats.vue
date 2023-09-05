<template>
  <div>
    <h1>Global Party Statistics</h1>
    <infobox :input="infoText"></infobox>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includestatfilter="true"
      :includeherolevel="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :includeheropartysize="true"
      :teamoneparty="true"
      :teamtwoparty="true"
      >
    </filters>
    
    <div v-if="partydata">

      <div class="container" id="5 Solo players">
        <h1>5 Solo Players vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.solo" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container" id="1 Double Stack">
        <h1>1 Double Stack vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container" id="2 Double Stack">
        <h1>2 Double Stack vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.double_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container" id="2 Double Stack">
        <h1>1 Triple Stack and 2 Solos vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.triple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container" id="2 Double Stack">
        <h1>1 Triple Stack and 1 Double Stack vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.triple_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container" id="2 Double Stack">
        <h1>1 Quadruple Stack and 1 Solo vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.quadruple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>



      <div class="container" id="2 Double Stack">
        <h1>5 player Stack vs.</h1>
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.quintuple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 border-b border-gray-200">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalPartyStats',
  components: {
  },
  props: {
     filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaulttimeframetype: String,
    defaulttimeframe: Array,
  },
  data(){
    return {
      loading: false,
      infoText: "Party win rates based on differing increments, stat types, game type, rank, and more. The hero filter allows you to see party data for games that only contained that hero. You can further sub-filter by looking at which party stack size hero was a part of by changing the Hero Party Size filter",
      partydata: null,

      //Sending to filter
      timeframetype: null,
      timeframe: null,
      region: null,
      statfilter: null,
      herolevel: null,
      role: null,
      hero: null,
      gametype: null,
      gamemap: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
      mirrormatch: null,
      heropartysize: null,
      teamoneparty: null,
      teamtwoparty: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;
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
        this.loading = true;
        this.data = [];

        const response = await this.$axios.post("/api/v1/global/party", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          hero: this.hero,
          game_type: this.gametype,
          map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          heropartysize: this.heropartysize,
          teamoneparty: this.teamoneparty,
          teamtwoparty: this.teamtwoparty,
        });

        this.partydata = response.data;
        this.loading = false;
      }catch(error){
        console.log(error)
      }
    },
   filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : [];
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : "";
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : [];
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : [];
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : [];
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : "";
      this.heropartysize = filteredData.single["Hero Party Size"] ? filteredData.single["Hero Party Size"] : "";
      this.teamoneparty = filteredData.single["Team One Party"] ? filteredData.single["Team One Party"] : "";
      this.teamtwoparty = filteredData.single["Team Two Party"] ? filteredData.single["Team Two Party"] : "";
      this.getData();
    },
    getStackName(ally_combo, enemy_combo){
      return "temp";
    }
  }
}
</script>