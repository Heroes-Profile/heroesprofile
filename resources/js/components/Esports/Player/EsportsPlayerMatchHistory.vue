<template>
  <div>
    <player-page-top>
      <template #heading>
        <page-heading heading="Match History" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
      </template>
      <div class="w-full">
        <filters v-if="ngsFilterOptions"
          :filters="filters"
          :isLoading="isLoading"
          :onFilter="() => ngsFilterApply()"
          :onChange="ngsFilterChange"
          :extrafilters="[
            { text: 'Seasons', values: ngsFilterOptions.seasons, defaultValue: ngsSeason },
            { text: 'Divisions', values: ngsFilterOptions.divisions, defaultValue: ngsDivision, show: !!(ngsFilterOptions.divisions) },
            { text: 'Teams', values: ngsTeamOptions, defaultValue: ngsTeam },
          ]"
          :excludetimeframes="true"
          :excludegametype="true"
          :hideadvancedfilteringbutton="true"
        >
        </filters>
      </div>
    </player-page-top>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>
    <div v-if="data">
      <!-- `compact` means the game rows, labelled Expanded -->
      <div class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-wrap justify-end items-center gap-4">
        <tab-button :tab1text="'Compact'" :tab2text="'Expanded'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="compact ? 'right' : 'left'"></tab-button>
        <pagination-bar :current="data.current_page" :last="data.last_page" @page="getNgsData"></pagination-bar>
      </div>
      <p v-if="!data.data.length" class="text-center text-subtext mt-10">No {{ league.name }} games for these filters.</p>
      <div v-else-if="compact" class="max-w-[1920px] mx-auto px-4 md:px-8 py-4">
        <panel-box>
          <div>
            <game-summary-header :esport="true"></game-summary-header>
            <game-summary-box v-for="item in data.data" :key="item.replayID" :esport="true" :esport-league="esport" :esport-series="esportfilters?.series" :tournament="esportfilters?.tournament" :data="item"></game-summary-box>
          </div>
        </panel-box>
      </div>
      <div v-else class="max-w-[1920px] mx-auto px-4 md:px-8 py-4">
        <panel-box>
          <!-- Wide rows scroll sideways on narrow screens -->
          <div class="overflow-x-auto">
            <div class="md:min-w-max w-full">
              <game-summary-header :compact="true" :esport="true" :sort-key="sortKey" :sort-dir="sortDir" @sort="sortTable"></game-summary-header>
              <game-summary-box v-for="item in sortedRows" :key="item.replayID" :compact="true" :esport="true" :esport-league="esport" :esport-series="esportfilters?.series" :tournament="esportfilters?.tournament" :data="item"></game-summary-box>
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
      <loading-component :compact="true" :overrideimage="league.loaderImage"></loading-component>
      <panel-box>
        <div v-if="compact">
          <game-summary-header :esport="true"></game-summary-header>
          <game-summary-skeleton v-for="index in 8" :key="index"></game-summary-skeleton>
        </div>
        <div v-else class="overflow-x-auto">
          <div class="md:min-w-max w-full">
            <game-summary-header :compact="true" :esport="true"></game-summary-header>
            <game-summary-skeleton v-for="row in 12" :key="row" :compact="true"></game-summary-skeleton>
          </div>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
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
    patreonUser: Boolean,
    // The season, division and team from the URL, plus HI's tournament and Other's series and icon
    esportfilters: Object,
    playermatchtablestyle: {
      type: [String, Boolean, Number]
    },
    authenticated: Boolean,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',
      compact: this.playermatchtablestyle == 1,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
    }
  },
  mounted() {
    this.getNgsData(1);
  },
  computed: {
    league() {
      return this.$esportLeague(this.esport, this.esportfilters);
    },
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    // The one-line view sorts by Game ID, as the player match history does
    sortedRows() {
      if (this.sortKey !== 'replayID') return this.data.data;
      const direction = this.sortDir === 'asc' ? 1 : -1;
      return this.data.data.slice().sort((a, b) => (a.replayID - b.replayID) * direction);
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
        const response = await this.$axios.post(`${this.league.api}/match/history`, {
          ...this.league.params,
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
      const params = new URLSearchParams(this.league.params);
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
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
  }
}
</script>
