<template>
  <!-- A player's row in a team column (Drafter, Auto Battler): the portrait ringed in the team's colour,
       dashed while it's the slot being filled, then a title and a status line -->
  <div :class="['min-h-[4rem] rounded-control bg-surface-1 px-3 py-2 flex items-center gap-3 border-2', active ? (color === 'teal' ? 'border-teal' : 'border-red') : 'border-surface-1']">
    <span :class="['w-12 h-12 shrink-0 rounded-full bg-cover bg-center', hero ? (color === 'teal' ? 'border-2 border-teal' : 'border-2 border-red')
      : active ? (color === 'teal' ? 'border-2 border-dashed border-teal bg-teal/20' : 'border-2 border-dashed border-red bg-red/20') : 'bg-surface-3']"
      :style="hero ? { backgroundImage: 'url(/images/heroes/' + hero.short_name + '.png)' } : {}"></span>
    <span class="flex flex-col min-w-0">
      <span class="font-semibold truncate">{{ title }}</span>
      <span :class="['text-sm truncate', statusHighlight ? (color === 'teal' ? 'text-lteal' : 'text-lred') : 'text-subtext']">{{ status }}</span>
    </span>
    <span v-if="$slots.default" class="ml-auto shrink-0"><slot></slot></span>
  </div>
</template>

<script>
export default {
  name: 'TeamSlot',
  props: {
    // teal (first team) or red
    color: { type: String, default: 'teal' },
    hero: { type: Object, default: null },
    active: { type: Boolean, default: false },
    title: { type: String, default: '' },
    status: { type: String, default: '' },
    // The status in the team's colour, e.g. "Picking now..."
    statusHighlight: { type: Boolean, default: false },
  },
};
</script>
