<template>
  <div>
    <page-heading :infoText1="infoText1" :battletag="team" :esport="esport" :heading="esport == 'HeroesInternational' ? 'Heroes International' : esport" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <div class="flex justify-center max-w-[1500px] mx-auto">
        <single-select-filter :values="data.seasons" :text="'Seasons'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifiedseason"></single-select-filter>
        <single-select-filter v-if="esport == 'NGS'" :values="data.divisions" :text="'Divisions'" @input-changed="handleInputChange" @dropdown-closed="handleDropdownClosed" :trackclosure="true" :defaultValue="modifieddivision"></single-select-filter>
      </div>

      <div class="flex md:p-20 gap-10 mx-auto justify-center items-between  max-md:flex-col max-md:items-center  ">
        <div class="flex-1 flex flex-wrap justify-between max-w-[400px] w-full items-between mt-[1em] max-md:order-1">
          <stat-box class="w-[48%]" :title="'Wins'" :value="data.wins.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Losses'" :value="data.losses.toLocaleString('en-US')"></stat-box>

          <stat-bar-box class="w-full" size="full" :title="'Win Rate'" :value="data.win_rate.toFixed(2)"></stat-bar-box>
          <stat-box class="w-[48%]" :title="'KDR'" :value="data.kdr" color="yellow"></stat-box>          
          <stat-box class="w-[48%]" :title="'KDA'" :value="data.kda" color="yellow"></stat-box>          
        </div>
        <div class="my-auto">
          <round-image :title="team" :image="data.icon_url" size="large" :rectangle="true"></round-image>
        </div>
        <div class="flex-1 flex flex-wrap max-w-[400px] text-left w-full items-between max-md:order-2">
          <stat-box class="w-[48%]" :title="'Takedowns'" :value="data.takedowns.toLocaleString('en-US')"></stat-box>
          <stat-box class="w-[48%]" :title="'Kills'" :value="data.kills.toLocaleString('en-US')"></stat-box>

          <stat-box class="w-full" :title="'Total Time spent dead'" :value="data.time_spent_dead"></stat-box>
          <stat-box class="w-[48%]" :title="'Assists'" :value="data.assists" color="teal"></stat-box>          
          <stat-box class="w-[48%]" :title="'Deaths'" :value="data.deaths" color="teal"></stat-box>          
        </div>
      </div>

      <div>

        <table class="mb-10 max-md:text-xs">
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
                <a class="link" :href="row.playerlink">{{ row.battletag }}</a>
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

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Hero Enemies and Allies</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="'Top Heroes Won Against'" :data="data.heroes_won_against.slice(0,5)" color="blue"></group-box>
            <group-box :useinputforhover="true" :text="'Top Heroes Lost Against'" :data="data.heroes_lost_against.slice(0,5)" color="red"></group-box>
          </div>
        </div>
      </div>

      <div class="bg-lighten p-10">
        <div class=" max-w-[90em] ml-auto mr-auto mb-10">
          <h2 class="text-3xl font-bold py-5 text-center">Enemy Teams</h2>
          <div class="flex flex-wrap justify-center gap-4">
            <a :href="team.enemy_link"  v-for="(team, index) in data.enemy_teams" :key="index" >
              <round-image :size="'big'" :title="team.team" :image="team.icon_url" :hovertextstyleoverride="true">
                <image-hover-box :title="team.team_name" :paragraph-one="team.inputhover"></image-hover-box>
              </round-image>
            </a>
          </div>
        </div>
      </div>


      <div class=" p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto mb-10">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes played by {{ team }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played'" :data="data.hero_top_three_most_played" color="blue"></group-box>
            <group-box :text="'Highest Win Rate'" :data="data.hero_top_three_highest_win_rate" color="teal"></group-box>
            <group-box :text="'Lowest Win Rate'" :data="data.hero_top_three_lowest_win_rate" color="red"></group-box>
          </div>
        </div>



        <div class="flex flex-wrap gap-2 max-w-[1500px] mx-auto mb-10">
          <hero-image-wrapper v-for="(item, index) in data.heroes" :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
          </hero-image-wrapper>
        </div>

      </div>

        <table class="max-w-[800px] md:min-w-[800px] mt-10 max-md:text-xs">
          <thead>
            <tr >
              <th @click="sortTable('hero_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Hero
              </th>
              <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Win Rate %
              </th>            
              <th @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Played
              </th>              
            </tr>
          </thead>
          <tbody>
            <tr class="@apply even:bg-gray-xlight" v-for="(row, index) in sortedData" :key="index">
              <td>
                <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
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

      
      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class=" p-10 mt-10">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">{{ team }} Hero Ban Data</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="`Most Banned by ${team}`" :data="data.team_ban_date.slice(0,5)" color="blue"></group-box>
            <group-box :useinputforhover="true" :text="`Most Banned Against ${team}`" :data="data.enemy_ban_date.slice(0,5)" color="red"></group-box>
          </div>
        </div>
      </div>



      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps played by {{ team }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Lowest Win Rate'" :data="data.map_top_three_lowest_win_rate" color="red"></group-box>
            <group-box :text="'Most Played'" :data="data.map_top_three_most_played" color="blue"></group-box>
            <group-box :text="'Highest Win Rate'" :data="data.map_top_three_highest_win_rate" color="teal"></group-box>
          </div>
        </div>
      </div>


      <div class="bg-lighten p-10">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps banned by {{ team }}</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :useinputforhover="true" :text="'Maps Banned'" :data="data.maps_banned" color="blue"></group-box>
          </div>
        </div>
        <div class="flex flex-wrap gap-2 max-w-[1500px] mx-auto mb-10 pt-10 justify-center">
          <map-image-wrapper v-for="(item, index) in data.maps_banned" :size="'big'" :map="item.game_map">
            <image-hover-box :title="item.game_map.name" :paragraph-one="item.inputhover"></image-hover-box>
          </map-image-wrapper>
        </div>


      </div>
      <div class="">

        <div class="p-10 max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>
          <game-summary-box 
            v-for="(item, index) in data.matches" 
            :esport="true" 
            :esport-league="esport"
            :data="item"
          ></game-summary-box>
          <div class="flex justify-end mt-4">
          <custom-button :href="`/Esports/${esport}/Team/${team}/Match/History${season ? '?season=${season}' : ''}` + (esport == 'NGS' ? division ? `&division=${division}`: '' : '') + (esport == 'HeroesInternational' ? `&tournament=${tournament}`: '')" class=" ml-auto" text="View Match History"></custom-button>
        </div>
        </div>
      </div>


    </div>

    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :overrideimage="isLoadingImageUrl"></loading-component>
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
    tournament: String,
    image: String,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedseason: null,
      modifieddivision: null,
      cancelTokenSource: null,
      teach: null
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
        return "/images/NGS/600-600-ngs_large_header.png";
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png";
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png";
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }
    },
    headingImageUrl(){
      if(this.esport == "NGS"){
        return "/Esports/NGS"
      }else if(this.esport == "CCL"){
        return "/Esports/CCL"
      }else if(this.esport == "MastersClash"){
        return "/Esports/MastersClash"
      }else if(this.esport == "HeroesInternational"){
        return "/Esports/HeroesInternational"
      }
    },
    isLoadingImageUrl(){
     if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `${this.team} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `${this.team} during season ${this.modifiedseason}`;
      }else if(this.esport == "MastersClash"){
        return `${this.team} during season ${this.modifiedseason}`;
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
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/single/team", {
          esport: this.esport,
          division: this.modifieddivision,
          team: this.team,
          season: this.modifiedseason,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data;
      }catch(error){
      //Do something here
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

      // Update the browser's URL without reisLoading the page
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