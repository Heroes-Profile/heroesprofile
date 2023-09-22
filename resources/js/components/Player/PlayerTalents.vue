<template>
  <div>

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
  },
  data(){
    return {
      isLoading: false,
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
      selectedHero: null,
      data: null,
      talentdetaildata: null,
      talentbuilddata: [],
    }
  },
  created(){
  },
  mounted() {
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      Promise.allSettled([
          this.getTalentData(),
          this.getTalentBuildData(),
          ]).then(results => {
      });
    }
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getTalentData(){
      try{
        this.isLoading = true;
        const response = await this.$axios.post("/api/v1/player/talents", {
          hero: this.selectedHero.name,
          battletag: this.battletag,
          region: this.region,
          blizz_id: this.blizzid,
          game_type: this.gametype,
        });
        this.talentdetaildata = response.data;
      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    async getTalentBuildData(){
      try{
        this.isLoading = true;
        const response = await this.$axios.post("/api/v1/player/talents/build", {
          hero: this.selectedHero.name,
          battletag: this.battletag,
          region: this.region,
          blizz_id: this.blizzid,
          game_type: this.gametype,
        });
        this.talentbuilddata = response.data;
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
      Promise.allSettled([
        this.getTalentData(),
        this.getTalentBuildData(),
        ]).then(results => {
      });
    },
    filterData(filteredData){
      /*
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
      this.data = [];
      this.sortKey = '';
      this.sortDir ='asc';
      */
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