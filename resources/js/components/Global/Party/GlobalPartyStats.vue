s<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Global Party Statistics'"></page-heading>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :herolevelinput="herolevel"
      :heroinput="getHeroID()"
      :gamemapinput="gamemap"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :teamonepartyinput="teamoneparty"      
      :teamtwopartyinput="teamtwoparty"      

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
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="partydata" class="max-w-[1500px] mx-auto">
      <div class="flex">
        <div class="w-auto inline-block m-1 ml-auto">
          <h2 class="bg-blue rounded-t p-2 text-sm text-center uppercase">Legend</h2>
          <div class=" bg-gray-light rounded-b p-5 gap-5 justify-center">
            <span class="text-black block">Group of 1 <i class="fas fa-user solo"></i></span>
            <span class="text-black block">Group of 2 <i class="fas fa-user double"></i><i class="fas fa-user double"></i></span>
            <span class="text-black block">Group of 3 <i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></span>
            <span class="text-black block">Group of 4 <i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></span>
            <span class="text-black block">Group of 5 <i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></span>
          </div>
        </div>
      </div>


      <div class=" mx-auto mb-10" id="5 Solo players">
        <h3 class="stack-header">5 Solo Players<div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> vs. </h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr >
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr  v-if="partydata.solo" v-for="row in partydata.solo" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 md:px-3 md:w-[35%]">
                <div class="flex flex-wrap max-md:flex-col max-md:items-start py">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10 flex-1">
                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex max-md:flex-col" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="1 Double Stack">
       <h3 class="stack-header">1 Double Stack <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> vs. </h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-if="partydata.double" v-for="row in partydata.double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 w-[35%]">
                <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10flex-1">
                    <span class="flex" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="2 Double Stack">
        <h3 class="stack-header">2 Double Stack vs.<div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> vs. </h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-if="partydata.double_double" v-for="row in partydata.double_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 w-[35%]">
                <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10 flex-1">
                    <span class="flex" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="1 Tripe 2 Solo">
        <h3 class="stack-header">1 Triple Stack and 2 Solos vs.<div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>vs.</h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-if="partydata.triple" v-for="row in partydata.triple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 w-[35%]">
                <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10 flex-1">
                    <span class="flex" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="1 Tripe 1 Double">
        <h3 class="stack-header">1 Triple Stack and 1 Double Stack vs.<div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>vs.</h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-if="partydata.triple_double" v-for="row in partydata.triple_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 w-[35%]">
                <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10 flex-1">
                    <span class="flex" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>


      <div class="container mx-auto mb-10" id="1 Quad 1 Solo">
        <h3 class="stack-header">1 Quadruple Stack and 1 Solo vs.<div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>vs.</h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-if="partydata.quadruple" v-for="row in partydata.quadruple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 w-[35%]">
                <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10 flex-1">
                    <span class="flex" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>



      <div class="container mx-auto " id="5 Player">
        <h3 class="stack-header">5 player Stack vs. <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>vs.</h3>
        <table class="max-sm:text-xs">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Stack
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Win Rate %
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Wins
              </th>                
              <th class="py-2 px-3  text-left text-sm leading-4 tracking-wider">
                Losses
              </th>                                 
            </tr>
          </thead>
          <tbody>
            <tr v-if="partydata.quintuple" v-for="row in partydata.quintuple" :key="(row.ally_combo + '|' + row.enemy_combo)">
              <td class="py-2 px-3 w-[35%]">
                <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                  <div class="flex-1">
                    {{ row.stack_size_name }}
                  </div>
                  <div class="md:mx-10 flex-1">
                    <span class="flex" v-if="row.stack_size_name == '5 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>
                    <span class="flex" v-if="row.stack_size_name == '1 Double, 3 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '2 Double, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 2 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Triple, 1 Double'">
                      <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 Quad, 1 Solo'">
                      <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div>
                    </span>

                    <span class="flex" v-if="row.stack_size_name == '1 team of 5'">
                      <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div>
                    </span>
                  </div>

                </div>
              </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
            </tr>
            <tr v-else>
              <td colspan="4" class="text-center">No Data</td>
            </tr>
          </tbody>
        </table>  
      </div>

    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
    <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
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
    advancedfiltering: Boolean,
    patreonUser: Boolean,
    urlparameters: Object,
    heroes: Object,

  },
  data(){
    return {
      dataError: false,
      isLoading: false,
      infoText: "Party win rates based on differing increments, stat types, game type, rank, and more. The hero filter allows you to see party data for games that only contained that hero.",
      partydata: null,
      cancelTokenSource: null,
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

    if(this.urlparameters){
      this.setURLParameters();
    }

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
      this.dataError = false;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
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
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.partydata = response.data;
      }catch(error){
        this.dataError = true;
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
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      
      //this.heropartysize = filteredData.single["Hero Party Size"] ? filteredData.single["Hero Party Size"] : null;
      
      this.teamoneparty = filteredData.single["Team One Party"] ? filteredData.single["Team One Party"] : null;
      this.teamtwoparty = filteredData.single["Team Two Party"] ? filteredData.single["Team Two Party"] : null;
      

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

      if(this.hero){
        queryString += `&hero=${this.hero}`;
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

      if(this.teamoneparty){
        queryString += `&teamoneparty=${this.teamoneparty}`;
      }

      if(this.teamtwoparty){
        queryString += `&teamtwoparty=${this.teamtwoparty}`;
      }

      const currentUrl = window.location.href;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}${queryString}`);

      this.partydata = null;
      this.getData();
    },
    getStackName(ally_combo, enemy_combo){
      return "temp";
    },
    getHeroID(){
      if(this.hero){
        return this.heroes.find(hero => hero.name === this.hero).id
      }
      return null;
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

      if(this.urlparameters["hero"]){
        this.hero = this.urlparameters["hero"];
      }

      if(this.urlparameters["game_map"]){
        this.gamemap = this.urlparameters["game_map"].split(',');
      }

      if (this.urlparameters["league_tier"]) {
        this.playerrank = this.urlparameters["league_tier"]
          .split(',')
          .map(tierName => {
              const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
              const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
              return tier?.code;
          });
      }

      if (this.urlparameters["hero_league_tier"]) {
        this.herorank = this.urlparameters["hero_league_tier"]
        .split(',')
        .map(tierName => {
            const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
            const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
            return tier?.code;
        });
      }

      if (this.urlparameters["role_league_tier"]) {
        this.rolerank = this.urlparameters["role_league_tier"]
        .split(',')
        .map(tierName => {
            const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
            const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
            return tier?.code;
        });
      }

      if(this.urlparameters["teamoneparty"]){
        this.teamoneparty = this.urlparameters["teamoneparty"];
      }

      if(this.urlparameters["teamtwoparty"]){
        this.teamtwoparty = this.urlparameters["teamtwoparty"];
      }

    },
  }
}
</script>