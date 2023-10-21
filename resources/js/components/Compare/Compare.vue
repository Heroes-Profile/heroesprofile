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
      :includeseason="true"
      :defaultHero="selectedHero.id"
      >
    </filters>





    <div class="flex flex-center">
      <div :class="'col-start-' + index + ' row-start-1 bg-teal'" v-for="index in range" :key="index">

        <div v-if="playerChosen(index)">
          {{ playerData[index].battletag_short }}({{ playerData[index].region_name }})
        </div>
        <div v-else>
          <hero-or-league-choice-box :index="index" @onDataReturn="handleDataReturn"></hero-or-league-choice-box>
        </div>


      </div>

      <custom-button v-if="this.range.length < 5" @click="newPlayerAddded" text="Change to a plus sign with text 'Add New Player to Compare'" alt="Change to a plus sign with text 'Add New Player to Compare'" size="small" :ignoreclick="true"></custom-button>

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
      }
    },
    created(){
      this.selectedHero = this.inputhero;
      this.gametype = this.gametypedefault;
    },
    mounted() {
    },
    computed: {
      seasonsWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.seasons];
        updatedList.unshift(newValue);
        return updatedList;
      },
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

    }
  }
</script>