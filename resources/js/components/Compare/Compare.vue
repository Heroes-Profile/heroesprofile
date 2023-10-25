<template>
  <div>

    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Compare' : 'Compare'"></page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :includeherorole="true"
      :includehero="true"
      :includesinglegamemap="true"
      :includesinglegametype="true"
      :includeseasonwithall="true"
      :defaultHero="selectedHero.id"
      :defaultSeason="season"
      >
    </filters>





    <div class="flex justify-center gap-2  mx-auto  ">
      <div class="border  rounded-md compare-selection-box"  v-for="index in range" :key="index">

        <div class=" p-2 rounded-t-md compare-selection-title" v-if="playerChosen(index)">
          {{ playerData[index].battletag_short }}({{ playerData[index].region_name }})
        </div>
        <div v-else class="p-2 mx-auto">
          <hero-or-league-choice-box :index="index" @onDataReturn="handleDataReturn"></hero-or-league-choice-box>
        </div>


      </div>
      <div class="border p-2 flex flex-col rounded-md" v-if="this.range.length < 5" @click="newPlayerAddded">

      <custom-button class="my-auto mx-auto text-xl"   text="+" alt="Change to a plus sign with text 'Add New Player to Compare'" size="small" :ignoreclick="true"></custom-button>
      Add a player to compare
      </div>

    </div>


    <div v-if="this.data" class="flex">
      (The value needs to be a # out of 100 - so you will need to compare all of the stats across players for a certain stat, make the largest stat = 100, and then have the other values be a comparison of that. Have the actual value be the "displaytext" field)

      <div v-for="stat in stats" :key="stat" class="flex">
        <div v-for="player in playerData" :key="player.battletag">
         <!-- <stat-bar-box  :title="`${player.battletag} ${stat}`" :displaytext="getStatValue(stat.replace(/ /g, '_').toLowerCase(), player.battletag)" :value="getStatValue(stat.replace(/ /g, '_').toLowerCase(), player.battletag)"></stat-bar-box>-->
          <stat-bar-box  :title="`${player.battletag} ${stat}`" displaytext="1.5" :value="75"></stat-bar-box>
        </div>
      </div>

      <img :src="getHeroImage()" />

    </div>


  </div>




</div>
</template>

<script>
  export default {
    name: 'Compare',
    components: {
    },
    props: {
      heroes: Array,
      filters: {
        type: Object,
        required: true
      },
      gametypedefault: Array,
      inputhero: Object,
      gametypedefault: Array,
    },
    data(){
      return {
        isLoading: false,
        range: [0],
        playerData: [],
        data: null,
        infoText: "Choose players using the boxes below to see a detailed stat comparison between multiple players. You can also choose to compare different league tiers. Use the filters above to refine your comparison.",
        selectedHero: null,
        gametype: null,
        season: null,
        gamemap: null,

        stats: [
          'Takedowns',
          'Deaths',
          'Experience Contribution',
          'Siege Damage',
          'Hero Damage',
          'Healing',
          'Damage Taken',
        ],

      }
    },
    created(){
      this.selectedHero = this.inputhero;
      this.gametype = this.gametypedefault;
      this.season = "All";
    },
    mounted() {
    },
    computed: {

    },
    watch: {
    },
    methods: {
      async getData(){
        this.isLoading = true;
        try{
          const response = await this.$axios.post("/api/v1/compare", {
            hero: this.selectedHero.name, 
            game_type: this.gametype, 
            season: this.season, 
            game_map: this.gamemap, 
            player1: this.playerData[0] ? this.playerData[0] : null,
            player2: this.playerData[1] ? this.playerData[1] : null,
            player3: this.playerData[2] ? this.playerData[2] : null,
            player4: this.playerData[3] ? this.playerData[3] : null,
            player5: this.playerData[4] ? this.playerData[4] : null,
          });

          this.data = response.data;
        }catch(error){
        //Do something here
        }
        this.isLoading = false;

      },
      filterData(filteredData){
        this.charttype = filteredData.single["Chart Type"] ? filteredData.single["Chart Type"] : "Account Level";
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      },
      handleDataReturn(index, payload) {
        this.playerData[index] = payload;

        this.getData();
      },
      newPlayerAddded(){
        this.range.push(1);
      },
      playerChosen(index) {
        if (index in this.playerData) {
          return true;
        }
        return false;
      },
      clickedHero(hero) {
        this.selectedHero = hero;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      },
      getHeroImage(){
        return `/images/heroes_rectangle_large/${this.selectedHero.short_name}.jpg`;
      },
      getStatValue(stat, battletag){
        const statValue = this.data[battletag].averages[stat];

        if (typeof statValue === "undefined") {
          return 0;
        }
        return this.data[battletag].averages[stat].avg_value;
      },
    }
  }
</script>