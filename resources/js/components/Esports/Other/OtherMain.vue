<template>
  <div>
    
    <!-- The heading with the filters beside it, as on the league pages -->
    <player-page-top>
      <template #heading>
        <page-heading heading="HGC and More Esports" :subheading="$t('Professional and third-party tournaments')">
          <a href="/Esports/Other" class="flex items-center justify-center w-28 h-28"><img :src="'/images/EsportOther/' + (hgcSeries[0]?.icon || 'logo.png')" alt="HGC" class="w-full h-full object-contain"></a>
        </page-heading>
      </template>
      <div class="w-full">
        <filters
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => filter()"
          :onChange="handleInputChange"
          :extrafilters="[
            { text: 'Series', values: seriesvalues },
            { text: 'Regions', values: regions },
            { text: 'Tournaments', values: tournaments },
            { text: 'Seasons', values: seasons },
            { text: 'Teams', values: teams },
            { text: 'Map', values: filters.game_maps },
            { text: 'Heroes', values: filters.heroes },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
          <!-- Labelled and sized like the dropdowns beside it -->
          <label class="p-2 flex flex-col gap-1.5 text-sm font-medium text-subtext max-md:w-full md:w-[12.5rem]">
            <span>{{ $t('Player') }}</span>
            <input type="text" class="h-[2.5rem] rounded bg-surface-3 hover:bg-surface-4 focus:outline-none focus:ring-1 focus:ring-teal text-white p-2 w-full min-w-0" :placeholder="$t('Search player')" v-model="userinput" @keyup.enter="filter()">
          </label>
        </filters>
      </div>
    </player-page-top>

    <!-- HGC first and outlined, then the other series -->
    <div class="flex flex-col gap-6 mt-6 mb-6">
      <esports-organizations :data="[...hgcSeries, ...withoutHGCSeries]" :esport="'Other'" highlight="HGC" :subtitle="$t('Pick a league or browse every match below.')"></esports-organizations>
    </div>

    <div v-if="data">
      <esports-recent-matches :current="data.current_page" :last="data.last_page" @page="getMatches" v-if="data.data.length" :data="data.data" :esport="'Other'" :title="$t('Recent matches')" :subtitle="$t('All tournaments · newest first')"></esports-recent-matches>
      <p v-else class="text-center text-subtext mt-10">{{ $t('No games match these filters.') }}</p>



    </div>


    
    <!-- The recent matches card in grey; the message sits on a tab of the card -->
    <div v-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <!-- Recent Matches: the titled card, the toggle and page numbers in its header, the expanded rows -->
      <panel-box title="Recent matches" :subtitle="$t('All tournaments · newest first')" :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true"></loading-component>
        <template #right>
          <div class="ml-auto flex flex-wrap items-center gap-4">
            <skeleton-box class="w-[10rem] h-9 rounded"></skeleton-box>
            <skeleton-box class="w-[17.75rem] h-[2.75rem] rounded"></skeleton-box>
          </div>
        </template>
        <div v-for="row in 8" :key="row" class="flex flex-wrap 2xl:flex-nowrap items-center gap-6 rounded bg-surface-1 px-6 py-4">
          <div class="flex flex-col gap-2 w-[18.75rem] shrink-0">
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
              <skeleton-box class="h-8 w-20 rounded"></skeleton-box>
              <skeleton-box class="h-3 w-28"></skeleton-box>
            </div>
            <div class="flex items-center gap-3">
              <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
              <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
            </div>
          </div>
          <div class="flex gap-2 w-[21.25rem] max-w-full shrink-0">
            <div v-for="team in 2" :key="team" class="flex flex-col gap-1.5 w-[9.375rem]">
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



