<template>
  <!-- A talent to pick: the icon and name, with its win rate when the page has one. Picked: a teal border and tick.
       Horizontal stacks the icon over the name, for the builder's columns. Greyed when the build has stats but none for it -->
  <button type="button"
    :class="['relative rounded-control border-2 transition-colors text-left min-w-0', isClicked ? 'border-teal bg-teal/10' : 'border-surface-1 bg-surface-1 hover:bg-surface-3',
      horizontal ? 'flex flex-col items-center gap-1.5 px-2 py-3 text-center' : 'flex items-center gap-3 px-3 py-2.5']">
    <talent-image-wrapper :talent="talent" size="talent" :popup-hoverable="false" :class="['shrink-0', { 'opacity-40': dimmed || noGames }]"></talent-image-wrapper>
    <span :class="['flex flex-col min-w-0', horizontal ? 'items-center' : 'flex-1']">
      <!-- Horizontal: two lines kept for the name, so a long name doesn't make its card taller than the rest -->
      <span :class="['leading-tight', horizontal ? 'text-xs min-h-[2.5em] line-clamp-2' : 'text-[0.9375rem]', isClicked ? 'font-semibold text-white' : dimmed || noGames ? 'text-subtext' : 'font-medium text-white']">{{ $talentName(talent) }}</span>
      <span v-if="talent.games_played" :class="['text-subtext tabular-nums', horizontal ? 'text-[0.625rem]' : 'text-[0.8125rem] mt-0.5']"><span :class="['font-bold', talent.win_rate > 50 ? 'text-win' : 'text-white']">{{ (talent.win_rate ?? 0).toFixed(2) }}%</span> · {{ $t(':played games', { played: talent.games_played.toLocaleString('en-US') }) }}</span>
      <span v-else-if="noGames" :class="['text-subtext/70', horizontal ? 'text-[0.625rem]' : 'text-[0.8125rem] mt-0.5']">{{ $t('No games') }}</span>
    </span>
    <span v-if="isClicked" :class="['w-4 h-4 shrink-0 rounded-[0.25rem] bg-teal flex items-center justify-center text-[0.625rem]', horizontal ? 'absolute top-1.5 right-1.5' : '']">
      <basil-icon name="check"></basil-icon>
    </span>
  </button>
</template>

<script>
export default {
  name: 'TalentBuilderClickBox',
  props: {
    talent: Object,
    isClicked: Boolean,
    // Another talent on this level is picked
    dimmed: Boolean,
    horizontal: { type: Boolean, default: false },
    // The build has stats, but none for this talent
    noGames: Boolean,
  },
};
</script>
