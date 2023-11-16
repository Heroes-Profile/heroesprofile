<template>
  <div class="match-page">
    <div v-if="data" class=" mx-auto">
      <div class="text-center">
       <!-- <div>
          <span>Match Scores</span>
          <span>Talents</span>
          <span>Experience</span>
          <span>MMR</span>
          <span>Advanced Stats</span>
        </div>-->

        <div class="mb-4">
          <h1>{{ formatDate(data.game_date) }}</h1>
        </div>

        <div class="w-full max-w-[1500px] bg-blue rounded flex justify-between gap-2 mx-auto p-4 mb-4">
          <span>{{ data.game_map.name }}</span>
          <span>{{ data.game_type }}</span>
          <span>{{ data.game_length }}</span>
        </div>
      </div>


      <div class=" p-10 text-center">
        <div class="flex flex-wrap justify-center max-w-[2000px] mx-auto gap-10">
          <div>
            <group-box class="w-full" :playerlink="true" :match="true" :esport="esport" :text="getTeamText(0, data.winner)" :data="data.players[0]" :color="data.winner == 0 ? 'teal' : 'red'"></group-box>


            <div v-if="data.replay_bans" class="flex flex-wrap justify-center">
              {{ esport ? this.data.team_names.team_one.team_name : "Team 1" }} Bans
              <hero-image-wrapper v-for="(item, index) in data.replay_bans[0]" :key="index" :hero="item.hero" :size="'big'"></hero-image-wrapper>
            </div>

            <div class="flex flex-wrap justify-center">
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Account Level'" :value="getAverageValue('account_level', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Team Level'" :value="data.players[0][0].score.level" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="esport" :title="'Avg. Hero Level'" :value="getAverageValue('avg_hero_level', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Takedowns'" :value="data.players[0][0].score.takedowns" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Average MMR'" :value="getAverageValue('player_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Average Hero MMR'" :value="getAverageValue('hero_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Average Role MMR'" :value="getAverageValue('role_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            </div>


            <div v-if="esport">
              Map Bans
              <map-image-wrapper v-if="data.map_bans.team_zero_ban_data.map_ban_one" :map="data.map_bans.team_zero_ban_data.map_ban_one" :size="'big'">
                <image-hover-box :title="data.map_bans.team_zero_ban_data.map_ban_one.name"></image-hover-box>
              </map-image-wrapper>
              <map-image-wrapper v-if="data.map_bans.team_zero_ban_data.map_ban_two" :map="data.map_bans.team_zero_ban_data.map_ban_two" :size="'big'">
                <image-hover-box :title="data.map_bans.team_zero_ban_data.map_ban_two.name"></image-hover-box>
              </map-image-wrapper>
            </div>


          </div>


          <div>
            <group-box class="w-full" :playerlink="true" :match="true" :esport="esport" :text="getTeamText(1, data.winner)" :data="data.players[1]" :color="data.winner == 1 ? 'teal' : 'red'"></group-box>

            <div v-if="data.replay_bans" class="flex flex-wrap justify-center">
              {{ esport ? this.data.team_names.team_two.team_name : "Team 2" }} Bans
              <hero-image-wrapper v-for="(item, index) in data.replay_bans[1]" :key="index" :hero="item.hero" :size="'big'"></hero-image-wrapper>
            </div>
            <div class="flex flex-wrap justify-center">

              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Account Level'" :value="getAverageValue('account_level', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Team Level'" :value="data.players[1][0].score.level" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="esport" :title="'Avg. Hero Level'" :value="getAverageValue('avg_hero_level', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" :title="'Takedowns'" :value="data.players[1][0].score.takedowns" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Average MMR'" :value="getAverageValue('player_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Average Hero MMR'" :value="getAverageValue('hero_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box class="min-w-[30%]" v-if="!esport" :title="'Average Role MMR'" :value="getAverageValue('role_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            </div>
            <div v-if="esport">
              Map Bans
              <map-image-wrapper v-if="data.map_bans.team_one_ban_data.map_ban_one" :map="data.map_bans.team_one_ban_data.map_ban_one" :size="'big'">
                <image-hover-box :title="data.map_bans.team_one_ban_data.map_ban_one.name"></image-hover-box>
              </map-image-wrapper>
              <map-image-wrapper v-if="data.map_bans.team_one_ban_data.map_ban_two" :map="data.map_bans.team_one_ban_data.map_ban_two" :size="'big'">
                <image-hover-box :title="data.map_bans.team_one_ban_data.map_ban_two.name"></image-hover-box>
              </map-image-wrapper>
            </div>

          </div >
        </div>
      </div>


      <div v-if="esport" class="max-w-[2000px] mx-auto">
        
        <table class="">
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
              <td width="25%">{{ row.replayID }}</td>
              <td width="25%">{{ row.round }}</td>
              <td width="25%">{{ row.game }}</td>
              <td width="25%">
                <map-image-wrapper :map="row.game_map">
                  <image-hover-box :title="row.game_map.name"></image-hover-box>
                </map-image-wrapper>
              </td>
            </tr>
          </tbody>
        </table>

      </div>

      <div class="bg-lighten">
        <div class="p-10  max-w-[2000px] mx-auto  ">
          <h2 class="text-3xl font-bold py-5">Match Scores</h2>
          <p>See advanced stats below</p>
          <p>
            Heroes Profile Score is a match based analysis ranking showing how a player performed in the match compared to other players in the same match.  100 would be a perfect match with most MVPs hovering between 70-75.
          </p>

          

          <div class="ml-auto flex justify-end">
            <div class="text-center flex items-center gap-2">
              Sort By: 
              <tab-button :tab1text="'Team'" :ignoreclick="true" :tab2text="'HP Score'" @tab-click="sortCombinedPlayers" > </tab-button>
            </div>
          </div>  
          


          <template v-for="(item, index) in combinedPlayers" :key="index">
            
            <div>
              <a class="flex flex-wrap items-end my-5 w-full justify-evenly"  :href="item.check ? 'javascript:void(0)' : esport ? '/Esports/' + esport + '/Player/' + item.battletag + '/' + item.blizz_id + '/Hero/' + item.hero.name : '/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/Hero/' + item.hero.name">
                <hero-image-wrapper :size="'big'" :hero="item.hero" class="mr-2"></hero-image-wrapper>
                <div>
                  <div class="flex justify-between flex-1">
                    <span> {{ item.battletag }}</span> 
                    <span>Heroes Profile Rating: {{ item.total_rank }}</span>
                  </div>
                  <div class="flex space-x-9 items-between w-full flex-1 ">
                    <div class="flex flex-1">
                      <stat-box :title="'Kills'" :value="item.score.kills" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box :title="'Takedowns'" :value="item.score.takedowns" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box :title="'Deaths'" :value="item.score.deaths" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                    </div>
                    <div class="flex flex-1">
                      <stat-box :title="'Siege Dmg.'" :value="item.score.siege_damage" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box :title="'Hero Dmg.'" :value="item.score.hero_damage" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box :title="'Healing'" :value="item.score.total_healing" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                    </div>
                    <div class="flex flex-1">
                      <stat-box :title="'Dmg. Taken'" :value="item.score.damage_taken" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                      <stat-box :title="'Exp. Con.'" :value="item.score.damage_taken" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
                    </div>
                  </div>

                </div>
              </a>
            </div>
          </template>
        </div>
      </div>


      <div v-if="data.draft_order && data.draft_order.length > 0" class="p-10 text-center max-w-[2000px] mx-auto">
        Draft Order

        <table class="">
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
              <td width="25%"><hero-image-wrapper :size="'medium'" :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}</td>
              <td width="25%">{{ row.pick_number + 1 }}</td>
              <td width="25%">{{ row.type == 0 ? "Ban" : "Pick" }}</td>
            </tr>
          </tbody>
        </table>
      </div>




      <div class="p-10  max-w-[1500px] mx-auto">
       <h2 class="text-3xl font-bold py-5">Talents</h2>
       <div class="flex gap-5 justify-between">
        <div class="">
          <div class="w-full  mb-5" v-for="(item, index) in data.players[0]" :key="index">
            
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
       <div class="">
        <div class="w-full  mb-5" v-for="(item, index) in data.players[1]" :key="index">
          
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

 <div class="bg-lighten p-10 text-center">
  <div class="flex flex-wrap justify-center max-w-[2000px] mx-auto">
    <div v-if="data.experience_breakdown">
      <dual-line-chart :data="data.experience_breakdown" :winner="data.winner"></dual-line-chart>
    </div>
  </div>
</div>


<div v-if="!esport" class=" overflow-scroll md:overflow-auto  h-[50vh] md:h-auto max-w-[2000px] mx-auto my-5">

  Team 1 Advanced MMR data

  {{}}
  <table :class="{ winner: data.players[0][0].winner === 1, loser: data.players[0][0].winner !== 1 }">
    <thead>
      <tr>
        <td colspan="2"></td>
        <td colspan="3">Pre-Match</td>
        <td colspan="3">Post-Match</td>
      </tr>
      <tr>
        <td >Player</td>
        <td >Hero</td>
        <td >Player MMR</td>
        <td >Hero MMR</td>
        <td >Role MMR</td>
        <td >Player MMR</td>
        <td >Hero MMR</td>
        <td >Role MMR</td>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in data.players[0]" :key="index">
        <td><a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/MMR/' + item.hero.name + '/' + data.game_type">{{ item.battletag }}</a></td>
        <td>{{ item.hero.name }}</td>
        <td>{{ Math.round(item.player_mmr - item.player_change)  }}</td>
        <td>{{ Math.round(item.hero_mmr - item.hero_change) }}</td>
        <td>{{ Math.round(item.role_mmr - item.role_change)}}</td>
        <td>{{ item.player_mmr }} ({{ item.player_change }})</td>
        <td>{{ item.hero_mmr }} ({{ item.hero_change }})</td>
        <td>{{ item.role_mmr }} ({{ item.role_change }})</td>
      </tr>
      <tr>
        <td></td>
        <td>Average</td>
        <td>{{ getAverageValue('prev_player_mmr', data.players[0]) }}</td>
        <td>{{ getAverageValue('prev_hero_mmr', data.players[0]) }}</td>
        <td>{{ getAverageValue('prev_role_mmr', data.players[0]) }}</td>
        <td>{{ getAverageValue('player_mmr', data.players[0]) }}</td>
        <td>{{ getAverageValue('hero_mmr', data.players[0]) }}</td>
        <td>{{ getAverageValue('role_mmr', data.players[0]) }}</td>
      </tr>
    </tbody>
  </table>
</div>


<div  v-if="!esport" class="   overflow-scroll md:overflow-auto  h-[50vh] md:h-auto max-w-[2000px] mx-auto my-5">

  Team 2 Advanced MMR data
  <table :class="{ winner: data.players[1][0].winner === 1, loser: data.players[1][0].winner !== 1 }">
    <thead>
      <tr>
        <td colspan="2"></td>
        <td colspan="3">Pre-Match</td>
        <td colspan="3">Post-Match</td>
      </tr>
      <tr>
        <td>Player</td>
        <td>Hero</td>
        <td>Player MMR</td>
        <td>Hero MMR</td>
        <td>Role MMR</td>
        <td>Player MMR</td>
        <td>Hero MMR</td>
        <td>Role MMR</td>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item, index) in data.players[1]" :key="index">
        <td><a :href="`/Player/${item.battletag}/${item.blizz_id}/${item.region}`">{{ item.battletag }}</a></td>
        <td>{{ item.hero.name }}</td>
        <td>{{ Math.round(item.player_mmr - item.player_change)  }}</td>
        <td>{{ Math.round(item.hero_mmr - item.hero_change) }}</td>
        <td>{{ Math.round(item.role_mmr - item.role_change)}}</td>
        <td>{{ item.player_mmr }} ({{ item.player_change }})</td>
        <td>{{ item.hero_mmr }} ({{ item.hero_change }})</td>
        <td>{{ item.role_mmr }} ({{ item.role_change }})</td>
      </tr>
      <tr>
        <td></td>
        <td>Average</td>
        <td>{{ getAverageValue('prev_player_mmr', data.players[1]) }}</td>
        <td>{{ getAverageValue('prev_hero_mmr', data.players[1]) }}</td>
        <td>{{ getAverageValue('prev_role_mmr', data.players[1]) }}</td>
        <td>{{ getAverageValue('player_mmr', data.players[1]) }}</td>
        <td>{{ getAverageValue('hero_mmr', data.players[1]) }}</td>
        <td>{{ getAverageValue('role_mmr', data.players[1]) }}</td>
      </tr>
    </tbody>
  </table>
</div>


<div class="max-w-[2000px] mx-auto my-5">
  Team 1 Advanced Stats
  <table :class="{ winner: data.players[0][0].winner === 1, loser: data.players[0][0].winner !== 1 }" v-for="(section, sectionIndex) in sections" :key="sectionIndex">
    <thead>
      <tr>
        <td >{{ section.title }}</td>
        <td
        v-for="(player, playerIndex) in data.players[0]"
        :key="playerIndex"
        
        >
        <a :href="`/Player/${player.battletag}/${player.blizz_id}/${player.region}`">{{ player.battletag }}</a>
      </td>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, rowIndex) in section.rows" :key="rowIndex">
      <td>{{ row.label }}</td>
      <td v-for="(player, playerIndex) in data.players[0]" :key="playerIndex">{{ formatValue(player.score[row.key]) }}</td>
    </tr>
  </tbody>
</table>
</div>

<div class="max-w-[2000px] mx-auto">
  Team 2 Advanced Stats
  <table :class="{ winner: data.players[1][0].winner === 1, loser: data.players[1][0].winner !== 1 }" v-for="(section, sectionIndex) in sections" :key="sectionIndex">
    <thead>
      <tr>
        <td >{{ section.title }}</td>
        <td
        v-for="(player, playerIndex) in data.players[1]"
        :key="playerIndex"
        
        >
        <a :href="`/Player/${player.battletag}/${player.blizz_id}/${player.region}`">{{ player.battletag }}</a>
      </td>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(row, rowIndex) in section.rows" :key="rowIndex">
      <td>{{ row.label }}</td>
      <td v-for="(player, playerIndex) in data.players[0]" :key="playerIndex">{{ formatValue(player.score[row.key]) }}</td>
    </tr>
  </tbody>
</table>
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
      replayid: Number
    },
    data(){
      return {
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
     async getData(){
      this.isLoading = true;
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();
      try{
        const response = await this.$axios.post("/api/v1/match/single", {
          esport: this.esport,
          replayID: this.replayid
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

      let sum;
      if (type === "prev_player_mmr") {
        sum = data.reduce((acc, curr) => acc + (curr.player_mmr - curr.player_change || 0), 0);
      } else if (type === "prev_hero_mmr") {
        sum = data.reduce((acc, curr) => acc + (curr.hero_mmr - curr.hero_change || 0), 0);
      } else if (type === "prev_role_mmr") {
        sum = data.reduce((acc, curr) => acc + (curr.role_mmr - curr.role_change || 0), 0);
      } else {
        sum = data.reduce((acc, curr) => acc + (curr[type] || 0), 0);
      }

      const average = sum / data.length;
      
      return average.toFixed(0); // adjust the number of decimal places as needed
    },

    getTeamText(team, winner){


      if(this.esport){
        let first_pick = this.data.first_pick;

        let teamName = "";
        if(team == 0){

          teamName = this.data.team_names.team_one.team_name;
        }else{
          teamName = this.data.team_names.team_two.team_name;
        }

        if(winner == 0 && team == 0){
          if(first_pick == team){
            return teamName + " - First Pick - Winner";
          }else{
            return teamName + " - Map Pick - Winner";
          }
        }else if(winner == 1 && team == 0){
          if(first_pick == team){
            return teamName + " - First Pick - Loser";
          }else{
            return teamName + " - Map Pick - Loser";
          }
        }else if(winner == 0 && team == 1){
          if(first_pick == team){
            return teamName + " - First Pick - Loser";
          }else{
            return teamName + " - Map Pick - Loser";
          }
        }else if(winner == 1 && team == 1){
          if(first_pick == team){
            return teamName + " - First Pick - Winner";
          }else{
            return teamName + " - Map Pick - Winner";
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
      return value ? value.toLocaleString() : 0;
    },
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }
    },
  }
}
</script>