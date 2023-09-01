<template>
  <div>
    <single-select-filter :values="this.filters.timeframe_type" :text="'Timeframe Type'" :defaultValue="this.defaultTimeframeType"></single-select-filter>
    <multi-select-filter :values="this.timeframes" :text="'Timeframes'" :defaultValue="this.defaultMinor"></multi-select-filter>
    <multi-select-filter :values="this.filters.regions" :text="'Regions'"></multi-select-filter>
    <single-select-filter :values="this.filters.stat_filter" :text="'Stat Filter'" :defaultValue="this.defaultStatType"></single-select-filter>
    <multi-select-filter :values="this.filters.hero_level" :text="'Hero Level'"></multi-select-filter>
    <single-select-filter :values="this.filters.role" :text="'Role'"></single-select-filter>
    <single-select-filter :values="this.filters.heroes" :text="'Heroes'"></single-select-filter>
    <multi-select-filter :values="this.filters.game_types" :text="'Game Type'"></multi-select-filter>
    <multi-select-filter :values="this.filters.game_maps" :text="'Map'"></multi-select-filter>
    <multi-select-filter :values="this.filters.rank_tiers" :text="'Player Rank'"></multi-select-filter>
    <multi-select-filter :values="this.filters.rank_tiers" :text="'Hero Rank'"></multi-select-filter>
    <multi-select-filter :values="this.filters.rank_tiers" :text="'Role Rank'"></multi-select-filter>
    <single-select-filter :values="this.filters.mirror" :text="'Mirror Matches'"></single-select-filter>
    <single-select-filter :values="this.filters.talent_build_types" :text="'Talent Build Type'"></single-select-filter>


    <button @click="applyFilter" class="mt-4 bg-blue-500 text-white p-2 rounded">
      Filter
    </button>
  </div>

</template>

<script>
export default {
  name: 'Filters',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    onFilter: {
      type: Function,
      required: true,
    },
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    defaultTimeframeType() {
      return this.filters.timeframe_type[1].code;
    },
    defaultMinor(){
      if(this.defaultTimeframeType == "minor"){
        return [this.filters.timeframes[1].code];
      }else if(this.defaultTimeframeType == "major"){
        return [this.filters.timeframes_grouped[0].code];
      }
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
  },
  watch: {
  },
  methods: {
    applyFilter() {
      // Call the parent's getData method and pass the selectedRegions
      this.onFilter(this.selectedRegions);
    },
  }
};
</script>
