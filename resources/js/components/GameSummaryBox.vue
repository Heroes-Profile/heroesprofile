<template>
  <div class="relative ml-10 ">
    <div class="mt-4 pl-[6em] m-l-auto w-full text-right min-h-4">{{caption}}</div>
  <div
    :class="[
      'flex border border-white border-2 bg-cover bg-no-repeat bg-center rounded-2xl border-red pl-[6em] ',
    {
      'border-teal': data.winner === 1
    }
    ]"
    :style="{ backgroundImage: `url('/images/maps/match/match-${data.game_map.sanitized_map_name}.jpg')` }"
  >    
    <div class=" bg-red-500 absolute -left-10 -bottom-[.5em]">
      <hero-image-wrapper size="xl" :hero="data.hero"></hero-image-wrapper>
    </div>
    <div class="flex w-full">

       <stat-box :title="'Player MMR'" :value="data.player_conservative_rating" :secondstat="data.player_change" :secondcaption="'Change'" secondtype="mmrchange" :color="data.winner === 1 ? 'teal' : 'red'"></stat-box>
       <stat-box :title="'Player MMR'" :value="data.player_conservative_rating" :secondstat="data.player_change" :secondcaption="'Change'" secondtype="mmrchange" :color="data.winner === 1 ? 'teal' : 'red'"></stat-box>
       <stat-box :title="'Player MMR'" :value="data.player_conservative_rating" :secondstat="data.player_change" :secondcaption="'Change'" secondtype="mmrchange" :color="data.winner === 1 ? 'teal' : 'red'"></stat-box>
      </div>


    

    <div class="flex gap-x-1 mx-2">
      <talent-image-wrapper v-if="data.level_one" :talent="data.level_one" :size="'medium'"></talent-image-wrapper>
      <talent-image-wrapper v-if="data.level_four" :talent="data.level_four" :size="'medium'"></talent-image-wrapper>
      <talent-image-wrapper v-if="data.level_seven" :talent="data.level_seven" :size="'medium'"></talent-image-wrapper>
      <talent-image-wrapper v-if="data.level_ten" :talent="data.level_ten" :size="'medium'"></talent-image-wrapper>
      <talent-image-wrapper v-if="data.level_thirteen" :talent="data.level_thirteen" :size="'medium'"></talent-image-wrapper>
      <talent-image-wrapper v-if="data.level_sixteen" :talent="data.level_sixteen" :size="'medium'"></talent-image-wrapper>
      <talent-image-wrapper v-if="data.level_twenty" :talent="data.level_twenty" :size="'medium'"></talent-image-wrapper>
    </div>
  </div>
</div>
</template>


<script>
export default {
  name: 'GameSummaryBox',
  components: {
  },
  props: {
    data: Object,
    caption: String
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
    console.log(this.data);
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>