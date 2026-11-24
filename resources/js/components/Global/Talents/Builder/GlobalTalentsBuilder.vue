<template>
  <div>
    <global-async-debug-banner page-label="Global Talent Builder" />
    <page-heading v-if="!selectedHero" :heading="'Talent Calculator & Builder'"></page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <!-- The hero on the left, the filters (with the hero among them) in their card beside it, as on the draft page -->
      <player-page-top>
        <template #heading>
          <page-heading :heading="$heroName(selectedHero)" :subheading="'Talent calculator & builder'" :portrait-desktop-only="true">
            <hero-image-wrapper :hero="selectedHero" size="heading" :includehover="false"></hero-image-wrapper>
            <template #aboveHeading>
              <a href="/Global/Talents/Builder" class="inline-flex max-md:flex max-md:w-fit items-center gap-1.5 text-sm font-normal text-lteal hover:underline mb-1"><basil-icon name="arrow-small" class="rotate-180 text-xs text-white"></basil-icon>{{ $t('All heroes') }}</a>
            </template>
          </page-heading>
        </template>
        <filters
          :onFilter="filterData"
          :filters="filters"
          :isLoading="isLoading"
          :includehero="true"
          :heroinput="selectedHero.id"
          :timeframetypeinput="timeframetype"
          :timeframeinput="timeframe"
          :gametypeinput="gametype"
          :regioninput="region"
          :herolevelinput="herolevel"
          :gamemapinput="gamemap"
          :playerrankinput="playerrank"
          :herorankinput="herorank"
          :rolerankinput="rolerank"
          :mirrormatchinput="mirrormatch"
          :gametypedefault="gametypedefault"
          :includetimeframe="true"
          :includeregion="true"
          :includeherolevel="true"
          :includegametype="true"
          :includegamemap="true"
          :includeplayerrank="true"
          :includeherorank="true"
          :includerolerank="true"
          :includemirror="true"
          :advancedfiltering="advancedfiltering"
          >
        </filters>
      </player-page-top>
      <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false" ref="dynamicAddPlacement"></dynamic-banner-ad>

      <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
        <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]"></patch-notes-panel>

        <!-- The diagram and reset on the left, how the builder loads and lays out on the right -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex flex-wrap items-center gap-4 max-sm:w-full">
            <button type="button" :disabled="!paths || !paths.length" class="flex items-center justify-between gap-4 h-[3.125rem] w-full sm:w-[26rem] px-4 rounded-control bg-surface-3 text-lg disabled:opacity-50" @click="pathsOpen = !pathsOpen">
              <span class="truncate">{{ $t('Alluvial diagram') }}<template v-if="paths && paths.length"> · {{ $t(':count builds', { count: paths.length.toLocaleString('en-US') }) }}</template></span>
              <basil-icon name="arrow-small" :class="['shrink-0 transition-transform', pathsOpen ? '-rotate-90' : 'rotate-90']"></basil-icon>
            </button>
            <custom-button class="max-sm:w-full" :text="'Reset talent data'" :alt="'Reset talent data'" :ignoreclick="true" @click="resetTalentData"></custom-button>
          </div>
          <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-subtext">
            <span class="flex items-center gap-2">{{ $t('Load') }}<tab-button :small="true" :dark="true" :tabs="[{ key: 'auto', text: 'Auto' }, { key: 'filter', text: 'On filter' }]" :overridedefaultside="localLoad" :ignoreclick="true" @tab-click="loadStyleSelected"></tab-button></span>
            <span v-if="!isMobile" class="flex items-center gap-2">{{ $t('Layout') }}<tab-button :small="true" :dark="true" :tabs="[{ key: 'vertical', text: 'Vertical' }, { key: 'horizontal', text: 'Horizontal' }]" :overridedefaultside="effectiveStyle" :ignoreclick="true" @tab-click="layoutStyleSelected"></tab-button></span>
          </div>
        </div>

        <talent-build-paths v-if="pathsOpen && data && paths && paths.length" :data="data" :paths="paths"></talent-build-paths>

        <div v-if="dataError && !isLoading" class="flex items-center justify-center">
          {{ $t('Error: Reload page/filter') }}
        </div>

        <!-- First load: the two cards in grey -->
        <div v-else-if="!data" class="grid xl:grid-cols-[minmax(0,1fr)_35rem] gap-6 xl:gap-8 items-start">
          <panel-box title="Pick talents" subtitle="Stats update for builds that match your picks. Greyed talents have no games with this build." :skeleton="true" :divider="false" class="relative md:!rounded-tl-none">
            <loading-component v-if="determineIfVeryLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Very large amount of data. Compiling can take 5-10 minutes. Please be patient.') }}</loading-component>
            <loading-component v-else-if="determineIfLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Large amount of data. Please be patient.') }}</loading-component>
            <loading-component v-else :compact="true" :tab="true" :textoverride="true">{{ $t('Loading talents...') }}</loading-component>
            <div class="flex flex-col gap-2.5">
              <div v-for="level in 7" :key="level" class="grid grid-cols-[4.25rem_minmax(0,1fr)] md:grid-cols-[5rem_minmax(0,1fr)] gap-2.5">
                <skeleton-box class="h-[4.25rem]"></skeleton-box>
                <div class="grid grid-cols-[repeat(auto-fill,minmax(14rem,1fr))] xl:grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-2.5">
                  <skeleton-box v-for="talent in 3" :key="talent" class="h-[4.25rem]"></skeleton-box>
                </div>
              </div>
            </div>
          </panel-box>
          <panel-box title="Selected build" :skeleton="true" :divider="false" class="border-t-4 border-teal">
            <div class="grid grid-cols-7 gap-2"><skeleton-box v-for="slot in 7" :key="slot" class="w-[2.625rem] h-[2.625rem] mx-auto !rounded-full"></skeleton-box></div>
            <div class="grid grid-cols-3 gap-3"><skeleton-box v-for="stat in 3" :key="stat" class="h-[4.5rem]"></skeleton-box></div>
            <skeleton-box class="h-16"></skeleton-box>
            <skeleton-box class="h-[3.125rem]"></skeleton-box>
          </panel-box>
        </div>

        <!-- While a pick reloads the stats, the cards stay and fade -->
        <div v-else :class="['grid xl:grid-cols-[minmax(0,1fr)_35rem] gap-6 xl:gap-8 items-start transition-opacity', { 'opacity-60 pointer-events-none': isLoading }]">
          <panel-box title="Pick talents" subtitle="Stats update for builds that match your picks. Greyed talents have no games with this build." :divider="false" :loading="isLoading">
            <div :class="effectiveStyle === 'horizontal' ? 'overflow-x-auto' : ''">
              <div :class="effectiveStyle === 'horizontal' ? 'grid grid-cols-[repeat(7,minmax(7rem,1fr))] gap-2 items-start' : 'flex flex-col gap-2.5'">
                <talent-builder-column v-for="level in LEVEL_NUMBERS" :key="level" :data="data[String(level)]" :level="level" :clickedData="clickedData" :horizontal="effectiveStyle === 'horizontal'" @talent-clicked="talentClicked" @clear="removeLevelSelections"></talent-builder-column>
              </div>
            </div>
          </panel-box>

          <panel-box title="Selected build" :divider="false" class="border-t-4 border-teal">
            <template #title>
              <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                <p class="font-normal">{{ $t('Build win rate and how many players have played that exact build. Win rate for builds to level 20 are inflated because teams that make it to level 20 win more. Therefore it is not an accurate representation of a builds viabllity. See table below for that calculation') }}</p>
              </round-image>
            </template>

            <!-- The seven picks, empty where a level is open -->
            <div class="grid grid-cols-7 gap-2">
              <span v-for="(level, index) in LEVEL_NUMBERS" :key="level" class="flex flex-col items-center gap-2">
                <talent-image-wrapper v-if="builddata && builddata[LEVEL_KEYS[index]]" :talent="builddata[LEVEL_KEYS[index]]" size="row"></talent-image-wrapper>
                <span v-else class="block w-[2.625rem] h-[2.625rem] rounded-full bg-surface-3"></span>
                <span :class="['text-xs', clickedData[level] ? 'text-lteal' : 'text-subtext']">{{ level }}</span>
              </span>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <stat-box class="!m-0 !min-w-0" :title="$t('Games played')" :value="builddata ? Number(builddata.games_played || 0).toLocaleString('en-US') : '—'"></stat-box>
              <stat-box class="!m-0 !min-w-0" :title="$t('Win rate')" :value="builddata && builddata.games_played ? Number(builddata.win_rate).toFixed(2) + '%' : '—'"></stat-box>
              <stat-box class="!m-0 !min-w-0" :title="$t('Talents picked')" :value="$t(':count of 7', { count: pickedCount })"></stat-box>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-control bg-surface-1 px-4 py-3">
              <div class="flex flex-col gap-1 min-w-0">
                <span class="text-xs text-subtext">{{ $t('Copy build to game') }}</span>
                <build-code v-if="pickedCount === 7 && builddata" :build="builddata" :hero="selectedHero" show="code" align="start"></build-code>
                <span v-else class="text-sm">{{ $t('Pick all 7 talents to get a build code') }}</span>
              </div>
              <i v-if="pickedCount !== 7" class="fas fa-copy text-subtext/50 shrink-0"></i>
            </div>

            <custom-button v-if="pickedCount" class="w-full font-semibold" color="teal" :text="'Find matching replays'" :alt="'Find matching replays'" :ignoreclick="true" @click="findReplays"></custom-button>
            <span v-else class="btn bg-teal w-full text-center font-semibold opacity-50 cursor-not-allowed" :title="$t('Pick a talent first')">{{ $t('Find matching replays') }}</span>

            <!-- The talent most games took on each open level -->
            <div v-if="suggestions.length" class="flex flex-col gap-2 pt-4 border-t border-surface-3">
              <h3 class="text-lg font-semibold">{{ $t('Most played from here') }}</h3>
              <p class="text-sm text-subtext mb-1">{{ $t('The talent most games took on each open level.') }}</p>
              <div v-for="item in suggestions" :key="item.level" class="flex items-center gap-3 rounded-control bg-surface-1 px-3 py-2">
                <span class="w-8 shrink-0 rounded-md bg-teal/20 py-0.5 text-center text-xs text-lteal">{{ item.level }}</span>
                <talent-image-wrapper :talent="item.talent" size="small"></talent-image-wrapper>
                <span class="flex flex-col flex-1 min-w-0">
                  <span class="font-semibold truncate">{{ $talentName(item.talent) }}</span>
                  <span class="text-xs text-subtext">{{ $t(':played games', { played: item.talent.games_played.toLocaleString('en-US') }) }}</span>
                </span>
                <span :class="['font-semibold tabular-nums', item.talent.win_rate > 50 ? 'text-win' : '']">{{ Number(item.talent.win_rate).toFixed(2) }}%</span>
                <button type="button" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-control hover:bg-surface-3 transition-colors text-lg" :title="$t('Pick :talent', { talent: $talentName(item.talent) })" @click="talentClicked(item.talent, 0, item.level)">+</button>
              </div>
            </div>
          </panel-box>
        </div>

        <panel-box v-if="showReplayList" id="matching-replays" :class="['scroll-mt-4', { 'relative md:!rounded-tl-none': replaysLoading }]" title="Matching replays" :subtitle="replaysSubtitle" :skeleton="replaysLoading" :divider="false">
          <template #title>
            <round-image class="text-base" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
              <p class="font-normal">{{ $t('Possible Replays do not take into account Hero Level, Player Rank, Hero Rank, Role Rank, or Mirror Match Filter options') }}</p>
            </round-image>
          </template>
          <template v-if="replaysLoading">
            <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Loading replays...') }}</loading-component>
            <table-skeleton :bare="true" :rows="8" :columns="['stat', 'talents', 'stat', 'stat']"></table-skeleton>
          </template>
          <p v-else-if="!replays || !replays.length" class="text-subtext">{{ $t('No replays match these talents.') }}</p>
          <template v-else>
            <scroll-hint-container>
              <table class="w-full min-w-[44rem] rounded-table">
                <thead>
                  <tr>
                    <th class="py-2 px-6 text-left text-sm leading-4 w-[16%]">{{ $t('Match ID') }}</th>
                    <th class="py-2 px-3 text-center text-sm leading-4">{{ $t('Talents') }}</th>
                    <th class="py-2 px-3 text-center text-sm leading-4 w-[9%]">{{ $t('Win') }}</th>
                    <th class="py-2 px-3 text-center text-sm leading-4 w-[9%]">{{ $t('Replay') }}</th>
                  </tr>
                </thead>
                <tbody class="text-center">
                  <tr v-for="row in pagedReplays" :key="row.replayID">
                    <td class="py-2 px-6 text-left font-semibold"><a class="group/bt inline-flex items-center" :href="`/Match/Single/${row.replayID}`"><span class="group-hover/bt:underline">{{ row.replayID }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a></td>
                    <td class="py-2 px-3">
                      <div class="flex gap-1.5 items-center justify-center">
                        <template v-for="key in LEVEL_KEYS" :key="key">
                          <talent-image-wrapper v-if="row[key]" :talent="row[key]" size="small"></talent-image-wrapper>
                          <span v-else class="block w-10 h-10 rounded-full bg-surface-3"></span>
                        </template>
                      </div>
                    </td>
                    <td class="py-2 px-3"><span v-if="row.winner !== undefined" :class="['rounded-md px-2 py-0.5 text-xs font-bold uppercase', row.winner ? 'bg-win/20 text-win' : 'bg-loss/20 text-loss']">{{ row.winner ? $t('Win') : $t('Loss') }}</span></td>
                    <td class="py-2 px-3"><a :href="`/Match/Single/${row.replayID}`" class="inline-flex w-8 h-8 items-center justify-center rounded-control hover:bg-surface-3" :aria-label="$t('Open match')"><basil-icon name="arrow-small"></basil-icon></a></td>
                  </tr>
                </tbody>
              </table>
            </scroll-hint-container>
            <div class="flex flex-wrap items-center justify-between gap-4">
              <span class="text-sm text-subtext">{{ $t('Showing :from–:to of :total replays', { from: (replayPage - 1) * REPLAYS_PER_PAGE + 1, to: Math.min(replayPage * REPLAYS_PER_PAGE, replays.length), total: replays.length }) }}</span>
              <pagination-bar v-if="replayPages > 1" :current="replayPage" :last="replayPages" @page="replayPage = $event"></pagination-bar>
            </div>
          </template>
        </panel-box>
      </div>
    </div>
  </div>
</template>

<script>
  import Cookies from 'js-cookie';

  const LEVEL_NUMBERS = [1, 4, 7, 10, 13, 16, 20];
  const LEVEL_KEYS = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

  export default {
    name: 'GlobalTalentsBuilder',
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
      talentimages: Object,
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,
      talentbuilderstyle: { type: String, default: 'vertical' },
      talentbuilderload: { type: String, default: 'auto' },
      authenticated: Boolean,
    },
    data(){
      return {
        LEVEL_NUMBERS,
        LEVEL_KEYS,
        REPLAYS_PER_PAGE: 8,
        replaysLoading: false,
        replayPage: 1,
        dataError: false,
        cancelTokenSource: null,
        isLoading: false,
        selectedHero: null,
        data: null,
        replays: null,
        builddata: null,
        paths: null,
        pathsOpen: false,
        infoText1: "Pick a talent in any tier below to start. The tool will calculate talent win rates dynamically as you change your build choices.",
        infoText2: "Build win chance and win rate, along with games played by the build can be found below the talents.",
        clickedData: {
          1: null,
          4: null,
          7: null,
          10: null,
          13: null,
          16: null,
          20: null,
        },

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        role: null,
        hero: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,
        showReplayList: false,
        isMobile: window.innerWidth < 1024,
        localStyle: 'vertical',
        localLoad: 'auto',
      }
    },
    created(){
      this.localStyle = this.talentbuilderstyle;
      this.localLoad = this.talentbuilderload;
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
      this._onResize = () => { this.isMobile = window.innerWidth < 1024; };
      window.addEventListener('resize', this._onResize);
    },
    beforeUnmount() {
      window.removeEventListener('resize', this._onResize);
    },
    computed: {
      effectiveStyle() {
        return this.isMobile ? 'vertical' : this.localStyle;
      },
      pickedCount() {
        return Object.values(this.clickedData).filter(id => id != null).length;
      },
      // On each open level, the talent with the most games
      suggestions() {
        if (!this.data) {
          return [];
        }
        return LEVEL_NUMBERS.filter(level => this.clickedData[level] == null).map(level => {
          const talents = Object.values(this.data[String(level)] || {}).filter(talent => talent.games_played);
          const talent = talents.sort((a, b) => b.games_played - a.games_played)[0];
          return talent ? { level, talent } : null;
        }).filter(Boolean);
      },
      replaysSubtitle() {
        const names = this.builddata ? LEVEL_KEYS.map(key => this.builddata[key]).filter(Boolean).map(talent => this.$talentName(talent)) : [];
        return names.length ? this.$t('Games where :hero took :talents.', { hero: this.$heroName(this.selectedHero), talents: names.join(', ') }) : '';
      },
      replayPages() {
        return Math.ceil((this.replays?.length || 0) / this.REPLAYS_PER_PAGE);
      },
      pagedReplays() {
        return (this.replays || []).slice((this.replayPage - 1) * this.REPLAYS_PER_PAGE, this.replayPage * this.REPLAYS_PER_PAGE);
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
      showReplayList(newValue) {
        if (newValue) {
          this.getReplayData();
        } 
      }    
    },
    methods: {
      // Opens the replay list, or reloads it for the current picks, and scrolls to it
      findReplays() {
        if (this.showReplayList) {
          this.getReplayData();
        } else {
          this.showReplayList = true;
        }
        this.$nextTick(() => document.getElementById('matching-replays')?.scrollIntoView({ behavior: 'smooth' }));
      },
      layoutStyleSelected(key) {
        this.localStyle = key === 'horizontal' ? 'horizontal' : 'vertical';
        Cookies.set('talentbuilderstyle', this.localStyle, { expires: 365, path: '/' });

        if (this.authenticated) {
          this.$axios.post('/api/v1/profile/save/settings', { talentbuilderstyle: this.localStyle }).catch(() => {});
        }
      },
      loadStyleSelected(key) {
        this.localLoad = key === 'filter' ? 'filter' : 'auto';
        Cookies.set('talentbuilderload', this.localLoad, { expires: 365, path: '/' });

        if (this.authenticated) {
          this.$axios.post('/api/v1/profile/save/settings', { talentbuilderload: this.localLoad }).catch(() => {});
        }
      },
      async getData(){
        if(this.showReplayList){
          this.showReplayList = false;
        }
        this.dataError = false;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
      
        this.replays = null;
        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents/builder", {
            hero: this.selectedHero.name,
            selectedtalents: this.clickedData,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
            build_paths: true,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }

          this.data = response.data.talentData;
          this.replays = response.data.replays;
          this.builddata = response.data.buildData;
          this.paths = response.data.paths;
        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
        }
      },
      async getReplayData(){
        this.replaysLoading = true;
        this.replayPage = 1;
        this.replays = null;
        try{
          const response = await this.$globalAsyncPost("/api/v1/global/talents/builder/replays", {
            hero: this.selectedHero.name,
            selectedtalents: this.clickedData,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirror: this.mirrormatch,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          
          this.replays = response.data;
        }catch(error){
          this.replays = [];
        }finally {
          this.replaysLoading = false;
        }
      },
      clearTalentSelections() {
        this.clickedData = {
          1: null,
          4: null,
          7: null,
          10: null,
          13: null,
          16: null,
          20: null,
        };
      },
      resetTalentData() {
        this.clearTalentSelections();
        if (this.localLoad === 'auto') {
          this.getData();
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      talentClicked(talent, index, level){
        this.clickedData[level] = talent.talent_id;
        if (this.localLoad === 'auto') {
          this.getData();
        }
      },

      clickedHero(hero) {
        this.selectedHero = hero;
        this.preloadTalentImages(hero);

        // Update document title dynamically
        document.title = `${this.selectedHero.name} Talent Builder | Heroes Profile`;

        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);

        this.getData();
      },
      preloadTalentImages(hero) {
        if(hero){
          this.talentimages[hero.name].forEach((image) => {
            const img = new Image();
            img.src = image;
          });
        }
      },
      filterData(filteredData){
        const heroId = filteredData.single.Heroes;
        if (heroId && heroId !== this.selectedHero.id) {
          this.selectedHero = this.heroes.find(hero => hero.id === heroId) || this.selectedHero;
          document.title = `${this.selectedHero.name} Talent Builder | Heroes Profile`;
          history.pushState(null, null, window.location.pathname.replace(/\/[^/]*$/, `/${this.selectedHero.name}`));
          this.clearTalentSelections();
          this.paths = null;
          this.pathsOpen = false;
        }
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;
        this.herorank = filteredData.multi["HP Hero Rank"] ? Array.from(filteredData.multi["HP Hero Rank"]) : null;
        this.rolerank = filteredData.multi["HP Role Rank"] ? Array.from(filteredData.multi["HP Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
        this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.defaultbuildtype;

        this.updateQueryString();





        this.data = null;
        this.replays = null;
        this.builddata = null;
        this.getData();
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframetype == "major_grouped"  || this.timeframe.length >= 3){
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
      removeLevelSelections(level){
        this.clickedData[level] = null;

        if (this.localLoad === 'filter') {
          return;
        }

        this.getData();
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

        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);
      },
    }
  }
</script>