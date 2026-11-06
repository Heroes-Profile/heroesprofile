<template>
  <div>
    <page-heading :heading="'Division ' + division + ' Match History'" esport="NGS" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>

    <filters
      :filters="filters"
      :isLoading="isLoading"
      :onFilter="() => filter()"
      :onChange="handleInputChange"
      :extrafilters="[
        { text: 'Seasons', values: filters.ngs_seasons, defaultValue: modifiedseason },
        { text: 'Teams', values: teamOptions, defaultValue: modifiedteam },
      ]"
      :excludetimeframes="true"
      :excludegametype="true"
      :hideadvancedfilteringbutton="true"
    >
    </filters>

    <!-- The NGS page's recent match rows, a page at a time, like the other match histories -->
    <div v-if="data" class="mt-4">
      <div v-if="data.pagination.last_page > 1" class="max-w-[1920px] mx-auto px-4 md:px-8 flex justify-end">
        <pagination-bar :current="data.pagination.current_page" :last="data.pagination.last_page" @page="getData"></pagination-bar>
      </div>
      <esports-recent-matches v-if="data.data.length" :data="data.data" :esport="'NGS'"></esports-recent-matches>
      <p v-else class="text-center text-subtext mt-10">No games in Division {{ division }} for Season {{ modifiedseason }}<template v-if="modifiedteam"> for {{ $ngsTeam(modifiedteam).name }}</template>.</p>
      <div v-if="data.pagination.last_page > 1" class="max-w-[1920px] mx-auto px-4 md:px-8 pb-4 flex justify-end">
        <pagination-bar :current="data.pagination.current_page" :last="data.pagination.last_page" @page="getData"></pagination-bar>
      </div>
    </div>
    <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 py-4 flex flex-col gap-6">
      <loading-component :compact="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
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
</template>

<script>
export default {
  name: 'NgsSingleDivisionMatchHistory',
  props: {
    esport: String,
    defaultseason: Number,
    season: [String, Number],
    team: String,
    filters: Object,
    division: String,
    patreonUser: Boolean,
  },
  data(){
    return {
      cancelTokenSource: null,
      isLoading: false,
      data: null,
      modifiedseason: null,
      modifiedteam: null,
      teams: [],
    }
  },
  computed: {
    teamOptions() {
      return [{ code: null, name: 'All' }, ...this.teams.map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
  },
  created(){
    // Use the list's own code so the dropdown's strict match finds it
    const season = this.season ?? this.defaultseason;
    const match = (this.filters?.ngs_seasons ?? []).find(value => String(value.code) === String(season));
    this.modifiedseason = match ? match.code : season;
    this.modifiedteam = this.team || null;
  },
  mounted() {
    this.getData(1);
  },
  methods: {
    async getData(page){
      if (this.isLoading || page < 1 || (this.data && page > this.data.pagination.last_page)) {
        return;
      }
      this.data = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/matches", {
          season: this.modifiedseason,
          division: this.division,
          team: this.modifiedteam,
          pagination_page: page,
          esport: "NGS",
        }, { cancelToken: this.cancelTokenSource.token });
        this.data = response.data;
        this.teams = response.data.teams || [];
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    handleInputChange(eventPayload){
      if(eventPayload.field == "Seasons"){
        this.modifiedseason = eventPayload.value;
      }
      if(eventPayload.field == "Teams"){
        this.modifiedteam = eventPayload.value || null;
      }
      const params = new URLSearchParams();
      if (this.modifiedseason) params.set('season', this.modifiedseason);
      if (this.modifiedteam) params.set('team', this.modifiedteam);
      const query = params.toString();
      history.pushState({}, "", `/Esports/NGS/Division/${this.division}/Match/History${query ? '?' + query : ''}`);
    },
    filter(){
      this.data = null;
      this.getData(1);
    },
  }
}
</script>
