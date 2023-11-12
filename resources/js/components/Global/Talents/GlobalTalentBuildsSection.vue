<template>
  <div>
    <table class="min-w-0 ml-0">
        <thead>
          <tr>
            <th :colspan="statfilter ? 5 : 4" class="text-center py-2 px-3 ">
              Builds
            </th>
          </tr>
        </thead>
      <thead>
        <tr>
          <th class=" text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Talents
          </th>
          <th class=" text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Copy Build top Game
          </th>
          <th class=" text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Total
          </th>                
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Chance %
          </th>        
          <th v-if="statfilter && statfilter != 'win_rate'" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Avg {{ statfilter.charAt(0).toUpperCase() + statfilter.slice(1) }}
          </th>                           
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in talentbuilddata" :key="row">
          <td class="py-2 px-3 ">
            <div class="flex flex-wrap gap-4">
              <talent-image-wrapper :talent="row.level_one"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_four"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_seven"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_ten"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_thirteen"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_sixteen"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_twenty"></talent-image-wrapper>
            </div>
          </td>
          <td class="py-2 px-3 ">
            {{ this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero) }}
            <custom-button @click="copyToClipboard(row)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
          </td>
          <td class="py-2 px-3 ">{{ row.games_played.toLocaleString() }}</td>
          <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
          <td v-if="statfilter && statfilter != 'win_rate'" class="py-2 px-3 ">{{ row.total_filter_type.toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'GlobalTalentBuildsSection',
  components: {
  },
  props: {
    talentbuilddata: {
      Type: Object|Array
    },
    buildtype: String,
    statfilter: String,
    talentimages: Array,
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
    getCopyBuildToGame(level_one, level_four, level_seven, level_ten, level_thirteen, level_sixteen, level_twenty, hero) {
      return "[T" + 
        (level_one ? level_one.sort : '0') + 
        (level_four ? level_four.sort : '0') + 
        (level_seven ? level_seven.sort : '0') + 
        (level_ten ? level_ten.sort : '0') + 
        (level_thirteen ? level_thirteen.sort : '0') + 
        (level_sixteen ? level_sixteen.sort : '0') + 
        (level_twenty ? level_twenty.sort : '0') +
        "," + hero.build_copy_name + "]"
    },
    copyToClipboard(row) {
      const textToCopy = this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero);
      navigator.clipboard.writeText(textToCopy).then(function() {
      }).catch(function(err) {
      });
    }
  }
}
</script>