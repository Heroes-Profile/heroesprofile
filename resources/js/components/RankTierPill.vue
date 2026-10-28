<template>
  <span v-if="tier" class="inline-block min-w-[10.5em] text-center px-3 py-1.5 rounded font-medium whitespace-nowrap" :style="style">{{ tier }}</span>
</template>

<script>
// Heroes Profile rank tiers (Bronze 5 to Grand Master), coloured as in the redesign's MMR card
export const TIER_COLORS = {
  Bronze: ['#a3684c', '#fff5e9'],
  Silver: ['#8a91ab', '#fcfbff'],
  Gold: ['#d5a94f', '#fef9e9'],
  Platinum: ['#ba79c4', '#fff2f7'],
  Diamond: ['#3668ad', '#def5ff'],
  Master: ['#7352c7', '#fff0ff'],
};

// Background and text colour for a tier or rank name ("Platinum 3", "Grandmaster #2")
export function tierStyle(tier) {
  const name = Object.keys(TIER_COLORS).find(key => String(tier).includes(key));
  const [backgroundColor, color] = name ? TIER_COLORS[name] : ['#393e4d', '#ffffff'];
  return { backgroundColor, color };
}

export default {
  name: 'RankTierPill',
  props: {
    tier: [String, Number],
  },
  computed: {
    style() {
      return tierStyle(this.tier);
    },
  },
};
</script>
