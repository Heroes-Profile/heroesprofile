<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="esport" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <single-select-filter :values="data.seasons" :text="'Seasons'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifiedseason"></single-select-filter>
      <single-select-filter :values="data.divisions" :text="'Divisions'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifieddivision"></single-select-filter>


      <div>
        <stat-box :title="'Wins'" :value="data.wins"></stat-box>
        <stat-box :title="'Losses'" :value="data.losses"></stat-box>
        <stat-bar-box :title="'Win Rate'" :value="data.win_rate"></stat-bar-box>         
        <stat-box :title="'KDR'" :value="data.kdr"></stat-box>
        <stat-box :title="'KDA'" :value="data.kda"></stat-box>
        <stat-box :title="'Takedowns'" :value="data.takedowns"></stat-box>
        <stat-box :title="'Kills'" :value="data.Kills"></stat-box>
        <stat-box :title="'Assists'" :value="data.assists"></stat-box>
        <stat-box :title="'# Games'" :value="data.total_games"></stat-box>
        <stat-box :title="'Deaths'" :value="data.deaths"></stat-box>
        <stat-box :title="'Time spent dead'" :value="data.time_spent_dead"></stat-box>
      </div>

      <div>

        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th>
                Player
              </th>
              <th>
                Games Played
              </th>
              <th>
                Most Played Hero
              </th>
              <th>
                Win Rate on Hero %
              </th>
              <th>
                Preferred Role
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in data.players" :key="index">
              <td>
                <a :href="row.playerlink">{{ row.battletag }}</a>
              </td>
              <td>
                {{ row.games_played }}
              </td>
              <td>
                <a :href="row.herolink">
                  <hero-image-wrapper :hero="row.most_played_hero"></hero-image-wrapper>
                </a>
              </td>
              <td>
                {{ row.win_rate_on_hero }}
              </td>
              <td>
                {{ row.most_played_role }}
              </td>
            </tr>
          </tbody>
        </table>


      </div>

      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Hero Enemies and Allies</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="'Top Heroes Lost Against'" :data="data.heroes_lost_against.slice(0,5)"></group-box>
            <group-box :useinputforhover="true" :text="'Top Heroes Won Against'" :data="data.heroes_won_against.slice(0,5)"></group-box>
          </div>
        </div>
      </div>

      <div class="">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Enemy Teams</h2>
          <div class="flex flex-wrap justify-center">
            <a :href="team.enemy_link"  v-for="(team, index) in data.enemy_teams" :key="index" >
              <round-image :size="'big'" :title="team.team" :image="team.icon_url" :hovertextstyleoverride="true">
                <image-hover-box :title="team.team_name" :paragraph-one="team.inputhover"></image-hover-box>
              </round-image>
            </a>
          </div>
        </div>
      </div>


      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes played by {{ team }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played'" :data="data.hero_top_three_most_played"></group-box>
            <group-box :text="'Highest Win Rate'" :data="data.hero_top_three_highest_win_rate"></group-box>
            <group-box :text="'Lowest Win Rate'" :data="data.hero_top_three_lowest_win_rate"></group-box>
          </div>
        </div>



        <div class="flex flex-wrap gap-2">
          <hero-image-wrapper v-for="(item, index) in data.heroes" :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
          </hero-image-wrapper>
        </div>


        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th @click="sortTable('hero_id')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Hero
              </th>
              <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Win Rate %
              </th>            
              <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Played
              </th>              
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in sortedData" :key="index">
              <td>
                <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}
              </td>
              <td>
                {{ row.win_rate }}
              </td>
              <td>
                {{ row.games_played }}
              </td>
            </tr>
          </tbody>
        </table>

      </div>

      <div class="">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">{{ team }} Hero Ban Data</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="`Most Banned by ${team}`" :data="data.team_ban_date.slice(0,5)"></group-box>
            <group-box :useinputforhover="true" :text="`Most Banned Against ${team}`" :data="data.enemy_ban_date.slice(0,5)"></group-box>
          </div>
        </div>
      </div>



      <div class="bg-lighten p-10">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps played by {{ team }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :playerlink="true" :text="'Most Played'" :data="data.map_top_three_most_played"></group-box>
            <group-box :playerlink="true" :text="'Highest Win Rate'" :data="data.map_top_three_highest_win_rate"></group-box>
            <group-box :playerlink="true" :text="'Lowest Win Rate'" :data="data.map_top_three_lowest_win_rate"></group-box>
          </div>
        </div>
      </div>


      <div class="">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps banned by {{ team }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="'Maps Banned'" :data="data.maps_banned"></group-box>
          </div>
        </div>

        <map-image-wrapper v-for="(item, index) in data.maps_banned" :size="'big'" :map="item.game_map">
          <image-hover-box :title="item.game_map.name" :paragraph-one="item.inputhover"></image-hover-box>
        </map-image-wrapper>


      </div>
      <div class="bg-lighten p-10">

        <div class="p-10 max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>
          <game-summary-box 
            v-for="(item, index) in data.matches" 
            :esport="true" 
            :esport-league="esport"
            :data="item"
          ></game-summary-box>
          <custom-button :href="`/Esports/${esport}/Team/${team}/Match/History`" class="flex justify-end " text="View Match History"></custom-button>
        </div>
      </div>


    </div>


  </div>
</template>

<script>
export default {
  name: 'EsportsSingleTeam',
  components: {
  },
  props: {
    esport: String,
    division: String, 
    team: String,
    season: {
      type: [Number, String]
    },
  },
  data(){
    return {
      data: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedseason: null,
      modifieddivision: null,
    }
  },
  created(){
    this.modifiedseason = this.season;
    this.modifieddivision = this.division;
  },
  mounted() {
    this.getData();
  },
  computed: {
    headingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/600-600-ngs_large_header.png"
      }
    },
    headingImageUrl(){
      if(this.esport == "NGS"){
        return "https://www.nexusgamingseries.org/"
      }
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `${this.team} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }
    },
    sortedData() {
      if (!this.sortKey) return this.data.heroes;
      return this.data.heroes.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
  },
  watch: {
  },
  methods: {
    async getData(){
      this.loading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/single/team", {
          esport: this.esport,
          division: this.modifieddivision,
          team: this.team,
          season: this.modifiedseason,
        });
        this.data = response.data;
      }catch(error){
      //Do something here
      }
      this.loading = false;
    },
    handleInputChange(eventPayload){
        if(eventPayload.field == "Seasons"){
            this.modifiedseason = eventPayload.value;
        }

        if(eventPayload.field == "Divisions"){
          this.modifieddivision = eventPayload.value;
        }

      let newURL = `/Esports/${this.esport}/Team/${this.team}`;
      if (this.modifiedseason && this.modifieddivision) {
        newURL += `?season=${this.modifiedseason}&division=${this.modifieddivision}`;
      } else if (this.modifiedseason) {
        newURL += `?season=${this.modifiedseason}`;
      } else if (this.modifieddivision) {
        newURL += `?division=${this.modifieddivision}`;
      }

      // Update the browser's URL without reloading the page
      history.pushState({}, "", newURL);
    },
    handleDropdownClosed(){
      this.data = null;
      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
  }
}
</script>