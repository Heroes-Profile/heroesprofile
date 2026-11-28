<template>
  <!-- The in-game scoreboard icon for kills, deaths or assists, lit on hover with its name in a popup -->
  <icon-with-hover popupsize="fit">
    <template #trigger>
      <span class="group/kda">
        <img :src="`/images/scorescreen/${stat}.png`" :alt="label" class="w-6 h-6 group-hover/kda:hidden"/>
        <img :src="`/images/scorescreen/${stat}_hover.png`" alt="" class="w-6 h-6 hidden group-hover/kda:block"/>
      </span>
    </template>
    <div>
      <p class="max-sm:text-xs">{{ label }}</p>
    </div>
  </icon-with-hover>
</template>

<script>
const LABELS = { kills: 'Kills', deaths: 'Deaths', assists: 'Assists' };

export default {
  name: 'KdaIcon',
  props: {
    stat: {
      type: String,
      required: true,
    },
  },
  computed: {
    label() {
      return LABELS[this.stat];
    },
  },
};
</script>
