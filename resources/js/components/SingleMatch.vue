<template>
  <div>
    <div v-if="data">
      <div>
        <span>Match Scores</span>
        <span>Talents</span>
        <span>Experience</span>
        <span>MMR</span>
        <span>Advanced Stats</span>
      </div>

      <div>
        <h1>{{ formatDate(data.game_date) }}</h1>
      </div>

      <div class="box thing woot woot">
        <span>{{ data.game_map }}</span>
        <span>{{ data.game_type }}</span>
        <span>{{ data.game_length }}</span>
      </div>


      <div class="bg-lighten p-10 text-center">
        <div class="flex flex-wrap justify-center">
          <div>
            <group-box :playerlink="true" :match="true" :text="getTeamText(1, data.winner)" :data="data.players[0]" :color="data.winner == 0 ? 'teal' : 'red'"></group-box>


            <div v-if="data.replay_bans" class="flex flex-wrap justify-center">
              Team 1 Bans
              <hero-image-wrapper v-for="(item, index) in data.replay_bans[0]" :key="index" :hero="item.hero" :size="'big'"></hero-image-wrapper>
            </div>


            <stat-box :title="'Account Level'" :value="getAverageValue('account_level', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Team Level'" :value="data.players[0][0].score.level" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Takedowns'" :value="data.players[0][0].score.takedowns" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Average MMR'" :value="getAverageValue('player_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Average Hero MMR'" :value="getAverageValue('hero_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Average Role MMR'" :value="getAverageValue('role_mmr', data.players[0])" :color="data.winner == 0 ? 'teal' : 'red'"></stat-box>
          </div>


          <div>
            <group-box :playerlink="true" :match="true" :text="getTeamText(2, data.winner)" :data="data.players[1]" :color="data.winner == 1 ? 'teal' : 'red'"></group-box>

            <div v-if="data.replay_bans" class="flex flex-wrap justify-center">
              Team 2 Bans
              <hero-image-wrapper v-for="(item, index) in data.replay_bans[1]" :key="index" :hero="item.hero" :size="'big'"></hero-image-wrapper>
            </div>

            <stat-box :title="'Account Level'" :value="getAverageValue('account_level', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Team Level'" :value="data.players[1][0].score.level" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Takedowns'" :value="data.players[1][0].score.takedowns" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Average MMR'" :value="getAverageValue('player_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Average Hero MMR'" :value="getAverageValue('hero_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
            <stat-box :title="'Average Role MMR'" :value="getAverageValue('role_mmr', data.players[1])" :color="data.winner == 1 ? 'teal' : 'red'"></stat-box>
          </div>
        </div>
      </div>

      <div class="p-10 text-center">
        Match Scores - See advanced stats below

            Heroes Profile Score is a match based analysis ranking showing how a player performed in the match compared to other players in the same match.  100 would be a perfect match with most MVPs hovering between 70-75.








        Sort By: 
        <custom-button
          @click="sortCombinedPlayers('team')"          
          text="Team"
          alt="Team"
          size="small"
          :ignoreclick="true"
        >
        
        </custom-button>

        <custom-button
          @click="sortCombinedPlayers('total_rank')"          
          text="HP Score"
          alt="HP Score"
          size="small"
          :ignoreclick="true"
        >
        </custom-button>



        <template v-for="(item, index) in combinedPlayers" :key="index">
          <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/Hero/' + item.hero.name">
            {{ item.battletag }}
            <div class="flex space-x-9 items-center">
              <hero-image-wrapper :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <stat-box :title="'Kills'" :value="item.score.kills" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Takedowns'" :value="item.score.takedowns" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Deaths'" :value="item.score.deaths" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Siege Dmg.'" :value="item.score.siege_damage" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Hero Dmg.'" :value="item.score.hero_damage" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Healing'" :value="item.score.total_healing" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Dmg. Taken'" :value="item.score.damage_taken" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
              <stat-box :title="'Exp. Con.'" :value="item.score.damage_taken" :color="item.winner == 1 ? 'teal' : 'red'"></stat-box>
            </div>

            <span>Heroes Profile Rating: {{ item.total_rank }}</span>
          </a>
        </template>
      </div>



      <div v-if="data.draft_order" class="p-10 text-center">
        Draft Order

        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Hero
              </th>
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Draft Order
              </th>            
              <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
                Type
              </th>                
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in data.draft_order" :key="index" class="">      
              <td><hero-image-wrapper :size="'small'" :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}</td>
              <td>{{ row.pick_number + 1 }}</td>
              <td>{{ row.type == 0 ? "Ban" : "Pick" }}</td>
            </tr>
          </tbody>
        </table>
      </div>



      <div class="p-10 text-center">
        Talents
        <template v-for="(item, index) in data.players[0]" :key="index">
          <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/Hero/' + item.hero.name">
            {{ item.battletag }} - {{ item.hero.name }}
            <div class="flex space-x-9 items-center">
              <hero-image-wrapper :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_one" :size="'medium'" :talent="item.talents.level_one"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_four" :size="'medium'" :talent="item.talents.level_four"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_seven" :size="'medium'" :talent="item.talents.level_seven"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_ten" :size="'medium'" :talent="item.talents.level_ten"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_thirteen" :size="'medium'" :talent="item.talents.level_thirteen"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_sixteen" :size="'medium'" :talent="item.talents.level_sixteen"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_twenty" :size="'medium'" :talent="item.talents.level_twenty"></talent-image-wrapper>
              {{ this.getCopyBuildToGame(item.talents.level_one, item.talents.level_four, item.talents.level_seven, item.talents.level_ten, item.talents.level_thirteen, item.talents.level_sixteen, item.talents.level_twenty, item.hero) }}
              <custom-button @click="copyToClipboard(item)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
            </div>
          </a>
        </template>

         <template v-for="(item, index) in data.players[1]" :key="index">
          <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/Hero/' + item.hero.name">
            {{ item.battletag }} - {{ item.hero.name }}
            <div class="flex space-x-9 items-center">
              <hero-image-wrapper :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_one" :size="'medium'" :talent="item.talents.level_one"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_four" :size="'medium'" :talent="item.talents.level_four"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_seven" :size="'medium'" :talent="item.talents.level_seven"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_ten" :size="'medium'" :talent="item.talents.level_ten"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_thirteen" :size="'medium'" :talent="item.talents.level_thirteen"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_sixteen" :size="'medium'" :talent="item.talents.level_sixteen"></talent-image-wrapper>
              <talent-image-wrapper v-if="item.talents.level_twenty" :size="'medium'" :talent="item.talents.level_twenty"></talent-image-wrapper>
              {{ this.getCopyBuildToGame(item.talents.level_one, item.talents.level_four, item.talents.level_seven, item.talents.level_ten, item.talents.level_thirteen, item.talents.level_sixteen, item.talents.level_twenty, item.hero) }}
              <custom-button @click="copyToClipboard(item)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
            </div>
          </a>
        </template>
      </div>

     <div class="bg-lighten p-10 text-center">
        <div class="flex flex-wrap justify-center">
          <div v-if="data.experience_breakdown">
            <dual-line-chart :data="data.experience_breakdown" :winner="data.winner"></dual-line-chart>
          </div>
        </div>
      </div>

      <div class="max-w-full  md:px-20 overflow-scroll md:overflow-auto max-w-full h-[50vh] md:h-auto">
        Team 1 Advanced MMR data
        <table >
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

      <div class="max-w-full  md:px-20 overflow-scroll md:overflow-auto max-w-full h-[50vh] md:h-auto">
        Team 2 Advanced MMR data
        <table >
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


      <div>
        Team 1 Advanced Stats
        <table v-for="(section, sectionIndex) in sections" :key="sectionIndex">
          <thead>
            <tr>
              <td :class="{ teal: data.winner === 0, red: data.winner !== 0 }">{{ section.title }}</td>
              <td
                v-for="(player, playerIndex) in data.players[0]"
                :key="playerIndex"
                :class="{ teal: data.winner === 0, red: data.winner !== 0 }"
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

      <div>
        Team 2 Advanced Stats
        <table v-for="(section, sectionIndex) in sections" :key="sectionIndex">
          <thead>
            <tr>
              <td :class="{ teal: data.winner === 0, red: data.winner !== 0 }">{{ section.title }}</td>
              <td
                v-for="(player, playerIndex) in data.players[1]"
                :key="playerIndex"
                :class="{ teal: data.winner === 0, red: data.winner !== 0 }"
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
    <div v-else>
      <loading-component></loading-component>
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
    replayid: Number
  },
  data(){
    return {
      data: null,
      combinedPlayers: null,
      showTooltip: false,
      sortDirection: 'desc',
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
      try{
        const response = await this.$axios.post("/api/v1/match/single", {
          replayID: this.replayid
        });
        this.data = response.data; 
        this.combinePlayerArrays();
      }catch(error){
        //Do something here
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
      let winnerText = (team == 1 && winner == 0) ? "Winner" : "Loser";
      return "Team " + team + " - " + winnerText;
    },
    combinePlayerArrays(){
      this.combinedPlayers = [...this.data.players[0], ...this.data.players[1]];
    },
    sortCombinedPlayers(type) {

      this.combinedPlayers.sort((a, b) => {
        if (this.sortDirection === 'desc') {
          if(type == "total_rank"){
            return b.total_rank - a.total_rank;
          }else if(type == "team"){
            return b.team - a.team;
          }
        } else {
          if(type == "total_rank"){
            return a.total_rank - b.total_rank;
          }else if(type == "team"){
            return a.team - b.team;
          }
        }
      });

      // Toggle the sorting direction for the next click
      this.sortDirection = this.sortDirection === 'desc' ? 'asc' : 'desc';
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
    }
  }
}
</script>