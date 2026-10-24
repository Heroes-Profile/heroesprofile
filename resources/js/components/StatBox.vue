<template>
  <div :class="[
    'inline-block m-1 max-md:mx-0 min-w-[10em] bg-surface-1 rounded px-3 py-2 text-left',
    accentClass,
    {
      'min-w-[16em]' : secondstat
    }
  ]">
    <div class="flex gap-6">
      <div class="flex flex-col min-w-0">
        <span class="text-sm text-subtext whitespace-nowrap">{{ title }}</span>
        <span class="text-xl font-semibold text-header">{{ value }}</span>
      </div>
      <div v-if="secondstat" class="flex flex-col min-w-0">
        <span class="text-sm text-subtext whitespace-nowrap">{{ secondcaption }}</span>
        <span :class="['text-xl font-semibold', changeClass]">{{ formattedChange }}</span>
      </div>
    </div>
  </div>
</template>

<script>
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
