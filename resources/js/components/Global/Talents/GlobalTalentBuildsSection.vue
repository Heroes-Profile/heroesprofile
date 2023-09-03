<template>
  <div>
    <table class="min-w-full bg-white">
      <thead>
        <tr colspan="4"> {{ "Builds" }}</tr>
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
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in talentbuilddata" :key="row">
          <td class="py-2 px-3 border-b border-gray-200">
            <div class="flex flex-wrap gap-4">
              <talent-box :talent="row.level_one"></talent-box>{{ row.level_one.talent_name }}
              <talent-box :talent="row.level_four"></talent-box>{{ row.level_one.level_four }}
              <talent-box :talent="row.level_seven"></talent-box>{{ row.level_one.level_seven }}
              <talent-box :talent="row.level_ten"></talent-box>{{ row.level_one.level_ten }}
              <talent-box :talent="row.level_thirteen"></talent-box>{{ row.level_one.level_thirteen }}
              <talent-box :talent="row.level_sixteen"></talent-box>{{ row.level_one.level_sixteen }}
              <talent-box :talent="row.level_twenty"></talent-box>{{ row.level_one.level_twenty }}
            </div>
          </td>
          <td class="py-2 px-3 border-b border-gray-200">{{ this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero)}}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
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
    talentbuilddata: Array,
    buildtype: String,
  },
  data(){
    return {
    }
  },
  created(){
    console.log(this.talentbuilddata)
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    getCopyBuildToGame(level_one, level_four, level_seven, level_ten, level_thirteen, level_sixteen, level_twenty, hero){
      return "[" + level_one.sort + level_four.sort + level_seven.sort + level_ten.sort + level_thirteen.sort + level_sixteen.sort + level_twenty.sort +"," + hero.build_copy_name + "]"
    }
  }
}
</script>