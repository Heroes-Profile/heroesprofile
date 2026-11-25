<template>
  <div>
    <global-async-debug-banner page-label="Global Draft" />
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" :infoText2="infoText2" -->
    <page-heading scope="Global" v-if="!selectedHero" :heading="'Hero Draft Stats & Pick Order'" :subheading="defaulttimeframe && defaulttimeframe.length ? $t('Pick & ban order by hero · Patch :patch', { patch: defaulttimeframe[0] }) : 'Pick & ban order by hero'"></page-heading>
    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes" subtitle="Pick a hero to see when it is usually picked and banned in the draft, and how its win rate changes with pick order."></hero-selection>
    </div>

    <div v-else>
      <!-- The hero on the left, its name the hero picker, the filters in their card beside it, as on the player pages -->
      <player-page-top>
        <template #heading>
          <page-heading scope="Global" :heading="$heroName(selectedHero)" :subheading="'Draft stats & pick order'" :portrait-desktop-only="true">
            <hero-image-wrapper :hero="selectedHero" size="heading" :includehover="false"></hero-image-wrapper>
            <!-- The hero name is the hero picker (no Heroes filter on this page) -->
            <template #headingText>
              <hero-heading-select :heroes="heroes" :value="selectedHero.id" textclass="" @hero-changed="handleInputChange({ value: $event })"></hero-heading-select>
            </template>
          </page-heading>
        </template>
        <filters
          :onFilter="filterData"
          :filters="filters"
          :isLoading="isLoading"
          :heroinput="selectedHero.id"
          :timeframetypeinput="timeframetype"
          :timeframeinput="timeframe"
          :regioninput="region"
          :herolevelinput="herolevel"
          :gamemapinput="gamemap"
          :playerrankinput="playerrank"
          :herorankinput="herorank"
          :rolerankinput="rolerank"
          :gametypedefault="gametypedefault"
          :includetimeframe="true"
          :includeregion="true"
          :includeherolevel="true"
          :includegamemap="true"
          :includeplayerrank="true"
          :includeherorank="true"
          :includerolerank="true"
          :advancedfiltering="advancedfiltering"
          >
        </filters>
      </player-page-top>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

      <div v-if="draftdata" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <patch-notes-panel v-if="patchNotesUrl" :version="timeframe[0]" class="!max-w-none !mb-0"></patch-notes-panel>

        <!-- Headline tiles -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
          <div v-for="tile in summaryTiles" :key="tile.label" :class="['bg-surface rounded p-6 flex flex-col gap-1 min-w-0 border-l-4', tile.border]">
            <span class="text-sm text-subtext">{{ $t(tile.label) }}</span>
            <span class="text-3xl font-semibold leading-[1.2]">{{ tile.value }}</span>
            <span class="text-sm text-subtext">{{ tile.caption }}</span>
          </div>
        </div>

        <panel-box title="Pick & ban order" :subtitle="$t('How often :hero is picked or banned at each draft slot, and how the team does afterwards.', { hero: $heroName(selectedHero) })">
          <template #right>
            <span class="flex items-center gap-4 text-sm text-subtext">
              <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-[0.125rem] bg-red"></span>{{ $t('Ban') }}</span>
              <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-[0.125rem] bg-teal"></span>{{ $t('Pick') }}</span>
            </span>
          </template>
          <div id="table-container" ref="tablecontainer" class="overflow-clip">
            <!-- Fixed columns never overflow, so on phones it's laid out wide and scaleTableToFit shrinks it -->
            <table id="responsive-table" class="min-w-0 max-md:min-w-[60rem] w-full rounded-table table-fixed" ref="responsivetable">
              <colgroup v-if="showWinRateChange">
                <col class="w-[15%]">
                <col class="w-[33%]">
                <col class="w-[13%]">
                <col class="w-[13%]">
                <col class="w-[13%]">
                <col class="w-[13%]">
              </colgroup>
              <colgroup v-else>
                <col class="w-[15%]">
                <col class="w-[40%]">
                <col class="w-[15%]">
                <col class="w-[15%]">
                <col class="w-[15%]">
              </colgroup>
              <thead>
                <tr>
                  <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Pick order') }}</th>
                  <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Pick / ban rate at position') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Team wins') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Team losses') }}</th>
                  <th class="py-2 px-4 text-center text-sm leading-4">{{ $t('Team win rate') }}</th>
                  <th v-if="showWinRateChange" class="py-2 px-4 text-center text-sm leading-4">{{ $t('Change') }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="phase in phases" :key="phase.label">
                  <tr>
                    <td :colspan="showWinRateChange ? 6 : 5" class="px-4 pt-4 pb-2 text-xs font-bold uppercase tracking-wider text-subtext">{{ $t(phase.label) }}</td>
                  </tr>
                  <tr v-for="row in phase.rows" :key="row.pick_number">
                    <td class="py-2.5 px-4">
                      <span :class="['flex items-center gap-2 border-l-2 pl-2', row.ban ? 'border-red' : 'border-teal']">
                        <span :class="['px-1.5 py-0.5 rounded-[0.25rem] text-[0.625rem] font-bold uppercase', row.ban ? 'bg-red/20 text-lred' : 'bg-teal/20 text-lteal']">{{ row.ban ? $t('Ban') : $t('Pick') }}</span>
                        <span class="font-semibold">{{ $t(row.label) }}</span>
                      </span>
                    </td>
                    <td class="py-2.5 px-4"><stat-bar :value="maxPopularity ? (row.popularity / maxPopularity) * 100 : 0" :display-text="row.popularity.toFixed(2)" suffix="%" :color="row.ban ? 'red' : 'teal'"></stat-bar></td>
                    <td class="py-2.5 px-4 text-center tabular-nums">{{ row.wins.toLocaleString('en-US') }}</td>
                    <td class="py-2.5 px-4 text-center tabular-nums">{{ row.losses.toLocaleString('en-US') }}</td>
                    <td :class="['py-2.5 px-4 text-center font-semibold tabular-nums', row.win_rate > 50 ? 'text-win' : '']">{{ row.win_rate.toFixed(2) }}%</td>
                    <td v-if="showWinRateChange" :class="['py-2.5 px-4 text-center whitespace-nowrap tabular-nums', row.win_rate_change > 0 ? 'text-win' : row.win_rate_change < 0 ? 'text-loss' : 'text-subtext']">
                      <template v-if="row.win_rate_change === null || row.win_rate_change === undefined">-</template>
                      <template v-else-if="row.win_rate_change > 0">&#9650; +{{ row.win_rate_change.toFixed(2) }}</template>
                      <template v-else-if="row.win_rate_change < 0">&#9660; {{ row.win_rate_change.toFixed(2) }}</template>
                      <template v-else>0.00</template>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </panel-box>
      </div>
      <!-- The same layout in grey while the draft stats load, the message on the order card's tab -->
      <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <!-- The collapsed Patch Notes card, so the page doesn't move when it loads -->
        <panel-box v-if="patchNotesUrl" title="Patch Notes" :skeleton="true" :divider="false" class="md:!py-4">
          <template #right>
            <span class="text-sm skeleton-text">{{ $t('Show') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
          </template>
        </panel-box>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
          <div v-for="tile in 4" :key="tile" class="bg-surface rounded p-6 flex flex-col gap-1 min-w-0 border-l-4 border-surface-3">
            <span class="text-sm skeleton-text w-fit">Banned most at</span>
            <span class="text-3xl font-semibold leading-[1.2] skeleton-text w-fit">Ban 1</span>
            <span class="text-sm skeleton-text w-fit">00.00% of drafts</span>
          </div>
        </div>
        <panel-box title="Pick & ban order" subtitle="How often this hero is picked or banned at each draft slot, and how the team does afterwards." :skeleton="true" class="relative md:!rounded-tl-none">
          <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading draft stats...') }}</loading-component>
          <table-skeleton :bare="true" :rows="16" :columns="['text', 'bar', 'stat', 'stat', 'stat']"></table-skeleton>
        </panel-box>
      </div>
      <div v-else-if="dataError" class="flex items-center justify-center">
        {{ $t('Error: Reload page/filter') }}
      </div>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
  export default {
    name: 'GlobalDraftStats',
    components: {
    },
    props: {
      filters: Object,
      inputhero: Object,
      heroes: Array,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
      defaultbuildtype: String,
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        dataError: false,
        windowWidth: window.innerWidth,
        isLoading: null,
        cancelTokenSource: null,
        infoText1: "Storm League Hero pick rates, ban rates, and pick order rate.",
        infoText2: "Teams win, losses, and win rate are based on where they pick a hero in the draft. So if a team bans Abathur at the first position of the draft, we are showing those teams wins and losses and win rates as well as when teams actually pick Abathur.",
        selectedHero: null,
        draftdata: null,

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
      }
    },
    created(){
      this.selectedHero = this.inputhero;
      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      if(this.selectedHero){
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
      // The draft's slots in order, each marked ban or pick
      rows() {
        return (Array.isArray(this.draftdata) ? this.draftdata : [])
          .slice()
          .sort((a, b) => a.pick_number - b.pick_number)
          .map(row => {
            const label = this.determinePickOrBan(row.pick_number) || '';
            return { ...row, label, ban: label.startsWith('Ban') };
          });
      },
      // The draft's four phases, in the order they happen
      phases() {
        return [
          { label: 'First ban phase', numbers: [0, 1, 2, 3] },
          { label: 'First pick phase', numbers: [4, 5, 6, 7, 8] },
          { label: 'Second ban phase', numbers: [9, 10] },
          { label: 'Second pick phase', numbers: [11, 12, 13, 14, 15] },
        ].map(phase => ({ label: phase.label, rows: this.rows.filter(row => phase.numbers.includes(row.pick_number)) }))
          .filter(phase => phase.rows.length);
      },
      // The server only sends change for one unfiltered patch and game type
      showWinRateChange() {
        return this.rows.some(row => row.win_rate_change !== null && row.win_rate_change !== undefined);
      },
      maxPopularity() {
        return Math.max(0, ...this.rows.map(row => row.popularity));
      },
      // Where the hero is banned and picked most, and the slots its team does best from
      summaryTiles() {
        const best = (rows, field) => rows.reduce((top, row) => (!top || row[field] > top[field] ? row : top), null);
        const bans = this.rows.filter(row => row.ban);
        const picks = this.rows.filter(row => !row.ban);
        const percent = value => value.toFixed(2);
        const tiles = [];
        const banned = best(bans, 'popularity');
        if (banned) tiles.push({ label: 'Banned most at', value: this.$t(banned.label), caption: this.$t(':rate% of drafts', { rate: percent(banned.popularity) }), border: 'border-red' });
        const picked = best(picks, 'popularity');
        if (picked) tiles.push({ label: 'Picked most at', value: this.$t(picked.label), caption: this.$t(':rate% of drafts', { rate: percent(picked.popularity) }), border: 'border-teal' });
        const bestTeam = best(this.rows, 'win_rate');
        if (bestTeam) tiles.push({ label: 'Best team win rate', value: this.$t(bestTeam.label), caption: this.$t(bestTeam.ban ? ':rate% when banned there' : ':rate% when picked there', { rate: percent(bestTeam.win_rate) }), border: 'border-win' });
        const bestPick = best(picks, 'win_rate');
        if (bestPick) tiles.push({ label: 'Best pick slot', value: this.$t(bestPick.label), caption: this.$t(':rate% team win rate', { rate: percent(bestPick.win_rate) }), border: 'border-yellow' });
        return tiles;
      },
      patchNotesUrl() {
        if (this.timeframetype !== 'minor' || !this.timeframe || this.timeframe.length !== 1) {
          return null;
        }
        const selectedVersion = this.timeframe[0];
        const match = this.filters.timeframes.find(t => t.code === selectedVersion);
        return match && match.patch_notes_url ? match.patch_notes_url : null;
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
          const response = await this.$globalAsyncPost("/api/v1/global/draft", {
            hero: this.selectedHero.name,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });
          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          this.draftdata = response.data;
        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.$nextTick(() => {
            if (window.matchMedia('(max-width: 767px)').matches) {
              scaleTableToFit(this.$refs.responsivetable, this.$refs.tablecontainer);
            }
          });
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      clickedHero(hero){
        this.selectedHero = hero;
        this.draftdata = null;

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Draft Stats | Heroes Profile`;

        let basePath = '/Global/Draft';
        history.pushState(null, null, `${basePath}/${this.selectedHero.name}`);
        this.getData();
      },
      herochanged(eventPayload){
      },
      filterData(filteredData){
        const heroId = filteredData.single.Heroes;
        if (heroId && heroId !== this.selectedHero.id) {
          this.selectedHero = this.heroes.find(hero => hero.id === heroId) || this.selectedHero;
          document.title = `${this.selectedHero.name} Draft Stats | Heroes Profile`;
          history.pushState(null, null, window.location.pathname.replace(/\/[^/]*$/, `/${this.selectedHero.name}`));
        }
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;


        this.updateQueryString();
        this.draftdata = null;
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

        if(this.gamemap){
          queryString += `&game_map=${this.gamemap}`;
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

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);
      },
      determinePickOrBan(pick_number) {
        const mapping = {
         0: "Ban 1",
         1: "Ban 2",
         2: "Ban 3",
         3: "Ban 4",
         4: "Pick 1",
         5: "Pick 2",
         6: "Pick 3",
         7: "Pick 4",
         8: "Pick 5",
         9: "Ban 5",
         10: "Ban 6",
         11: "Pick 6",
         12: "Pick 7",
         13: "Pick 8",
         14: "Pick 9",
         15: "Pick 10",
       };
       
       return mapping[pick_number];
     },
     redirectChangeHero(){
      window.location.href = "/Global/Draft";
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
    },
    handleInputChange(eventPayload){
      if(eventPayload.value != ""){
        this.selectedHero = this.heroes.find(hero => hero.id === eventPayload.value);

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Draft Stats | Heroes Profile`;

        let currentPath = window.location.pathname;
        let newPath = currentPath.replace(/\/[^/]*$/, `/${this.selectedHero.name}`);
        history.pushState(null, null, newPath);
        this.updateQueryString();

        this.draftdata = null;

        //Have to use setTimeout to make this occur on next tic to allow header info/text to update properly.  
        setTimeout(() => {
          this.getData();
        }, .25);
      }


      },
  }
}
</script>