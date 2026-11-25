<template>
  <!-- A hero's talent builds: the talents, how often and how well they're played, and the code to copy -->
  <div class="min-w-0">
  <!-- Phones: a card per build, its talents, numbers and copy button stacked, so nothing has to shrink to fit -->
  <div class="md:hidden flex flex-col gap-2">
    <div v-for="(row, index) in talentbuilddata" :key="index" class="rounded-control bg-surface-1 p-3 flex flex-col gap-3 min-w-0">
      <div ref="mobiletalents" class="min-w-0 overflow-clip"><build-code :build="row" :hero="row.hero" show="talents" align="start" :popup-hoverable="false"></build-code></div>
      <div class="grid grid-cols-3 gap-2 text-sm">
        <span class="flex flex-col"><span class="text-xs text-subtext">{{ $t('Games') }}</span><span class="font-semibold tabular-nums">{{ getValueLocal(row.games_played) }}</span></span>
        <span class="flex flex-col"><span class="text-xs text-subtext">{{ $t('Win chance') }}</span><span class="font-semibold tabular-nums">{{ (Number(row.win_rate) || 0).toFixed(2) }}%</span></span>
        <span v-if="heroWinRate != null" class="flex flex-col"><span class="text-xs text-subtext">{{ $t('vs hero avg') }}</span><span :class="['font-semibold tabular-nums', vsHero(row) >= 0 ? 'text-win' : 'text-loss']">{{ vsHeroText(row) }}</span></span>
        <span v-else class="flex flex-col"><span class="text-xs text-subtext">{{ $t('Confidence') }}</span><span class="tabular-nums text-subtext">{{ confidenceText(row) }}</span></span>
      </div>
      <span v-if="statfilter && statfilter != 'win_rate'" class="text-xs text-subtext">{{ statFilterLabel(statfilter) }}: {{ getValueLocal(row.total_filter_type) }}</span>
      <build-code :build="row" :hero="row.hero" show="code" align="start"></build-code>
    </div>
  </div>
  <div id="table-container2" ref="tablecontainer2" class="min-w-0 max-md:hidden">
    <table class="min-w-0 w-full rounded-table" ref="responsivetable2">
      <thead>
        <tr>
          <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Build') }}</th>
          <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('Games') }}</th>
          <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">
            <span class="inline-flex items-center gap-1.5">
              {{ $t('Win chance') }}
              <round-image :popup-hoverable="true" class="hidden md:block" size="small" icon="fas fa-info" title="info" popupsize="large">
                <p class="font-normal whitespace-normal">{{ $t('Win Chance is our calculated probability that a full talent build leads to a win, from level 1 through level 20. It is not a simple win rate. Because many games end before level 20, we infer what build a player was heading toward from the talents they did pick, and factor in both games that reached level 20 and games that did not.') }}</p>
                <a href="/FAQ#win-chance" class="group/bt inline-flex items-center mt-2 font-normal"><span class="group-hover/bt:underline">Read more in the FAQ</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
              </round-image>
            </span>
          </th>
          <th v-if="heroWinRate != null" class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap" :title="$t('Win chance against the hero\'s win rate in these filters')">{{ $t('vs hero avg') }}</th>
          <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap" :title="$t('How far the win chance could be off, from how many games it is based on (95% confidence)')">{{ $t('Confidence') }}</th>
          <th v-if="statfilter && statfilter != 'win_rate'" class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ statFilterLabel(statfilter) }}</th>
          <th class="py-2 px-4 text-center text-sm leading-4 whitespace-nowrap">{{ $t('Build code') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in talentbuilddata" :key="index">
          <td class="py-2.5 px-4"><build-code :build="row" :hero="row.hero" show="talents" align="start" :popup-hoverable="false"></build-code></td>
          <td class="py-2.5 px-4 text-center tabular-nums whitespace-nowrap">{{ getValueLocal(row.games_played) }}</td>
          <td class="py-2.5 px-4"><stat-bar class="min-w-[9rem]" :value="Number(row.win_rate) || 0" :display-text="(Number(row.win_rate) || 0).toFixed(2)" suffix="%" color="teal"></stat-bar></td>
          <td v-if="heroWinRate != null" :class="['py-2.5 px-4 text-center font-semibold tabular-nums whitespace-nowrap', vsHero(row) >= 0 ? 'text-win' : 'text-loss']">{{ vsHeroText(row) }}</td>
          <td class="py-2.5 px-4 text-center text-subtext tabular-nums whitespace-nowrap">{{ confidenceText(row) }}</td>
          <td v-if="statfilter && statfilter != 'win_rate'" class="py-2.5 px-4 text-center tabular-nums whitespace-nowrap">{{ getValueLocal(row.total_filter_type) }}</td>
          <td class="py-2.5 px-4 text-center"><build-code :build="row" :hero="row.hero" show="code" align="center"></build-code></td>
        </tr>
      </tbody>
    </table>
  </div>
  </div>
</template>

<script>
import { statFilterLabel } from '../../../utils/statFilterLabel';
import { scaleTableToFit } from '../../../utils/scaleTable';

export default {
  name: 'GlobalTalentBuildsSection',
  components: {
  },
  props: {
    talentbuilddata: {
      Type: Object|Array
    },
    buildtype: String,
    statfilter: String,
    talentimages: Array,
    // The hero's win rate in the same filters; with it, each build shows how far above or below it is
    heroWinRate: { type: Number, default: null },
  },
  data() {
    return {
      windowWidth: window.innerWidth,
    };
  },

  mounted() {
    this.fitMobileTalents();
  },
  updated() {
    this.fitMobileTalents();
  },
  computed: {
  },
  watch: {
  },
  methods: {
    statFilterLabel,
    // On the narrowest phones a build's seven talents are wider than its card; shrink that row to fit
    fitMobileTalents() {
      this.$nextTick(() => {
        if (!window.matchMedia('(max-width: 767px)').matches) return;
        (this.$refs.mobiletalents || []).forEach(box => scaleTableToFit(box.firstElementChild, box));
      });
    },
    vsHero(row) {
      return (Number(row.win_rate) || 0) - this.heroWinRate;
    },
    vsHeroText(row) {
      const diff = this.vsHero(row);
      return (diff >= 0 ? '+' : '−') + Math.abs(diff).toFixed(2) + '%';
    },
    // The 95% margin of error for a win rate from this many games
    confidenceText(row) {
      const games = Number(row.games_played) || 0;
      if (!games) return '—';
      const rate = Math.min(1, Math.max(0, (Number(row.win_rate) || 0) / 100));
      return '±' + (1.96 * Math.sqrt((rate * (1 - rate)) / games) * 100).toFixed(2) + '%';
    },
    getValueLocal(value){
      return value ? value.toLocaleString('en-US') : "";
    },
  }
}
</script>