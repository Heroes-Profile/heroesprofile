<template>
  <panel-box :inner="true" :title="$t('Party Size Win Rates')" :link-text="linkText" :link-href="linkHref">
    <div class="flex flex-col gap-3">
      <div v-for="stack in partySizes" :key="stack.label" class="flex flex-col gap-1.5">
        <div class="flex justify-between">
          <span class="text-header">{{ $t(stack.label) }}</span>
          <span class="text-subtext">{{ $t('Games: :games', { games: stack.games.toLocaleString('en-US') }) }}</span>
        </div>
        <stat-bar :value="stack.winRate.toFixed(2)" suffix="%" :color="$winRateColor(stack.winRate)"></stat-bar>
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
    linkText: String,
    linkHref: String,
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
