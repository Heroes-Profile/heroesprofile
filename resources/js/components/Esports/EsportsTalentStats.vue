<template>
  <div>
      <global-talent-details-section :talentdetaildata="talentdetaildata" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-details-section>
      <global-talent-builds-section :talentbuilddata="talentbuilddata" :buildtype="'Popular'" :statfilter="'win_rate'" :talentimages="talentimages[selectedHero.name]"></global-talent-builds-section>
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
  },
  watch: {
  },
  methods: {
  }
}
</script>