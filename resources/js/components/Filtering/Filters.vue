<template>
  <div>
    <div class="flex flex-wrap items-center bg-gray-dark md:px-20">
      <single-select-filter v-if="includeherorole" :values="this.filters.hero_role" :text="'Hero or Role'" @input-changed="handleInputChange" :defaultValue="'Hero'"></single-select-filter>
      <single-select-filter v-if="playerheroroletype" :values="this.filters.type" :text="'Type'" @input-changed="handleInputChange" :defaultValue="'Player'"></single-select-filter>
      <single-select-filter v-if="includegroupsize" :values="this.filters.group_size" :text="'Group Size'" @input-changed="handleInputChange" :defaultValue="'Solo'"></single-select-filter>
      <single-select-filter v-if="includecharttype" :values="this.filters.chart_type" :text="'Chart Type'" @input-changed="handleInputChange" :defaultValue="'Account Level'"></single-select-filter>
      <single-select-filter v-if="includetimeframetype" :values="this.filters.timeframe_type" :text="'Timeframe Type'" :defaultValue="this.defaultTimeframeType" @input-changed="handleInputChange"></single-select-filter>
      <multi-select-filter v-if="includetimeframe" :values="this.timeframes" :text="'Timeframes'" :defaultValue="this.defaultMinor" @input-changed="handleInputChange"></multi-select-filter>
      <multi-select-filter v-if="includeregion" :values="this.filters.regions" :text="'Regions'" @input-changed="handleInputChange"></multi-select-filter>
      
      <single-select-filter v-if="modifiedincludeheroes" :values="this.filters.heroes" :text="'Heroes'" @input-changed="handleInputChange"></single-select-filter>
      <multi-select-filter v-if="modifiedincludegametype" :values="this.filters.game_types" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="this.defaultGameType"></multi-select-filter>
      <multi-select-filter v-if="includegametypefull" :values="this.filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="this.defaultGameType"></multi-select-filter>
      <single-select-filter v-if="includesinglegametype" :values="this.filters.game_types" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="this.defaultGameType[0]"></single-select-filter>
      <single-select-filter v-if="includesinglegametypefull" :values="this.filters.game_types_full" :text="'Game Type'" @input-changed="handleInputChange" :defaultValue="this.defaultGameType[0]"></single-select-filter>
      <single-select-filter v-if="includeseason" :values="seasons" :text="'Season'" @input-changed="handleInputChange" :defaultValue="this.defaultSeason"></single-select-filter>
      <input type="date" v-if="includegamedate" v-model="selectedGameDate" @input="handleGameDateChange">
      <multi-select-filter v-if="includegamemap" :values="this.filters.game_maps" :text="'Map'" @input-changed="handleInputChange"></multi-select-filter>
      <single-select-filter v-if="includesinglegamemap" :values="this.filters.game_maps" :text="'Map'" @input-changed="handleInputChange"></single-select-filter>
      <multi-select-filter v-if="includeplayerrank" :values="this.filters.rank_tiers" :text="'Player Rank'" @input-changed="handleInputChange"></multi-select-filter>
      
      <single-select-filter v-if="includetalentbuildtype" :values="this.filters.talent_build_types" :text="'Talent Build Type'" @input-changed="handleInputChange" :defaultValue="this.filters.talent_build_types[0].code"></single-select-filter>
      <single-select-filter v-if="includeminimumgames" :values="this.filters.minimum_games" :text="'Minimum Games'" @input-changed="handleInputChange" :defaultValue="modifiedminimumgamedefault"></single-select-filter>
      <single-select-filter v-if="includeheropartysize" :values="this.filters.hero_party_size" :text="'Hero Party Size'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter v-if="includeteamoneparty" :values="this.filters.party_combinations" :text="'Team One Party'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter v-if="includeteamtwoparty" :values="this.filters.party_combinations" :text="'Team Two Party'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter v-if="modifiedincludeminimumaccountlevel" :values="this.filters.minimum_account_level" :text="'Min. Account Level'" @input-changed="handleInputChange" :defaultValue="'100'"></single-select-filter>
      <single-select-filter v-if="modifiedincludexaxisincrements" :values="this.filters.x_axis_increments" :text="'X Axis Increments'" @input-changed="handleInputChange" :defaultValue="'25'"></single-select-filter>
      

      <template v-if="toggleExtraFilters">
        <single-select-filter v-if="includestatfilter" :values="this.filters.stat_filter" :text="'Stat Filter'" :defaultValue="this.defaultStatType" @input-changed="handleInputChange"></single-select-filter>
        <multi-select-filter v-if="includeherolevel" :values="this.filters.hero_level" :text="'Hero Level'" @input-changed="handleInputChange"></multi-select-filter>
        <single-select-filter v-if="includerole" :values="this.filters.role" :text="'Role'" @input-changed="handleInputChange"></single-select-filter>
        <multi-select-filter v-if="includeherorank" :values="this.filters.rank_tiers" :text="'Hero Rank'" @input-changed="handleInputChange"></multi-select-filter>
        <multi-select-filter v-if="includerolerank" :values="this.filters.rank_tiers" :text="'Role Rank'" @input-changed="handleInputChange"></multi-select-filter>
        <single-select-filter v-if="includemirror" :values="this.filters.mirror" :text="'Mirror Matches'" @input-changed="handleInputChange" :defaultValue="this.filters.mirror[0].code"></single-select-filter>

      </template>

      <button :disabled="disabledFilter" @click="applyFilter" class="ml-10 p-2" :class="{'bg-blue text-white': !disabledFilter, 'bg-gray-400 text-gray-700': disabledFilter}">
        Filter
      </button>
    </div>
    <custom-button @click="toggleExtraFilters = !toggleExtraFilters" :text="toggleButtonText" :alt="toggleButtonText" size="small" :ignoreclick="true"></custom-button>


   
  </div>

</template>

<script>

export default {
  name: 'Filters',
  components: {
  },
  props: {
    isLoading: Boolean,

    includeherorole: Boolean,
    playerheroroletype: Boolean,
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
    includegamemap: Boolean,
    includesinglegamemap: Boolean,
    includeplayerrank: Boolean,
    includeherorank: Boolean,
    includerolerank: Boolean,
    includemirror: Boolean,
    includetalentbuildtype: Boolean,
    includeminimumgames: Boolean,
    includeheropartysize: Boolean,
    includeteamoneparty: Boolean,
    includeteamtwoparty: Boolean,
    includeminimumaccountlevel: Boolean,
    includexaxisincrements: Boolean,
    includesinglegametypefull: Boolean,
    minimumseason: Number,
    includegamedate: Boolean,

    filters: {
      type: Object,
      required: true
    },
    onFilter: {
      type: Function,
      required: true,
    },
    gametypedefault: Array,
    minimumgamesdefault: String,
    defaultSeason: String,
    advancedfiltering: String,
  },
  data(){
    return {
      selectedSingleFilters: {},
      selectedMultiFilters: {},
      defaultTimeframeType: this.filters.timeframe_type[1].code,
      defaultGameType: [],

      modifiedincluderegion: null,
      modifiedincludeminimumaccountlevel: null,
      modifiedincludexaxisincrements: null,
      modifiedincludegametype: null,
      modifiedminimumgamedefault: null,
      modifiedincludeheroes: null,
      selectedGameDate: null,
      toggleExtraFilters: null,
    }
  },
  created(){    
    this.defaultGameType = this.gametypedefault;
    this.selectedSingleFilters = {
    };

    this.selectedMultiFilters = {
    };

    this.modifiedincludeminimumaccountlevel = this.includeminimumaccountlevel;
    this.modifiedincludexaxisincrements = this.includexaxisincrements;
    this.modifiedincludegametype = this.includegametype;
    this.modifiedincludeheroes = this.includehero;

    this.modifiedminimumgamedefault = this.minimumgamesdefault ? this.minimumgamesdefault : 0;

        

    this.selectedSingleFilters["Timeframe Type"] = this.defaultTimeframeType;
    this.selectedMultiFilters["Game Type"] = this.gametypedefault;
    this.selectedMultiFilters["Timeframes"] = this.defaultMinor;
    this.selectedSingleFilters["Stat Filter"] = this.defaultStatType;
    
    this.toggleExtraFilters = this.advancedfiltering;
  },
  mounted() {
  },
  computed: {
    disabledFilter(){
      if(this.isLoading || !this.selectedMultiFilters.hasOwnProperty('Timeframes') || !this.selectedMultiFilters.hasOwnProperty('Game Type')){
        return true;
      }

      return false;
    },
    defaultMinor() {
      return this.getDefaultMinorBasedOnTimeframeType();
    },
    defaultStatType(){
      return this.filters.stat_filter[0].code;
    },
    timeframes(){
      if(this.defaultTimeframeType == "minor"){
        return this.filters.timeframes;
      }else if(this.defaultTimeframeType == "major"){
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
    }
  },
  watch: {
  },
  methods: {
    handleInputChange(eventPayload) {
      if(eventPayload.field == "Timeframe Type" && eventPayload.value == "minor"){
        this.defaultTimeframeType = eventPayload.value;
      }else if(eventPayload.field == "Timeframe Type" && eventPayload.value == "major"){
        this.defaultTimeframeType = eventPayload.value;
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
    },
    handleGameDateChange() {
    },
    getDefaultMinorBasedOnTimeframeType() {
      if(this.defaultTimeframeType == "minor"){
        return [this.filters.timeframes[0]?.code || ''];  // Use optional chaining to avoid errors
      } else if(this.defaultTimeframeType == "major"){
        return [this.filters.timeframes_grouped[0]?.code || '']; // Use optional chaining to avoid errors
      }
      return ''; // Default return value, adjust as needed
    },
    applyFilter() {
      if (this.selectedMultiFilters.hasOwnProperty('Timeframes') && this.selectedMultiFilters.hasOwnProperty('Game Type')) {
        const allSelectedFilters = {
          single: this.selectedSingleFilters,
          multi: this.selectedMultiFilters
        };
        this.onFilter(allSelectedFilters); 
      }
    },
  }
};
</script>
