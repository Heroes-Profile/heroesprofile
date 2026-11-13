<template>
  <div>
    <div class="flex items-start gap-6 mb-8 mt-2">
      <div v-if="partyWinRates.length" class="flex-1 min-w-0">
        <h2 class="text-lg font-semibold mb-3">{{ $t('Party Size Win Rates') }}</h2>
        <div v-for="row in partyWinRates" :key="row.label" class="mb-3">
          <div class="text-sm mb-1">
            <span class="font-medium">{{ $t(row.label) }}</span>
            <span class="ml-4 text-subtext">{{ $t('Total Games: :value', { value: (row.wins + row.losses).toLocaleString('en-US') }) }}</span>
          </div>
          <div class="flex items-center w-full h-7 rounded overflow-hidden bg-surface-3">
            <div :style="{ width: row.win_rate.toFixed(2) + '%', backgroundColor: row.color }" class="h-full flex items-center pl-2">
              <span class="text-white text-xs font-semibold whitespace-nowrap">{{ row.win_rate.toFixed(2) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <div class="w-auto shrink-0 m-1">
        <h2 class="text-lg font-semibold mb-3">{{ $t('Legend') }}</h2>
        <div class="bg-surface-1 rounded p-5 flex flex-col gap-1">
          <span class="block">{{ $t('Group of 1') }} <i class="fas fa-user solo"></i></span>
          <span class="block">{{ $t('Group of 2') }} <i class="fas fa-user double"></i><i class="fas fa-user double"></i></span>
          <span class="block">{{ $t('Group of 3') }} <i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></span>
          <span class="block">{{ $t('Group of 4') }} <i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></span>
          <span class="block">{{ $t('Group of 5') }} <i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></span>
        </div>
      </div>
    </div>


    <panel-box :divider="false" class="mb-6" id="5 Solo players">
      <h3 class="stack-header">{{ $t(':teamlabel: 5 Solo Players', { teamlabel: $t(teamlabel) }) }}<div class="stack-wrapper "><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr >
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr  v-if="partydata.solo" v-for="row in partydata.solo" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 md:px-3 md:w-[35%]">
              <div class="flex flex-wrap max-md:flex-col max-md:items-center justify-center">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>


    <panel-box :divider="false" class="mb-6" id="1 Double Stack">
     <h3 class="stack-header">{{ $t(':teamlabel: 1 Double Stack', { teamlabel: $t(teamlabel) }) }} <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-if="partydata.double" v-for="row in partydata.double" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 md:px-3 md:w-[35%]">
              <div class="flex flex-wrap max-md:flex-col max-md:items-center justify-center">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            </td>              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
            <td class="py-2 px-3 ">{{ row.wins.toLocaleString('en-US') }}</td>
            <td class="py-2 px-3 ">{{ row.losses.toLocaleString('en-US') }}</td>
          </tr>
          <tr v-else>
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>


    <panel-box :divider="false" class="mb-6" id="2 Double Stack">
      <h3 class="stack-header">{{ $t(':teamlabel: 2 Double Stack', { teamlabel: $t(teamlabel) }) }} <div class="stack-wrapper "><div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-if="partydata.double_double" v-for="row in partydata.double_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 px-3 w-[35%]">
              <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>


    <panel-box :divider="false" class="mb-6" id="1 Tripe 2 Solo">
      <h3 class="stack-header">{{ $t(':teamlabel: 1 Triple Stack and 2 Solos', { teamlabel: $t(teamlabel) }) }} <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div><div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-if="partydata.triple" v-for="row in partydata.triple" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 px-3 w-[35%]">
              <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>


    <panel-box :divider="false" class="mb-6" id="1 Tripe 1 Double">
      <h3 class="stack-header">{{ $t(':teamlabel: 1 Triple Stack and 1 Double Stack', { teamlabel: $t(teamlabel) }) }} <div class="stack-wrapper "><div class="stack-single stack-triple"><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i><i class="fas fa-user triple"></i></div> <div class="stack-single stack-double"><i class="fas fa-user double"></i><i class="fas fa-user double"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-if="partydata.triple_double" v-for="row in partydata.triple_double" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 px-3 w-[35%]">
              <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>


    <panel-box :divider="false" class="mb-6" id="1 Quad 1 Solo">
      <h3 class="stack-header">{{ $t(':teamlabel: 1 Quadruple Stack and 1 Solo', { teamlabel: $t(teamlabel) }) }} <div class="stack-wrapper "><div class="stack-single stack-quad"><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i><i class="fas fa-user quadruple"></i></div> <div class="stack-single stack-solo"><i class="fas fa-user solo"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-if="partydata.quadruple" v-for="row in partydata.quadruple" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 px-3 w-[35%]">
              <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>



    <panel-box :divider="false" class="mb-6" id="5 Player">
      <h3 class="stack-header">{{ $t(':teamlabel: 5 player Stack', { teamlabel: $t(teamlabel) }) }} <div class="stack-wrapper "><div class="stack-single stack-quint"><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i><i class="fas fa-user quintuple"></i></div></div> {{ $t('vs.') }}</h3>
      <table class="min-w-0 w-full responsive-table max-sm:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Enemy Team') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Win Rate %') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Wins') }}
            </th>                
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ $t('Losses') }}
            </th>                                 
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-if="partydata.quintuple" v-for="row in partydata.quintuple" :key="(row.ally_combo + '|' + row.enemy_combo)">
            <td class="py-2 px-3 w-[35%]">
              <div class="flex flex-wrap py max-md:flex-col max-md:items-start">
                <div class="flex-1">
                  {{ $t(row.stack_size_name) }}
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
            <td colspan="4" class="text-center">{{ $t('No Data') }}</td>
          </tr>
        </tbody>
      </table>  
    </panel-box>
  </div>
</template>

<script>
export default {
  name: 'PartyStatsDisplay',
  props: {
    partydata: {
      type: Object,
      required: true
    },
    teamlabel: {
      type: String,
      default: 'Team'
    },
  },
  computed: {
    partyWinRates() {
      if (!this.partydata) return [];
      const groups = [
        { label: 'Solo',        keys: ['solo'],                          color: '#213d7a' },
        { label: 'Two Stack',   keys: ['double', 'double_double'],       color: '#008b8b' },
        { label: 'Three Stack', keys: ['triple', 'triple_double'],       color: '#b33616' },
        { label: 'Four Stack',  keys: ['quadruple'],                     color: '#d98c40' },
        { label: 'Five Stack',  keys: ['quintuple'],                     color: '#213d7a' },
      ];
      return groups.map(g => {
        let wins = 0, losses = 0;
        g.keys.forEach(key => {
          if (this.partydata[key]) {
            const rows = Array.isArray(this.partydata[key])
              ? this.partydata[key]
              : Object.values(this.partydata[key]);
            rows.forEach(row => {
              wins += row.wins;
              losses += row.losses;
            });
          }
        });
        const total = wins + losses;
        return { label: g.label, color: g.color, wins, losses, win_rate: total > 0 ? (wins / total) * 100 : 0 };
      }).filter(r => (r.wins + r.losses) > 0);
    },
  },
}
</script>
