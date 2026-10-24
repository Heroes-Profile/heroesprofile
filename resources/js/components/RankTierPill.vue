<template>
  <span v-if="tier" class="inline-block min-w-[10.5em] text-center px-3 py-1.5 rounded font-medium whitespace-nowrap" :style="style">{{ tier }}</span>
</template>

<script>
// Heroes Profile rank tiers (Bronze 5 to Grand Master), coloured as in the redesign's MMR card
const COLORS = {
  Bronze: ['#a3684c', '#fff5e9'],
  Silver: ['#8a91ab', '#fcfbff'],
  Gold: ['#d5a94f', '#fef9e9'],
  Platinum: ['#ba79c4', '#fff2f7'],
  Diamond: ['#3668ad', '#def5ff'],
  Master: ['#7352c7', '#fff0ff'],
};

export default {
  name: 'RankTierPill',
  props: {
    tier: [String, Number],
  },
  computed: {
    style() {
      const name = Object.keys(COLORS).find(key => String(this.tier).includes(key));
      const [background, color] = name ? COLORS[name] : ['#393e4d', '#ffffff'];
      return { backgroundColor: background, color };
    },
  },
};
</script>
