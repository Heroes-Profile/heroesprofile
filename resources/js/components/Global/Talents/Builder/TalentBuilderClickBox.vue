<template>
  <!-- A talent to pick: the icon and name, with its win rate when the page has one. Picked: a teal border and tick.
       Horizontal stacks the icon over the name, for the builder's columns -->
  <button type="button"
    :class="['relative rounded-control border-2 transition-colors text-left min-w-0', isClicked ? 'border-teal bg-teal/10' : 'border-surface-1 bg-surface-1 hover:bg-surface-3',
      horizontal ? 'flex flex-col items-center gap-1.5 px-2 py-3 text-center' : 'flex items-center gap-2 px-2 py-1.5']">
    <talent-image-wrapper :talent="talent" size="talent" :popup-hoverable="false" :class="['shrink-0', { 'opacity-50': dimmed }]"></talent-image-wrapper>
    <span :class="['flex flex-col min-w-0', horizontal ? 'items-center' : 'flex-1']">
      <!-- Horizontal: two lines kept for the name, so a long name doesn't make its card taller than the rest -->
      <span :class="['text-xs leading-tight', { 'min-h-[2.5em] line-clamp-2': horizontal }, isClicked ? 'font-semibold text-white' : dimmed ? 'text-subtext' : 'text-white']">{{ $talentName(talent) }}</span>
      <span v-if="talent.games_played" class="text-[0.625rem] text-subtext tabular-nums">{{ (talent.win_rate ?? 0).toFixed(2) }}% · {{ $t(':played games', { played: talent.games_played.toLocaleString('en-US') }) }}</span>
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
  },
};
</script>
