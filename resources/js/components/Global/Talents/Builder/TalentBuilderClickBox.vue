<template>
  <div :class="horizontal ? 'border-b text-black flex flex-col justify-stretch min-h-[15.625rem]' : 'text-black flex flex-col justify-stretch min-w-[13.125rem] max-md:min-w-[8.75rem]'">
    <div
      :class="['cursor-pointer p-4 transition duration-300 hover:bg-gray-md hover:text-black h-full m-2', horizontal ? 'min-h-[15.625rem]' : '', { 'bg-teal text-white': isClicked }]"
    >
      <div class="flex flex-col items-center justify-center text-center">
        <h3 :class="horizontal ? 'min-h-[5rem]' : 'pb-4'"> {{ $talentName(talent) }}</h3>
        <talent-image-wrapper :talent="talent" :size="'medium'" :class="horizontal ? 'mb-10' : ''"></talent-image-wrapper>
        <span v-if="talent.games_played">{{ (talent.win_rate ?? 0).toFixed(2) }}%</span>
        <span v-if="talent.games_played" class="text-xs">{{ $t(':played games', { played: talent.games_played.toLocaleString('en-US') }) }}</span>
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
    horizontal: { type: Boolean, default: false },
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