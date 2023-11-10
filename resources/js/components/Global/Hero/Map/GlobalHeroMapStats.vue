<template>
  <div>

    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Map Statistics' : 'Hero Map Statistics'">
      <hero-image-wrapper v-if="selectedHero" :hero="selectedHero" :size="'big'"></hero-image-wrapper>
    </page-heading>
    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>


    <div v-else>
      <filters 
      :onFilter="filterData" 
      :filters="filters"
      :isLoading="isLoading" 
      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includeherolevel="true"
      :includegametype="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :advancedfiltering="advancedfiltering"

      >
    </filters>
    <div v-if="data">
      <div class="max-w-[1500px] mx-auto"><span class="flex gap-4 mb-2"> {{ this.selectedHero.name }} {{ "Map Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span></div>


      <div class="max-w-full  md:px-20 overflow-scroll md:overflow-auto max-w-full h-[50vh] md:h-auto">
        <table >
          <thead>
            <tr>
              <th 
                v-for="column in dynamicColumns" 
                :key="column.value" 
                :class="['py-2', 'px-3', 'border-b', 'border-gray-200', 'text-left', 'text-sm', 'leading-4', 'text-gray-500', 'tracking-wider', column.sortable ? 'cursor-pointer' : '']"
                @click="column.sortable ? sortTable(column.value) : null"
                >
                  {{ column.text }}
              </th>        
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in sortedData" :key="row.rank">
              <td v-for="column in dynamicColumns" :key="column.value" class="">
                <div v-if="column.value === 'battletag'">
                  <a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row[column.value] }}</a>
                </div>
                <div v-else-if="column.value === 'most_played_hero'">
                  <div  v-if="row.most_played_hero" class="flex gap-x-2 items-center">
                    <hero-image-wrapper :hero="row.most_played_hero">
                      <image-hover-box :title="row.most_played_hero.name" :paragraph-one="'Games Played:' + row.hero_build_games_played"></image-hover-box>
                    </hero-image-wrapper>
                  </div>
                </div>
                <div v-else>
                  {{ row[column.value] }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
    <div v-else>
      <loading-component v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
      <loading-component v-else></loading-component>
    </div>
  </div>



</div>
</template>

<script>
  export default {
    name: 'GlobalHeroMapStats',
    components: {
    },
    props: {
      inputhero: Object,
      heroes: Array,
      filters: {
        type: Object,
        required: true
      },
      gametypedefault: Array,
      defaulttimeframe: Array,
      defaulttimeframetype: String,
      advancedfiltering: Boolean,
    },
    data(){
      return {
        isLoading: false,
        infoText: "Hero Maps provide information on which maps are good for each hero",
        selectedHero: null,
        data: null,
        sortKey: '',
        sortDir: 'desc',

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        gametype: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,
      }
    },
    created(){
      this.gametype = this.gametypedefault;
      this.timeframe = this.defaulttimeframe;
      this.timeframetype = this.defaulttimeframetype;

      if(this.inputhero){
        this.selectedHero = this.inputhero;
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
       dynamicColumns() {
        if (this.gametype.includes("sl")) {
          return [
            { text: 'Map', value: 'name', sortable: true },
            { text: 'Win Rate %', value: 'win_rate', sortable: true },
            { text: 'Ban Rate %', value: 'ban_rate', sortable: true },
            { text: 'Games Played', value: 'games_played', sortable: true },
          ];
        } else {
          return [
            { text: 'Map', value: 'name', sortable: true },
            { text: 'Win Rate %', value: 'win_rate', sortable: true },
            { text: 'Games Played', value: 'games_played', sortable: true },
          ];
        }
      },
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
      clickedHero(hero) {
        this.selectedHero = hero;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
        this.getData();
      },
      async getData(){
        this.isLoading = true;
        try{
          const response = await this.$axios.post("/api/v1/global/hero/map", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirrormatch: this.mirrormatch,
          });
          this.data = response.data;
        }catch(error){
          //Do something here
        }

        this.isLoading = false;
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;

        this.data = null;
        this.getData();
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframe.length >= 3){
          return  true;
        }
        return false;
      },
      redirectChangeHero(){
        window.location.href = "/Global/Hero/Maps";
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