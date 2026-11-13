<template>
  <div>
    <global-async-debug-banner page-label="Global Hero" />

    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading heading="Hero Tier List & Win Rates">
      <template v-if="isSLRank" #headingInfo>
        <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
          <p class="font-normal">
            In-game rank uses Storm League ranks read off loading screens by the uploader's opt-in rank reader. Only games with a rank read for a player count, so sample sizes are much smaller than HP rank.
            <a href="/Upload/Uploader#rank-reading" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">How rank reading works</span><i class="fas fa-chevron-right text-xs ml-2"></i></a>
          </p>
        </round-image>
      </template>
    </page-heading>
    
    

    <filters 
      :isLoading="isLoading"
      :onFilter="filterData" 
      :filters="filters" 
      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :statfilterinput="statfilter"
      :herolevelinput="herolevel"
      :heroinput="getHeroID()"
      :roleinput="role"
      :gamemapinput="gamemap"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :rolerankinput="rolerank"
      :talentbuildtypeinput="talentbuildtype"
      :mirrormatchinput="mirrormatch"
      :ranksourceinput="ranksource"
      :slrankinput="slrank"

      :gametypedefault="gametypedefault"
      :includetimeframe="true"
      :includeregion="true"
      :includestatfilter="true"
      :includeherolevel="true"
      :includerole="true"
      :includehero="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :includegroupsize="true"
      :groupsizeadvanced="true"
      :groupsizemulti="true"
      :groupSizeDefaultValue="groupsize"
      :includetalentbuildtype="true"
      :includeranksource="true"
      :advancedfiltering="advancedfiltering"
      :buildtypedefault="defaultbuildtype"
  
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="this.data.data" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <div class="flex flex-wrap justify-between gap-2">
        <div>
          <custom-button v-if="patchNotesUrl" @click="togglePatchNotes" :text="timeframe[0] + ' Patch Notes'" :alt="timeframe[0] + ' Patch Notes'" size="small" :ignoreclick="true"></custom-button>
        </div>
        <custom-button @click="toggleChartValue" text="Toggle Chart" alt="Toggle Chart" size="small" :ignoreclick="true"></custom-button>
      </div>

      <patch-notes-panel v-if="showPatchNotes && patchNotesUrl" :version="timeframe[0]"></patch-notes-panel>

      <div v-if="togglechart">
        <bubble-chart :heroData="this.data.data"></bubble-chart>
      </div>
      <panel-box>
        <div id="table-container" ref="tablecontainer" class="overflow-hidden">
      <table id="responsive-table" class="min-w-0 w-full responsive-table relative" ref="responsivetable">
        <thead>
          <tr>
            <th class="py-2 px-3"></th>
            <th class="py-2 px-3 text-left text-sm leading-4">
              {{ $t('Avg') }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueFixed(data.average_win_rate) }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ "&#177;" }}{{ getValueFixed(data.average_confidence_interval)}}
            </th>
            <th v-if="showWinRateChange" class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueFixed(data.average_positive_win_rate_change) }}{{ "|" }}{{ getValueFixed(data.average_negative_win_rate_change) }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueFixed(data.average_popularity) }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueFixed(data.average_pick_rate) }}
            </th>
            <th v-if="this.gametype.includes('sl') && !this.groupSizeActive" class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueFixed(data.average_ban_rate) }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueLocal(data.average_positive_influence) }}{{ "|" }}{{ getValueLocal(data.average_negative_influence) }}
            </th>
            <th class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueLocal(data.average_games_played) }}
            </th>

            <th  v-if="this.showStatTypeColumn"  class="py-2 px-3 text-center text-sm leading-4">
              {{ getValueLocal(getValueFixed(data.average_total_filter_type)) }}
            </th>

            <th v-if="!this.groupSizeActive" class="py-2 px-3 text-center text-sm leading-4">
            </th>
          </tr>
        </thead>
        <thead class="top-0 w-full sticky z-40">
          <tr>
            <th @click="sortTable('rank')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'rank' }">
              {{ rankChanged ? 'Sorted Rank|(Rank)' : 'Rank' }}<sort-indicator :column="'rank'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('name')" class="py-2 px-3 text-left text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'name' }">
              {{ $t('Hero') }}<sort-indicator :column="'name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate' }">
              {{ $t('Win Rate %') }}<sort-indicator :column="'win_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('confidence_interval')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'confidence_interval' }">
              {{ $t('Win Rate Confidence') }}<sort-indicator :column="'confidence_interval'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th v-if="showWinRateChange" @click="sortTable('win_rate_change')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'win_rate_change' }">
              {{ $t('Win Rate Change') }}<sort-indicator :column="'win_rate_change'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('popularity')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'popularity' }">
              {{ $t('Popularity %') }}<sort-indicator :column="'popularity'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th @click="sortTable('pick_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'pick_rate' }">
              {{ $t('Pick Rate %') }}<sort-indicator :column="'pick_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>   
            <th v-if="this.gametype.includes('sl') && !this.groupSizeActive" @click="sortTable('ban_rate')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'ban_rate' }">
              {{ $t('Ban Rate %') }}<sort-indicator :column="'ban_rate'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>    
            <th @click="sortTable('influence')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'influence' }">
              <div class="flex items-center">
                <div class="">
                  {{ $t('Influence') }}
                </div>
                <round-image class="hidden md:block" size="small" icon="fas fa-info" title="info" popupsize="large">
                  <slot>
                    <div>
                      <p>Influence is an integer scaled from -5000 to 5000 that combines Win Rate, Pick Rate, and Ban Rate to determine the impact a hero will have on a particular team.</p>
                    </div>
                  </slot>
                </round-image>
              </div><sort-indicator :column="'influence'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>                  
            <th @click="sortTable('games_played')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'games_played' }">
              {{ $t('Games Played') }}<sort-indicator :column="'games_played'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>       
            <th v-if="this.showStatTypeColumn" @click="sortTable('total_filter_type')" class="py-2 px-3 text-center text-sm leading-4 cursor-pointer" :class="{ 'bg-teal text-white': sortKey === 'total_filter_type' }">
              {{ this.statfiltername }}<sort-indicator :column="'total_filter_type'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
            <th v-if="!this.groupSizeActive" class="py-2 px-3 text-center text-sm leading-4 w-px whitespace-nowrap">
              {{ $t('Builds') }}
            </th>                              
          </tr>
        </thead>
        <tbody class="text-center">
          <template v-for="(row, index) in sortedData">
            <tr>
              <td class="py-2 px-3"><div class="flex justify-center gap-1"><div v-if="rankChanged" class="bg-blue text-white min-w-[2em] p-1 rounded-control text-center">{{ sortDir == 'desc' ? index + 1 : sortedData.length - index }}</div><span class="p-1">{{ row.rank }}</span></div></td>
              <td class="py-2 px-3 flex items-center gap-1 max-md:w-[9.375rem]">
                <a class="group/bt flex w-full items-center justify-start" :href="getGlobalTalentsURL(row)" >
                  <hero-image-wrapper class="mr-2" mobileClick="true" :hero="row" :includehover="false"></hero-image-wrapper><span class="hidden md:block font-bold group-hover/bt:underline">{{ $heroName(row.name) }}</span>
                </a>
              </td>
              <td class="  ">{{ getValueFixed(row.win_rate) }}</td>
              <td class="py-2 px-3 "><span v-html="'&#177;'"></span>{{ getValueFixed(row.confidence_interval) }}</td>
              <td v-if="showWinRateChange && row.win_rate_change == 0" class="py-2 px-3 ">0.00</td>
              <td v-else-if="showWinRateChange && row.win_rate_change < 0" class="py-2 px-3 ">{{ getValueFixed(row.win_rate_change) }}</td>
              <td v-else-if="showWinRateChange && row.win_rate_change >= 0" class="py-2 px-3 "><span v-html="'&plus;'"></span>{{ getValueFixed(row.win_rate_change) }}</td>
              <td class="py-2 px-3">{{ getValueFixed(row.popularity) }}</td>
              <td class="py-2 px-3">{{ getValueFixed(row.pick_rate) }}</td>
              <td  v-if="this.gametype.includes('sl') && !this.groupSizeActive" class="py-2 px-3">{{ getValueFixed(row.ban_rate) }}</td>
              <td class="py-2 px-3">{{ getValueLocal(row.influence) }}</td>
              <td class="py-2 px-3 ">{{ getValueLocal(row.games_played) }}</td>
              <td v-if="this.showStatTypeColumn" class="py-2 px-3 ">{{ getValueLocal(getValueFixed(row.total_filter_type)) }}</td>
              <td v-if="!groupSizeActive" class="py-2 px-3 ">
                <!-- Like Friend Foe's Matches column: a chevron that opens this hero's builds below the row -->
                <button type="button" @click="viewtalentbuilds(row.name, index)" :aria-expanded="!!toggletalentbuilds[row.name]" :aria-label="`${row.name} talent builds`" title="Talent Builds" class="w-full flex items-center justify-center min-h-[2rem] hover:text-lteal transition-colors">
                  <img v-if="loadingStates[row.name]" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="Loading"/>
                  <i v-else :class="['fas fa-chevron-right transition-transform', { 'rotate-90': toggletalentbuilds[row.name] }]"></i>
                </button>
              </td>
            </tr>
            <tr v-if="toggletalentbuilds[row.name] && talentbuilddata[row.name]">
              <td colspan="12">
                <global-talent-builds-section v-if="toggletalentbuilds[row.name] && talentbuilddata[row.name]" :talentbuilddata="talentbuilddata[row.name]" :buildtype="selectedbuildtype"></global-talent-builds-section>
              </td>
            </tr> 
          </template>
        </tbody>
      </table>
    </div>
      </panel-box>
    </div>
    <!-- The page's layout with grey blocks while it loads, like the leaderboard -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <loading-component v-if="determineIfVeryLargeData()" :compact="true" :textoverride="true">{{ $t('Very large amount of data. Compiling can take 5-10 minutes. Please be patient.') }}</loading-component>
      <loading-component v-else-if="determineIfLargeData()" :compact="true" :textoverride="true">{{ $t('Large amount of data. Please be patient.') }}</loading-component>
      <loading-component v-else :compact="true" :textoverride="true">{{ $t('Loading hero stats...') }}</loading-component>
      <table-skeleton :columns="['rank', 'hero', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'rank']"></table-skeleton>
    </div>
    <div v-else-if="dataError" class="flex flex-col items-center justify-center">
      {{ $t('Error: Reload page/filter') }}
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
export default {
  name: 'GlobalHeroStats',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaulttimeframetype: String,
    defaulttimeframe: Array,
    advancedfiltering: Boolean,
    patreonUser: Boolean,
    defaultbuildtype: String,
    heroes: Object,
    urlparameters: Object,
  },
  data(){
    return {
      dataError: false,
      windowWidth: window.innerWidth,
      isLoading: false,
    	infoText: "Hero win rates based on differing increments, stat types, game type, or rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
      sortKey: '',
      sortDir: 'desc',
      data: [],
      togglechart: false,
      showPatchNotes: false,
      toggletalentbuilds: {},
      talentbuilddata: {},
      selectedbuildtype: null,
      cancelTokenSource: null,
      statfiltername: null,

      //Sending to filter
      timeframetype: null,
      timeframe: null,
      region: null,
      statfilter: "win_rate",
      herolevel: null,
      role: null,
      hero: null,
      gametype: null,
      gamemap: null,
      playerrank: null,
      herorank: null,
      rolerank: null,
      mirrormatch: 0,
      groupsize: [],
      ranksource: 'hp',
      slrank: null,
      talentbuildtype: null,
      loadingStates: {},
      tablewidth: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.timeframe = this.defaulttimeframe;
    this.timeframetype = this.defaulttimeframetype;
    this.talentbuildtype = this.defaultbuildtype;


    if(this.urlparameters){
      this.setURLParameters();
    }

  	this.getData();
  },
  computed: {
    isSLRank(){
      return this.ranksource === 'sl';
    },
    showStatTypeColumn(){
      if(this.statfilter && this.statfilter != "win_rate" && !this.isLoading){
        return true;
      }
      return false;
    },
    groupSizeActive(){
      // `All`, or every size picked, is the unfiltered table rather than a stack size.
      const selected = Array.isArray(this.groupsize) ? this.groupsize : (this.groupsize ? [this.groupsize] : []);
      const allSizes = ["Solo", "Duo", "3 Players", "4 Players", "5 Players"];
      return selected.length > 0 && !selected.includes("All") && !allSizes.every(size => selected.includes(size));
    },
    patchNotesUrl() {
      if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
        return null;
      }
      const selectedVersion = this.timeframe[0];
      const match = this.filters.timeframes.find(t => t.code === selectedVersion);
      return match && match.patch_notes_url ? match.patch_notes_url : null;
    },
    showWinRateChange(){
      return (
        this.timeframetype !== 'major' &&
        this.timeframetype !== 'major_grouped' &&
        this.timeframe.length === 1 &&
        !this.region &&
        this.gametype.length === 1 &&
        this.gametype[0] !== 'br' &&
        this.gametype[0] !== 'cu' &&
        !this.gamemap &&
        !this.playerrank &&
        !this.herorank &&
        !this.rolerank &&
        !this.herolevel &&
        this.statfilter === 'win_rate' &&
        this.mirrormatch == 0 &&
        !this.groupSizeActive &&
        !this.isSLRank
      );

    },
    // Sorting by anything but the default order gives each row a new position
    rankChanged() {
      return !!this.sortKey && this.sortKey !== 'rank' && this.sortKey !== (this.statfilter === 'win_rate' ? 'win_rate' : 'total_filter_type');
    },
    sortedData() {
      const rows = Array.isArray(this.data?.data) ? this.data.data.filter(row => row) : [];
      if (!this.sortKey) return rows;
      return rows.sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    queryString(){      
      let queryString  = `?timeframe_type=${this.timeframetype}`;
      queryString += `&timeframe=${this.timeframe}`;

      queryString += `&game_type=${this.gametype}`;

      if(this.region){
        queryString += `&region=${this.region}`;
      }

      if(this.herolevel){
        queryString += `&hero_level=${this.herolevel}`;
      }

      if(this.gamemap){
        queryString += `&game_map=${this.gamemap}`;
      }

      if(this.hero){
        queryString += `&hero=${this.hero}`;
      }

      if(this.role){
        queryString += `&role=${this.role}`;
      }

    
      if(this.playerrank){
        queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
      }

      if(this.herorank){
        queryString += `&hero_league_tier=${this.convertRankIDtoName(this.herorank)}`;
      }

      if(this.rolerank){
        queryString += `&role_league_tier=${this.convertRankIDtoName(this.rolerank)}`;
      }

      if(this.groupSizeActive){
        queryString += `&group_size=${this.groupsize.join(',')}`;
      }

      if(this.isSLRank){
        queryString += `&rank_source=sl`;

        if(this.slrank){
          queryString += `&sl_rank=${this.slrank}`;
        }
      }

      queryString += `&statfilter=${this.statfilter}`;
      queryString += `&build_type=${this.talentbuildtype}`;
      queryString += `&mirror=${this.mirrormatch}`;

      return queryString;
    },
  },
  watch: {
  },
  methods: {
  	async getData(){
      this.dataError = false;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        this.data = [];

        const response = await this.$globalAsyncPost("/api/v1/global/hero", {
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          role: this.role,
          hero: this.hero,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirror: this.mirrormatch,
          groupsize: this.groupsize,
          rank_source: this.isSLRank ? 'sl' : null,
          sl_rank: this.isSLRank ? this.slrank : null,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        // The API returns heroes in rank order
        let rank = 0;
        (response.data?.data || []).forEach(row => { if (row) row.rank = ++rank; });
        this.data = response.data;
        this.loadingStates = this.sortedData.map(() => false);
      }catch(error){
        this.dataError = true;
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            this.tablewidth = scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
    async getTalentBuildData(hero, index){
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try{
        this.loadingStates[hero] = true;
        const response = await this.$globalAsyncPost("/api/v1/global/talents/build", {
          hero: hero,
          timeframe_type: this.timeframetype,
          timeframe: this.timeframe,
          region: this.region,
          statfilter: this.statfilter,
          hero_level: this.herolevel,
          game_type: this.gametype,
          game_map: this.gamemap,
          league_tier: this.playerrank,
          hero_league_tier: this.herorank,
          role_league_tier: this.rolerank,
          mirror: this.mirrormatch,
          talentbuildtype: this.talentbuildtype,
          rank_source: this.isSLRank ? 'sl' : null,
          sl_rank: this.isSLRank ? this.slrank : null,
        },
        {
          cancelToken: this.cancelTokenSource.token,
        });

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.sortedData[index].talentbuilddata = response.data;
        this.talentbuilddata[hero] = response.data;
        this.loadingStates[hero] = false;

      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable) {
            this.tablewidth = scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
      this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
      this.statfilter = filteredData.single["Stat Filter"] ? filteredData.single["Stat Filter"] : this.statfilter;
      this.statfiltername = this.statfilter ? this.filters.stat_filter.find(s => s.code === this.statfilter).name : null;

      this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
      this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
      this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
      this.hero = this.hero ? this.heroes.find(hero => hero.id === this.hero).name : null;

      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
      this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
      this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
      this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
      this.groupsize = filteredData.multi["Group Size"] ? Array.from(filteredData.multi["Group Size"]) : [];
      this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.talentbuildtype;
      this.ranksource = filteredData.single["Rank Source"] ? filteredData.single["Rank Source"] : 'hp';
      this.slrank = this.isSLRank && filteredData.multi["SL Rank"] ? Array.from(filteredData.multi["SL Rank"]) : null;

      if(this.groupSizeActive && this.statfilter != 'win_rate'){
        this.statfilter = 'win_rate';
        this.statfiltername = this.filters.stat_filter.find(s => s.code === this.statfilter).name;
      }
      
      this.talentbuilddata = {};
      this.toggletalentbuilds = {};
      this.loadingStates = {};
      this.showPatchNotes = false;
      this.sortKey = '';
      this.sortDir = 'desc';

      const currentUrl = window.location.href;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}${this.queryString}`);
   
      this.data = null;

      this.getData();
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
    toggleChartValue() {
      this.togglechart = !this.togglechart;
    },
    togglePatchNotes() {
      this.showPatchNotes = !this.showPatchNotes;
    },
    viewtalentbuilds(hero, index){
      if (!this.talentbuilddata[hero]) {
        this.loadingStates[hero] = true;
        this.getTalentBuildData(hero, index);
      }
      this.toggletalentbuilds[hero] = !this.toggletalentbuilds[hero];
    },
    determineIfLargeData(){
      if(this.timeframetype == "major" || this.timeframetype == "major_grouped" || this.timeframe.length >= 3 || this.statfilter != "win_rate"){
        return  true;
      }
      return false;
    },
    determineIfVeryLargeData(){
      if((this.timeframetype == "major" || this.timeframetype == "major_grouped") && this.timeframe && this.timeframe.length > 1){
        return true;
      }
      return false;
    },
    getGlobalTalentsURL(hero){
      var url = "";
      if(hero){
        if(this.queryString){
          url = '/Global/Talents/' + hero.name + this.queryString.replace(/&group_size=[^&]*/, '');
        }else{
          url = '/Global/Talents/' + hero.name;
        }
      }
      return url;
    },
    getValueFixed(value){
      return value != null && value !== "" && !isNaN(value) ? Number(value).toFixed(2) : "";
    },
    getValueLocal(value){
      return value != null && value !== "" ? value.toLocaleString('en-US') : "";
    },
    getHeroID(){
      if(this.hero){
        return this.heroes.find(hero => hero.name === this.hero).id
      }
      return null;
    },
    convertRankIDtoName(rankIDs) {
      return rankIDs.map(rankID => this.filters.rank_tiers.find(tier => tier.code == rankID).name);
    },
    setURLParameters(){
      if(this.urlparameters["timeframe_type"]){
        this.timeframetype = this.urlparameters["timeframe_type"];
      }
      
      if(this.urlparameters["timeframe"]){
        this.timeframe = this.urlparameters["timeframe"].split(',');
      }

      if(this.urlparameters["game_type"]){
        this.gametype = this.urlparameters["game_type"].split(',');
      }

      if(this.urlparameters["region"]){
        this.region = this.urlparameters["region"].split(',');
      }

      if(this.urlparameters["statfilter"]){
        this.statfilter = this.urlparameters["statfilter"];
        this.statfiltername = this.statfilter ? this.filters.stat_filter.find(s => s.code === this.statfilter).name : null;
      }
      
      if(this.urlparameters["hero_level"]){
        this.herolevel = this.urlparameters["hero_level"].split(',');
      }

      if(this.urlparameters["hero"]){
        this.hero = this.urlparameters["hero"];
      }

      if(this.urlparameters["role"]){
        this.role = this.urlparameters["role"];
      }

      if(this.urlparameters["game_map"]){
        this.gamemap = this.urlparameters["game_map"].split(',');
      }

      if (this.urlparameters["league_tier"]) {
        this.playerrank = this.urlparameters["league_tier"]
          .split(',')
          .map(tierName => {
              const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
              const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
              return tier?.code;
          });
      }

      if (this.urlparameters["hero_league_tier"]) {
        this.herorank = this.urlparameters["hero_league_tier"]
        .split(',')
        .map(tierName => {
            const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
            const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
            return tier?.code;
        });
      }

      if (this.urlparameters["role_league_tier"]) {
        this.rolerank = this.urlparameters["role_league_tier"]
        .split(',')
        .map(tierName => {
            const capitalizedTierName = tierName.charAt(0).toUpperCase() + tierName.slice(1);
            const tier = this.filters.rank_tiers.find(tier => tier.name === capitalizedTierName);
            return tier?.code;
        });
      }


      if (this.urlparameters["build_type"]) {
        this.talentbuildtype = this.urlparameters["build_type"];
      }

      if (this.urlparameters["mirror"]) {
        this.mirrormatch = this.urlparameters["mirror"];
      }

      if (this.urlparameters["rank_source"]) {
        this.ranksource = this.urlparameters["rank_source"];
      }

      if (this.isSLRank) {
        this.gametype = ['sl'];
      }

      if (this.urlparameters["sl_rank"] && this.isSLRank) {
        this.slrank = this.urlparameters["sl_rank"].split(',');
      }

      if (this.urlparameters["group_size"]) {
        this.groupsize = this.urlparameters["group_size"].split(',');

        if(this.groupSizeActive && this.statfilter != 'win_rate'){
          this.statfilter = 'win_rate';
          this.statfiltername = this.filters.stat_filter.find(s => s.code === this.statfilter).name;
        }
      }
    },
  }
}
</script>