<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Friend vs Foe'"  :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="friendIsLoading || enemyIsLoading"
      :gametypedefault="gametype"
      :includehero="true"
      :includegamemap="true"
      :includegametypefull="true"
      :includeseasonwithall="true"
      :includegroupsize="true"
      :hideadvancedfilteringbutton="true"
      :groupSizeDefaultValue="'All'"
      :overrideGroupSizeRemoval="true"
      >
    </filters>
    
    <takeover-ad :patreon-user="patreonUser"></takeover-ad>

    <div v-if="frienddata && enemydata" class="gap-1 mx-auto  flex justify-center max-w-[1500px] max-md:flex-col max-md:text-sm max-md:max-w-[100vw]">
      <div>
        <table class="min-w-0 max-w-[750px] max-md:max-w-[100vw]">
          <thead >
            <tr >
              <th  @click="sortTableFriend('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Player
              </th>
              <th @click="sortTableFriend('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[35%]">
                Favorite Hero
              </th>            
              <th @click="sortTableFriend('total_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[20%]">
                Games Played <br/>With
              </th>
              <th  @click="sortTableFriend('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%] ">
                Win Rate with Friend
              </th>                  
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataFriends" :key="row.blizz_id">
              <td class="py-2 px-3 ">
                <div class="flex items-center">
                  <div class="" v-if="row.hp_owner">
                    <i class="fas fa-crown text" style="color:gold;"></i>
                  </div>
                  <div class="" v-else-if="row.patreon">
                    <i class="fas fa-star" style="color:gold"></i>
                  </div>
                  <a class="link" @click="this.$redirectToProfile(row.split_battletag, row.blizz_id, row.region_id, false)" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" >{{ row.battletag }}</a>

                </div>
              </td>
              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.heroData.hero">
                  <image-hover-box :title="row.heroData.hero.name" :paragraph-one="'Games Played:' + row.hero_games.toLocaleString('en-US')"></image-hover-box>
                </hero-image-wrapper>
               <span class="max-md:hidden"> {{ row.heroData.hero.name }} </span>
              </td>
              <td class="py-2 px-3 ">{{ row.total_games_played.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div>
        <table class="min-w-0 max-w-[750px] max-md:max-w-[100vw]">
          <thead class="bg-red">
            <tr>
              <th @click="sortTableEnemy('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Player
              </th>
              <th @click="sortTableEnemy('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[35%]">
                Favorite Hero
              </th>            
              <th @click="sortTableEnemy('total_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[20%]">
                Games Played <br/>Against
              </th>
              <th @click="sortTableEnemy('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Foe's Win Rate Against {{ battletag }}
              </th>                  
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataEnemies" :key="row.blizz_id">
              <td class="py-2 px-3 ">

                <div class="flex items-center">
                  <div class="" v-if="row.hp_owner">
                    <i class="fas fa-crown text" style="color:gold;"></i>
                  </div>
                  <div class="" v-else-if="row.patreon">
                    <i class="fas fa-star" style="color:gold"></i>
                  </div>
                  <a class="link" @click="this.$redirectToProfile(row.split_battletag, row.blizz_id, row.region_id, false)" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" >{{ row.battletag }}</a>

                </div>
              </td>

              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.heroData.hero">
                  <h2>{{ row.heroData.hero.name }}</h2>
                  <p>Games Played: {{ row.hero_games.toLocaleString('en-US') }}</p>
                </hero-image-wrapper>
                <span class="max-md:hidden">{{ row.heroData.hero.name }} </span>
              </td>
              <td class="py-2 px-3 ">{{ row.total_games_played.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else-if="friendIsLoading || enemyIsLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FriendFoe',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    playerloadsetting: {
      type: [String, Boolean]
    },
    battletag: String,
    blizzid: String, 
    region: String,
    gametypedefault: Array,
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
  },
  data(){
    return {
      friendIsLoading: false,
      enemyIsLoading: false,
      infoText: "Friends and Foe data showing who " + this.battletag + " plays the most games with and against",
      frienddata: null,
      enemydata: null,
      friendSortKey: '',
      friendSortDir: 'desc',

      enemySortKey: '',
      enemySortDir: 'desc',

      gametype: null,
      gamemap: null,
      season: null,
      friendCancelTokenSource: null,
      enemyCancelTokenSource: null,
      groupsize: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      Promise.allSettled([
      this.getData("friend"),
      this.getData("enemy"),
    ]).then(results => {
      if (results[0].status === "fulfilled") {
        this.frienddata = results[0].value;
      } else {
      }
      
      if (results[1].status === "fulfilled") {
        this.enemydata = results[1].value;
      } else {
      }
    });
  }



  },
  computed: {
    sortedDataFriends() {
      if (!this.friendSortKey) return this.frienddata;
      return this.frienddata.slice().sort((a, b) => {
        const valA = a[this.friendSortKey];
        const valB = b[this.friendSortKey];
        if (this.friendSortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    sortedDataEnemies() {
      if (!this.enemySortKey) return this.enemydata;
      return this.enemydata.slice().sort((a, b) => {
        const valA = a[this.enemySortKey];
        const valB = b[this.enemySortKey];
        if (this.enemySortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
        return true;
      }
      return false;
    },
  },
  watch: {
  },
  methods: {
    async getData(type){

      let cancelTokenSource;


      if (cancelTokenSource) {
        cancelTokenSource.cancel('Request canceled');
      }


      if (type === 'friend') {
        this.friendCancelTokenSource = this.$axios.CancelToken.source();
        cancelTokenSource = this.friendCancelTokenSource;
        this.friendIsLoading = true;
      } else if (type === 'enemy') {
        this.enemyCancelTokenSource = this.$axios.CancelToken.source();
        cancelTokenSource = this.enemyCancelTokenSource;
        this.enemyIsLoading = true;
      }


      try{
        const response = await this.$axios.post("/api/v1/player/friendfoe", {
          type: type,
          blizz_id: this.blizzid,
          region: this.region,
          
          game_type: this.gametype,
          season: this.season,
          hero: this.hero,
          game_map: this.gamemap,
          groupsize: this.groupsize,
        }, 
        {
          cancelToken: cancelTokenSource.token,
        });
        
        return response.data;
      }catch(error){
        //Do something here
      }finally {
        if (type === 'friend') {
          this.friendCancelTokenSource = null;
          this.friendIsLoading = false;
        } else if (type === 'enemy') {
          this.enemyCancelTokenSource = null;
          this.enemyIsLoading = false;
        }
      }
    },
    cancelAxiosRequest() {
      if (this.friendCancelTokenSource || this.enemyCancelTokenSource) {
        this.friendCancelTokenSource.cancel('Request canceled by user');
        this.enemyCancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.hero = filteredData.single["Heroes"] ? filteredData.single["Heroes"] : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametypedefault;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : null;
      this.groupsize = filteredData.single["Group Size"] && (filteredData.single["Group Size"] != 'All') ? filteredData.single["Group Size"] : null;

      this.frienddata = null;
      this.enemydata = null;
      Promise.allSettled([
        this.getData("friend"),
        this.getData("enemy"),
      ]).then(results => {
        if (results[0].status === "fulfilled") {
          this.frienddata = results[0].value;
        } else {
          console.error('Friend data retrieval failed', results[0].reason);
        }
        
        if (results[1].status === "fulfilled") {
          this.enemydata = results[1].value;
        } else {
          console.error('Enemy data retrieval failed', results[1].reason);
        }
      });
    },
    sortTableFriend(key) {
      if (key === this.friendSortKey) {
        this.friendSortDir = this.friendSortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.friendSortDir = 'desc';
      }
      this.friendSortKey = key;
    },
    sortTableEnemy(key) {
      if (key === this.enemySortKey) {
        this.enemySortDir = this.enemySortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.enemySortDir = 'desc';
      }
      this.enemySortKey = key;
    },
  }
}
</script>