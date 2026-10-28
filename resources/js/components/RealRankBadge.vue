<template>
  <div :class="label == 'right' ? 'flex items-center gap-2' : 'flex flex-col items-center'">
    <div v-if="image" class="relative shrink-0" :style="{ width: badgeWidth + 'px' }">
      <img :src="image" :alt="name" class="w-full">
      <span v-if="rank.league == 'grandmaster' && rank.ladder_position" class="absolute left-0 right-0 text-center font-bold text-white leading-none" :style="ladderStyle">#{{ rank.ladder_position }}</span>
    </div>
    <div v-if="label" :class="labelClass">{{ name }}</div>
  </div>
</template>

<script>
// "Platinum 3", "Master", "Grandmaster #2"
export function rankName(rank) {
  if (rank.league == 'unranked') {
    return 'Unranked';
  }
  if (rank.league == 'grandmaster') {
    return 'Grandmaster' + (rank.ladder_position ? ' #' + rank.ladder_position : '');
  }
  if (rank.league == 'master') {
    return 'Master';
  }
  return rank.league.charAt(0).toUpperCase() + rank.league.slice(1) + ' ' + rank.division;
}

// Image widths in public/images/ranks: league badges are 292 wide (cropped from the game's 350), Master 380, Grandmaster 420,
// all at the game's scale
const IMAGE_WIDTHS = { master: 380, grandmaster: 420 };

export default {
  name: 'RealRankBadge',
  props: {
    // { league, division, ladder_position }
    rank: Object,
    // Width of a league badge; Master and Grandmaster come out a little larger, as in the game
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
        return '/images/ranks/' + league + '.png';
      }
      return '/images/ranks/' + league + '_' + this.rank.division + '.png';
    },
    name(){
      return rankName(this.rank);
    },
    badgeWidth(){
      return Math.round(this.width * (IMAGE_WIDTHS[this.rank.league] || 292) / 292);
    },
    // The ladder number sits between the "Grand Master" label and the crown's tip.
    // Longer numbers get a smaller font so "#999" still fits inside the disc (about half the badge's width).
    ladderStyle(){
      const width = this.badgeWidth;
      const height = width * 440 / 420;
      const characters = String(this.rank.ladder_position).length + 1;
      const fontSize = Math.min(height * 0.19, width * 0.44 / (0.62 * characters));
      return { top: (height * 0.36 + (height * 0.19 - fontSize) / 2) + 'px', fontSize: fontSize + 'px', textShadow: '0 0 6px rgba(110, 180, 255, 0.9)' };
    },
  },
}
</script>
