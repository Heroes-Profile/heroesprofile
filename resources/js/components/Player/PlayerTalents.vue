<template>
  <div>
    <page-heading heading="Talent Statistics" :infoText1="selectedHero ? selectedHero.name + ' talent stats and builds player by ' + battletag : ' talent stats and builds player by ' + battletag" :battletag="battletag" :esport="esport" :heading-image="esport ? league.headingImage : null" :heading-image-url="esport ? league.headingImageUrl : null" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
      <hero-image-wrapper v-if="selectedHero" :hero="selectedHero" :size="'big'"></hero-image-wrapper>
      <template v-if="selectedHero" #aboveHeading>
        <hero-heading-select :heroes="heroes" :value="selectedHero.id" @hero-changed="changeHero($event)"></hero-heading-select>
      </template>
    </page-heading>
    
    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>
    <div v-else>
      <div v-if="esport && ngsFilterOptions" class="flex flex-wrap justify-center max-w-[1500px] mx-auto">
        <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" @dropdown-closed="ngsDropdownClosed" :trackclosure="true" :defaultValue="ngsSeason"></single-select-filter>
        <single-select-filter v-if="ngsFilterOptions.divisions" :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" @dropdown-closed="ngsDropdownClosed" :trackclosure="true" :defaultValue="ngsDivision"></single-select-filter>
        <single-select-filter v-if="ngsFilterOptions.teams.length" :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" @dropdown-closed="ngsDropdownClosed" :trackclosure="true" :defaultValue="ngsTeam"></single-select-filter>
      </div>
      <filters v-if="!esport"
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypedefault="gametype"
        :includegametypefull="true"
        :includemultiseason="true"
        :includegamemap="true"
        :hideadvancedfilteringbutton="true"
        :includegamedaterange="true"
        >
      </filters>
      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

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
    playerloadsetting: {
      type: [String, Boolean]
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
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
    esport: String,
    esportfilters: Object,

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
      startdate: null,
      enddate: null,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
      ngsFiltersChanged: false,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
  },
  mounted() {
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      if(this.esport){
        this.getNgsData();
      }else if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    }
  },
  computed: {
    league() {
      return this.esport ? this.$esportLeague(this.esport, this.esportfilters) : null;
    },
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    ngsPlayerUrl() {
      return this.esport ? `${this.league.root}/Player/${this.battletag}/${this.blizzid}` : null;
    },
    // The league and the filters picked here, for links to the player's other pages
    ngsQuery() {
      if (!this.esport) {
        return '';
      }
      const params = new URLSearchParams(this.league.params);
      if (this.ngsSeason) params.set('season', this.ngsSeason);
      if (this.ngsDivision) params.set('division', this.ngsDivision);
      if (this.ngsTeam) params.set('team', this.ngsTeam);
      const query = params.toString();
      return query ? `?${query}` : '';
    },
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
    ngsParams(extra = {}) {
      return { ...this.league.params, battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam, ...extra };
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field && this[field] !== eventPayload.value) {
        this[field] = eventPayload.value;
        this.ngsFiltersChanged = true;
      }
      history.pushState({}, '', window.location.pathname + this.ngsQuery);
    },
    ngsDropdownClosed() {
      if (this.ngsFiltersChanged) {
        this.ngsFiltersChanged = false;
        this.getNgsData();
      }
    },
    async getNgsData(){
      this.talentdetaildata = null;
      this.talentbuilddata = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post(`${this.league.api}/talents`, this.ngsParams({ hero: this.selectedHero.name }));
        this.ngsFilterOptions = response.data.filters;
        this.talentdetaildata = response.data.talentData;
        this.talentbuilddata = response.data.buildData;
      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },
    loadData(){
      if(this.esport){
        this.getNgsData();
      }else{
        this.getData();
      }
    },
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
          start_date: this.startdate,
          end_date: this.enddate,
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
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}` + (this.esport ? this.ngsQuery : ''));
      this.loadData();
    },
    changeHero(heroId) {
      this.selectedHero = this.heroes.find(hero => hero.id === heroId);
      this.preloadTalentImages(this.selectedHero);
      history.pushState(null, null, this.esport
        ? `${this.ngsPlayerUrl}/Talents/${encodeURIComponent(this.selectedHero.name)}${this.ngsQuery}`
        : `/Player/${this.battletag}/${this.blizzid}/${this.region}/Talents/${this.selectedHero.name}`);
      this.talentdetaildata = null;
      this.talentbuilddata = null;
      this.loadData();
    },
    filterData(filteredData){
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;


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
      window.location.href = this.esport
        ? `${this.ngsPlayerUrl}/Talents${this.ngsQuery}`
        : `/Player/${this.battletag}/${this.blizzid}/${this.region}/Talents`;
    }
  }
}
</script>