<template>
  <div>
    <page-heading :infoText1="infoText" :heading="battletag +`(`+ regionsmap[region] + `)`"></page-heading>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="friendIsLoading || enemyIsLoading"
      :gametypedefault="gametype"
      :includehero="true"
      :includegamemap="true"
      :includegametypefull="true"
      :includeseason="true"
      :hideadvancedfilteringbutton="true"
      >
    </filters>

    <div v-if="frienddata && enemydata" class="gap-1 mx-auto  flex justify-center">
      <div>
        <table class="min-w-0">
          <thead>
            <tr>
              <th  @click="sortTableFriend('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Player
              </th>
              <th @click="sortTableFriend('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Favorite Hero
              </th>            
              <th @click="sortTableFriend('total_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Games Played With
              </th>
              <th @click="sortTableFriend('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Win Rate with Friend
              </th>                  
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataFriends" :key="row.blizz_id">
              <td class="py-2 px-3 "><a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row.battletag }}</a></td>
              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.heroData.hero">
                  <image-hover-box :title="row.heroData.hero.name" :paragraph-one="'Games Played:' + row.total_games_played.toLocaleString()"></image-hover-box>
                </hero-image-wrapper>
                {{ row.heroData.hero.name }}
              </td>
              <td class="py-2 px-3 ">{{ row.total_games_played.toLocaleString() }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div>
        <table class="min-w-0 ">
          <thead class="bg-red">
            <tr>
              <th @click="sortTableEnemy('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Player
              </th>
              <th @click="sortTableEnemy('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Favorite Hero
              </th>            
              <th @click="sortTableEnemy('total_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Games Played Against
              </th>
              <th @click="sortTableEnemy('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Foe's Win Rate Against {{ battletag }}
              </th>                  
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataEnemies" :key="row.blizz_id">
              <td class="py-2 px-3 "><a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row.battletag }}</a></td>
              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.heroData.hero">
                  <h2>{{ row.heroData.hero.name }}</h2>
                  <p>Games Played: {{ row.total_games_played.toLocaleString() }}</p>
                </hero-image-wrapper>
                {{ row.heroData.hero.name }}
              </td>
              <td class="py-2 px-3 ">{{ row.total_games_played.toLocaleString() }}</td>
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
    battletag: String,
    blizzid: String, 
    region: String,
    gametypedefault: Array,
    regionsmap: Object,
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

      gametype: ["qm"],
      gamemap: null,
      season: null,
      friendCancelTokenSource: null,
      enemyCancelTokenSource: null,
    }
  },
  created(){
  },
  mounted() {
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