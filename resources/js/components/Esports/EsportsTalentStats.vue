<template>
  <!-- The same panels as the hero view on /Global/Talents, from this league's games -->
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
    <panel-box title="Talent stats" subtitle="Win rate, popularity and games for every talent. The best win rate on each tier is highlighted.">
      <compact-talent-grid :talent-data="talentLevels"></compact-talent-grid>
    </panel-box>

    <panel-box id="builds" title="Talent builds" subtitle="Copy a build code and paste it in game chat." class="scroll-mt-4">
      <global-talent-builds-section :talentbuilddata="talentbuilddata" :esport="'true'" :buildtype="'Popular'" :statfilter="'win_rate'"></global-talent-builds-section>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <span class="text-sm text-subtext">{{ $t('Want something else? Make your own build in the Talent Builder.') }}</span>
        <custom-button :href="'/Global/Talents/Builder/' + selectedHero.name" :text="'Open Talent Builder'" :alt="'Open Talent Builder'" color="teal"></custom-button>
      </div>
    </panel-box>
  </div>
</template>

<script>
export default {
  name: 'EsportsTalentStats',
  components: {
  },
  props: {
    talentdetaildata: {
      type: [Array, Object]
    },
    talentbuilddata: Array,
    talentimages: Object,
    selectedHero: Object,
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
    // Each level's talents as a list; the server keys them by their place on the level, which can arrive as an object
    talentLevels() {
      return Object.fromEntries(Object.entries(this.talentdetaildata || {}).map(([level, talents]) => [level, Object.values(talents || {})]));
    },
  },
  watch: {
  },
  methods: {
  }
}
</script>