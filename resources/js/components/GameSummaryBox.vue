<template>
  <div
    class="relative flex border border-white bg-cover bg-no-repeat bg-center"
    :style="{ backgroundImage: `url('/images/maps/match/match-${data.game_map.sanitized_map_name}.jpg')` }"
  >    
    <div class="flex-1 bg-red-500 p-4"><round-box-small :hero="data.hero"></round-box-small></div>
    <div class="flex-1 bg-green-500 p-4">

       <stat-box :title="'Player MMR'" :value="data.player_conservative_rating" :mmrchange="data.player_change" :color="data.winner === 1 ? 'blue' : 'red'"></stat-box>
      </div>


    

    <div class="flex-1 flex gap-x-1">
      <talent-box v-if="data.level_one" :talent="data.level_one"></talent-box>
      <talent-box v-if="data.level_four" :talent="data.level_four"></talent-box>
      <talent-box v-if="data.level_seven" :talent="data.level_seven"></talent-box>
      <talent-box v-if="data.level_ten" :talent="data.level_ten"></talent-box>
      <talent-box v-if="data.level_thirteen" :talent="data.level_thirteen"></talent-box>
      <talent-box v-if="data.level_sixteen" :talent="data.level_sixteen"></talent-box>
      <talent-box v-if="data.level_twenty" :talent="data.level_twenty"></talent-box>
    </div>
  </div>
</template>


<script>
export default {
  name: 'GameSummaryBox',
  components: {
  },
  props: {
    data: Object
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
          console.log(this.data.level_seven);

  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>