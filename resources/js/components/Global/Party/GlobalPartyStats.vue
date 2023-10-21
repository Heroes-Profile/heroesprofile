s<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Global Party Statistics'"></page-heading>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includeherolevel="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includeteamoneparty="true"
      :includeteamtwoparty="true"
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    
    <div v-if="partydata" class="max-w-[1500px] mx-auto">

      <div class=" mx-auto mb-10" id="5 Solo players">
        <h3 class="stack-header">5 Solo Players vs. <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div></h3>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.solo" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="1 Double Stack">
       <h2 class="stack-header">1 Double Stack vs.<div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div></h2>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="2 Double Stack">
        <h2 class="stack-header">2 Double Stack vs.<div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> </h2>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.double_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="2 Double Stack">
        <h2 class="stack-header">1 Triple Stack and 2 Solos vs.<div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div></h2>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.triple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="2 Double Stack">
        <h2 class="stack-header">1 Triple Stack and 1 Double Stack vs.<div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div></h2>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.triple_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="2 Double Stack">
        <h2 class="stack-header">1 Quadruple Stack and 1 Solo vs.<div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div></h2>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.quadruple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>



      <div class="container mx-auto " id="2 Double Stack">
        <h2 class="stack-header">5 player Stack vs. <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div> </h2>
        <table class="">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in partydata.quintuple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 ">{{ row.stack_size_name }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate }}</td>
              <td class="py-2 px-3 ">{{ row.wins }}</td>
              <td class="py-2 px-3 ">{{ row.losses }}</td>
            </tr>
          </tbody>
        </table>  
      </div>

    </div>
    <div v-else>
      <loading-component></loading-component>
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
    advancedfiltering: String,
  },
  data(){
    return {
      isLoading: false,
      infoText: "Party win rates based on differing increments, stat types, game type, rank, and more. The hero filter allows you to see party data for games that only contained that hero.",
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
      mirrormatch: 0,
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
      this.isLoading = true;
      try{
        this.data = [];

        const response = await this.$axios.post("/api/v1/global/party", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          hero: this.hero,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirrormatch: this.mirrormatch,
          teamoneparty: this.teamoneparty,
          teamtwoparty: this.teamtwoparty,
        });

        this.partydata = response.data;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
   filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.heropartysize = filteredData.single["Hero Party Size"] ? filteredData.single["Hero Party Size"] : null;
      this.teamoneparty = filteredData.single["Team One Party"] ? filteredData.single["Team One Party"] : null;
      this.teamtwoparty = filteredData.single["Team Two Party"] ? filteredData.single["Team Two Party"] : null;
      
      this.partydata = null;
      this.getData();
    },
    getStackName(ally_combo, enemy_combo){
      return "temp";
    }
  }
}
</script>