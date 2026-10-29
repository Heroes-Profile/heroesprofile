<template>
  <!-- Same column widths and padding as the game-summary-box rows -->
  <div class="flex justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext max-2xl:hidden">
    <span class="flex-1 max-w-[410px] pl-6">Match</span>
    <span class="w-[430px] shrink-0 px-4">Statistics</span>
    <span class="w-[126px] shrink-0"></span>
    <span class="w-[362px] shrink-0 px-4">Talents</span>
    <span class="w-[362px] shrink-0 pl-1.5">Players</span>
  </div>
</template>

<script>
export default {
  name: 'GameSummaryHeader',
};
</script>
