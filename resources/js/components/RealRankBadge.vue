<template>
  <div :class="label == 'right' ? 'flex items-center gap-2' : 'flex flex-col items-center'">
    <div v-if="image" class="relative shrink-0" :style="{ width: badgeWidth + 'px' }">
      <img :src="image" :alt="name" class="w-full">
      <span v-if="rank.league == 'grandmaster' && rank.ladder_position" class="absolute left-0 right-0 text-center font-bold text-white leading-none" :style="ladderStyle">{{ rank.ladder_position }}</span>
    </div>
    <div v-if="label" :class="labelClass">{{ name }}</div>
  </div>
</template>

<script>
import { translate as translateText } from '../utils/translate';

// "Platinum 3", "Master", "Grand Master #2", in English: tierStyle() picks the colour by these names.
// Pass the translator to get them in the visitor's language.
export function rankName(rank, translate = (text) => text) {
  if (rank.league == 'unranked') {
    return translate('Unranked');
  }
  if (rank.league == 'grandmaster') {
    return translate('Grand Master') + (rank.ladder_position ? ' #' + rank.ladder_position : '');
  }
  if (rank.league == 'master') {
    return translate('Master');
  }
  return translate(rank.league.charAt(0).toUpperCase() + rank.league.slice(1)) + ' ' + rank.division;
}

// The same name in the visitor's language, for showing
export function localRankName(rank) {
  return rankName(rank, translateText);
}

// Image widths in public/images/ranks: league badges are 292 wide (cropped from the game's 350), Master and Grand Master 380,
// all at the game's scale
const IMAGE_WIDTHS = { master: 380, grandmaster: 380 };
// Bump when the badge art changes so Cloudflare and browsers fetch the new files
const IMAGE_VERSION = 2;

export default {
  name: 'RealRankBadge',
  props: {
    // { league, division, ladder_position }
    rank: Object,
    // Width of a league badge; Master and Grand Master come out a little larger, as in the game
    width: Number,
    // Where the rank's name goes: 'below', 'right', or nothing for the badge alone
    label: String,
    labelClass: String,
  },
  computed: {
    image(){
      const league = this.rank && this.rank.league;
      if(!league || league == 'unranked'){
        return null;
      }
      if(league == 'master' || league == 'grandmaster'){
        return '/images/ranks/' + league + '.png?v=' + IMAGE_VERSION;
      }
      return '/images/ranks/' + league + '_' + this.rank.division + '.png?v=' + IMAGE_VERSION;
    },
    name(){
      return localRankName(this.rank);
    },
    badgeWidth(){
      return Math.round(this.width * (IMAGE_WIDTHS[this.rank.league] || 292) / 292);
    },
    // The ladder number, bare as the game shows it, centred in the gap between the "Grand Master" label (ends 36% down)
    // and the crown's spikes (start 53% down). Longer numbers get a smaller font so "999" still fits the disc.
    ladderStyle(){
      const width = this.badgeWidth;
      const height = width;
      const characters = String(this.rank.ladder_position).length;
      const fontSize = Math.min(height * 0.19, width * 0.44 / (0.6 * characters));
      // Roboto's digits sit 0.12em above the middle of their line box
      return { top: (height * 0.445 - fontSize * 0.38) + 'px', fontSize: fontSize + 'px', textShadow: '0 0 6px rgba(110, 180, 255, 0.9)' };
    },
  },
}
</script>
