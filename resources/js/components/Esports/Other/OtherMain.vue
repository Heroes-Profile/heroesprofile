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
            { text: 'Regions', values: narrowToSeries(regions, region) },
            { text: 'Tournaments', values: narrowToSeries(tournaments, tournament) },
            { text: 'Seasons', values: narrowToSeries(seasons, season) },
            { text: 'Teams', values: narrowToSeries(teams, team) },
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


    
    <!-- The load failed: the error page's card in place of the data -->
    <div v-if="loadError && !isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
    <!-- The recent matches card in grey, in the list's own layout; the message sits on a tab of the card -->
    <esports-recent-matches v-if="isLoading" :skeleton="true" :data="[]" :esport="'Other'" :title="$t('Recent matches')" :subtitle="$t('All tournaments · newest first')"></esports-recent-matches>
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
      loadError: null,
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
    // The chosen series' options, once each; the picked one stays so the dropdown keeps showing it
    narrowToSeries(list, selected) {
      const seriesName = this.series.find(item => item.series_id === this.inputseries)?.name;
      const seen = new Set();
      return (list || []).filter(row => {
        if ((seriesName && row.series !== seriesName && row.code !== selected) || seen.has(row.code)) {
          return false;
        }
        seen.add(row.code);
        return true;
      }).map(({ code, name }) => ({ code, name }));
    },
    async getMatches(page){
      this.loadError = null;
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
        this.loadError = this.$loadError(error, () => this.getMatches(page));
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



