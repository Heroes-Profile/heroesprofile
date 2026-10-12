<template>
  <div v-if="visible" class="xalatath-scoreboard relative text-white text-center px-4 py-3">
    <button type="button" class="absolute top-2 right-3 text-xs opacity-60 hover:opacity-100" title="Dismiss" @click="dismiss">
      Dismiss <i class="fas fa-times ml-1"></i>
    </button>
    <div class="font-logo text-2xl md:text-3xl tracking-wide xalatath-title mb-1">Xal'atath has withdrawn</div>
    <div class="text-sm opacity-80 mb-3">
      The Void reached stage {{ ending.stage }} of 5 with {{ format(ending.corruption) }} games and bans before it receded.
    </div>

    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 max-w-[1500px] mx-auto max-md:hidden">
      <div v-for="tile in tiles" :key="tile.label" class="flex flex-col min-w-[110px]">
        <span class="text-xs uppercase opacity-75">{{ tile.label }}</span>
        <span class="text-lg font-bold tabular-nums">{{ format(tile.value) }}</span>
      </div>
    </div>

    <a href="/Event/Xalatath" class="inline-block mt-3 underline hover:opacity-80">See the full recap <i class="fas fa-chevron-right ml-1"></i></a>
  </div>
</template>

<script>
import { mainTiles, formatNumber } from './xalatathTiles';

const STORAGE_KEY = 'xalatathFinalBannerDismissed';

export default {
  name: 'XalatathFinalBanner',
  props: {
    ending: {
      type: Object,
      required: true,
    },
  },
  data() {
    let dismissed = false;
    try {
      dismissed = !!localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      dismissed = false;
    }
    return {
      visible: !dismissed,
    };
  },
  computed: {
    tiles() {
      return mainTiles(this.ending.totals);
    },
  },
  methods: {
    format: formatNumber,
    dismiss() {
      try {
        localStorage.setItem(STORAGE_KEY, '1');
      } catch (e) {
        // storage blocked; just hides for this page view
      }
      this.visible = false;
    },
  },
};
</script>
