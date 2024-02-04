<template>
  <div>
    <page-heading heading="Talent Statistics" :infoText1="selectedHero ? selectedHero.name + ' talent stats and builds player by ' + battletag : ' talent stats and builds player by ' + battletag" :battletag="battletag +`(`+ regionsmap[region] + `)`" :isPatreon="isPatreon" :isOwner="isOwner">
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
        :gametypedefault="gametype"
        :includegametypefull="true"
        :includeseason="true"
        :includegamemap="true"
        :hideadvancedfilteringbutton="true"
        :includegamedate="true"
        >
      </filters>
      <takeover-ad :patreon-user="patreonUser"></takeover-ad>

      <div  v-if="talentdetaildata" class="container mx-auto md:px-4">
        <span class="flex gap-4 mb-2"> {{ this.selectedHero.name }} {{ "Talent Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span>
        <global-talent-details-section :talentdetaildata="talentdetaildata" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
      </div>
      <div v-else-if="isLoading">
        <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <div  v-if="talentbuilddata" class="cmx-auto md:px-4 w-auto flex flex-col items-center">
        {{ this.selectedHero.name }} {{ "Talent Builds"}}
        <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="'Popular'" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
      </div>
      <div v-else-if="isLoading">
        <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
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
    isPatreon: Boolean,
    patreonUser: Boolean,
    gametypedefault: Array,

  },
  data(){
    return {
      cancelTokenSource: null,
      isLoading: false,
      gametype: null,
      selectedHero: null,
      data: null,
      talentdetaildata: null,
      talentbuilddata: null,
      season: null,
      gamemap: null,
      fromdate: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
  },
  mounted() {
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getData();
    }
  },
  computed: {
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
        return true;
      }
      return false;
    }
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        const response = await this.$axios.post("/api/v1/player/talents", {
          hero: this.selectedHero.name,
          battletag: this.battletag,
          region: this.region,
          blizz_id: this.blizzid,
          game_type: this.gametype,
          season: this.season,
          game_map: this.gamemap,
          fromdate: this.fromdate,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.talentdetaildata = response.data.talentData
        this.talentbuilddata = response.data.buildData;
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
    clickedHero(hero) {
      this.selectedHero = hero;
      this.preloadTalentImages(hero);

      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      this.getData();
    },
    filterData(filteredData){
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.fromdate = filteredData.single["From Date"] ? filteredData.single["From Date"] : null;


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
    redirectChangeHero(){
      window.location.href = `/Player/${this.battletag}/${this.blizzid}/${this.region}/Talents`;
    }
  }
}
</script>