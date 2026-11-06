<template>
  <div>
    
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'See below the tournaments for filterable list of replays'" -->
    <page-heading :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div>
      <esports-organizations :data="hgcSeries" :esport="'Other'" class="hgcSeries-wrapper"></esports-organizations>
      <esports-organizations :data="withoutHGCSeries" :esport="'Other'"></esports-organizations>
    </div>

    <div class="max-w-[1920px] mx-auto px-4 md:px-8 mb-8"><panel-box><div class="filter-row justify-center">
      <single-select-filter :values="seriesvalues" :text="'Series'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="regions" :text="'Regions'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="tournaments" :text="'Tournaments'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="seasons" :text="'Seasons'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="teams" :text="'Teams'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="filters.game_maps" :text="'Map'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="filters.heroes" :text="'Heroes'" @input-changed="handleInputChange"></single-select-filter>


      <input type="text" class="form-control variable-text rounded-l-control p-2" :placeholder="'Search for player'" :aria-label="'filter'" aria-describedby="basic-addon2" v-model="userinput" @keyup.enter="filter()">

      <button :disabled="isLoading" @click="filter()" class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>

    </div></panel-box></div>

    <div v-if="data">
      <div v-if="data.last_page > 1" class="max-w-[1920px] mx-auto px-4 md:px-8 flex justify-end">
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getMatches"></pagination-bar>
      </div>
      <esports-recent-matches v-if="data.data.length" :data="data.data" :esport="'Other'"></esports-recent-matches>
      <p v-else class="text-center text-subtext mt-10">No games match these filters.</p>

      <div v-if="data.last_page > 1" class="max-w-[1920px] mx-auto px-4 md:px-8 flex justify-end">
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getMatches"></pagination-bar>
      </div>


    </div>


    
    <div v-if="isLoading">
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <loading-component :compact="true"></loading-component>
        <!-- The recent match rows, in grey -->
        <panel-box>
          <div v-for="row in 8" :key="row" class="flex flex-wrap 2xl:flex-nowrap items-center gap-6 rounded bg-surface-1 px-6 py-4">
            <div class="flex flex-col gap-2 w-[300px] shrink-0">
              <skeleton-box class="h-4 w-40"></skeleton-box>
              <skeleton-box class="h-4 w-32"></skeleton-box>
              <skeleton-box class="h-3 w-36"></skeleton-box>
              <skeleton-box class="h-3 w-28"></skeleton-box>
            </div>
            <div class="flex-1 grid grid-cols-[1fr_auto_1fr] items-center gap-6">
              <div class="flex flex-row-reverse items-center gap-3">
                <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
                <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
              </div>
              <div class="flex flex-col items-center gap-2">
                <skeleton-box class="h-8 w-20"></skeleton-box>
                <skeleton-box class="h-3 w-28"></skeleton-box>
              </div>
              <div class="flex items-center gap-3">
                <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
                <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
              </div>
            </div>
            <div class="flex gap-2 w-[340px] max-w-full shrink-0">
              <div v-for="team in 2" :key="team" class="flex flex-col gap-1.5 w-[150px]">
                <div v-for="player in 5" :key="player" class="flex items-center gap-1">
                  <skeleton-box class="w-4 h-4 shrink-0 !rounded-full"></skeleton-box>
                  <skeleton-box class="h-3 w-24"></skeleton-box>
                </div>
              </div>
            </div>
          </div>
        </panel-box>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OtherMain',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    series: Array,
    seasons: Array,
    regions: Array,
    tournaments: Array,
    teams: Array,
    heroes: Object,
  },
  data(){
    return {
      isLoading: false,
      data: null,

      inputseries: null,
      region: null,
      tournament: null,
      season: null,
      team: null,
      map: null,
      userinput: null,
      selectedHero: null,
    }
  },
  computed: {
    hgcSeries() {
      return this.series.filter(item => item.name === "HGC");
    },
    withoutHGCSeries() {
      return this.series.filter(item => item.name !== "HGC");
    },
    seriesvalues() {
      return this.series.map(seriesItem => ({
        code: seriesItem.series_id,
        name: seriesItem.name
      }));
    }
  },
  mounted() {
    this.getMatches(1);
  },
  methods: {
    async getMatches(page){
      if (this.isLoading || page < 1) {
        return;
      }
      this.data = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post(`/api/v1/esports/other/get/all/matches`, {
          series: this.inputseries,
          region: this.region,
          tournament: this.tournament,
          season: this.season,
          team: this.team,
          map: this.map,
          userinput: this.userinput,
          hero: this.selectedHero ? this.selectedHero.name : null,
          pagination_page: page,
        }, 
        {
        });

        this.data = response.data;

      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },
    
    handleInputChange(eventPayload) {
      if(eventPayload.field == "Series"){
        this.inputseries = eventPayload.value;
      }else if(eventPayload.field == "Regions"){
        this.region = eventPayload.value;
      }else if(eventPayload.field == "Tournaments"){
        this.tournament = eventPayload.value;
      }else if(eventPayload.field == "Seasons"){
        this.season = eventPayload.value;
      }else if(eventPayload.field == "Teams"){
        this.team = eventPayload.value;
      }else if(eventPayload.field == "Map"){
        this.map = eventPayload.value;
      }else if(eventPayload.field == "Heroes"){
        this.selectedHero = this.heroes.find(value => value.id === eventPayload.value);
      }
    },

    filter(){
      this.getMatches(1);
    },
  },
  
}
</script>



