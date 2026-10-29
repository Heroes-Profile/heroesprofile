<template>
  <!-- Same column widths and padding as the game-summary-box rows -->
  <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-dark font-medium text-subtext max-2xl:hidden">
    <span class="flex-1 max-w-[410px] pl-6">Match</span>
    <span class="w-[430px] shrink-0 px-4">Statistics</span>
    <!-- The in-game scoreboard icons, in the same order as the row's kills | deaths | assists -->
    <span class="w-[140px] shrink-0 grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] items-center justify-items-center">
      <icon-with-hover v-for="(stat, index) in kdaStats" :key="stat.icon" :style="{ gridColumn: index * 2 + 1 }" popupsize="fit">
        <template #trigger>
          <span class="group/kda">
            <img :src="`/images/scorescreen/${stat.icon}.png`" :alt="stat.label" class="w-6 h-6 group-hover/kda:hidden"/>
            <img :src="`/images/scorescreen/${stat.icon}_hover.png`" alt="" class="w-6 h-6 hidden group-hover/kda:block"/>
          </span>
        </template>
        <div>
          <p class="max-sm:text-xs">{{ stat.label }}</p>
        </div>
      </icon-with-hover>
    </span>
    <span class="w-[362px] shrink-0 px-4">Talents</span>
    <span class="w-[362px] shrink-0 pl-1.5">Players</span>
  </div>
</template>

<script>
export default {
  name: 'GameSummaryHeader',
  data() {
    return {
      kdaStats: [
        { icon: 'kills', label: 'Kills' },
        { icon: 'deaths', label: 'Deaths' },
        { icon: 'assists', label: 'Assists' },
      ],
    };
  },
};
</script>
