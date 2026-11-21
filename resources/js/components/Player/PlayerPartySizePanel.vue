<template>
  <panel-box :inner="true" :title="$t(title)" :link-text="linkText" :link-href="linkHref" :skeleton="skeleton">
    <!-- Spread over the card, so it lines up with a taller card beside it -->
    <div class="flex-1 flex flex-col justify-between gap-3">
      <div v-for="stack in partySizes" :key="stack.label" class="flex flex-col gap-1.5">
        <div class="flex justify-between leading-[1.2]">
          <span :class="['text-header', { 'skeleton-text': skeleton }]">{{ $t(stack.label) }}</span>
          <span v-if="stack.games" class="text-subtext">{{ $t('Games: :games', { games: stack.games.toLocaleString('en-US') }) }}</span>
        </div>
        <!-- No games at this party size: the empty bar without its 0.00% -->
        <stat-bar :value="stack.winRate.toFixed(2)" suffix="%" :color="$winRateColor(stack.winRate)" :hide-value="!stack.games"></stat-bar>
      </div>
    </div>
  </panel-box>
</template>

<script>
export default {
  name: 'PlayerPartySizePanel',
  props: {
    data: Object,
    // The profile's fields are stack_one_wins, a single hero's are stack_size_one_wins
    prefix: {
      type: String,
      default: 'stack_',
    },
    // Profile, hero, map and role pages count the player's own party; the Party pages pass their own wording
    title: {
      type: String,
      default: 'Win Rate by Own Party Size',
    },
    linkText: String,
    linkHref: String,
    // A loading skeleton: labels and link as grey blocks their size, bars empty
    skeleton: Boolean,
  },
  computed: {
    partySizes(){
      return [
        { label: 'Solo', key: 'one' },
        { label: '2 - Stack', key: 'two' },
        { label: '3 - Stack', key: 'three' },
        { label: '4 - Stack', key: 'four' },
        { label: '5 - Stack', key: 'five' },
      ].map(stack => ({
        label: stack.label,
        games: this.data[`${this.prefix}${stack.key}_wins`] + this.data[`${this.prefix}${stack.key}_losses`],
        winRate: this.data[`${this.prefix}${stack.key}_win_rate`],
      }));
    },
  },
};
</script>
