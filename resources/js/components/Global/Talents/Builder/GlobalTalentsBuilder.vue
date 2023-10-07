<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Talent Builder'"></page-heading>

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
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :advancedfiltering="advancedfiltering"
      >
      </filters>
    </div>


    <div v-if="isLoading">
      <loading-component></loading-component>
    </div>
    
    <div v-if="data"  class="flex">
      <talent-builder-column :data="data['1']" :level="1" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['4']" :level="4" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['7']" :level="7" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['10']" :level="10" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['13']" :level="13" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['16']" :level="16" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['20']" :level="20" :clickedData="clickedData"></talent-builder-column>
    </div>



  </div>
</template>

<script>
export default {
  name: 'GlobalTalentsBuilder',
  components: {
  },
  props: {
      filters: Object,
      inputhero: Object,
      heroes: Array,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
      defaultbuildtype: String,
      talentimages: Object,
      advancedfiltering: String,
  },
  data(){
    return {
      isLoading: false,
      selectedHero: null,
      data: null,
      infoText: "Pick a talent in any tier below to start. The tool will calculate talent win rates dynamically as you change your build choices. Talents on tiers already selected will change based on updates made to the build. Select Hero. You can also see what games have been played for the build you are making. See Games.",
      clickedData: {
        1: null,
        4: null,
        7: null,
        10: null,
        13: null,
        16: null,
        20: null,
      },

      //Sending to filter
       timeframetype: null,
       timeframe: null,
       region: null,
       herolevel: null,
       role: null,
       hero: null,
       gametype: null,
       gamemap: null,
       playerrank: null,
       herorank: null,
       rolerank: null,
       mirrormatch: 0,

    }
  },
  created(){
    this.selectedHero = this.inputhero;
    this.timeframe = this.defaulttimeframe;
    this.gametype = this.gametypedefault;
    this.timeframetype = this.defaulttimeframetype;
    this.getData();
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
        const response = await this.$axios.post("/api/v1/global/talents/builder", {
          hero: this.selectedHero.name,
          selectedtalents: this.clickedData,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          hero_level: this.herolevel,
          game_type: this.gametype,
          game_map: this.gamemap,
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

    talentClicked(talent, index, level){
      this.clickedData[level] = talent.talent_id;

            console.log(this.clickedData);

      this.getData();
    },

    clickedHero(hero) {
      this.selectedHero = hero;
      this.preloadTalentImages(hero);

      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
    },
    preloadTalentImages(hero) {
      if(hero){
        this.talentimages[hero.name].forEach((image) => {
          const img = new Image();
          img.src = image;
        });
      }
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
      this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
      this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.defaultbuildtype;

      this.getData();
    },
  }
}
</script>