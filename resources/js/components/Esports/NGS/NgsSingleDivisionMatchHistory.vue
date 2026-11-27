<template>
  <div>
    <!-- The division's heading with its filters beside it, as on the other NGS pages -->
    <player-page-top>
      <template #heading>
        <page-heading :heading="$t('Division :division Match History', { division })" esport="NGS" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>
      </template>
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
    </player-page-top>

    <!-- The NGS page's recent match rows, a page at a time, like the other match histories -->
    <div v-if="data" class="mt-4">
      <esports-recent-matches :current="data.pagination.current_page" :last="data.pagination.last_page" @page="getData" v-if="data.data.length" :data="data.data" :esport="'NGS'"></esports-recent-matches>
      <p v-else class="text-center text-subtext mt-10">{{ $t('No games in Division :division for Season :modifiedseason', { division: division, modifiedseason: modifiedseason }) }}<template v-if="modifiedteam"> {{ $t('for :modifiedteam', { modifiedteam: $ngsTeam(modifiedteam).name }) }}</template>.</p>
    </div>
    <!-- The load failed: the error page's card in place of the data -->
    <div v-else-if="loadError" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
    <div v-else-if="isLoading" class="relative max-w-[120rem] mx-auto px-4 md:px-8 py-4 flex flex-col gap-6 md:[&>*:nth-child(2)]:!rounded-tl-none md:[&>*:first-child]:!left-8">
        <!-- The Compact / Expanded toggle and the page numbers, as on the loaded list -->
        <div class="flex flex-wrap justify-end items-center gap-4">
          <skeleton-box class="w-[13.25rem] h-[2.75rem] rounded"></skeleton-box>
          <skeleton-box class="w-[17.75rem] h-[2.75rem] rounded"></skeleton-box>
        </div>
        <panel-box class="relative md:!rounded-tl-none">
          <!-- The message sits on a tab of the card, as on the match histories -->
          <loading-component :compact="true" :tab="true" :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
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
      loadError: null,
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
      this.loadError = null;
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
        this.loadError = this.$loadError(error, () => this.getData(page));
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
