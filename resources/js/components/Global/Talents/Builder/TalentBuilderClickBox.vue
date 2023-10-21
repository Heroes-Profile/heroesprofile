<template>
  <div class=" border-b text-black  flex flex-col justify-stretch">
    <div
      class="cursor-pointer p-4 transition duration-300 hover:bg-gray-md h-full min-h-[170px] m-2"
      :class="{ 'bg-gray-medium': isClicked }"
    >
      <div class="flex flex-col items-center justify-center ">
        {{ talent.title }}
        <talent-image-wrapper :talent="talent" :size="'medium'"></talent-image-wrapper>
        <span v-if="talent.win_rate">{{ talent.win_rate }}%</span>
        <span v-if="talent.games_played">{{ talent.games_played }} games</span>
      </div>
    </div>
  </div>
</template>



<script>
export default {
  name: 'TalentBuilderClickBox',
  components: {
  },
  props: {
    talent: Object,
    winRate: Number,
    totalGames: Number,
    isClicked: Boolean,
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