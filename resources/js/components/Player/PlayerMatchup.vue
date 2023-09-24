<template>
  <div>
    <page-heading :heading="'Matchups'" :infoText1="infotext"></page-heading>
    
    <filters 
    :onFilter="filterData" 
    :filters="filters" 
    :isLoading="isLoading"
    :gametypedefault="gametype"
    :includehero="true"
    :includegamemap="true"
    :includegametypefull="true"
    :includeseason="true"
    >
  </filters>

  <div v-if="data">
    <div class="flex flex-wrap justify-center">
      <group-box :text="'Top 5 Allies with more than 5 games'" :data="topfiveheroes"></group-box>
      <group-box :text="'Top 5 Enemies with more than 5 games'" :data="topfiveenemies"></group-box>
    </div>
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th @click="sortTable('name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Hero
          </th>
          <th @click="sortTable('ally_win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate as Ally %
          </th>            
          <th @click="sortTable('enemy_win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate Against Hero %
          </th>
          <th @click="sortTable('ally_games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played As Ally
          </th>
          <th @click="sortTable('enemy_games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played Against
          </th>                 
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in sortedData" :key="index">
          <td class="py-2 px-3 border-b border-gray-200 flex items-center gap-1">
            <a :href="'/Player/' + battletag + '/' + blizzid + '/' + region + '/Hero/Single/' + row.hero.name"><hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}</a>
          </td>
           <td class="py-2 px-3 border-b border-gray-200">
            {{ row.ally_win_rate }}
          </td>
          <td class="py-2 px-3 border-b border-gray-200">
            {{ row.enemy_win_rate }}
          </td>
          <td class="py-2 px-3 border-b border-gray-200">
            {{ row.ally_games_played }}
          </td>
          <td class="py-2 px-3 border-b border-gray-200">
            {{ row.enemy_games_played }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div v-else>
    <loading-component :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
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
      battletag: String,
      blizzid: String, 
      region: String,
    },
    data(){
      return {
        isLoading: false,
        infotext: "Hero Matchups provide information on which heroes you are good with and against",
        gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
        data: null,
        sortKey: '',
        sortDir: 'desc',
        topfiveheroes: [],
        topfiveenemies: [],
      }
    },
    created(){
    },
    mounted() {
      this.getData();
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
          const response = await this.$axios.post("/api/v1/player/matchups", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.gametype, 
            map: this.gamemap,
            season: this.season,
            hero: this.hero,
          });
          this.data = response.data.tabledata;
          this.topfiveheroes = response.data.top_five_heroes;
          this.topfiveenemies = response.data.top_five_enemies;
        }catch(error){
          //Do something here
        }
        this.isLoading = false;
      },
      sortTable(key) {
        if (key === this.sortKey) {
          this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortDir = 'desc';
        }
        this.sortKey = key;
      },
      filterData(){

      },

    }
  }
</script>