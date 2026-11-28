<template>
  <div>
    <global-async-debug-banner page-label="Global Matchups Talents" />
    <player-page-top>
      <template #heading>
        <page-heading scope="Global" :heading="'Talent Matchups'" :subheading="'Hero vs hero talent stats'"></page-heading>
      </template>
      <filters
        ref="filters"
        :onFilter="filterData"
        :filters="filters"
        :isLoading="isLoading"

        :timeframetypeinput="timeframetype"
        :timeframeinput="timeframe"
        :gametypeinput="gametype"
        :gamemapinput="gamemap"
        :playerrankinput="playerrank"

        :gametypedefault="gametypedefault"
        :includetimeframe="true"
        :includegametype="true"
        :includegamemap="true"
        :includeplayerrank="true"
        :advancedfiltering="advancedfiltering"
        :hideadvancedfilteringbutton="true"
        :disablefilter="!this.shouldFilterData"
        >
      </filters>
    </player-page-top>
    <takeover-ad :patreon-user="patreonUser"></takeover-ad>
    <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false"></dynamic-banner-ad>

    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
      <!-- No matchup yet: pick both heroes, with how the page works beside them -->
      <panel-box v-if="!shouldFilterData" title="Choose a matchup" subtitle="Pick your hero and the hero you face or play with" :divider="false">
        <template #right>
          <tab-button :small="true" tab1text="Enemy" tab2text="Ally" :ignoreclick="true" :overridedefaultside="enemyorallyselected" @tab-click="heroOrEnemySideSelected"></tab-button>
        </template>
        <div class="grid xl:grid-cols-[auto_minmax(0,1fr)] gap-6 xl:gap-8">
          <!-- As the Figma picker: the dropdowns as wide as the art, 12px above it, and VS centred between the two pictures.
               Beside the steps, the art grows to their height -->
          <div class="grid grid-cols-[minmax(0,10rem)_auto_minmax(0,10rem)] md:grid-cols-[14rem_auto_14rem] xl:grid-rows-[auto_minmax(20.125rem,1fr)] gap-x-4 md:gap-x-8 gap-y-3 items-start justify-start">
            <single-select-filter :compact="true" :key="'hero-' + (hero ? hero.id : '')" :values="firstHeroInputs" :text="'Hero'" @input-changed="herochanged" :defaultValue="hero ? hero.id : null"></single-select-filter>
            <span></span>
            <single-select-filter :compact="true" :key="'enemyally-' + (enemyally ? enemyally.id : '')" :values="secondHeroInputs" :text="'Matchup hero'" @input-changed="allyenemychanged" :defaultValue="enemyally ? enemyally.id : null"></single-select-filter>
            <hero-image-wrapper v-if="hero && hero.short_name" class="self-stretch xl:[&>div]:!h-full" :rectangle="true" size="large" :hero="hero" :excludehover="true"></hero-image-wrapper>
            <span v-else></span>
            <!-- Swaps the two heroes, so the other one's talents are listed; off until one is picked, and while a matchup loads -->
            <button type="button" @click="swapHeroes" :disabled="!canSwap" :title="heroPicked ? $t('Swap heroes') : $t('Pick a hero first')" :class="['self-center shrink-0 w-14 h-14 rounded-full bg-surface-3 transition-colors flex items-center justify-center text-lg font-bold text-subtext', canSwap ? 'hover:bg-surface-4 hover:text-white' : 'opacity-50 cursor-not-allowed']">{{ vsorwith }}</button>
            <hero-image-wrapper v-if="enemyally && enemyally.short_name" class="self-stretch xl:[&>div]:!h-full" :rectangle="true" size="large" :hero="enemyally" :excludehover="true"></hero-image-wrapper>
            <span v-else></span>
          </div>
          <!-- As Figma's steps: they share the picker's height -->
          <ol class="flex flex-col gap-3">
            <li v-for="(step, index) in steps" :key="step.title" class="flex-1 flex items-center gap-4 rounded-control bg-surface-1 p-5">
              <span class="shrink-0 w-10 h-10 rounded-full bg-surface-3 flex items-center justify-center text-lg font-bold text-teal">{{ index + 1 }}</span>
              <span class="flex flex-col gap-1 min-w-0">
                <span class="text-lg font-semibold">{{ step.title }}</span>
                <span class="text-sm text-subtext">{{ step.text }}</span>
              </span>
            </li>
          </ol>
        </div>
      </panel-box>

      <template v-else>
        <!-- The matchup: both heroes, changeable here, and the listed hero's win rate in it -->
        <!-- Phones: the heroes and VS stacked on the left, the win rate beside them on the right -->
        <div class="rounded-panel bg-surface px-5 md:px-6 py-4 md:flex md:flex-wrap md:items-center md:gap-6 max-md:grid max-md:grid-cols-[auto_minmax(0,1fr)] max-md:items-center max-md:gap-x-4 max-md:gap-y-2">
          <div class="flex items-center gap-3 max-md:col-start-1">
            <hero-image-wrapper :hero="hero" size="medium" :excludehover="true"></hero-image-wrapper>
            <single-select-filter :key="'hero-' + (hero ? hero.id : '')" :values="firstHeroInputs" :text="'Hero'" @input-changed="herochanged" :defaultValue="hero.id"></single-select-filter>
          </div>
          <!-- Phones: between the two heroes, centred under them -->
          <span class="max-md:col-start-1 max-md:flex max-md:justify-center"><button type="button" @click="swapHeroes" :disabled="!canSwap" :title="$t('Swap heroes')" :class="['shrink-0 w-10 h-10 rounded-full bg-surface-3 transition-colors flex items-center justify-center text-sm font-bold text-subtext', canSwap ? 'hover:bg-surface-4 hover:text-white' : 'opacity-50 cursor-not-allowed']">{{ vsorwith }}</button></span>
          <div class="flex items-center gap-3 max-md:col-start-1">
            <hero-image-wrapper :hero="enemyally" size="medium" :excludehover="true"></hero-image-wrapper>
            <single-select-filter :key="'enemyally-' + (enemyally ? enemyally.id : '')" :values="secondHeroInputs" :text="'Matchup hero'" @input-changed="allyenemychanged" :defaultValue="enemyally.id"></single-select-filter>
          </div>
          <div v-if="!isLoading && talentdetaildata" class="ml-auto flex flex-col items-end text-right max-md:col-start-2 max-md:row-start-1 max-md:row-span-3">
            <span class="text-xs text-subtext">{{ matchupLabel }}</span>
            <span class="text-lg font-bold">{{ $t(':rate% win rate', { rate: Number(firstwinratedata).toFixed(2) }) }}<template v-if="matchupgames != null"> · {{ $t(':games games', { games: matchupgames.toLocaleString('en-US') }) }}</template></span>
          </div>
        </div>

        <patch-notes-panel v-if="patchNotesUrl" class="!max-w-none !mb-0" :version="timeframe[0]"></patch-notes-panel>

        <panel-box v-if="isLoading" :title="talentsTitle" :subtitle="talentsSubtitle" class="relative md:!rounded-tl-none">
          <loading-component v-if="determineIfVeryLargeData()" :compact="true" :tab="true" :textoverride="true">{{ $t('Very large amount of data. Compiling can take 5-10 minutes. Please be patient.') }}</loading-component>
          <loading-component v-else :compact="true" :tab="true" :textoverride="true">{{ $t('Large amount of data. Please be patient.') }}</loading-component>
          <compact-talent-grid :skeleton="true"></compact-talent-grid>
        </panel-box>
        <div v-else-if="loadError" class="max-w-[120rem] mx-auto px-4 md:px-8">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
        <panel-box v-else-if="talentdetaildata" :title="talentsTitle" :subtitle="talentsSubtitle">
          <template #right>
            <div class="flex flex-wrap items-center gap-4">
              <!-- Whose talents are listed; stays until it's decided where this goes -->
              <tab-button :small="true" :tab1text="$heroName(hero)" :tab2text="$heroName(enemyally)" :ignoreclick="true" :overridedefaultside="talentheroselected" @tab-click="talentHeroOrEnemySideSelected"></tab-button>
              <tab-button :small="true" tab1text="Enemy" tab2text="Ally" :ignoreclick="true" :overridedefaultside="enemyorallyselected" @tab-click="heroOrEnemySideSelected"></tab-button>
            </div>
          </template>
          <compact-talent-grid :talent-data="talentdetaildata"></compact-talent-grid>
        </panel-box>
      </template>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'GlobalMatchupsTalentStats',
    components: {
    },
    props: {
      heroes: Array,
      filters: Object,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
      inputhero: Object,
      inputenemyally: Object,
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        loadError: null,
        isLoading: false,
        infoText1: 'This page allows you to look at talent win rates and popularity, for a hero against, or with another hero. If you click on the "enemy" button, you will see talent data for games where those two heroes played against each other. If you click on the "ally" button, you will see talent data for games where those two heroes were on the same team.',
        infoText2: "NOTICE: This page may take longer to load data than our normal pages.",
        cancelTokenSource: null,
        talentdetaildata: null,

      //Sending to filter
        hero: null,
        enemyally: null,
        timeframetype: null,
        timeframe: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        vsorwith: "VS",
        talentheroselected: "left",
        talentview: "hero",
        enemyorallyselected: "left",
        type: "Enemy",
        firstwinratedata: null,
        secondwinratedata: null,
        matchupgames: null,
        // The hero pair the talents on screen are for
        loadedPair: null,
      }
    },
    created(){
      this.hero = this.inputhero;
      this.enemyally = this.inputenemyally;

      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      if(this.shouldFilterData){
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
      showTalentHeroToggle(){
        if(!this.hero || !this.enemyally){
          return false;
        }
        return (this.hero.name != "Auto Select" && this.enemyally.name != "Auto Select") ? true : false;
      },
      firstHeroInputs(){
        if(this.enemyally){
          return this.filters.heroes.filter(hero => hero.name !== this.enemyally.name);
        }
        return this.filters.heroes;
      },
      secondHeroInputs(){
        if(this.hero){
          return this.filters.heroes.filter(hero => hero.name !== this.hero.name);
        }
        return this.filters.heroes;
      },
      // How the page works, beside the hero pickers
      steps(){
        return [
          { title: this.$t('Choose your hero'), text: this.$t('The hero whose talents you want to check.') },
          { title: this.$t('Choose the matchup hero'), text: this.$t('Any hero from the roster. The talents load once both are picked.') },
          { title: this.$t('Enemy or Ally'), text: this.$t('Enemy: talents that win against this hero. Ally: talents that win with this hero on your team.') },
          { title: this.$t('Swap the heroes'), text: this.$t('Click VS between the two heroes to swap them and see the talents of the other hero in the same matchup.') },
          { title: this.$t('Read the tiers'), text: this.$t('Win rate, popularity and games for every talent. The best talent on each tier is highlighted.') },
        ];
      },
      // The listed hero first: the talents toggle can put the matchup hero there
      listedHeroes(){
        return this.talentview === 'hero' ? [this.hero, this.enemyally] : [this.enemyally, this.hero];
      },
      matchupLabel(){
        const [hero, other] = this.listedHeroes.map(item => this.$heroName(item));
        return this.type === 'Ally' ? this.$t(':hero with :other', { hero, other }) : this.$t(':hero vs :other', { hero, other });
      },
      talentsTitle(){
        const [hero, other] = this.listedHeroes.map(item => this.$heroName(item));
        return this.type === 'Ally' ? this.$t(':hero talents with :other', { hero, other }) : this.$t(':hero talents against :other', { hero, other });
      },
      talentsSubtitle(){
        const [hero, other] = this.listedHeroes.map(item => this.$heroName(item));
        return this.type === 'Ally'
          ? this.$t('Win rate, popularity and games for every :hero talent in games with :other on the same team. The best win rate on each tier is highlighted.', { hero, other })
          : this.$t('Win rate, popularity and games for every :hero talent in games against :other. The best win rate on each tier is highlighted.', { hero, other });
      },
      // Either slot holds a real hero rather than the Auto Select placeholder
      heroPicked() {
        return [this.hero, this.enemyally].some(hero => hero && hero.name != "Auto Select");
      },
      // Not while a matchup loads, nor with nothing to swap
      canSwap() {
        return this.heroPicked && !this.isLoading;
      },
      shouldFilterData(){
        if((this.hero && this.hero.name != "Auto Select") && (this.enemyally && this.enemyally.name != "Auto Select")){
          return true;
        }
        return false;
      },
    },
    watch: {
    },
    methods: {
      determineIfVeryLargeData(){
        if((this.timeframetype == "major" || this.timeframetype == "major_grouped") && this.timeframe && this.timeframe.length > 1){
          return true;
        }
        return false;
      },
      async getData(){
        this.loadError = null;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
        
        try{
          const response = await this.$globalAsyncPost("/api/v1/global/matchups/talents", {
            hero: this.hero.name,
            ally_enemy: this.enemyally.name,
            type: this.type,
            talent_view: this.talentview,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if(response.data.status == "failure to validate inputs"){
            throw new Error("Failure to validate inputs");
          }
          this.talentdetaildata = response.data.data;
          this.loadedPair = this.hero.name + '|' + this.enemyally.name;
          this.firstwinratedata = response.data.first_win_rate;
          this.secondwinratedata = response.data.second_win_rate;
          this.matchupgames = response.data.games_played ?? null;
        }catch(error){
          this.loadError = this.$loadError(error, () => this.getData());
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
      herochanged(eventPayload){
        this.hero = this.heroes.find(hero => hero.id === eventPayload.value) ?? this.hero;
        this.loadMatchup();
      },
      allyenemychanged(eventPayload){
        this.enemyally = this.heroes.find(hero => hero.id === eventPayload.value) ?? this.enemyally;
        this.loadMatchup();
      },
      swapHeroes(){
        [this.hero, this.enemyally] = [this.enemyally, this.hero];
        this.loadMatchup();
      },
      // On picking either hero, once both are set and the pair is new: load it as the Filter button does,
      // with the filters as they stand
      loadMatchup() {
        if(!this.shouldFilterData || this.hero.name + '|' + this.enemyally.name === this.loadedPair){
          return;
        }
        history.pushState(null, null, `/Global/Matchups/Talents/${this.hero.name}/${this.enemyally.name}${window.location.search}`);
        // As the page's own title for that URL
        document.title = this.$t(':hero vs :other Talent Matchups', { hero: this.$heroName(this.hero), other: this.$heroName(this.enemyally) }) + ' | Heroes Profile';
        this.$refs.filters.applyFilter();
      },

      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaultMinor;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["HP Player Rank"] ? Array.from(filteredData.multi["HP Player Rank"]) : null;


        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
        queryString += `&game_type=${this.gametype}`;
      
        if(this.gamemap){
          queryString += `&game_map=${this.gamemap}`;
        }

        if(this.playerrank){
          queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
        }

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);


        this.talentdetaildata = null;
        this.getData();
      },

      talentHeroOrEnemySideSelected(side){
        this.talentheroselected = side;
        if(side == "left"){
          this.talentview = "hero";
        }else{
          this.talentview = "ally_enemy";
        }
        if(this.shouldFilterData){
          this.talentdetaildata = null;
          this.getData();
        }
      },  

      heroOrEnemySideSelected(side){
        if(side == "left"){
          this.type = "Enemy";
          this.vsorwith = "VS";
        }else if(side == "right"){
          this.type = "Ally";
          this.vsorwith = "+";
        }
        this.enemyorallyselected = side;
        this.firstwinratedata = "";
        this.secondwinratedata = "";

        if(this.shouldFilterData){
          this.talentdetaildata = null;
          this.getData();
        }
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

      },
    }
  }
</script>