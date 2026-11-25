<template>
  <div>
    <global-async-debug-banner page-label="Global Hero Maps" />

    <div v-if="!selectedHero">
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
      <page-heading scope="Global" :heading="'Hero Map Win Rates'" :subheading="'Win rates by map'"></page-heading>
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <!-- As the other global hero pages: the hero's heading, its name the hero picker, with the filters beside it -->
      <player-page-top>
        <template #heading>
          <page-heading scope="Global" :heading="$heroName(selectedHero)" :subheading="'Best maps & map win rates'" :portrait-desktop-only="true">
            <hero-image-wrapper :hero="selectedHero" size="heading" :includehover="false"></hero-image-wrapper>
            <template #headingText>
              <hero-heading-select :heroes="heroes" :value="selectedHero.id" textclass="" @hero-changed="handleInputChange({ value: $event })"></hero-heading-select>
            </template>
          </page-heading>
        </template>
        <filters
          :onFilter="filterData"
          :filters="filters"
          :isLoading="isLoading"

          :timeframetypeinput="timeframetype"
          :timeframeinput="timeframe"
          :gametypeinput="gametype"
          :regioninput="region"
          :herolevelinput="herolevel"
          :playerrankinput="playerrank"
          :herorankinput="herorank"
          :rolerankinput="rolerank"
          :mirrormatchinput="mirrormatch"

          :gametypedefault="gametypedefault"
          :includetimeframe="true"
          :includeregion="true"
          :includeherolevel="true"
          :includegametype="true"
          :includeplayerrank="true"
          :includeherorank="true"
          :includerolerank="true"
          :includemirror="true"
          :advancedfiltering="advancedfiltering"
          >
        </filters>
      </player-page-top>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

      <div v-if="data" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
        <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]"></patch-notes-panel>

        <panel-box title="Map win rates" :subtitle="$t('Win rate for :hero on each map. Click a column to sort.', { hero: $heroName(selectedHero) })">
          <div id="table-container" ref="tablecontainer" class="overflow-clip max-sm:text-xs">
            <table id="responsive-table" class="w-full rounded-table relative" ref="responsivetable">
              <thead>
                <tr>
                  <th
                    v-for="column in dynamicColumns"
                    :key="column.value"
                    :class="['py-2 px-3 text-sm leading-4 whitespace-nowrap', column.value === 'name' ? 'text-left' : 'text-center', column.sortable ? 'cursor-pointer' : '', { 'bg-teal text-white': column.sortable && sortKey === column.value }]"
                    @click="column.sortable ? sortTable(column.value) : null"
                    >
                      <!-- Phones get the short label where there is one -->
                      <template v-if="column.short"><span class="md:hidden">{{ $t(column.short) }}</span><span class="max-md:hidden">{{ $t(column.text) }}</span></template><template v-else>{{ $t(column.text) }}</template><sort-indicator v-if="column.sortable" :column="column.value" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
                  </th>
                </tr>
              </thead>
              <tbody class="text-center">
                <tr v-for="row in sortedData" :key="row.name">
                  <td v-for="column in dynamicColumns" :key="column.value" :class="column.value === 'name' ? 'text-left' : ''">
                    <span v-if="column.value === 'name'" class="font-semibold">{{ $mapName(row.name) }}</span>
                    <span v-else-if="column.value === 'win_rate_change'" :class="['whitespace-nowrap', row.win_rate_change > 0 ? 'text-win' : row.win_rate_change < 0 ? 'text-loss' : 'text-subtext']">
                      <template v-if="row.win_rate_change === null">-</template>
                      <template v-else-if="row.win_rate_change > 0">&#9650; +{{ row.win_rate_change.toFixed(2) }}</template>
                      <template v-else-if="row.win_rate_change < 0">&#9660; {{ row.win_rate_change.toFixed(2) }}</template>
                      <template v-else>0.00</template>
                    </span>
                    <!-- Rates always with two decimals, as on the other global tables -->
                    <template v-else>{{ column.percent && row[column.value] !== null && row[column.value] !== '' && !isNaN(row[column.value]) ? Number(row[column.value]).toFixed(2) + '%' : row[column.value] }}</template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </panel-box>
      </div>
      <div v-else-if="isLoading" class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
        <!-- The collapsed Patch Notes card, so the page doesn't move when it loads -->
        <panel-box v-if="patchNotesUrl" title="Patch Notes" :skeleton="true" :divider="false" class="md:!py-4">
          <template #right>
            <span class="text-sm skeleton-text">{{ $t('Show') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
          </template>
        </panel-box>
        <!-- The table is what's loading, so the message sits on its card's tab -->
        <panel-box title="Map win rates" :subtitle="$t('Win rate for :hero on each map. Click a column to sort.', { hero: $heroName(selectedHero) })" :skeleton="true" class="relative md:!rounded-tl-none">
          <loading-component v-if="determineIfVeryLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Very large amount of data. Compiling can take 5-10 minutes. Please be patient.') }}</loading-component>
          <loading-component v-else-if="determineIfLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Large amount of data. Please be patient.') }}</loading-component>
          <loading-component v-else :compact="true" :tab="true" :textoverride="true">{{ $t('Loading map stats...') }}</loading-component>
          <table-skeleton :bare="true" :rows="13" :columns="['stat', 'stat', 'stat', 'stat']"></table-skeleton>
        </panel-box>
      </div>
      <div v-else-if="dataError" class="flex items-center justify-center">
        {{ $t('Error: Reload page/filter') }}
      </div>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../../utils/scaleTable';
  export default {
    name: 'GlobalHeroMapStats',
    components: {
    },
    props: {
      inputhero: Object,
      heroes: Array,
      filters: {
        type: Object,
        required: true
      },
      gametypedefault: Array,
      defaulttimeframe: Array,
      defaulttimeframetype: String,
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        dataError: false,
        windowWidth: window.innerWidth,
        isLoading: false,
        infoText: "Hero Maps provide information on which maps are good for each hero",
        selectedHero: null,
        data: null,
        sortKey: '',
        sortDir: 'desc',
        cancelTokenSource: null,
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        gametype: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,
      }
    },
    created(){
      this.gametype = this.gametypedefault;
      this.timeframe = this.defaulttimeframe;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      
      if(this.inputhero){
        this.selectedHero = this.inputhero;
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
      patchNotesUrl() {
        if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
          return null;
        }
        const selectedVersion = this.timeframe[0];
        const match = this.filters.timeframes.find(t => t.code === selectedVersion);
        return match && match.patch_notes_url ? match.patch_notes_url : null;
      },
       dynamicColumns() {
        return [
          { text: 'Map', value: 'name', sortable: true },
          { text: 'Win Rate', value: 'win_rate', sortable: true, percent: true },
          ...(this.showWinRateChange ? [{ text: 'Change', value: 'win_rate_change', sortable: true }] : []),
          ...(this.gametype.includes("sl") ? [{ text: 'Ban Rate', value: 'ban_rate', sortable: true, percent: true }] : []),
          { text: 'Games Played', short: 'Games', value: 'games_played', sortable: true },
        ];
      },
      // The server only sends change for one unfiltered patch and game type
      showWinRateChange() {
        return Array.isArray(this.data) && this.data.some(row => row.win_rate_change !== null && row.win_rate_change !== undefined);
      },
      sortedData() {
        if (!this.sortKey) return this.data;
        return this.data.slice().sort((a, b) => {
          const valA = a[this.sortKey];
          const valB = b[this.sortKey];
          if (this.sortDir === 'asc') {
            return valA < valB ? -1 : 1;
          } else {
            return valA > valB ? -1 : 1;
          }
        });
      },
    },
    watch: {
    },
    methods: {
      clickedHero(hero) {
        this.selectedHero = hero;
        this.data = null;

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Map Stats | Heroes Profile`;

        let basePath = '/Global/Hero/Maps';
        history.pushState(null, null, `${basePath}/${this.selectedHero.name}`);
        this.getData();
      },
      async getData(){
        this.dataError = false;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/global/hero/map", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          
          this.data = response.data;
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

        this.isLoading = false;
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
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;



        this.updateQueryString();
        this.data = null; 
        this.getData();
      },
      updateQueryString(){
        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
        queryString += `&game_type=${this.gametype}`;

        if(this.region){
          queryString += `&region=${this.region}`;
        }
        
        if(this.herolevel){
          queryString += `&hero_level=${this.herolevel}`;
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

        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframetype == "major_major" || this.timeframe.length >= 3){
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
      redirectChangeHero(){
        window.location.href = "/Global/Hero/Maps";
      },
      sortTable(key) {
        if (key === this.sortKey) {
          this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortDir = 'desc';
        }
        this.sortKey = key;
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
        
        if(this.urlparameters["hero_level"]){
          this.herolevel = this.urlparameters["hero_level"].split(',');
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

        if (this.urlparameters["mirror"]) {
          this.mirrormatch = this.urlparameters["mirror"];
        }
      },
      handleInputChange(eventPayload){
        if(eventPayload.value != ""){
          this.selectedHero = this.heroes.find(hero => hero.id === eventPayload.value);

          // Update document title dynamically
          document.title = `${this.selectedHero.name} Map Stats | Heroes Profile`;

          let currentPath = window.location.pathname;
          let newPath = currentPath.replace(/\/[^/]*$/, `/${this.selectedHero.name}`);
          history.pushState(null, null, newPath);
          this.updateQueryString();

          this.data = null;

          //Have to use setTimeout to make this occur on next tic to allow header info/text to update properly.  
          setTimeout(() => {
            this.getData();
          }, .25);
        }


      },
    }
  }
</script>