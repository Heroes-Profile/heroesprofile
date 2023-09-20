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
        <h1>{{ data.game_date }}</h1>
      </div>

      <div class="box thing woot woot">
        <span>{{ data.game_map }}</span>
        <span>{{ data.game_type }}</span>
        <span>{{ data.game_length }}</span>
      </div>


      <div class="bg-lighten p-10 text-center">
        <div class="flex flex-wrap justify-center">
          <div>
            <group-box :text="getTeamText(1, data.players[0])" :data="data.players[0]"></group-box>
            <stat-box :title="'Account Level'" :value="getAverageValue('account_level', data.players[0])"></stat-box>
            <stat-box :title="'Team Level'" :value="data.players[0][0].score.level"></stat-box>
            <stat-box :title="'Takedowns'" :value="data.players[0][0].score.takedowns"></stat-box>
            <stat-box :title="'Average MMR'" :value="getAverageValue('player_mmr', data.players[0])"></stat-box>
            <stat-box :title="'Average Hero MMR'" :value="getAverageValue('hero_mmr', data.players[0])"></stat-box>
            <stat-box :title="'Average Role MMR'" :value="getAverageValue('role_mmr', data.players[0])"></stat-box>
          </div>


          <div>
            <group-box :text="getTeamText(2, data.players[1])" :data="data.players[1]"></group-box>
            <stat-box :title="'Account Level'" :value="getAverageValue('account_level', data.players[1])"></stat-box>
            <stat-box :title="'Team Level'" :value="data.players[1][0].score.level"></stat-box>
            <stat-box :title="'Takedowns'" :value="data.players[1][0].score.takedowns"></stat-box>
            <stat-box :title="'Average MMR'" :value="getAverageValue('player_mmr', data.players[1])"></stat-box>
            <stat-box :title="'Average Hero MMR'" :value="getAverageValue('hero_mmr', data.players[1])"></stat-box>
            <stat-box :title="'Average Role MMR'" :value="getAverageValue('role_mmr', data.players[1])"></stat-box>
          </div>
        </div>
      </div>

      <div class="p-10 text-center">
        Match Scores - See advanced stats below
        <template v-for="(item, index) in combinedPlayers" :key="index">
          <a :href="'/Player/' + item.battletag + '/' + item.blizz_id + '/' + data.region + '/Hero/' + item.hero.name">
            {{ item.battletag }}
            <div class="flex space-x-9 items-center">
              <hero-image-wrapper :size="'big'" :hero="item.hero"></hero-image-wrapper>
              <stat-box :title="'Kills'" :value="item.score.kills"></stat-box>
              <stat-box :title="'Takedowns'" :value="item.score.takedowns"></stat-box>
              <stat-box :title="'Deaths'" :value="item.score.deaths"></stat-box>
              <stat-box :title="'Siege Dmg.'" :value="item.score.siege_damage"></stat-box>
              <stat-box :title="'Hero Dmg.'" :value="item.score.hero_damage"></stat-box>
              <stat-box :title="'Healing'" :value="item.score.healing"></stat-box>
              <stat-box :title="'Dmg. Taken'" :value="item.score.damage_taken"></stat-box>
              <stat-box :title="'Exp. Con.'" :value="item.score.damage_taken"></stat-box>
            </div>
          </a>
        </template>
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

        This is where I need to be the experience graph charts


      <div class="draft section">
        
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
              <td>{{ item.battletag }}</td>
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
    </div>

  </div>
</template>

<script>
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
      console.log(this.combinedPlayers);
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
        console.log(error);
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

    getTeamText(team, data){
      let winner = data[0].winner ? "Winner" : "Loser";
      return "Team " + team + " - " + winner;
    },
    combinePlayerArrays(){
      this.combinedPlayers = [...this.data.players[0], ...this.data.players[1]];

      console.log(this.combinedPlayers);
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
        console.log('Text successfully copied to clipboard');
      }).catch(function(err) {
        console.error('Unable to copy text to clipboard', err);
      });
    }
  }
}
</script>