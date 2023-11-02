<template>
  <div>
    <page-heading :infoText1="infoText1" :infoText2="infoText2" :heading="'Leaderboard'"></page-heading>
      



    <div class="max-w-[1500px] mx-auto my-2 text-right">
    <custom-button @click="showLeaderboardRequirements = !showLeaderboardRequirements" :text="'Show Leaderboard Requirements'" :alt="'Show Leaderboard Requirements'" size="small" :ignoreclick="true"></custom-button></div>
      <div v-if="showLeaderboardRequirements" class="flex flex-col items-center p-[2em] border w-auto ml-auto mr-auto max-w-[1500px] bg-teal mb-2">
        <h3 class="font-bold text-2xl uppercase">To be eligible for leaderboards, the following conditions must be met:</h3>
        <div class="bg-teal p-[1em] pl-[2em] ">
      <ol class="list-disc">
        <li>Account level must be greater than or equal to 250.</li>
          <li> Must have played at least 5 times the number of weeks since the season started.</li>
          <li>Must have a win rate greater than or equal to 50%.</li>
     
      </ol>
    </div>

      <h3 class="font-bold text-2xl uppercase pb-2">Heroes Profile Rating formula</h3>
      <img class="max-w-[1000px]" :src="'/images/miscellaneous/mmr_calculation.png'"/>
    </div>




    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :defaultSeason="defaultseason"
      :playerheroroletype="true"
      :includehero="false"
      :defaultHero="hero"
      :defaultRole="role"
      :includegroupsize="true"
      :includesinglegametype="true"
      :includeseason="true"
      :includesingleleaguetier="true"
      :includesingleregion="true"
      :minimumseason="13"
      :hideadvancedfilteringbutton="true"
      :advancedfiltering="advancedfiltering"
    >
    </filters>
    <div v-if="data">
      <div class="max-w-[2000px]  md:px-20   h-[50vh] md:h-auto ml-auto mr-auto">
        <table class="">
          <thead>
            <tr>
              <th @click="sortTable('rank')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Rank
              </th>
              <th @click="sortTable('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Battletag
              </th>            
              <th @click="sortTable('region_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Region
              </th>
              <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Win Rate %
              </th>
              <th @click="sortTable('rating')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Heroes Profile Rating
              </th>      
              <th @click="sortTable('mmr')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                {{ leaderboardtype }} MMR
              </th> 
              <th @click="sortTable('tier')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Tier
              </th>    
              <th @click="sortTable('games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Played
              </th>     


              <th v-if="leaderboardtype == 'Player' || leaderboardtype == 'Role' " class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Most Played Hero
              </th>    

              <template v-else-if="leaderboardtype == 'Hero'">
                <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                  Most Played Build
                </th>  
                <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                  Copy Build to Game
                </th>  
                <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                  Games Played With Build
                </th>    
              </template>
       


            </tr>
          </thead>
          <tbody>
            <template v-for="(row, index) in sortedData">
              <tr>
                <td>{{ (index + 1) }}</td>
                <td><a :href="`/Player/${row.split_battletag}/${row.blizz_id}/${row.region_id}`">{{ row.split_battletag }}</a></td>
                <td>{{ row.region }}</td>
                <td>{{ row.win_rate }}</td>
                <td>{{ row.rating }}</td>
                <td>{{ row.mmr }}</td>
                <td>{{ row.tier }}</td>
                <td>{{ row.games_played }}</td>

                <td v-if="(leaderboardtype === 'Player' || leaderboardtype === 'Role') && row.most_played_hero">
                  <hero-image-wrapper :hero="row.most_played_hero">
                    <image-hover-box :title="row.most_played_hero.name" :paragraph-one="'Games Played:' + row.hero_build_games_played"></image-hover-box>
                  </hero-image-wrapper>
                </td>


                <template v-else-if="leaderboardtype == 'Hero'">
                  <td class="py-2 px-3 ">
                    <div class="flex flex-wrap gap-4">
                      <talent-image-wrapper :talent="row.level_one"></talent-image-wrapper>
                      <talent-image-wrapper :talent="row.level_four"></talent-image-wrapper>
                      <talent-image-wrapper :talent="row.level_seven"></talent-image-wrapper>
                      <talent-image-wrapper :talent="row.level_ten"></talent-image-wrapper>
                      <talent-image-wrapper :talent="row.level_thirteen"></talent-image-wrapper>
                      <talent-image-wrapper :talent="row.level_sixteen"></talent-image-wrapper>
                      <talent-image-wrapper :talent="row.level_twenty"></talent-image-wrapper>
                    </div>
                  </td>
                  <td class="py-2 px-3 ">
                    {{ this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero) }}
                    <custom-button @click="copyToClipboard(row)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
                  </td>
                  <td>{{ row.hero_build_games_played }}</td>
                </template>
              </tr>
            </template>
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
  name: 'GlobalLeaderboard',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaultseason: String,
    advancedfiltering: Boolean,
  },
  data(){
    return {
      isLoading: false,
      infoText1: "Leaderboards are a reflection of user uploaded data. Due to replay file corruption or other issues, the data is not always reflective of real player stats. Please keep that in mind when reviewing leaderboards.",
      infoText2: " Only users who upload replays through an approved automatic uploader will be able to rank on the leaderboards. The main uploader can be found at Heroes Profile Uploader while the secondary uploader that works on platforms other than windows can be found at Heroes Profile Electron Uploader. For any questions, please contact zemill@heroesprofile.com ",
      columns: [
        { text: 'Rank', value: 'rank', sortable: true },
        { text: 'Battletag', value: 'battletag', sortable: true },
        { text: 'Region', value: 'region', sortable: true },
        { text: 'Win Rate %', value: 'win_rate', sortable: true },
        { text: 'Heroes Profile Rating', value: 'rating', sortable: true },
        { text: 'Player MMR', value: 'conservative_rating', sortable: true }, 
        { text: 'Tier', value: 'tier', sortable: true }, 
        { text: 'Games Played', value: 'games_played', sortable: true }, 
        { text: 'Most Played Hero', value: 'most_played_hero', sortable: false }, 
      ],
      leaderboardtype: "Player",
      groupsize: "Solo",
      gametype: null,
      season: null,
      region: null,
      data: null,
      hero: 1,
      role: "Bruiser",
      sortKey: '',
      sortDir: 'desc',
      showLeaderboardRequirements: false,
    }
  },
  created(){
    this.gametype = this.gametypedefault[0];
    this.season = this.defaultseason;
    this.getData();
  },
  mounted() {
  },
  computed: {
    sortedData() {
      if (!this.sortKey) return this.data;
      return this.data.slice().sort((a, b) => {
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
      try{
        const response = await this.$axios.post("/api/v1/global/leaderboard", {
          season: this.season, 
          game_type: this.gametype,
          type: this.leaderboardtype.toLowerCase(),
          groupsize: this.groupsize,
          hero: this.hero,
          role: this.role,
          region: this.region,
        });

        this.data = response.data;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },

    filterData(filteredData){
      this.leaderboardtype = filteredData.single["Type"] ? filteredData.single["Type"] : this.leaderboardtype;
      this.groupsize = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : this.groupsize;
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : this.season;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : this.hero;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : this.role;
      this.region = filteredData.single["Regions"] ? filteredData.single["Regions"] : this.region;



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
    copyToClipboard(row) {
      const textToCopy = this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero);
      navigator.clipboard.writeText(textToCopy).then(function() {
      }).catch(function(err) {
      });
    },
  }
}
</script>