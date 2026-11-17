<template>
  <!-- A label, not a button: fills its cell up to 200px, with the inputs' corner rather than the buttons' -->
  <span v-if="tier" :class="['block w-full max-w-[12.5rem] min-w-[10.25rem] px-3 py-2 rounded-control font-medium whitespace-nowrap', align === 'left' ? 'text-left' : 'mx-auto text-center']" :style="style">{{ tierLabel }}</span>
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

// Background and text colour for a tier or rank name ("Platinum 3", "Grand Master #2")
export function tierStyle(tier) {
  const name = Object.keys(TIER_COLORS).find(key => String(tier).includes(key));
  const [backgroundColor, color] = name ? TIER_COLORS[name] : ['#393e4d', '#ffffff'];
  return { backgroundColor, color };
}

export default {
  name: 'RankTierPill',
  props: {
    tier: [String, Number],
    // 'center' (tables) or 'left' (the MMR card's fixed column)
    align: { type: String, default: 'center' },
  },
  computed: {
    tierLabel() {
      const [, league, division] = String(this.tier).match(/^(.*?)(?:\s+(\d+))?$/) || [];
      return division ? `${this.$t(league)} ${division}` : this.$t(String(this.tier));
    },
    style() {
      return tierStyle(this.tier);
    },
  },
};
</script>
