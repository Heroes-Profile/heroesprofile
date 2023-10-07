<template>
  <div>
    <page-heading :infoText1="inputhero.name + ' talent stats and builds player by ' + battletag" :heading="battletag +`(`+ regionsmap[region] + `)`">
      <hero-image-wrapper :hero="inputhero" :size="'big'"></hero-image-wrapper>
    </page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>
    <div v-else>
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypedefault="gametype"
        :includegametypefull="true"
        :includeseason="true"
        :includegamemap="true"
        :hideadvancedfilteringbutton="true"
        >
      </filters>
      <div  v-if="talentdetaildata" class="container mx-auto px-4">
        <global-talent-details-section :talentdetaildata="talentdetaildata" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
      </div>
      <div v-else>
        <loading-component></loading-component>
      </div>
      <div  v-if="talentbuilddata" class="container mx-auto px-4">
        {{ this.selectedHero.name }} {{ "Talent Builds"}}
        <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="'Popular'" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
      </div>
      <div v-else>
        <loading-component></loading-component>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerTalents',
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
    inputhero: Object,
    heroes: Array,
    talentimages: Object,
    regionsmap: Object,
  },
  data(){
    return {
      isLoading: false,
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
      selectedHero: null,
      data: null,
      talentdetaildata: null,
      talentbuilddata: null,
      season: null,
      gamemap: null,
    }
  },
  created(){
  },
  mounted() {
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getData();
    }
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        this.isLoading = true;
        const response = await this.$axios.post("/api/v1/player/talents", {
          hero: this.selectedHero.name,
          battletag: this.battletag,
          region: this.region,
          blizz_id: this.blizzid,
          game_type: this.gametype,
          season: this.season,
          game_map: this.gamemap,
        });
        this.talentdetaildata = response.data.talentData
        this.talentbuilddata = response.data.buildData;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    clickedHero(hero) {
      this.selectedHero = hero;
      this.preloadTalentImages(hero);

      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      this.getData();
    },
    filterData(filteredData){
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : this.season;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;


      this.talentdetaildata = null;
      this.talentbuilddata = null;
      this.getData();
    },
    preloadTalentImages(hero) {
      if(hero){
        this.talentimages[hero.name].forEach((image) => {
          const img = new Image();
          img.src = image;
        });
      }
    },
  }
}
</script>