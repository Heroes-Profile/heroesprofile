<template>
  <!-- NGS: laid out like the player match history -->
  <div v-if="esport === 'NGS'">
    <div class="flex max-w-[1920px] mx-auto">
      <div class="">
        <page-heading heading="Match History" :battletag="battletag" :esport="esport" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>
      </div>
      <div class="w-full">
        <panel-box v-if="ngsFilterOptions">
          <div class="filter-row justify-center">
            <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" @dropdown-closed="ngsFilterApply" :trackclosure="true" :defaultValue="ngsSeason"></single-select-filter>
            <single-select-filter :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" @dropdown-closed="ngsFilterApply" :trackclosure="true" :defaultValue="ngsDivision"></single-select-filter>
            <single-select-filter :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" @dropdown-closed="ngsFilterApply" :trackclosure="true" :defaultValue="ngsTeam"></single-select-filter>
          </div>
        </panel-box>
      </div>
    </div>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    <div v-if="data">
      <!-- `compact` means the game rows, labelled Expanded -->
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-wrap justify-end items-center gap-4">
        <tab-button :tab1text="'Compact'" :tab2text="'Expanded'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getNgsData"></pagination-bar>
      </div>
      <p v-if="!data.data.length" class="text-center text-subtext mt-10">No NGS games for these filters.</p>
      <div v-else-if="compact" class="max-w-[1920px] mx-auto px-4 md:px-8 py-4">
        <panel-box>
          <div>
            <game-summary-header :esport="true"></game-summary-header>
            <game-summary-box v-for="item in data.data" :key="item.replayID" :esport="true" :esport-league="esport" :data="item"></game-summary-box>
          </div>
        </panel-box>
      </div>
      <div v-else class="max-w-[1920px] mx-auto px-4 md:px-8 py-4">
        <panel-box>
          <!-- Wide rows scroll sideways on narrow screens -->
          <div class="overflow-x-auto">
            <div class="min-w-max w-full">
              <game-summary-header :compact="true" :esport="true" :sort-key="sortKey" :sort-dir="sortDir" @sort="sortTable"></game-summary-header>
              <game-summary-box v-for="item in sortedRows" :key="item.replayID" :compact="true" :esport="true" :esport-league="esport" :data="item"></game-summary-box>
            </div>
          </div>
        </panel-box>
      </div>
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 pb-4 flex justify-end">
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getNgsData"></pagination-bar>
      </div>
    </div>
    <!-- The page's layout with grey blocks while it loads, like the profile -->
    <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 py-4 space-y-8">
      <loading-component :compact="true" :overrideimage="getLoadingImage()"></loading-component>
      <panel-box>
        <div v-if="compact">
          <game-summary-header :esport="true"></game-summary-header>
          <game-summary-skeleton v-for="index in 8" :key="index"></game-summary-skeleton>
        </div>
        <div v-else class="overflow-x-auto">
          <div class="min-w-max w-full">
            <game-summary-header :compact="true" :esport="true"></game-summary-header>
            <game-summary-skeleton v-for="row in 12" :key="row" :compact="true"></game-summary-skeleton>
          </div>
        </div>
      </panel-box>
    </div>
  </div>
  <div v-else>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div v-if="data">
      <table class="mt-10">
        <thead>
          <tr>
            <th @click="sortTable('replayID')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'replayID' }">
              Match ID<sort-indicator :column="'replayID'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('game_date')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'game_date' }">
              Game Date<sort-indicator :column="'game_date'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>     
            <th @click="sortTable('game_map')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'game_map' }">
              Game Map<sort-indicator :column="'game_map'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('hero_id')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'hero_id' }">
              Hero<sort-indicator :column="'hero_id'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>    
            <th @click="sortTable('winner')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'winner' }">
              Winner<sort-indicator :column="'winner'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>    
            <th class="py-2 px-3 text-center text-sm leading-4">
              Talents
            </th>                   
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-for="(row, index) in sortedData" :key="index">
            <td>
              <a class="group/bt inline-flex items-center" :href="`/Esports/${esport}${esport === 'Other' ? `/${series}` : ''}/Match/Single/` + row.replayID + (tournament ? '?tournament=' + tournament : '')"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
            </td>
            <td>
              {{ formatDate(row.game_date) }}
            </td>
            <td>
              {{ row.game_map }}
            </td>
            <td class="py-2 px-3  flex items-center gap-1">
              <hero-image-wrapper :hero="row.hero"></hero-image-wrapper>{{ row.hero.name }}
            </td>
            <td>
              {{ row.winner }}
            </td>
            <td>

              <div class="flex gap-x-1 mx-2 items-center">
                <talent-image-wrapper :talent="row.level_one" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_four" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_seven" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_ten" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_thirteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_sixteen" :size="'small'"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_twenty" :size="'small'"></talent-image-wrapper>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else-if="isLoading">
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6"><loading-component :compact="true" :overrideimage="getLoadingImage()"></loading-component><table-skeleton></table-skeleton></div>
    </div>
  </div>
</template>

<script>
import moment from 'moment-timezone';
import Cookies from 'js-cookie';

export default {
  name: 'EsportsPlayerMatchHistory',
  components: {
  },
  props: {
    filters: Object,
    battletag: String,
    blizzid: String, 
    esport: String,
    season: Number,
    seriesimage: String,
    series: String,
    tournament: String,
    patreonUser: Boolean,
    // NGS: the season, division and team from the URL, the saved row layout, and whether to save it to the account
    esportfilters: Object,
    playermatchtablestyle: {
      type: [String, Boolean, Number]
    },
    authenticated: Boolean,
  },
  data(){
    return {
      cancelTokenSource: null,
      userTimezone: moment.tz.guess(),
      isLoading: false,
      data: null,
      gamemap: null,
      season: null,
      sortKey: '',
      sortDir: 'desc',
      modifiedSeason: null,
      compact: this.playermatchtablestyle == 1,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
    }
  },
  created(){
    this.modifiedSeason = this.season;
  },
  mounted() {
    if (this.esport === 'NGS') {
      this.getNgsData(1);
      return;
    }
    this.getData(1);
  },
  computed: {
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    // The one-line view sorts by Game ID, as the player match history does
    sortedRows() {
      if (this.sortKey !== 'replayID') return this.data.data;
      const direction = this.sortDir === 'asc' ? 1 : -1;
      return this.data.data.slice().sort((a, b) => (a.replayID - b.replayID) * direction);
    },
    headingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/ngs_large.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "MastersClash"){
        return "/images/MCL/no-image.png"
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
      }else if(this.esport == "Other"){
        return "/Esports/Other/" + this.series; 
      }
    },
    sortedData() {
      if (!this.sortKey) return this.data.data;
      return this.data.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    infoText1(){
      if(this.esport == "NGS"){
        return `Match History for ${this.battletag} in division ${this.modifieddivision ? this.modifieddivision : " All "} during season ${this.modifiedseason ? this.modifiedseason : " All "}`
      }else if(this.esport == "CCL"){
        return `Match History for ${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "MastersClash"){
        return `Match History for ${this.battletag} during season ${this.modifiedseason}`;
      }else if(this.esport == "Other"){
        return `Match History for ${this.battletag} in series ${this.series}`;
      }
    },
  },
  watch: {
  },
  methods: {
    // Like the player match history: a cookie for everyone, and the account setting when logged in
    setStyle(side) {
      this.compact = side === 'right';
      Cookies.set('playerhistorytable', this.compact ? '1' : '0', { expires: 365, path: '/' });
      if (this.authenticated) {
        this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.compact }).catch(() => {});
      }
    },
    async getNgsData(page){
      if (this.isLoading || page < 1 || (this.data && page > this.data.last_page)) {
        return;
      }
      this.data = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/player/match/history", {
          battletag: this.battletag,
          blizz_id: this.blizzid,
          season: this.ngsSeason,
          division: this.ngsDivision,
          team: this.ngsTeam,
          pagination_page: page,
        });
        this.ngsFilterOptions = response.data.filters;
        this.data = response.data;
      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field) {
        this[field] = eventPayload.value;
      }
      const params = new URLSearchParams();
      if (this.ngsSeason) params.set('season', this.ngsSeason);
      if (this.ngsDivision) params.set('division', this.ngsDivision);
      if (this.ngsTeam) params.set('team', this.ngsTeam);
      const query = params.toString();
      history.pushState({}, '', window.location.pathname + (query ? `?${query}` : ''));
    },
    ngsFilterApply() {
      this.data = null;
      this.getNgsData(1);
    },
    async getData(page){      
     if (this.isLoading || page < 1 || (this.data && page > this.data.last_page)) {
        return;
      }
    
      this.data = null;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      
      var url = "/api/v1/esports/single/player/match/history";
      
      if(this.series){
        url = "/api/v1/esports/other/single/player/match/history";
      }

      try{
        const response = await this.$axios.post(url, {
          esport: this.esport,
          series: this.series,
          battletag: this.battletag,
          blizz_id: this.blizzid,
          pagination_page: page,
          tournament: this.tournament,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        this.data = response.data;
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
    filterData(filteredData){
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.season = filteredData.single.Season ? filteredData.single.Season : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;


      this.data = null;
      this.getData(1);
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
    getLoadingImage(){
      if(this.esport == "NGS"){
        return "/images/NGS/no-image-clipped.png"
      }else if(this.esport == "CCL"){
        return "/images/CCL/600-600-HHE_CCL_Logo_rectangle.png"
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }else if(this.esport == "Other"){
        return "/images/EsportOther/" + this.seriesimage;
      }
    },
  }
}
</script>