<template>
  <div :class="[
    'inline-block m-1 max-md:mx-0 min-w-[10em] rounded px-3 py-2 text-left',
    tier ? '' : 'bg-surface-1',
    accentClass,
    {
      'min-w-[16em]' : secondstat
    }
  ]" :style="tier ? tierStyle(tier) : null">
    <div class="flex gap-6">
      <div :class="['flex min-w-0', valueFirst ? 'flex-col-reverse' : 'flex-col']">
        <span :class="['text-sm whitespace-nowrap', { 'text-subtext': !tier }]">{{ title }}</span>
        <span :class="['text-xl font-semibold', { 'text-header': !tier }]">{{ value }}</span>
      </div>
      <div v-if="secondstat" class="flex flex-col min-w-0">
        <span class="text-sm text-subtext whitespace-nowrap">{{ secondcaption }}</span>
        <span :class="['text-xl font-semibold', changeClass]">{{ formattedChange }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { tierStyle } from './RankTierPill.vue';

// The colour a page passes becomes a stripe down the left edge
const ACCENTS = {
  blue: 'border-l-4 border-blue',
  lblue: 'border-l-4 border-lblue',
  teal: 'border-l-4 border-teal',
  red: 'border-l-4 border-red',
  yellow: 'border-l-4 border-yellow',
  purple: 'border-l-4 border-purple',
  'gray-dark': 'border-l-4 border-gray-dark',
};

  export default {
    name: 'StatBox',
    components: {
    },
    props: {
      title: String,
      secondstat: {
        type: [String, Number]
      },
      secondtype: String,
      secondcaption: String,
      color: String,
      winner: Boolean,
      value: {
        type: [String, Number]
      },
      // A rank ("Platinum 3") fills the box with that tier's colour
      tier: String,
      valueFirst: Boolean,
    },
    methods: {
      tierStyle,
    },
    computed: {
      accentClass() {
        return ACCENTS[this.color] || '';
      },
      changeClass() {
        if(this.secondtype != 'mmrchange'){
          return 'text-header';
        }
        return Number(this.secondstat) >= 0 ? 'text-win' : 'text-red';
      },
      formattedChange() {
        if(this.secondtype == 'mmrchange'){
          return this.secondstat > 0 ? `+${this.secondstat}` : this.secondstat;
        }
        else{
          return this.secondstat;
        }
      },
    },
  }
</script>
