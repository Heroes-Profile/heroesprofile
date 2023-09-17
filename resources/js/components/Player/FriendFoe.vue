<template>
  <div>
    Friends And Foes
    for
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :gametypedefault="gametypedefault"
      :includehero="true"
      :includegamemap="true"
      :includegametypefull="true"
      :includeseason="true"
      >
    </filters>
    <span><a :href="`/Player/${battletag}/${blizzid}/${region}`" target="_blank">{{ battletag }}</a></span>


    <div class="flex flex-wrap gap-1">
      <div v-if="frienddata && enemydata">
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th @click="sortTableFriend('battletag')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Player
              </th>
              <th @click="sortTableFriend('hero')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Favorite Hero
              </th>            
              <th @click="sortTableFriend('total_games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Played With
              </th>
              <th @click="sortTableFriend('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Win Rate with Friend
              </th>                  
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataFriends" :key="row.blizz_id">
              <td class="py-2 px-3 border-b border-gray-200"><a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row.battletag }}</a></td>
              <td class="py-2 px-3 border-b border-gray-200"><hero-box-small :hero="row.heroData.hero"></hero-box-small>({{row.heroData.total_games_played}})</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.total_games_played }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="enemydata && frienddata">
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th @click="sortTableEnemy('battletag')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Player
              </th>
              <th @click="sortTableEnemy('hero')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Favorite Hero
              </th>            
              <th @click="sortTableEnemy('total_games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Games Played Against
              </th>
              <th @click="sortTableEnemy('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Foe's Win Rate Against {{ battletag }}
              </th>                  
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataEnemies" :key="row.blizz_id">
              <td class="py-2 px-3 border-b border-gray-200"><a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row.battletag }}</a></td>
              <td class="py-2 px-3 border-b border-gray-200"><hero-box-small :hero="row.heroData.hero"></hero-box-small>({{row.heroData.total_games_played}})</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.total_games_played }}</td>
              <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            </tr>
          </tbody>
        </table>
      </div>

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


  },
  data(){
    return {
      frienddata: [],
      enemydata: [],
      friendSortKey: '',
      friendSortDir: 'asc',

      enemySortKey: '',
      enemySortDir: 'asc',

      gametype: null,
      gamemap: null,
      season: null,
    }
  },
  created(){
  },
  mounted() {
    this.gametype = this.gametypedefault;
    this.getData("friend");
    this.getData("enemy");
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
      try{
            console.log("Game type = ");

        console.log(this.gametype);
        const response = await this.$axios.post("/api/v1/player/friendfoe", {
          type: type,
          blizz_id: this.blizzid,
          region: this.region,
          
          game_type: this.gametype,
          map: this.gamemap,
          season: this.season,
          hero: this.hero,
        });
        if(type == "friend"){
          this.frienddata = response.data;
        }else{
          this.enemydata = response.data;
        }
      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
      this.hero = filteredData.single["Heroes"] ? filteredData.single["Heroes"] : "";
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametypedefault;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : [];
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : "";

      console.log(this.hero);
      console.log(this.gametype);
      console.log(this.gamemap);
      console.log(this.season);

      this.frienddata = [];
      this.enemydata = [];
      this.getData("friend");
      this.getData("enemy");
    },
    sortTableFriend(key) {
      if (key === this.friendSortKey) {
        this.friendSortDir = this.friendSortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.friendSortDir = 'asc';
      }
      this.friendSortKey = key;
    },
    sortTableEnemy(key) {
      if (key === this.enemySortKey) {
        this.enemySortDir = this.enemySortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.enemySortDir = 'asc';
      }
      this.enemySortKey = key;
    },
  }
}
</script>