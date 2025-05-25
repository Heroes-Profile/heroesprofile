<template>
  <div class=" text-black flex flex-col justify-stretch min-w-[210px] ">
    <div
      class="cursor-pointer p-4 transition duration-300 hover:bg-gray-md hover:text-black h-full m-2 "
      :class="{ 'bg-teal text-white': isClicked }"
    >
      <div class="flex flex-col items-center justify-center text-center">
       <h3 class="pb-4"> {{ talent.title }}</h3>
        <talent-image-wrapper :talent="talent" :size="'medium'" class=""></talent-image-wrapper>
        <span v-if="talent.win_rate">{{ talent.win_rate.toFixed(2) }}%</span>
        <span v-if="talent.games_played" class="text-xs">{{ talent.games_played.toLocaleString('en-US') }} games</span>
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