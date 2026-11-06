<template>
  <div>
    <player-page-top>
      <template #heading>
        <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="selectedHero ? selectedHero.name + ' talent stats and builds player by ' + battletag : ' talent stats and builds player by ' + battletag" -->
        <page-heading v-if="esport" heading="Talent Statistics" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
        <page-heading v-else heading="Talent Statistics" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
          <template v-if="selectedHero" #aboveHeading>
            <hero-heading-select :heroes="heroes" :value="selectedHero.id" @hero-changed="changeHero($event)"></hero-heading-select>
          </template>
        </page-heading>
      </template>
      <div v-if="esport" class="w-full">
        <panel-box :filters="true" v-if="ngsFilterOptions" :key="ngsFilterKey">
        <div class="filter-row justify-center">
          <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" :defaultValue="ngsSeason"></single-select-filter>
          <single-select-filter v-if="ngsFilterOptions.divisions" :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" :defaultValue="ngsDivision"></single-select-filter>
          <single-select-filter :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" :defaultValue="ngsTeam"></single-select-filter>
          <button :disabled="isLoading" @click="ngsFilterApply" data-filter-apply class="btn font-semibold h-10 m-2 md:w-[120px] max-md:flex-[1_1_8rem]" :class="isLoading ? 'bg-gray-md' : 'bg-blue hover:bg-lblue'">Filter</button>
        </div>
      </panel-box>
      </div>
      <div v-else-if="selectedHero" class="w-full">
        <filters
          :onFilter="filterData"
          :filters="filters"
          :isLoading="isLoading"
          :gametypedefault="gametype"
          :includegametypefull="true"
          :includemultiseason="true"
          :includegamemap="true"
          :hideadvancedfilteringbutton="true"
          :includegamedaterange="true"
          >
        </filters>
      </div>
    </player-page-top>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes" @search="heroSearchQuery = $event"></hero-selection>

      <!-- The player's most played build per hero, like the list on /Global/Talents -->
      <div class="max-w-[1500px] mx-auto md:px-4 mt-8 w-full flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-4 max-md:justify-center max-md:text-center">
          <h2 class="heading">{{ esport ? 'Most Played Builds Per Hero' : 'Most Played Builds Per Hero and Game Type' }}</h2>
          <tab-button :key="esport ? 'ngs-' + ngsRangeSide : 'range'" :tab1text="'All Time'" :tab2text="'Current Season'" :ignoreclick="true" @tab-click="setBuildsRange" :overridedefaultside="esport ? ngsRangeSide : (buildsCurrentSeason ? 'right' : 'left')"></tab-button>
        </div>
        <loading-component v-if="isBuildsLoading" :compact="true" :textoverride="true" :overrideimage="league?.loaderImage">Loading talent builds{{ buildsTotal ? ` (${buildsLoaded} of ${buildsTotal} heroes)` : '' }}...</loading-component>
        <p v-if="buildsError" class="text-sm text-subtext">Couldn't load talent builds. Reload the page to try again.</p>
        <global-talent-builds-all v-else-if="heroesWithBuilds.length" :key="buildsCurrentSeason ? 'season' : 'all'" :heroes="heroesWithBuilds" :talentbuilddataall="talentbuilddataall" :game-types="esport ? [{ key: 'ngs', label: league.name }] : undefined"></global-talent-builds-all>
        <table-skeleton v-else-if="isBuildsLoading" :bare="true" :rows="10" :columns="['avatar', 'talents', 'talents', 'talents']"></table-skeleton>
        <p v-else-if="talentbuilddataall && esport" class="text-sm text-subtext">No {{ league.name }} builds for these filters.</p>
        <p v-else-if="talentbuilddataall" class="text-sm text-subtext">No builds {{ buildsCurrentSeason ? 'this season' : 'yet' }} for Quick Match, Storm League or ARAM.</p>
      </div>
    </div>
    <div v-else>
      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

      <div  v-if="talentdetaildata" class="container mx-auto md:px-4">
        <span class="flex gap-4 mb-2"> {{ this.selectedHero.name }} {{ "Talent Stats"}}  <custom-button @click="redirectChangeHero" :text="'Change Hero'" :alt="'Change Hero'" size="small" :ignoreclick="true"></custom-button></span>
        <global-talent-details-section :talentdetaildata="talentdetaildata" :esport="esport ? 'true' : undefined" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
      </div>
      <!-- The talent tables' layout with grey blocks while they load, like Overall Talent Stats on the NGS page -->
      <div v-else-if="isLoading" class="max-w-[1920px] mx-auto px-4 md:px-8 flex flex-col gap-6">
        <loading-component :compact="true" :overrideimage="league?.loaderImage"></loading-component>
        <div class="container mx-auto md:px-4 flex flex-col items-center">
          <div v-for="level in 7" :key="level" class="w-full bg-surface overflow-hidden">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-20"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="3" :columns="['hero', 'stat', 'stat', 'stat']"></table-skeleton>
          </div>
          <div class="w-full max-w-[800px] bg-surface overflow-hidden mt-6">
            <div class="flex justify-center py-2.5"><skeleton-box class="h-4 w-16"></skeleton-box></div>
            <table-skeleton :bare="true" :rows="7" :columns="['talentrow', 'stat', 'stat', 'code']"></table-skeleton>
          </div>
        </div>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <div  v-if="talentbuilddata" class="cmx-auto md:px-4 w-auto flex flex-col items-center">
        {{ this.selectedHero.name }} {{ "Talent Builds"}}
        <global-talent-builds-section :talentbuilddata="talentbuilddata" :esport="esport ? 'true' : undefined" :buildtype="'Popular'" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerTalents',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    playerloadsetting: {
      type: [String, Boolean]
    },
    battletag: String,
    blizzid: String, 
    region: String,
    inputhero: Object,
    heroes: Array,
    talentimages: Object,
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    gametypedefault: Array,
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
    esport: String,
    esportfilters: Object,
  },
  data(){
    return {
      cancelTokenSource: null,
      isLoading: false,
      gametype: null,
      selectedHero: null,
      data: null,
      talentdetaildata: null,
      talentbuilddata: null,
      season: null,
      gamemap: null,
      startdate: null,
      enddate: null,
      heroSearchQuery: '',
      talentbuilddataall: null,
      isBuildsLoading: false,
      buildsLoaded: 0,
      buildsTotal: 0,
      buildsError: false,
      buildsCurrentSeason: false,
      // Bumped when the toggle sets the season, so the Seasons dropdown shows it
      ngsFilterKey: 0,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
  },
  mounted() {
    const autoLoad = this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true";
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      if(autoLoad){
        this.getData();
      }
    }else if(autoLoad){
      this.getBuildsAll();
    }
  },
  computed: {
    league() {
      return this.esport ? this.$esportLeague(this.esport, this.esportfilters) : null;
    },
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    ngsPlayerUrl() {
      return `${this.league.root}/Player/${this.battletag}/${this.blizzid}`;
    },
    ngsLatestSeason() {
      // The first entry is All
      return this.ngsFilterOptions?.seasons?.[1]?.code ?? null;
    },
    ngsRangeSide() {
      return this.ngsLatestSeason && String(this.ngsSeason) === this.ngsLatestSeason ? 'right' : 'left';
    },
    playerUrl() {
      return this.esport ? this.ngsPlayerUrl : `/Player/${this.battletag}/${this.blizzid}/${this.region}`;
    },
    // Heroes the player has a build for, narrowed by the hero picker's search
    heroesWithBuilds() {
      if (!this.talentbuilddataall) return [];
      const q = this.heroSearchQuery.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();
      return this.heroes.filter(hero => this.talentbuilddataall[hero.name]
        && (!q || hero.name.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase().includes(q)));
    },
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
        return true;
      }
      return false;
    }
  },
  watch: {
  },
  methods: {
    ngsParams(extra = {}) {
      return { ...this.league.params, battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam, ...extra };
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
    ngsFilterApply(){
      if (this.selectedHero) {
        this.talentdetaildata = null;
        this.talentbuilddata = null;
        this.getData();
      } else {
        this.getBuildsAll();
      }
    },
    async getData(){
      try{
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        const response = this.esport
          ? await this.$axios.post(`${this.league.api}/talents`, this.ngsParams({ hero: this.selectedHero.name }), { cancelToken: this.cancelTokenSource.token })
          : await this.$axios.post("/api/v1/player/talents", {
          hero: this.selectedHero.name,
          battletag: this.battletag,
          region: this.region,
          blizz_id: this.blizzid,
          game_type: this.gametype,
          season: this.season,
          game_map: this.gamemap,
          start_date: this.startdate,
          end_date: this.enddate,
        }, 
        {
          cancelToken: this.cancelTokenSource.token,
        });
        if (this.esport) {
          this.ngsFilterOptions = response.data.filters;
        }
        this.talentdetaildata = response.data.talentData
        this.talentbuilddata = response.data.buildData;
      }catch(error){
        //Do something here
      }finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
      }
    },
    // The heroes the player has games on, then each hero's builds a few at a time, filling the table as they land
    async getBuildsAll(){
      // Kept off the reactive data: Vue would hand back a proxy and the run checks below would never match
      this.buildsCancel?.cancel('Request canceled');
      const source = this.$axios.CancelToken.source();
      this.buildsCancel = source;
      const run = this.buildsRun = (this.buildsRun || 0) + 1;
      this.isBuildsLoading = true;
      this.buildsError = false;
      this.talentbuilddataall = null;
      this.buildsLoaded = 0;
      this.buildsTotal = 0;

      const params = {
        battletag: this.battletag,
        blizz_id: this.blizzid,
        region: this.region,
        season: this.buildsCurrentSeason && this.filters.seasons.length ? [this.filters.seasons[0].code] : null,
      };

      try{
        const response = this.esport
          ? await this.$axios.post(`${this.league.api}/talents/builds`, this.ngsParams(), { cancelToken: source.token })
          : await this.$axios.post("/api/v1/player/talents/build/heroes", params, { cancelToken: source.token });
        if (this.esport) {
          this.ngsFilterOptions = response.data.filters;
        }
        if (!Array.isArray(response.data?.heroes)) {
          throw new Error("Failure to validate inputs");
        }
        // Heroes already up to date in the cache come back with the list; only the rest are asked for one by one
        const cached = response.data.builds || {};
        const played = this.heroes.filter(hero => response.data.heroes.includes(hero.id));
        const queue = played.filter(hero => !(hero.name in cached));
        this.buildsTotal = played.length;
        this.buildsLoaded = played.length - queue.length;
        this.talentbuilddataall = Object.fromEntries(Object.entries(cached).filter(([, builds]) => Object.keys(builds).length));

        const worker = async () => {
          while (queue.length && this.buildsRun === run) {
            const hero = queue.shift();
            try {
              const builds = await this.$axios.post("/api/v1/player/talents/build/hero", { ...params, hero: hero.name }, { cancelToken: source.token });
              if (this.buildsRun === run && builds.data && Object.keys(builds.data).length && !builds.data.status) {
                this.talentbuilddataall = { ...this.talentbuilddataall, [hero.name]: builds.data };
              }
            } catch (error) {
              if (this.$axios.isCancel(error)) {
                return;
              }
              // One hero failing leaves its row out; the rest still load
            }
            if (this.buildsRun === run) {
              this.buildsLoaded++;
            }
          }
        };
        await Promise.all(Array.from({ length: 4 }, worker));
      }catch(error){
        if (!this.$axios.isCancel(error) && this.buildsRun === run) {
          this.buildsError = true;
        }
      }finally {
        // A newer run (the toggle flipped) owns the loading state now
        if (this.buildsRun === run) {
          this.buildsCancel = null;
          this.isBuildsLoading = false;
        }
      }
    },
    setBuildsRange(side){
      // Esports: Current Season is the newest league season the player has games in, set through the Seasons filter
      if (this.esport) {
        const season = side === 'right' ? this.ngsLatestSeason : null;
        if (season === this.ngsSeason) {
          return;
        }
        this.ngsFilterChange({ field: 'Seasons', value: season });
        this.ngsFilterKey++;
        this.ngsFilterApply();
        return;
      }
      const currentSeason = side === 'right';
      if (currentSeason !== this.buildsCurrentSeason) {
        this.buildsCurrentSeason = currentSeason;
        this.getBuildsAll();
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    clickedHero(hero) {
      this.selectedHero = hero;
      this.preloadTalentImages(hero);

      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}${window.location.search}`);
      this.getData();
    },
    changeHero(heroId) {
      this.selectedHero = this.heroes.find(hero => hero.id === heroId);
      this.preloadTalentImages(this.selectedHero);
      history.pushState(null, null, `${this.playerUrl}/Talents/${this.selectedHero.name}${window.location.search}`);
      this.talentdetaildata = null;
      this.talentbuilddata = null;
      this.getData();
    },
    filterData(filteredData){
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;


      this.talentdetaildata = null;
      this.talentbuilddata = null;
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
    redirectChangeHero(){
      window.location.href = `${this.playerUrl}/Talents${window.location.search}`;
    }
  }
}
</script>