<template>
  <!-- A compact match row on phones: two short lines instead of the desktop columns -->
  <div :class="['group/row relative flex items-center gap-3 rounded overflow-hidden mb-1 pl-5 pr-3 py-2.5', bg]">
    <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
    <a :href="href" class="absolute inset-0 z-[1]" aria-label="Open match"></a>
    <div v-if="winner !== null" class="absolute inset-0 [mask-image:linear-gradient(to_right,black_60%,transparent)]" :class="winner ? 'bg-[rgba(0,139,139,0.35)]' : 'bg-[rgba(179,54,22,0.13)]'"></div>
    <div v-if="mapArt" class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="{ backgroundImage: `url('/images/maps/match/match-${mapArt}.jpg')` }"></div>
    <div v-if="winner !== null" class="absolute inset-y-0 left-0 w-1.5" :class="winner ? 'bg-win' : 'bg-red'"></div>
    <hero-image-wrapper v-if="hero" class="relative shrink-0" size="row" :hero="hero" :excludehover="true"></hero-image-wrapper>
    <div class="relative flex-1 min-w-0 flex flex-col gap-0.5">
      <div class="flex items-baseline justify-between gap-2">
        <span class="font-bold truncate">{{ title }}</span>
        <span v-if="result" class="shrink-0 text-sm font-medium" :class="winner ? 'text-win' : 'text-red'">{{ result }}</span>
      </div>
      <span class="text-xs truncate">{{ date }}</span>
      <div class="text-sm text-subtext truncate"><slot></slot></div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MatchRowMobile',
  props: {
    href: String,
    hero: Object,
    title: String,
    date: String,
    // true for a win, false for a loss, null for neither (no tint or side bar)
    winner: { type: Boolean, default: null },
    result: String,
    // A map's sanitized name, for the faded map art
    mapArt: String,
    bg: { type: String, default: 'bg-surface-1' },
  },
};
</script>
