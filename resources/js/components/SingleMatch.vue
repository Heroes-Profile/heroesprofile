<template>
  <div class="match-page">
    <div v-if="data" class=" mx-auto">
      
<div class="" :style="`background-size: cover; background-position:center; background-image: url('/images/maps/background/header-${data.game_map.sanitized_map_name}.jpg')`">
<div class="text-center pt-4">
       <!-- <div>
          <span>Match Scores</span>
          <span>Talents</span>
          <span>Experience</span>
          <span>MMR</span>
          <span>Advanced Stats</span>
        </div>-->


        <div class="mb-4">
          <h1 class="max-md:text-[1.5em]">{{ formatDate(data.game_date) }}</h1>
        </div>
        <div class="w-full max-w-[1000px] bg-blue rounded flex justify-between gap-2 mx-auto p-4 mb-4">
          <span>{{ data.game_map.name }}</span>
          <span v-if="!esport">
            {{ data.game_type }}
          </span>
          <span v-else>
            {{ getEsportTitle() }}
          </span>
          <span>{{ data.game_length }}</span>
          <span v-if="data.downloadable || (esport && (esport == 'CCL' || esport == 'Other'))" class="link" @click="downloadReplay(data, replayid)">
            Download Replay
          </span>
        </div>
      </div>


      <div class=" mdp-10 text-center ">
        <div class="flex  justify-center max-w-[1500px] mx-auto  md:gap-10">
          <div class=" max-w-[50%]  md:max-w-[600px]">
            <group-box class="md:w-full max-sm:text-xs" :playerlink="true" :match="true" :esport="esport" :series="series" :tournament="tournament" :winnerloser="getWinnerLoser(0, data.winner)" :esportteamname="getEsportTeamName(0)" popupsize="large" :data="data.players[0]" :color="data.winner == 0 ? 'teal' : 'red'" :winner="data.winner == 0 ? true : false"></group-box>


            <div v-if="data.replay_bans && data.replay_bans.length > 0" class="mb-10">
              {{ esport ? this.data.team_names.team_one.team_name : "Team 1" }} Bans
              <div class="flex gap-2 justify-center mt-4">
                <template v-for="(item, index) in data.replay_bans[0]" :key="index">
                  <hero-image-wrapper v-if="item.hero !== 0" :hero="item.hero" :size="'big'"></hero-image-wrapper>
                </template>
              </div>
            </div>

            <div class="flex flex-wrap justify-center mb-4">
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. Account Level'" :value="getAverageValue('account_level', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Team Level'" :value="data.players[0][0].score.level" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="esport" :title="'Avg. Hero Level'" :value="getAverageValue('avg_hero_level', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Takedowns'" :value="getTakedownsValue(data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. HP MMR'" :value="getAverageValue('player_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. HP Hero MMR'" :value="getAverageValue('hero_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. HP Role MMR'" :value="getAverageValue('role_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            </div>


            <div v-if="esport && esport != 'Other'">
              Map Bans
              <div class="flex gap-2 justify-center mt-4">
                <map-image-wrapper v-if="data.map_bans.team_zero_ban_data.map_ban_one" :map="data.map_bans.team_zero_ban_data.map_ban_one" :size="'big'">
                  <image-hover-box :title="data.map_bans.team_zero_ban_data.map_ban_one.name"></image-hover-box>
                </map-image-wrapper>
                <map-image-wrapper v-if="data.map_bans.team_zero_ban_data.map_ban_two" :map="data.map_bans.team_zero_ban_data.map_ban_two" :size="'big'">
                  <image-hover-box :title="data.map_bans.team_zero_ban_data.map_ban_two.name"></image-hover-box>
                </map-image-wrapper>
              </div>
            </div>


          </div>


          <div class=" max-w-[50%]  md:max-w-[600px]">
            <group-box class="md:w-full max-sm:text-xs" :playerlink="true" :match="true" :esport="esport" :series="series" :tournament="tournament" :winnerloser="getWinnerLoser(1, data.winner)" :esportteamname="getEsportTeamName(1)" :data="data.players[1]" :color="data.winner == 1 ? 'teal' : 'red'" :winner="data.winner == 1 ? true : false"></group-box>

            <div v-if="data.replay_bans && data.replay_bans.length > 0" class="mb-10">
              {{ esport ? this.data.team_names.team_two.team_name : "Team 2" }} Bans
              <div class="flex gap-2 justify-center mt-4">
                <template v-for="(item, index) in data.replay_bans[1]" :key="index">
                  <hero-image-wrapper v-if="item.hero !== 0" :hero="item.hero" :size="'big'"></hero-image-wrapper>
                </template>              
              </div>
            </div>
            <div class="flex flex-wrap justify-center mb-4">

              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. Account Level'" :value="getAverageValue('account_level', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Team Level'" :value="data.players[1][0].score.level" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="esport" :title="'Avg. Hero Level'" :value="getAverageValue('avg_hero_level', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Takedowns'" :value="getTakedownsValue(data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. HP MMR'" :value="getAverageValue('player_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. HP Hero MMR'" :value="getAverageValue('hero_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. HP Role MMR'" :value="getAverageValue('role_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            </div>
            <div v-if="esport && esport != 'Other'" class="">
              Map Bans
              <div class="flex gap-2 justify-center mt-4">
              <map-image-wrapper v-if="data.map_bans.team_one_ban_data.map_ban_one" :map="data.map_bans.team_one_ban_data.map_ban_one" :size="'big'">
                <image-hover-box :title="data.map_bans.team_one_ban_data.map_ban_one.name"></image-hover-box>
              </map-image-wrapper>
              <map-image-wrapper v-if="data.map_bans.team_one_ban_data.map_ban_two" :map="data.map_bans.team_one_ban_data.map_ban_two" :size="'big'">
                <image-hover-box :title="data.map_bans.team_one_ban_data.map_ban_two.name"></image-hover-box>
              </map-image-wrapper>
            </div>
            </div>

          </div >
        </div>
      </div>
    </div>

      <div v-if="esport && esport != 'Other'" class="max-w-[1500px] mx-auto mb-10">
        
        <table class="min-w-[1000px] max-w-[1000px]">
          <thead>
            <tr>
              <th>
                Game ID
              </th>        
              <th>
                Round
              </th>     
              <th>
                Game
              </th> 
              <th>
                Game Map
              </th>                      
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in data.match_games" :key="index">
              <td width="25%">
                <a v-if="esport" class="link" :href="`/Esports/${esport}/Match/Single/${row.replayID}${tournament ? '?tournament=' + tournament : ''}`">{{ row.replayID }}</a>
              </td>
              <td width="25%">{{ row.round }}</td>
              <td width="25%">{{ row.game }}</td>
              <td width="25%">
                <map-image-wrapper size="small" :map="row.game_map">
                  <image-hover-box :title="row.game_map.name"></image-hover-box>
                </map-image-wrapper>
              </td>
            </tr>
          </tbody>
        </table>

      </div>

      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

      <div class="bg-lighten">
        <div class="p-10  max-w-[1500px] mx-auto  ">
          <h2 class="text-3xl font-bold py-5">Match Scores</h2>
          <p>See advanced stats below</p>
         
       
          


          <div class="ml-auto flex justify-end">
            <div class="text-center flex items-center gap-2">
              Sort By: 
              <tab-button :tab1text="'Team'" :ignoreclick="true" :tab2text="'HP Score'" @tab-click="sortCombinedPlayers" > </tab-button>
              <round-image class="mt-2"  size="small"    icon="fas fa-info"   title="info"  popupsize="large">
                <slot>
                  <div>
                    <p class="max-sm:text-xs">Heroes Profile Score is a match based analysis ranking showing how a player performed in the match compared to other players in the same match.  100 would be a perfect match with most MVPs hovering between 70-75.</p>
                  </div>
                </slot>
              </round-image>
            </div>
          </div>  
          


          <template v-for="(item, index) in combinedPlayers" :key="index">
            
            <div>
              <a class="flex flex-wrap items-end my-5 w-full justify-evenly"  :href="matchScorePlayerURL(item)">
                <hero-image-wrapper :size="'big'" :hero="item.hero" class="mr-2"></hero-image-wrapper>
                <div>
                  <div class="flex flex-wrap justify-between flex-1">
                    <span> {{ item.battletag }}</span> 
                    <span>Heroes Profile Rating: {{ item.total_rank }}</span>
                  </div>
                  <div class="flex flex-wrap md:space-x-9 items-between w-full flex-1 ">
                    <div class="flex flex-1">
                      <stat-box class="min-w-[8em]" :title="'Kills'" :value="item.score.kills" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box class="min-w-[8em]" :title="'Takedowns'" :value="item.score.takedowns" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box class="min-w-[8em]" :title="'Deaths'" :value="item.score.deaths" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                    </div>
                    <div class="flex flex-1">
                      <stat-box class="min-w-[8em]" :title="'Siege Dmg.'" :value="item.score.siege_damage" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box class="min-w-[8em]" :title="'Hero Dmg.'" :value="item.score.hero_damage" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box class="min-w-[8em]" :title="'Healing'" :value="item.score.total_healing" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                    </div>
                    <div class="flex flex-1">
                      <stat-box class="min-w-[8em]" :title="'Dmg. Taken'" :value="item.score.damage_taken ? item.score.damage_taken : 0 " :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box class="min-w-[8em]" :title="'Exp. Con.'" :value="item.score.experience_contribution" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                    </div>
                  </div>

                </div>
              </a>
            </div>
          </template>
        </div>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>
      <div v-if="data.draft_order && data.draft_order.length > 0" class="md:p-10 text-center max-w-[2000px] mx-auto max-sm:text-xs">
        Draft Order

        <table class="md:min-w-[600px] max-w-[600px] mt-2">
          <thead>
            <tr>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Hero
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Draft Order
              </th>            
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Type
              </th>                
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in data.draft_order" :key="index" class="">      
              <td width="25%">
                <div v-if="row.hero != 'No Pick'" class="flex gap-4 items-center">
                  <hero-image-wrapper :size="'medium'" :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
                </div>
                <span v-else>No Pick</span>
              </td>
              <td width="25%">{{ row.pick_number + 1 }}</td>
              <td width="25%">{{ row.type == 0 ? "Ban" : "Pick" }}</td>
            </tr>
          </tbody>
        </table>
      </div>



      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>
      <div class="p-10  max-w-[1500px] mx-auto">
       <h2 class="text-3xl font-bold py-5">Talents</h2>
       <div class="flex flex-wrap gap-20 justify-around">
        <div class="">
          <div class="w-full  mb-10" v-for="(item, index) in data.players[0]" :key="index">
            
            <a class="flex  w-full"  :href="getPlayerProfileLink(item, true)">
              <hero-image-wrapper class="mr-5" :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <div>
                {{ item.battletag }} - {{ item.hero.name }}
                <div class="flex  items-center gap-2 mb-2">

                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_one"></talent-image-wrapper>
                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_four"></talent-image-wrapper>
                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_seven"></talent-image-wrapper>
                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_ten"></talent-image-wrapper>
                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_thirteen"></talent-image-wrapper>
                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_sixteen"></talent-image-wrapper>
                  <talent-image-wrapper :size="'medium'" :talent="item.talents.level_twenty"></talent-image-wrapper>

                  
                </div>
              </div>
            </a>
            <div class="text-xs text-right">
             {{ this.getCopyBuildToGame(item.talents.level_one, item.talents.level_four, item.talents.level_seven, item.talents.level_ten, item.talents.level_thirteen, item.talents.level_sixteen, item.talents.level_twenty, item.hero) }}
             <custom-button class="text-xs" @click="copyToClipboard(item)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
           </div>
         </div>
         
       </div>
       <div class="">
        <div class="w-full  mb-10" v-for="(item, index) in data.players[1]" :key="index">
          
          <a class="flex  w-full"  :href="item.check ? 'javascript:void(0)' : esport ? '/Esports/' + esport + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/Hero/' + item.hero.name">
            <hero-image-wrapper class="mr-5" :size="'big'" :hero="item.hero"></hero-image-wrapper>
            <div>
              {{ item.battletag }} - {{ item.hero.name }}
              <div class="flex  items-center gap-2 mb-2">
                

                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_one"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_four"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_seven"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_ten"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_thirteen"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_sixteen"></talent-image-wrapper>
                <talent-image-wrapper :size="'medium'" :talent="item.talents.level_twenty"></talent-image-wrapper>

                
              </div>
            </div>
          </a>
          <div class="text-xs text-right">
           {{ this.getCopyBuildToGame(item.talents.level_one, item.talents.level_four, item.talents.level_seven, item.talents.level_ten, item.talents.level_thirteen, item.talents.level_sixteen, item.talents.level_twenty, item.hero) }}
           <custom-button class="text-xs" @click="copyToClipboard(item)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
         </div>
       </div>

       
     </div>
   </div>

 </div>
  <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false"></dynamic-banner-ad>

 <div v-if="data.experience_breakdown" class="bg-lighten p-10 text-center">
  <div class="flex flex-wrap justify-center max-w-[2000px] mx-auto">
    <div >
      <dual-line-chart :data="data.experience_breakdown" :winner="data.winner"></dual-line-chart>
    </div>
  </div>
</div>


<div v-if="!esport" class=" max-sm:text-sm max-w-[1500px] mx-auto my-5">

  Team 1 Advanced HP MMR data

  <div  ref="tablecontainer" class="table-container w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" " >


  <table :class="['responsive-table', 'relative', { winner: data.players[0][0].winner === 1, loser: data.players[0][0].winner !== 1 }]">
    <thead>
      <tr >
        <td class="color-cell bg-black " colspan="2"></td>
        <td class="color-cell bg-yellow" colspan="3">Pre-Match</td>
        <td class="color-cell bg-blue" colspan="3">Post-Match</td>
      </tr>
      <tr>
        <td >Player</td>
        <td >Hero</td>
        <td >HP Player MMR</td>
        <td >HP Hero MMR</td>
        <td >HP Role MMR</td>
        <td >HP Player MMR</td>
        <td >HP Hero MMR</td>
        <td >HP Role MMR</td>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in data.players[0]" :key="index">
        <td class="bg-blue text-white border-white border"><a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/MMR'">{{ item.battletag }}</a></td>
        <td>{{ item.hero.name }}</td>
        <td>{{ Math.round(item.player_mmr - item.player_change)  }}</td>
        <td>{{ Math.round(item.hero_mmr - item.hero_change) }}</td>
        <td>{{ Math.round(item.role_mmr - item.role_change)}}</td>
        <td>{{ item.player_mmr }} ({{ item.player_change }})</td>
        <td>{{ item.hero_mmr }} ({{ item.hero_change }})</td>
        <td>{{ item.role_mmr }} ({{ item.role_change }})</td>
      </tr>
      <tr>
        <td class="bg-blue border-white border"></td>
        <td class="color-cell bg-blue text-white">Average</td>
        <td class="color-cell bg-blue text-white">{{ getAverageValue('prev_player_mmr', data.players[0]) }}</td>
        <td class="color-cell bg-blue text-white">{{ getAverageValue('prev_hero_mmr', data.players[0]) }}</td>
        <td class="color-cell bg-blue text-white">{{ getAverageValue('prev_role_mmr', data.players[0]) }}</td>
        <td class="color-cell bg-blue text-white">{{ getAverageValue('player_mmr', data.players[0]) }}</td>
        <td class="color-cell bg-blue text-white">{{ getAverageValue('hero_mmr', data.players[0]) }}</td>
        <td class="color-cell bg-blue text-white">{{ getAverageValue('role_mmr', data.players[0]) }}</td>
      </tr>
    </tbody>
  </table>
</div>
</div>


<div  v-if="!esport" class="  max-sm:text-sm max-w-[1500px] mx-auto my-5">

  Team 2 Advanced HP MMR data

  <div  ref="tablecontainer" class="table-container w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" " >


  <table :class="['responsive-table', 'relative', { winner: data.players[1][0].winner === 1, loser: data.players[1][0].winner !== 1 }]">
    <thead>
      <tr>
       <td class="color-cell bg-black " colspan="2"></td>
        <td class="color-cell bg-yellow" colspan="3">Pre-Match</td>
        <td class="color-cell bg-blue" colspan="3">Post-Match</td>
      </tr>
      <tr>
        <td>Player</td>
        <td>Hero</td>
        <td>HP Player MMR</td>
        <td>HP Hero MMR</td>
        <td>HP Role MMR</td>
        <td>HP Player MMR</td>
        <td>HP Hero MMR</td>
        <td>HP Role MMR</td>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in data.players[1]" :key="index">
        <td class="bg-blue text-white border-white border"><a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/MMR'">{{ item.battletag }}</a></td>
        <td>{{ item.hero.name }}</td>
        <td>{{ Math.round(item.player_mmr - item.player_change)  }}</td>
        <td>{{ Math.round(item.hero_mmr - item.hero_change) }}</td>
        <td>{{ Math.round(item.role_mmr - item.role_change)}}</td>
        <td>{{ item.player_mmr }} ({{ item.player_change }})</td>
        <td>{{ item.hero_mmr }} ({{ item.hero_change }})</td>
        <td>{{ item.role_mmr }} ({{ item.role_change }})</td>
      </tr>
      <tr>
        <td class="bg-blue text-white border-white border"></td>
        <td class="color-cell bg-blue text-white border-white border">Average</td>
        <td class="color-cell bg-blue text-white border-white border">{{ getAverageValue('prev_player_mmr', data.players[1]) }}</td>
        <td class="color-cell bg-blue text-white border-white border">{{ getAverageValue('prev_hero_mmr', data.players[1]) }}</td>
        <td class="color-cell bg-blue text-white border-white border">{{ getAverageValue('prev_role_mmr', data.players[1]) }}</td>
        <td class="color-cell bg-blue text-white border-white border">{{ getAverageValue('player_mmr', data.players[1]) }}</td>
        <td class="color-cell bg-blue text-white border-white border">{{ getAverageValue('hero_mmr', data.players[1]) }}</td>
        <td class="color-cell bg-blue text-white border-white border">{{ getAverageValue('role_mmr', data.players[1]) }}</td>
      </tr>
    </tbody>
  </table>
</div>
</div>

<dynamic-banner-ad :patreon-user="patreonUser" :index="5" :mobile-override="false"></dynamic-banner-ad>

<div class="max-sm:text-sm max-w-[1500px] mx-auto my-5">
  Team 1 Advanced Stats

  <div  ref="tablecontainer" class="table-container w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" " v-for="(section, sectionIndex) in sections" :key="sectionIndex">

 
  <table :class="['responsive-table', 'relative', { winner: data.players[0][0].winner === 1, loser: data.players[0][0].winner !== 1 }]" >
    <thead>
      <tr>
        <td >{{ section.title }}</td>
        <td v-for="(player, playerIndex) in data.players[0]" :key="playerIndex">
          <a :href="getPlayerProfileLink(player, true)">{{ player.battletag }}</a>
        </td>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, rowIndex) in section.rows" :key="rowIndex">
      <td class="bg-blue text-white border-white border">{{ row.label }}</td>
      <td v-for="(player, playerIndex) in data.players[0]" :key="playerIndex">{{ formatValue(player.score[row.key]) }}</td>
    </tr>
  </tbody>
</table>
</div>
</div>
<div class="max-sm:text-sm max-w-[1500px] mx-auto my-5">
<dynamic-banner-ad :patreon-user="patreonUser" :index="6" :mobile-override="false"></dynamic-banner-ad>
 
  Team 2 Advanced Stats
  <div  ref="tablecontainer" class="table-container w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" " v-for="(section, sectionIndex) in sections" :key="sectionIndex">
  <table :id="'responsive-table-' +sectionIndex" :ref="'responsivetable'+sectionIndex" :class="['responsive-table', 'relative', { winner: data.players[1][0].winner === 1, loser: data.players[1][0].winner !== 1 }]" >
    <thead>
      <tr>
        <td class="bg-blue text-white border-white border">{{ section.title }}</td>
        <td
        v-for="(player, playerIndex) in data.players[1]"
        :key="playerIndex"
        
        >
        <a :href="getPlayerProfileLink(player, true)">{{ player.battletag }}</a>
      </td>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, rowIndex) in section.rows" :key="rowIndex">
      <td class="bg-blue text-white border-white border">{{ row.label }}</td>
      <td v-for="(player, playerIndex) in data.players[1]" :key="playerIndex">{{ formatValue(player.score[row.key]) }}</td>
    </tr>
  </tbody>
</table>
</div>
</div>



</div>
<div v-else-if="isLoading">
  <loading-component v-if="esport" @cancel-request="cancelAxiosRequest" :overrideimage="getLoadingImage()"></loading-component>
  <loading-component v-else @cancel-request="cancelAxiosRequest"></loading-component>
</div>
</div>

</template>

<script>
  import moment from 'moment-timezone';

  export default {
    name: 'SingleMatch',
    components: {
    },
    props: {
      esport: String,
      series: String,
      replayid: Number,
      user: Object,
      patreonUser: Boolean,
      tournament: String,
    },
    data(){
      return {
        windowWidth: window.innerWidth,
        cancelTokenSource: null,
        isLoading: false,
        userTimezone: moment.tz.guess(),
        data: null,
        combinedPlayers: null,
        showTooltip: false,
        sortDirectionTeam: 'desc',
        sortDirectionHpScore: 'desc',
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
          title: 'Defense/Healing',
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
      this.getData();
    },
    mounted() {
    },
    computed: {
    },
    watch: {
      combinedPlayers(){
      }
    },
    methods: {
      resizeTables(){
        if(this.$el && this.$el.querySelectorAll('table')){
          const tables = this.$el.querySelectorAll('table');
          tables.forEach(table => {
            var newTableWidth = this.windowWidth /table.clientWidth;
            var tablewrapper = table.closest('.table-container');
            if(tablewrapper){
            table.style.transformOrigin = 'top left';
              table.style.transform = `scale(${newTableWidth})`;
              tablewrapper.style.height = (table.clientHeight * newTableWidth) + 'px';

            }
          })
        }
      },
     async getData(){
      this.isLoading = true;
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$axios.post("/api/v1/match/single", {
          esport: this.esport,
          replayID: this.replayid,
          user: this.user,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data; 
        this.combinePlayerArrays();
      }catch(error){
      //Do something here
      }finally {
        this.cancelTokenSource = null; // Reset cancel token source
        this.isLoading = false;
        this.$nextTick(() => {
          if(this.windowWidth < 1500){
            this.resizeTables();
          }
        
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    getAverageValue(type, data) {
      if (!data || data.length === 0) {
        return 0;
      }

      const filteredData = data.filter(obj => obj.blizz_id !== null);

      let sum;
      if (type === "prev_player_mmr") {
        sum = filteredData.reduce((acc, curr) => acc + (curr.player_mmr - curr.player_change || 0), 0);
      } else if (type === "prev_hero_mmr") {
        sum = filteredData.reduce((acc, curr) => acc + (curr.hero_mmr - curr.hero_change || 0), 0);
      } else if (type === "prev_role_mmr") {
        sum = filteredData.reduce((acc, curr) => acc + (curr.role_mmr - curr.role_change || 0), 0);
      } else {
        sum = filteredData.reduce((acc, curr) => acc + (curr[type] || 0), 0);
      }

      const length = filteredData.length;
      
      const average = sum / length;
      
      return average.toFixed(0);
    },

    getEsportTeamName(team){
      if(!this.esport){
        return null;
      }
      let teamName = "";
        if(team == 0){
          teamName = this.data.team_names.team_one.team_name;
        }else{
          teamName = this.data.team_names.team_two.team_name;
        }
      return teamName;
    },

    getWinnerLoser(team, winner){


      if(this.esport){
        let first_pick = this.data.first_pick;

        if(winner == 0 && team == 0){
          if(first_pick == team){
            return "First Pick - Winner";
          }else{
            return "Map Pick - Winner";
          }
        }else if(winner == 1 && team == 0){
          if(first_pick == team){
            return "First Pick - Loser";
          }else{
            return "Map Pick - Loser";
          }
        }else if(winner == 0 && team == 1){
          if(first_pick == team){
            return "First Pick - Loser";
          }else{
            return "Map Pick - Loser";
          }
        }else if(winner == 1 && team == 1){
          if(first_pick == team){
            return "First Pick - Winner";
          }else{
            return "Map Pick - Winner";
          }
        }
      }


      if(winner == 0 && team == 0){
        return "Team 1 Winner";
      }else if(winner == 1 && team == 0){
        return "Team 1 Loser";
      }else if(winner == 1 && team == 1){
        return "Team 2 Winner";
      }else if(winner == 0 && team == 1){
        return "Team 2 Loser";
      }


    },
    combinePlayerArrays(){
      this.combinedPlayers = [...this.data.players[0], ...this.data.players[1]];
    },
    sortCombinedPlayers(side) {
      let type = "team";
      if(side == 'left'){
        type = "team";
      }
      else{
        type = "total_rank";
      }
      this.combinedPlayers.sort((a, b) => {
        if(type == "total_rank"){
          if (this.sortDirectionHpScore === 'desc') {
            return b.total_rank - a.total_rank;
          }else{
            return a.total_rank - b.total_rank;
          }
        }else{
          if (this.sortDirectionHpScore === 'desc') {
            return b.team - a.team;
          }else{
            return a.team - b.team;
          }
        }
      });

      if (this.sortDirectionHpScore === 'desc') {
        this.sortDirectionHpScore = this.sortDirectionHpScore === 'desc' ? 'asc' : 'desc';
      }else{
        this.sortDirectionHpScore = this.sortDirectionHpScore === 'desc' ? 'asc' : 'desc';
      }
    },
    getCopyBuildToGame(level_one, level_four, level_seven, level_ten, level_thirteen, level_sixteen, level_twenty, hero) {
      return "[T" + 
      (level_one ? level_one.sort : '0') + 
      (level_four ? level_four.sort : '0') + 
      (level_seven ? level_seven.sort : '0') + 
      (level_ten ? level_ten.sort : '0') + 
      (level_thirteen ? level_thirteen.sort : '0') + 
      (level_sixteen ? level_sixteen.sort : '0') + 
      (level_twenty ? level_twenty.sort : '0') +
      "," + hero.build_copy_name + "]"
    },
    copyToClipboard(item) {
      const textToCopy = this.getCopyBuildToGame(item.talents.level_one, item.talents.level_four, item.talents.level_seven, item.talents.level_ten, item.talents.level_thirteen, item.talents.level_sixteen, item.talents.level_twenty, item.hero);
      navigator.clipboard.writeText(textToCopy).then(function() {
        
      }).catch(function(err) {
        
      });
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
    formatValue(value){
      return value ? value.toLocaleString('en-US') : 0;
    },
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }
    },
    async downloadReplay(data, replayID){
      if(this.esport){
        if(this.esport == "CCL"){
          window.location = `https://storage.googleapis.com/heroesprofile-ccl/${replayID}.StormReplay`;
        }else if(this.esport == "Other"){
          window.location = `https://storage.googleapis.com/heroesprofile-esport-other/${replayID}.StormReplay`;
        }
      }else{
        window.location = `https://api.heroesprofile.com/openApi/Replay/Download?replayID=${replayID}`;
      }
    },
    getTakedownsValue(data){
      let totalKills = 0;

      for (let i = 0; i < data.length; i++) {
          totalKills += data[i].score.kills;
      }

      return totalKills;
    },
    matchScorePlayerURL(item){
      if(item.check){
        return 'javascript:void(0)';
      }
      if(this.esport){
        if(this.series){
          return'/Esports/' + this.esport + "/" + this.series + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name;
        }

        if(this.esport == "HeroesInternational"){
          return '/Esports/' + this.esport + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name + "?tournament=" + this.tournament;
        }
        return '/Esports/' + this.esport + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name;
      }
      return '/Player/' + item.battletag + '/' + item.blizz_id + '/' + this.data.region + '/Hero/' + item.hero.name;
    },

    getPlayerProfileLink(item, heroPage){
      if(item.check){
        return 'javascript:void(0)';
      }
      var url = "";
      if(this.esport){
        if(this.esport == "Other"){
          url = '/Esports/' + this.esport + '/' + this.series + '/Player/' + item.battletag + '/' + item.blizz_id;

          if(heroPage){
            url = url + '/Hero/' + item.hero.name;
          }
          return url;
    
        }else{
          url = '/Esports/' + this.esport + '/Player/' + item.battletag + '/' + item.blizz_id;

          if(heroPage){
            url = url + '/Hero/' + item.hero.name;
          }

          if(this.esport == "HeroesInternational"){
            url = url + '?tournament=' + this.tournament;
          }
          return url;
        }
      }
      url = '/Player/' + item.battletag + '/' + item.blizz_id + '/' + this.data.region;
      if(heroPage){
        url = url + '/Hero/' + item.hero.name;
      }
      return url;
    },
    getEsportTitle(){
      if(this.series){
        return this.series;
      }else if(this.esport == "HeroesInternational"){
        if(this.tournament == "main"){
          return "Heroes International";
        }
        if(this.tournament == "nationscup"){
          return "Heroes International Nations Cup";
        }
      }

      return this.esport;
    },
  }
}
</script>