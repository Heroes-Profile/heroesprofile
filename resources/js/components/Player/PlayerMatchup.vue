<template>
  <div>
    
    <page-heading :heading="'Matchups'" :infoText1="infotext" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
    
    

    <filters 
    :onFilter="filterData" 
    :filters="filters" 
    :isLoading="isLoading"
    :gametypedefault="gametype"
    :includegamemap="true"
    :includegametypefull="true"
    :hideadvancedfilteringbutton="true"
    >
  </filters>
  <takeover-ad :patreon-user="patreonUser"></takeover-ad>

  <div v-if="data">
    <div class="flex flex-wrap justify-center">
      <group-box :text="'Top 5 Allies with more than 5 games'" :data="topfiveheroes"></group-box>
      <group-box :text="'Top 5 Enemies with more than 5 games'" :data="topfiveenemies"></group-box>
    </div>
    <table class="max-md:text-xs">
      <thead>
        <tr>
          <th @click="sortTable('name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Hero
          </th>
          <th @click="sortTable('ally_win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate as Ally %
          </th>            
          <th @click="sortTable('enemy_win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate Against Hero %
          </th>
          <th @click="sortTable('ally_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played As Ally
          </th>
          <th @click="sortTable('enemy_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played Against
          </th>                 
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in sortedData" :key="index">
          <td class="py-2 px-3 flex items-center gap-1">
            <a class="link" :href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Hero/Single/' + row.hero.name">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
            </a>
          </td>
           <td class="py-2 px-3 ">
            {{ row.ally_win_rate ? row.ally_win_rate.toFixed(2) : "No data" }}
          </td>
          <td class="py-2 px-3 ">
            {{ row.enemy_win_rate ? row.enemy_win_rate.toFixed(2) : "No data" }}
          </td>
          <td class="py-2 px-3 ">
            {{ row.ally_games_played ? row.ally_games_played.toLocaleString('en-US') : "No data" }}
          </td>
          <td class="py-2 px-3 ">
            {{ row.enemy_games_played ? row.enemy_games_played.toLocaleString('en-US') : "No data" }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div v-else-if="isLoading">
    <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
  </div>



</div>
</template>

<script>
  export default {
    name: 'PlayerMatchup',
    components: {
    },
    props: {
      filters: Object,
      playerloadsetting: {
        type: [String, Boolean]
      },
      battletag: String,
      blizzid: String, 
      region: String,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
      regionsmap: Object,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        infotext: "Hero Matchups provide information on which heroes " + this.battletag + " is good with and against",
        gametype: null,
        data: null,
        sortKey: '',
        sortDir: 'desc',
        topfiveheroes: [],
        topfiveenemies: [],
      }
    },
    created(){
      this.gametype = this.gametypedefault;

    },
    mounted() {
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
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
      async getData(){
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
      
        try{
          const response = await this.$axios.post("/api/v1/player/matchups", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.gametype, 
            game_map: this.gamemap,
            hero: this.hero,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });
          this.data = response.data.tabledata;
          this.topfiveheroes = response.data.top_five_heroes;
          this.topfiveenemies = response.data.top_five_enemies;
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
      sortTable(key) {
        if (key === this.sortKey) {
          this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortDir = 'desc';
        }
        this.sortKey = key;
      },
      filterData(filteredData){
        this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;

        this.data = null;
        this.topfiveheroes = null;
        this.topfiveenemies = null;
        this.getData();
      },

    }
  }
</script>