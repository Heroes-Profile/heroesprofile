<template>
  <div>
    <button  class="md:hidden p-2 bg-blue w-full" @click="showMobile">Show Filters</button>
    <div v-show="showNav" class="bg-gray-dark py-2 md:px-20 mb-[2em] max-md:fixed max-md:top-0 max-md:z-50 max-md:h-[100vh] max-md:flex max-md:flex-wrap max-md:px-4 max-md:flex-col ">
      <button class="md:hidden ml-auto text-2xl" @click="showMobile">x</button>
      <div class="flex  items-center justify-center max-md:flex-wrap max-md:flex-1  max-md:items-start max-md:justify-start max-md:flex-col">
        <div class="flex flex-wrap items-center justify-center ">
          <!--Hero or Role -->
          <single-select-filter v-if="includeherorole" 
            :values="filters.hero_role" 
            :text="'Hero or Role'" 
            @input-changed="handleInputChange"
            :defaultValue="'Hero'"
          ></single-select-filter>

          <!-- Type -->
          <single-select-filter v-if="playerheroroletype" 
            :values="filters.type" 
            :text="'Type'"
            @input-changed="handleInputChange" 
            :defaultValue="'Player'"
          ></single-select-filter>

          <!-- Leaderboard -->
          <single-select-filter v-if="leaderboardfiltertype" 
            :values="filters.leaderboard_type" 
            :text="'Leaderboard Type'"
            @input-changed="handleInputChange" 
            :defaultValue="'Player'"
          ></single-select-filter>

          <!-- Heroes Swap -->
          <single-select-filter v-if="modifiedincludeheroes && swapHeroesFilter" 
            :values="filters.heroes" 
            :text="'Heroes'" 
            :defaultValue="heroinput"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Role Swap -->
          <single-select-filter v-if="modifiedincluderole && swapRolesFilter" 
            :values="filters.role" 
            :text="'Role'" 
            :defaultValue="role"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Group Size -->
          <single-select-filter v-if="modifiedincludegroupsize" 
            :values="filters.group_size" 
            :text="'Group Size'" 
            @input-changed="handleInputChange" 
            :defaultValue="modifiedGroupSizeDefaultValue"
          ></single-select-filter>

          <!--<single-select-filter v-if="includecharttype" :values="filters.chart_type" :text="'Chart Type'" @input-changed="handleInputChange" :defaultValue="'Account Level'"></single-select-filter>-->

          <!-- Timeframe Type -->
          <single-select-filter v-if="includetimeframetype" 
            :values="filters.timeframe_type" 
            :text="'Timeframe Type'" 
            :defaultValue="timeframetype" 
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Timeframe Type -->
          <single-select-filter v-if="includetimeframetypewithlastupdate" 
            :values="timeframeTypeWithLastUpdate" 
            :text="'Timeframe Type'" 
            :defaultValue="timeframetype" 
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Timeframes -->
          <multi-select-filter v-if="includetimeframemodified" 
            :values="timeframes" 
            :text="'Timeframes'" 
            :defaultValue="timeframe" 
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Current Game Type Multiselect-->
          <multi-select-filter v-if="modifiedincludegametype" 
            :values="filters.game_types" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></multi-select-filter>

          <!-- All Game Types Multiselect -->
          <multi-select-filter v-if="includegametypefull" 
            :values="filters.game_types_full" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype"
          ></multi-select-filter>

          <!-- Leaderboard Game Type Excluding UD -->
          <single-select-filter v-if="includesinglegametypeleaderboard" 
            :values="leaderboardGameTypes" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype[0]"
          ></single-select-filter>

          <!-- Current Game Type Single -->
          <single-select-filter v-if="includesinglegametype" 
            :values="filters.game_types" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype[0]"
          ></single-select-filter>


          <!-- All Game Type Single -->
          <single-select-filter v-if="includesinglegametypefull" 
            :values="filters.game_types_full" 
            :text="'Game Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="gametype[0]"
          ></single-select-filter>
          
          <!-- Regions Multiselect-->
          <multi-select-filter v-if="includeregion" 
            :values="filters.regions" 
            :text="'Regions'" 
            :defaultValue="region"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Regions Single-->
          <single-select-filter v-if="includesingleregion" 
            :values="filters.regions" 
            :text="'Regions'" 
            :defaultValue="region"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Stat Type Filter -->
          <single-select-filter v-if="showStatTypeFilter" 
            :values="filters.stat_filter" 
            :text="'Stat Filter'" 
            :defaultValue="statfilter" 
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Hero Level -->
          <multi-select-filter v-if="includeherolevel && toggleExtraFilters" 
            :values="filters.hero_level" 
            :text="'Hero Level'" 
            :defaultValue="herolevel"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Heroes -->
          <single-select-filter v-if="modifiedincludeheroes && !swapHeroesFilter" 
            :values="filters.heroes" 
            :text="'Heroes'" 
            :defaultValue="heroinput"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Role -->
          <single-select-filter v-if="modifiedincluderole && !swapRolesFilter" 
            :values="filters.role" 
            :text="'Role'" 
            :defaultValue="role"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Season -->
          <single-select-filter v-if="modifiedincludeseason" 
            :values="seasons" 
            :text="'Season'" 
            @input-changed="handleInputChange" 
            :defaultValue="defaultSeason"
          ></single-select-filter>

          <!-- Match Prediction Seasons -->
          <single-select-filter v-if="modifiedincludematchpredictionseason" 
            :values="this.filters.match_prediction_seasons" 
            :text="'Match Prediction Season'" 
            @input-changed="handleInputChange" 
            :defaultValue="defaultpredictionseason"
          ></single-select-filter>


          <!-- All Seasons -->
          <single-select-filter v-if="includeseasonwithall" 
            :values="seasonsWithAll" 
            :text="'Season'" 
            @input-changed="handleInputChange" 
            :defaultValue="defaultSeason"
          ></single-select-filter>

          <!-- Game Map Multiselect -->
          <multi-select-filter v-if="includegamemap" 
            :values="filters.game_maps" 
            :text="'Map'" 
            :defaultValue="gamemap"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Game Map Single -->
          <single-select-filter v-if="includesinglegamemap" 
            :values="filters.game_maps" 
            :text="'Map'" 
            :defaultValue="gamemap[0]"
            @input-changed="handleInputChange"
          ></single-select-filter>


          <!-- Tier Single -->
          <single-select-filter v-if="modifiedincludetier" 
            :values="filters.rank_tiers" 
            :text="'Rank'" 
            :defaultValue="tierrank"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Player Rank -->
          <multi-select-filter v-if="includeplayerrank" 
            :values="filters.rank_tiers" 
            :text="'Player Rank'"
            :defaultValue="playerrank"
            @input-changed="handleInputChange"
          ></multi-select-filter>
          


          <!-- Hero Rank -->
          <multi-select-filter v-if="includeherorank && toggleExtraFilters" 
            :values="filters.rank_tiers" 
            :text="'Hero Rank'" 
            :defaultValue="herorank"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Role Rank -->
          <multi-select-filter v-if="includerolerank && toggleExtraFilters" 
            :values="filters.rank_tiers" 
            :text="'Role Rank'" 
            :defaultValue="rolerank"
            @input-changed="handleInputChange"
          ></multi-select-filter>

          <!-- Talent built Type -->
          <single-select-filter v-if="includetalentbuildtype" 
            :values="filters.talent_build_types" 
            :text="'Talent Build Type'" 
            @input-changed="handleInputChange" 
            :defaultValue="talentbuildtype"
          ></single-select-filter>

          <!-- Minimum Games -->
          <single-select-filter v-if="includeminimumgames" 
            :values="filters.minimum_games" 
            :text="'Minimum Games'" 
            @input-changed="handleInputChange" 
            :defaultValue="modifiedminimumgamedefault"
          ></single-select-filter>

          <!-- Team One Party -->
          <single-select-filter v-if="includeteamoneparty" 
            :values="filters.party_combinations" 
            :text="'Team One Party'" 
            :defaultValue="teamonepartyinput"
            @input-changed="handleInputChange"
          ></single-select-filter>

          <!-- Team Two Party -->
          <single-select-filter v-if="includeteamtwoparty" 
            :values="filters.party_combinations" 
            :text="'Team Two Party'" 
            :defaultValue="teamtwopartyinput"
            @input-changed="handleInputChange"
          ></single-select-filter>


          <!--<single-select-filter v-if="modifiedincludeminimumaccountlevel" :values="filters.minimum_account_level" :text="'Min. Account Level'" @input-changed="handleInputChange" :defaultValue="'100'"></single-select-filter>-->
          <!--<single-select-filter v-if="modifiedincludexaxisincrements" :values="filters.x_axis_increments" :text="'X Axis Increments'" @input-changed="handleInputChange" :defaultValue="'25'"></single-select-filter>-->

          <!-- Mirror -->
          <single-select-filter v-if="includemirror && toggleExtraFilters" 
            :values="filters.mirror" 
            :text="'Mirror Matches'" 
            @input-changed="handleInputChange" 
            :defaultValue="mirrormatch"
          ></single-select-filter>

          <!-- Game Date -->
          <div id="filter-label" class="relative">
            <div v-if="includegamedate" class="flex items-end  text-sm font-medium text-gray-700 cursor-pointer p-2  transition-colors">
              <div class="flex flex-col"><span>From Date  </span>
              <input type="date" 
                v-model="selectedGameDate" 
                @input="handleGameDateChange"
                @blur="handleDateInputBlur"
                class="w-[200px] h-[40px] overflow-hidden hover:bg-teal border-solid border-[1px] border-white bg-blue p-2 text-white"
              ></div>
              <button class="h-[40px] bg-blue md:m-t-auto  p-2 border-r-[1px] border-t-[1px] border-b-[1px] hover:bg-teal" @click="resetGameDate()">X</button>
            </div>
          </div>
        </div>
        <button :disabled="disabledFilter" @click="applyFilter"  :class="{'bg-teal rounded text-white md:ml-10 px-4 py-2 md:mt-auto mb-2 hover:bg-lteal max-md:mb-auto max-md:w-full max-md:mt-10': !disabledFilter, 'bg-gray-md rounded text-white md:ml-10 px-4 py-2 mt-auto mb-2 hover:bg-gray-md max-md:mt-auto max-md:w-full': disabledFilter}">
          Filter
        </button>

        
      </div>
      <div class="flex justify-end max-md:mb-auto">
        <button class="m-l-auto underline" v-if="!hideadvancedfilteringbutton" @click="toggleExtraFilters = !toggleExtraFilters" >{{toggleButtonText}}</button>
      </div>
    </div>
  </div>
</template>

<script>

  export default {
    name: 'Filters',
    components: {
    },
    props: {
      isLoading: Boolean,
      timeframetypeinput: String,
      timeframeinput: Array,
      gametypeinput: Array,
      regioninput: Array,
      statfilterinput: String,
      herolevelinput: Array,
      heroinput: Number,
      roleinput: String,
      gamemapinput: Array,
      playerrankinput: Array,
      herorankinput: Array,
      rolerankinput: Array,
      talentbuildtypeinput: String,
      mirrormatchinput: {
        type: [String, Number]
      },
      teamonepartyinput: String,
      teamtwopartyinput: String,
      includesingleregion: Boolean,
      includeherorole: Boolean,
      playerheroroletype: Boolean,
      leaderboardfiltertype: Boolean,
      includegroupsize: Boolean,
      includecharttype: Boolean,
      includetimeframetype: Boolean,
      includetimeframe: Boolean,
      includeseason: Boolean,
      includeregion: Boolean,
      includestatfilter: Boolean,
      includeherolevel: Boolean,
      includerole: Boolean,
      includehero: Boolean,
      includegametype: Boolean,
      includegametypefull: Boolean,
      includesinglegametype: Boolean,
      includesinglegametypeleaderboard: Boolean,
      includegamemap: Boolean,
      includesinglegamemap: Boolean,
      includeplayerrank: Boolean,
      includeherorank: Boolean,
      includerolerank: Boolean,
      includemirror: Boolean,
      includetalentbuildtype: Boolean,
      includeminimumgames: Boolean,
      includeteamoneparty: Boolean,
      includeteamtwoparty: Boolean,
      includeminimumaccountlevel: Boolean,
      includexaxisincrements: Boolean,
      includesinglegametypefull: Boolean,
      minimumseason: Number,
      includegamedate: Boolean,
      hideadvancedfilteringbutton: Boolean,
      includeseasonwithall: Boolean,
      overrideGroupSizeRemoval: Boolean,
      includetimeframetypewithlastupdate: Boolean,
      includetier: Boolean,
      filters: {
        type: Object,
        required: true
      },
      onFilter: {
        type: Function,
        required: true,
      },
      gametypedefault: Array,
      minimumgamesdefault: {
        tpye: [String, Number]
      },
      defaultSeason: String,
      defaultpredictionseason: String,
      defaultPredictionSeason: String,
      advancedfiltering: Boolean,
      groupSizeDefaultValue: String,
      rolerequired: Boolean,
      excludetimeframes: Boolean,
      disablefilter: Boolean,
    },
    data(){
      return {
        timeframetype: String,
        timeframe: Array,
        gametype: Array,
        region: Array,
        herolevel: Array,
        hero: Number,
        role: String,
        gamemap: Array,
        playerrank: Array,
        tierrank: String,
        herorank: Array,
        rolerank: Array,
        talentbuildtype: String,
        mirrormatch: {
          type: [String, Number]
        },

        selectedSingleFilters: {},
        selectedMultiFilters: {},
        defaultGameType: [],

        modifiedincluderegion: null,
        modifiedincludeminimumaccountlevel: null,
        modifiedincludexaxisincrements: null,
        modifiedincludegametype: null,
        modifiedminimumgamedefault: null,
        modifiedincludeheroes: null,
        selectedGameDate: null,
        toggleExtraFilters: null,
        modifiedincluderole: null,
        modifiedincludegroupsize: null,
        showNav: true,
        defaultRoleModified: null,
        modifiedGroupSizeDefaultValue: null,
        swapHeroesFilter: false,
        swapRolesFilter: false,
        includetimeframemodified: null,
        seasonvalue: null,
        modifiedincludetier: null,
        modifiedincludeseason: null,
      }
    },
    created(){
      this.timeframetype = this.timeframetypeinput ? this.timeframetypeinput : this.defaultTimeFrameType;

      if(this.timeframetype == "last_update"){
        this.includetimeframemodified = false;
      }else{
        this.includetimeframemodified = this.includetimeframe;
      }

      this.timeframe = this.timeframeinput ? this.timeframeinput : this.getDefaultMinorBasedOnTimeframeType();
      this.gametype = this.gametypeinput ? this.gametypeinput : this.gametypedefault;
      this.region = this.regioninput ? this.regioninput : null;
      this.statfilter = this.statfilterinput ? this.statfilterinput : this.defaultStatType;
      this.herolevel = this.herolevelinput ? this.herolevelinput : null;
      this.hero = this.heroinput ? this.heroinput : null;
      this.role = this.roleinput ? this.roleinput : null;
      this.gamemap = this.gamemapinput ? this.gamemapinput : null;
      this.playerrank = this.playerrankinput ? this.playerrankinput : null;
      this.herorank = this.herorankinput ? this.herorankinput : null;
      this.rolerank = this.rolerankinput ? this.rolerankinput : null;
      this.talentbuildtype = this.talentbuildtypeinput ? this.talentbuildtypeinput : "Popular";
      this.mirrormatch = this.mirrormatchinput ? this.mirrormatchinput : this.filters.mirror[0].code;

      this.selectedSingleFilters = {
      };

      this.selectedMultiFilters = {
      };

      this.modifiedincludeminimumaccountlevel = this.includeminimumaccountlevel;
      this.modifiedincludexaxisincrements = this.includexaxisincrements;
      this.modifiedincludegametype = this.includegametype;
      this.modifiedincludeheroes = this.includehero;

      this.modifiedminimumgamedefault = this.minimumgamesdefault ? this.minimumgamesdefault : 0;

      this.selectedSingleFilters["Timeframe Type"] = this.timeframetype;
      this.selectedMultiFilters["Game Type"] = this.gametype;
      this.selectedMultiFilters["Timeframes"] = this.getDefaultMinorBasedOnTimeframeType();
      this.selectedSingleFilters["Stat Filter"] = this.defaultStatType;

      this.toggleExtraFilters = this.advancedfiltering;

      this.modifiedincluderole = this.includerole
      this.modifiedincludegroupsize = this.includegroupsize;
      this.modifiedincludetier = this.includetier;
      this.modifiedincludeseason = this.includeseason;

      if(!this.groupSizeDefaultValue){
        this.modifiedGroupSizeDefaultValue = "Solo";
      }

      this.checkScreenWidth();

      window.addEventListener('resize', this.checkScreenWidth);
    },
    mounted() {
    },
    computed: {
      showStatTypeFilter(){

        if(this.includestatfilter && this.toggleExtraFilters){
          if(this.selectedMultiFilters.hasOwnProperty('Timeframes')){
            if(this.selectedSingleFilters['Timeframe Type'] == 'major' || this.selectedMultiFilters['Timeframes'].length > 5){
              return false;
            }
          }
          return true;
        }
         


        return false;
      },
      timeframeTypeWithLastUpdate(){
        const updatedTimeframeTypes = [...this.filters.timeframe_type];
        updatedTimeframeTypes.push({ code: 'last_update', name: 'Last Update' });
        return updatedTimeframeTypes;
      },
      defaultTimeFrameType(){
        return this.filters.timeframe_type[1].code;
      },
      disabledFilter(){
        if(this.isLoading){
          return true;
        }

        if(!this.selectedMultiFilters.hasOwnProperty('Game Type')){
          return true;
        }

        if(!this.excludetimeframes){
          (3);

          if(!this.selectedMultiFilters.hasOwnProperty('Timeframes') && this.selectedSingleFilters["Timeframe Type"] != "last_update"){
            return true;
          }
        }

        if(this.disablefilter == true){
          return true;
        }
        return false;
      },
      defaultStatType(){
        return this.filters.stat_filter[0].code;
      },
      timeframes(){
        if(this.timeframetype == "minor" || this.timeframetype == "last_update"){
          return this.filters.timeframes;
        }else if(this.timeframetype == "major"){
          return this.filters.timeframes_grouped;
        }
      },
      seasons(){
        if(this.minimumseason){
          return this.filters.seasons.filter(season => season.code >= this.minimumseason);
        }
        return this.filters.seasons;
      },
      toggleButtonText(){
        if(this.toggleExtraFilters){
          return "Hide Advanced Filters";
        }
        return "Show Advanced Filters";
      },
      seasonsWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.seasons];
        updatedList.unshift(newValue);
        return updatedList;
      },
      leaderboardGameTypes(){
        if(!this.seasonvalue || this.seasonvalue >= 26){
          return this.filters.game_types.filter(gameType => gameType.code !== 'ud');
        }
        return this.filters.game_types;
      }
    },
    watch: {
      toggleExtraFilters(value){
        if(!value){
          delete this.selectedMultiFilters['Stat Filter'];
          delete this.selectedMultiFilters['Hero Level'];
          delete this.selectedMultiFilters['Map'];
          delete this.selectedMultiFilters['Hero Rank'];
          delete this.selectedMultiFilters['Role Rank'];
          delete this.selectedMultiFilters['Mirror Matches'];
        }
      },
    },
    destroyed(){
      window.removeEventListener('resize', this.checkScreenWidth);
    },
    methods: {
      handleInputChange(eventPayload) {
        if(eventPayload.field == "Timeframe Type"){
          if(eventPayload.value == 'last_update'){
            this.includetimeframemodified = false;
            delete this.selectedMultiFilters["Timeframes"];
          }else{
            this.timeframetype = eventPayload.value;
            this.timeframe = this.getDefaultMinorBasedOnTimeframeType();
            this.includetimeframemodified = true;
          }
        }

        if(eventPayload.type === 'single') {
          if (eventPayload.value === '') {
            delete this.selectedSingleFilters[eventPayload.field];
          } else {
            this.selectedSingleFilters[eventPayload.field] = eventPayload.value;
          }
        } else if(eventPayload.type === 'multi') {
          if(eventPayload.value.length == 0){
            delete this.selectedMultiFilters[eventPayload.field];
          }else{
            this.selectedMultiFilters[eventPayload.field] = eventPayload.value;
          }
        }


        if(eventPayload.field == "Chart Type" && eventPayload.value == "Hero Level"){
          this.modifiedincludeminimumaccountlevel = false;
          this.modifiedincludexaxisincrements = false;
          this.modifiedincludegametype = true;
          this.modifiedincludeheroes = true;
          

        }else if(eventPayload.field == "Chart Type" && eventPayload.value == "Account Level"){
          this.modifiedincludeminimumaccountlevel = true;
          this.modifiedincludexaxisincrements = true;
          this.modifiedincludegametype = false;
          this.modifiedincludeheroes = false;
        }


        if(eventPayload.field == "Type" || eventPayload.field == "Leaderboard Type"){
          this.modifiedincludeheroes = (eventPayload.value == "Hero");
          this.modifiedincluderole = (eventPayload.value == "Role");

          if(eventPayload.field == "Leaderboard Type"){
            this.modifiedincludeseason = true;
            this.modifiedincludematchpredictionseason = false;
            this.modifiedincludegroupsize = true;
            this.modifiedincludetier = true;
          }
   

          if(eventPayload.value == "Player"){
            delete this.selectedSingleFilters['Heroes'];
            delete this.selectedSingleFilters['Role'];
            this.swapHeroesFilter = false;
            this.swapRolesFilter = false;
          }else if(eventPayload.value == "Hero"){
            delete this.selectedSingleFilters['Role'];
            this.hero = 1;
            this.selectedSingleFilters.Heroes = 1;
            this.swapHeroesFilter = true;
            this.swapRolesFilter = false;
          }else if(eventPayload.value == "Role"){
            delete this.selectedSingleFilters['Heroes'];
            this.role = "Support";
            this.selectedSingleFilters["Role"] = "Support";
            this.swapRolesFilter = true;
            this.swapHeroesFilter = false;
          }else if(eventPayload.value == "Match Prediction"){
            this.modifiedincludegroupsize = false;
            this.modifiedincludetier = false;
            this.modifiedincludeseason = false;
            this.modifiedincludematchpredictionseason = true;

            delete this.selectedSingleFilters['Group Size'];
            delete this.selectedSingleFilters['Rank'];
            delete this.selectedSingleFilters['Season'];
            delete this.selectedSingleFilters['Heroes'];
            delete this.selectedSingleFilters['Role'];

          }
        }
        if(eventPayload.field == "Season" && this.includegroupsize){
          if(!this.overrideGroupSizeRemoval){
            this.modifiedincludegroupsize = (eventPayload.value >= 20);
          }
        }
        
        if(eventPayload.field == "Season"){
          this.seasonvalue = eventPayload.value;
        }


      },
      handleGameDateChange() {
        this.selectedSingleFilters["From Date"] = this.selectedGameDate;
      },
      handleDateInputBlur() {
        if(this.selectedGameDate == ""){
          this.selectedGameDate = null;
        }
        this.selectedSingleFilters["From Date"] = this.selectedGameDate;
      },
      resetGameDate(){
        this.selectedGameDate = null;
        delete this.selectedSingleFilters["From Date"];
      },
      getDefaultMinorBasedOnTimeframeType() {
        if(this.timeframetype == "minor"){
          return [this.filters.timeframes[0]?.code || ''];
        } else if(this.timeframetype == "major"){
          return [this.filters.timeframes_grouped[0]?.code || ''];
        }
        return '';
      },
      applyFilter() {
        if ((this.selectedMultiFilters.hasOwnProperty('Timeframes') || this.selectedSingleFilters["Timeframe Type"] == "last_update") && this.selectedMultiFilters.hasOwnProperty('Game Type')) {
          if(window.innerWidth < 768){
            this.showNav = false;
          }
          const allSelectedFilters = {
            single: this.selectedSingleFilters,
            multi: this.selectedMultiFilters
          };
          this.onFilter(allSelectedFilters); 
        }
      },
      showMobile(){
        this.showNav = !this.showNav;
      },
      checkScreenWidth() {
      // Set showDiv to true on desktop (width greater than 768 pixels) and false on mobile
          this.showNav = window.innerWidth > 768;
       },
    }
  };
</script>
