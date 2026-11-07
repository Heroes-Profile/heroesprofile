<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="`${esport == 'HeroesInternational' ? 'Heroes International' : esport} ${team} Match History`" -->
    <page-heading :heading="teamName(team) + ' Match History'" :esport="esport == 'Other' ? null : esport" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <!-- The league pages' match rows, a page at a time, like the other match histories -->
    <div v-if="data" class="mt-4">
      <div v-if="data.pagination.last_page > 1" class="max-w-[120rem] mx-auto px-4 md:px-8 flex justify-end">
        <pagination-bar :current="data.pagination.current_page" :last="data.pagination.last_page" @page="getData"></pagination-bar>
      </div>
      <esports-recent-matches v-if="data.data.length" :data="data.data" :esport="recentMatchesEsport" :series="series" :tournament="tournament"></esports-recent-matches>
      <p v-else class="text-center text-subtext mt-10">No games for {{ teamName(team) }}<template v-if="subtitle"> in {{ subtitle }}</template>.</p>
      <div v-if="data.pagination.last_page > 1" class="max-w-[120rem] mx-auto px-4 md:px-8 pb-4 flex justify-end">
        <pagination-bar :current="data.pagination.current_page" :last="data.pagination.last_page" @page="getData"></pagination-bar>
      </div>
    </div>
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 py-4 flex flex-col gap-6">
      <loading-component :compact="true" :overrideimage="getLoadingImage()"></loading-component>
      <panel-box>
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
              <skeleton-box class="h-8 w-20"></skeleton-box>
              <skeleton-box class="h-3 w-28"></skeleton-box>
            </div>
            <div class="flex items-center gap-3">
              <skeleton-box class="w-12 h-12 shrink-0 !rounded-full"></skeleton-box>
              <skeleton-box class="h-5 w-40 max-w-full"></skeleton-box>
            </div>
          </div>
          <div class="flex gap-2 w-[21.25rem] max-w-full shrink-0">
            <div v-for="side in 2" :key="side" class="flex flex-col gap-1.5 w-[9.375rem]">
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
  name: 'EsportsMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    esport: String,
    series: String,
    season: {
      type: [String, Number]
    },
    type: String,
    team: String,
    division: String,
    tournament: String,
    seriesimage: String,
    patreonUser: Boolean,
  },
  data(){
    return {
      cancelTokenSource: null,
      isLoading: false,
      data: null,
      modifiedSeason: null,
    }
  },
  created(){
    this.modifiedSeason = this.season;
  },
  mounted() {
    this.getData(1);
  },
  computed: {
    headingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/ngs_large.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
    headingImageUrl(){
      if(this.esport == "NGS"){
        return "/Esports/NGS"
      }else if(this.esport == "CCL"){
        return "/Esports/CCL"
      }else if(this.esport == "MastersClash"){
        return "/Esports/MastersClash"
      }else if(this.esport == "HeroesInternational"){
        return "/Esports/HeroesInternational"
      }else if(this.esport == "Other"){
        return "/Esports/Other/" + this.series;
      }
    },
    // The recent match rows' league key; HI splits main and Nations Cup
    recentMatchesEsport() {
      if (this.esport == 'HeroesInternational') {
        return this.tournament == 'nationscup' ? 'hi_nc' : 'hi';
      }
      return this.esport;
    },
    subtitle() {
      return [
        this.modifiedSeason ? 'Season ' + this.modifiedSeason : null,
        this.esport == 'NGS' && this.division ? 'Division ' + this.division : null,
      ].filter(Boolean).join(', ');
    },
  },
  methods: {
    async getData(page){
      const lastPage = this.data && this.data.pagination.last_page;
      if (this.isLoading || page < 1 || (lastPage && page > lastPage)) {
        return;
      }

      this.data = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      // NGS and Masters Clash replays carry team names; CCL and HI only ids, looked up by name.
      // Other series come from their own controller.
      const byName = ['NGS', 'MastersClash'].includes(this.esport);
      const request = this.esport == 'Other'
        ? this.$axios.post("/api/v1/esports/other/team/match/history", {
          esport: this.esport,
          series: this.series,
          season: this.modifiedSeason,
          team: this.team,
          pagination_page: page,
        }, { cancelToken: this.cancelTokenSource.token })
        : this.$axios.post("/api/v1/esports/single/team/matches", {
          esport: this.esport,
          tournament: this.tournament,
          season: this.modifiedSeason,
          division: this.esport == 'NGS' ? this.division : null,
          team: byName ? this.team : null,
          team_name: byName ? null : this.team,
          pagination_page: page,
        }, { cancelToken: this.cancelTokenSource.token });

      try{
        const response = await request;
        this.data = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    teamName(name) {
      return this.esport == 'NGS' ? this.$ngsTeam(name).name : name;
    },
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
      }else if(this.esport == "HeroesInternational"){
        return "/images/HI/heroes_international.png";
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
  }
}
</script>
