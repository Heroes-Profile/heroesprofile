<template>
  <div>
    <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th :colspan="statfilter ? 5 : 4" class="text-center py-2 px-3 border-b border-gray-200">
              Builds
            </th>
          </tr>
        </thead>
      <thead>
        <tr>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Talents
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Copy Build top Game
          </th>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Total
          </th>                
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Chance
          </th>        
          <th v-if="statfilter" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Avg {{ statfilter.charAt(0).toUpperCase() + statfilter.slice(1) }}
          </th>                           
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in talentbuilddata" :key="row">
          <td class="py-2 px-3 border-b border-gray-200">
            <div class="flex flex-wrap gap-4">
              <talent-box :talent="row.level_one"></talent-box>
              <talent-box :talent="row.level_four"></talent-box>
              <talent-box :talent="row.level_seven"></talent-box>
              <talent-box :talent="row.level_ten"></talent-box>
              <talent-box :talent="row.level_thirteen"></talent-box>
              <talent-box :talent="row.level_sixteen"></talent-box>
              <talent-box :talent="row.level_twenty"></talent-box>
            </div>
          </td>
          <td class="py-2 px-3 border-b border-gray-200">
            {{ this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero) }}
            <custom-button @click="copyToClipboard(row)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
          </td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
          <td v-if="statfilter" class="py-2 px-3 border-b border-gray-200">{{ row.total_filter_type }}</td>
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
    getCopyBuildToGame(level_one, level_four, level_seven, level_ten, level_thirteen, level_sixteen, level_twenty, hero){
      return "[T" + level_one.sort + level_four.sort + level_seven.sort + level_ten.sort + level_thirteen.sort + level_sixteen.sort + level_twenty.sort +"," + hero.build_copy_name + "]"
    },
    copyToClipboard(row) {
      const textToCopy = this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero);
      navigator.clipboard.writeText(textToCopy).then(function() {
        console.log('Text successfully copied to clipboard');
      }).catch(function(err) {
        console.error('Unable to copy text to clipboard', err);
      });
    }
  }
}
</script>