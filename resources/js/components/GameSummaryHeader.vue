<template>
  <!-- The compact rows' columns; Game Type and Game ID sort -->
  <div v-if="compact" class="flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext">
    <span class="w-[320px] shrink-0 pl-5 cursor-default">Match</span>
    <span v-if="esport" class="w-[160px] shrink-0 px-4 cursor-default">Game</span>
    <span v-else class="w-[160px] shrink-0 px-4 cursor-pointer" @click="$emit('sort', 'game_type_id')">Game Type<sort-indicator column="game_type_id" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
    <span class="w-[140px] shrink-0 px-4 cursor-pointer" @click="$emit('sort', 'replayID')">Game ID<sort-indicator column="replayID" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator></span>
    <span v-if="esport" class="w-[270px] shrink-0 px-4">Team</span>
    <span v-else class="w-[130px] shrink-0 px-4">Player</span>
    <span v-if="!esport" class="w-[140px] shrink-0 grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] items-center justify-items-center">
      <kda-icon v-for="(stat, index) in kdaStats" :key="stat" :stat="stat" :style="{ gridColumn: index * 2 + 1 }"></kda-icon>
    </span>
    <span class="w-[426px] shrink-0 px-4">Talents</span>
  </div>
  <!-- Same column widths and padding as the game-summary-box rows -->
  <div v-else class="flex justify-between items-center pb-4 mb-4 border-b border-gray-dark font-medium text-subtext max-2xl:hidden">
    <span class="flex-1 max-w-[410px] pl-6">Match</span>
    <span class="w-[430px] shrink-0 px-4">{{ esport ? 'Teams' : 'Statistics' }}</span>
    <!-- The in-game scoreboard icons, in the same order as the row's kills | deaths | assists -->
    <span class="w-[140px] shrink-0 grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] items-center justify-items-center">
      <kda-icon v-for="(stat, index) in kdaStats" :key="stat" :stat="stat" :style="{ gridColumn: index * 2 + 1 }"></kda-icon>
    </span>
    <span class="w-[362px] shrink-0 px-4">Talents</span>
    <span class="w-[362px] shrink-0 pl-1.5">Players</span>
  </div>
</template>

<script>
export default {
  name: 'GameSummaryHeader',
  props: {
    compact: Boolean,
    // Esports rows show the game and teams where others show game type and MMR
    esport: Boolean,
    sortKey: String,
    sortDir: String,
  },
  emits: ['sort'],
  data() {
    return {
      kdaStats: ['kills', 'deaths', 'assists'],
    };
  },
};
</script>
