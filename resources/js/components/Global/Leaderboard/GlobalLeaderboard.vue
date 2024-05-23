<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'Leaderboard'">
      <template #extraInfo>
        <p class="text-sm max-md:text-sm">
          Only users who upload replays through an approved automatic uploader will be able to rank on the leaderboards.
          The main uploader can be found at
          <a class="link" href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases" target="_blank" rel="noopener noreferrer">Heroes Profile Uploader</a>
          while the secondary uploader that works on platforms other than Windows can be found at
          <a class="link" href="https://github.com/Heroes-Profile/heroesprofile-electron-uploader/releases" target="_blank" rel="noopener noreferrer">Heroes Profile Electron Uploader</a>.
          For any questions, please contact zemill@heroesprofile.com.
        </p>
      </template>
    </page-heading>
    <div class="max-w-[1500px] mx-auto my-2 text-right">
      <custom-button @click="showLeaderboardRequirements = !showLeaderboardRequirements" :text="'Show Leaderboard Requirements'" :alt="'Show Leaderboard Requirements'" size="small" :ignoreclick="true"></custom-button></div>
      <div v-if="showLeaderboardRequirements" class="flex flex-col items-center p-[2em] border w-auto ml-auto mr-auto max-w-[1500px] bg-teal mb-2">
        <h3 class="font-bold md:text-2xl max-md:text-base uppercase">To be eligible for leaderboards, the following conditions must be met:</h3>
        <div class="bg-teal p-[1em] pl-[2em] ">
          <ol class="list-disc max-md:text-sm">
            <li>Account level must be greater than or equal to 250.</li>
            <li> Must have played at least {{ weeksSinceStartScalar }} times the number of weeks since the season started. (Currently that is {{ weekssincestart * weeksSinceStartScalar }} games).  Note:  If there are less than 10 players meeting these requirements, then the number of games required are reduced until 10 players are found.</li>
            <li>Must have a win rate greater than or equal to 50%.</li>
          </ol>
        </div>
        <h3 class="font-bold text-2xl uppercase pb-2 max-md:text-base">Heroes Profile Rating formula</h3>
        <img class="max-w-[1000px] w-full" :src="'/images/miscellaneous/mmr_calculation.png'"/>
      </div>
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"

        :gametypeinput="[gametype]"
        :regioninput="region"
        


        :gametypedefault="gametypedefault"
        :defaultSeason="defaultseason"
        :playerheroroletype="true"
        :includehero="false"
        :defaultHero="hero"
        :defaultRole="role"
        :includegroupsize="true"
        :includesinglegametypeleaderboard="true"
        :includeseason="true"
        :includesingleleaguetier="true"
        :includesingleregion="true"
        :minimumseason="13"
        :hideadvancedfilteringbutton="true"
        :advancedfiltering="advancedfiltering"
        :excludetimeframes="true"

        :includetier="true"
        :tierrank="''"
      >
      </filters>
      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
      <div v-if="data">
        <div class="flex">
          <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" ">

            <div class="flex flex-wrap justify-between">
              <div class="mb-4 mx-4">
                <label for="search" class="sr-only form-control text-black rounded-l p-2">Search Battletag:</label>
                <input
                  v-model="searchTerm"
                  @input="performSearch"
                  id="search"
                  type="text"
                  placeholder="Search Battletag"
                  class="p-2 border border-gray-300 rounded-md  text-black"
                />
              </div>

              <div v-if="season == defaultseason" class="max-w-[1500px] max-md:mx-4 flex justify-end mb-2 items-center gap-4 ml-auto">
                <div v-if="!patreonUser">
                  <a class="link" href="/Authenticate/Battlenet" target="_blank">Log in</a> and subscribe to <a class="link" href="https://www.patreon.com/heroesprofile" target="_blank">Patreon</a> to use 
                </div>
                <custom-button @click="calculateHPRating(user)" text="Calculate my HP Rating" alt="Calculate my HP Rating" size="small" :ignoreclick="true" :disabled="!patreonUser" :loading="ratingLoading"></custom-button>
                <span v-if="playerRating">
                  {{ ratingText(playerRating, playerRatingGamesPlayed) }}
                </span>
                <span v-else-if="playerRating == 0">
                 No data for the selected filters
                </span>
              </div>
            </div>


            <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
              <thead class=" top-0 w-full  z-40">
                <tr class="">
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
                  <th @click="sortTable('tier_id')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
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
                <template v-for="(row, index) in filteredData">
                  <tr v-if="!patreonUser && index != 0 && index % 50 === 0">
                    <td colspan="10" class="align-content-center">
                      <dynamic-banner-ad :patreon-user="patreonUser" :index="index + 3" :mobile-override="true"></dynamic-banner-ad>
                    </td>
                  </tr>
                  <tr>
                    <td>{{ row.rank }}</td>
                    <td>
                      <div class="flex items-center">
                        <div class="" v-if="row.hp_owner">
                          <icon-with-hover class="mt-2"  size="small"    icon="fas fa-crown"   title="info"  popupsize="small" style="color:rgba(216, 184, 0, 0.719);">
                            <slot>
                              <div>
                                <p class="max-sm:text-xs">Site Owner</p>
                              </div>
                            </slot>
                          </icon-with-hover>
         
                        </div>
                        <div class="" v-else-if="row.patreonUser">
                          <icon-with-hover class="mt-2"  size="small"    icon="fas fa-star"   title="info"  popupsize="small" style="color:rgba(216, 184, 0, 0.719);">
                              <slot>
                                <div>
                                  <p class="max-sm:text-xs">Patreon Subscriber</p>
                                </div>
                              </slot>
                        </icon-with-hover>
                        </div>
                        <a class="link" @click="this.$redirectToProfile(row.split_battletag, row.blizz_id, row.region_id, false)" :href="`/Player/${row.split_battletag}/${row.blizz_id}/${row.region_id}`" >{{ row.split_battletag }}</a>
                      </div>
                    </td>
                    <td>{{ row.region }}</td>
                    <td>{{ row.win_rate.toFixed(2) }}</td>
                    <td>{{ row.rating.toFixed(2) }}</td>
                    <td>{{ row.mmr.toLocaleString('en-US') }}</td>
                    <td>{{ row.tier }}</td>
                    <td>{{ row.games_played.toLocaleString('en-US') }}</td>

                    <td class="py-2 px-3 flex items-center gap-1" v-if="(leaderboardtype === 'Player' || leaderboardtype === 'Role')">
                      <template v-if="row.most_played_hero">
                        <hero-image-wrapper :hero="row.most_played_hero">
                          <image-hover-box :title="row.most_played_hero.name" :paragraph-one="'Games Played:' + row.hero_build_games_played"></image-hover-box>
                        </hero-image-wrapper>
                       <span class="max-md:hidden"> {{ row.most_played_hero.name }}</span>
                      </template>
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
                      <td class="py-2 px-3">
                        <div v-if="row.level_one">
                          {{ this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero) }}
                          <custom-button @click="copyToClipboard(row)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true"></custom-button>
                        </div>
                     
                      </td>
                      <td>
                        <div v-if="row.level_one">
                          {{ row.hero_build_games_played.toLocaleString('en-US') }}
                        </div>
                      </td>
                    </template>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
      </div>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalLeaderboard',
  components: {
  },
  props: {
    user: Object,
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaultseason: String,
    advancedfiltering: Boolean,
    weekssincestart: Number,
  },
  data(){
    return {
      windowWidth: window.innerWidth,
      isLoading: false,
      cancelTokenSource: null,
      infoText1: "Leaderboards are a reflection of user uploaded data. Due to replay file corruption or other issues, the data is not always reflective of real player stats. Please keep that in mind when reviewing leaderboards.",
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
      searchTerm: '',
      playerRating: null,
      ratingLoading: false,
      playerRatingGamesPlayed: null,
      tierrank: null,
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
    weeksSinceStartScalar(){
      if(this.leaderboardtype != "Player"){
        return 2;
      }
      return 5;
    },
    filteredData() {
      const searchTerm = this.searchTerm.toLowerCase();
      return this.sortedData.filter(row => row.battletag.toLowerCase().includes(searchTerm));
    },
    patreonUser(){
      if(this.user && this.user.patreon == 1){
        return true;
      }
      return false;
    }
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
        const response = await this.$axios.post("/api/v1/global/leaderboard", {
          season: this.season, 
          game_type: this.gametype,
          type: this.leaderboardtype.toLowerCase(),
          groupsize: this.groupsize,
          hero: this.hero,
          role: this.role,
          region: this.region,
          tierrank: this.tierrank,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        
        this.data = response.data;
        
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            const newTableWidth = this.windowWidth /responsivetable.clientWidth;
            responsivetable.style.transformOrigin = 'top left';
            responsivetable.style.transform = `scale(${newTableWidth})`;
            const container = this.$refs.tablecontainer;
            container.style.height = (responsivetable.clientHeight * newTableWidth) + 'px';
          }
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.leaderboardtype = filteredData.single["Type"] ? filteredData.single["Type"] : this.leaderboardtype;
      this.groupsize = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : this.groupsize;
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : this.season;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.region = filteredData.single["Regions"] ? filteredData.single["Regions"] : null;

      this.tierrank = filteredData.single.Rank ? filteredData.single.Rank : null;

      this.sortKey = '';
      this.sortDir = 'desc';
      this.playerRating = null;
      this.playerRatingGamesPlayed = null;
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
    ratingText(playerRating, playerRatingGamesPlayed){
      let scalar = this.weekssincestart * this.weeksSinceStartScalar;
      if(playerRatingGamesPlayed < scalar){
        let gameDifference = scalar - playerRatingGamesPlayed;

        return `Rating of ${playerRating} over ${playerRatingGamesPlayed} games.  ${gameDifference} more games to rank.`;
      }
      return `Rating of ${playerRating} over ${playerRatingGamesPlayed} games`;
    },
    async calculateHPRating(user){
      if(this.patreonUser){
        this.playerRating = null;
        this.ratingLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/global/leaderboard/calculate/rating", {
            season: this.season, 
            game_type: this.gametype,
            type: this.leaderboardtype.toLowerCase(),
            groupsize: this.groupsize,
            hero: this.hero,
            role: this.role,
            region: user.region,
            blizz_id: user.blizz_id
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });

          this.playerRating = response.data.rating;
          this.playerRatingGamesPlayed = response.data.games_played;
        }catch(error){
          //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.ratingLoading = false;
        }
      }

    }
  }
}
</script>