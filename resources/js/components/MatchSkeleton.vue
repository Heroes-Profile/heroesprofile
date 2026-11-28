<template>
  <!-- The match page in grey while it loads: the page's own markup with grey text and portraits, so every card is its
       loaded size. Heading, the two team cards, then Match Scores, Talents, HP MMR Change and Experience -->
  <div>
    <div class="max-w-[120rem] mx-auto px-4 md:px-8 pt-10 pb-6 flex flex-wrap items-end justify-between gap-6">
      <div class="flex flex-col gap-2 min-w-0">
        <h1 class="text-4xl md:text-6xl font-bold"><span class="skeleton-text">Cursed Hollow</span></h1>
        <p class="text-base md:text-2xl uppercase"><span class="skeleton-text">Storm League · 20:00 · 00/00/00 · Match 00000000</span></p>
      </div>
      <div class="flex flex-wrap gap-3">
        <span class="btn font-semibold skeleton-text">{{ $t('Watch Replay') }}</span>
        <span class="btn font-semibold skeleton-text max-md:hidden">{{ $t('Download Replay') }}</span>
      </div>
    </div>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 grid xl:grid-cols-2 gap-6">
      <div v-for="team in 2" :key="team" :class="['relative rounded-panel bg-surface border-t-4 border-gray-dark p-5 md:p-6 flex flex-col gap-5 min-w-0', { 'md:!rounded-tl-none': team === 1 }]">
        <loading-component v-if="team === 1" :compact="true" :tab="true" :overrideimage="overrideimage"></loading-component>
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-x-3 gap-y-1 min-w-0">
            <h2 class="text-2xl font-bold"><span class="skeleton-text">Team {{ team }}</span></h2>
            <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-bold uppercase skeleton-text">{{ $t('Victory') }}</span>
            <span v-for="stat in ['Team Level', 'Takedowns']" :key="stat" class="shrink-0 text-sm whitespace-nowrap"><span class="skeleton-text">{{ $t(stat) }} 00</span></span>
          </div>
        </div>
        <div class="grid grid-cols-5 gap-2">
          <div v-for="player in 5" :key="player" class="flex flex-col items-center gap-1.5 text-center min-w-0">
            <skeleton-box class="w-20 h-20 max-md:w-16 max-md:h-16 !rounded-full"></skeleton-box>
            <span class="max-w-full text-sm font-semibold"><span class="skeleton-text">Player</span></span>
            <span class="max-w-full text-xs"><span class="skeleton-text">Samuro</span></span>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <span class="text-xs font-semibold uppercase"><span class="skeleton-text">{{ $t('Team averages') }}</span></span>
          <div :class="['grid gap-2', esport ? 'grid-cols-1' : 'grid-cols-2 md:grid-cols-4']">
            <!-- An unseen stat box under each, so they're the real ones' size -->
            <div v-for="stat in esport ? 1 : 4" :key="stat" class="relative">
              <stat-box class="!m-0 !min-w-0 invisible" aria-hidden="true" title="HP MMR" value="0000"></stat-box>
              <skeleton-box class="absolute inset-0 rounded"></skeleton-box>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 mt-6 pb-10 flex flex-col gap-6">
      <panel-box title="Match Scores" subtitle="Best value in each column is highlighted." :skeleton="true" :divider="false">
        <div class="overflow-hidden">
          <table class="w-full min-w-[60rem] rounded-table">
            <thead>
              <tr>
                <th class="py-2 px-3 text-left text-sm leading-4">{{ $t('Player') }}</th>
                <th v-for="column in scoreColumns" :key="column.label" :class="['py-2 px-3 text-center text-sm leading-4', column.width]">
                  {{ $t(column.label) }}<span v-if="column.info" class="inline-flex align-middle ml-1"><round-image size="small" icon="fas fa-info" title="info"></round-image></span>
                </th>
              </tr>
            </thead>
            <tbody class="text-center">
              <template v-for="team in 2" :key="team">
                <tr>
                  <td :colspan="scoreColumns.length + 1" class="py-2 px-3 text-left text-xs font-semibold uppercase"><span class="skeleton-text">Team {{ team }} · {{ $t('Victory') }}</span></td>
                </tr>
                <tr v-for="player in 5" :key="player">
                  <td class="py-2 px-3 text-left">
                    <span class="inline-flex items-center gap-2.5 min-w-0">
                      <skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
                      <span class="flex flex-col items-start min-w-0">
                        <span class="font-semibold skeleton-text">Player</span>
                        <span class="text-xs skeleton-text">Samuro</span>
                      </span>
                    </span>
                  </td>
                  <td v-for="column in scoreColumns" :key="column.label"><span class="skeleton-text">00,000</span></td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </panel-box>

      <panel-box title="Talents" subtitle="Talents picked at levels 1, 4, 7, 10, 13, 16 and 20. Empty slots were not reached." :skeleton="true" :divider="false">
        <div class="grid xl:grid-cols-2 gap-6">
          <div v-for="team in 2" :key="team" class="flex flex-col gap-2 min-w-0">
            <span class="text-xs font-semibold uppercase"><span class="skeleton-text">Team {{ team }} · {{ $t('Victory') }}</span></span>
            <div v-for="player in 5" :key="player" :class="['flex flex-wrap md:flex-nowrap items-center gap-4 rounded px-4 py-3', player % 2 ? 'bg-surface-1' : 'bg-surface-2']">
              <span class="flex items-center gap-4 min-w-0 flex-1">
                <skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
                <span class="flex flex-col items-start gap-0.5 min-w-0">
                  <span class="font-bold skeleton-text">Player</span>
                  <span class="text-[0.8125rem] skeleton-text">Samuro</span>
                </span>
              </span>
              <div class="flex gap-1.5">
                <skeleton-box v-for="talent in 7" :key="talent" class="w-[2.625rem] h-[2.625rem] shrink-0 rounded"></skeleton-box>
              </div>
            </div>
          </div>
        </div>
      </panel-box>

      <panel-box v-if="!esport" title="HP MMR Change" subtitle="Heroes Profile MMR before → after this match." :skeleton="true" :divider="false">
        <div class="overflow-hidden">
          <table class="w-full min-w-[50rem] rounded-table">
            <thead>
              <tr>
                <th class="py-2 px-3 text-left text-sm leading-4">{{ $t('Player') }}</th>
                <template v-for="label in ['Player MMR', 'Hero MMR', 'Role MMR', 'Map MMR']" :key="label">
                  <th class="py-2 px-3 text-center text-sm leading-4">{{ $t(label) }}</th>
                  <th class="py-2 px-3 w-12"></th>
                </template>
              </tr>
            </thead>
            <tbody class="text-center">
              <template v-for="team in 2" :key="team">
                <tr>
                  <td colspan="9" class="py-2 px-3 text-left text-xs font-semibold uppercase"><span class="skeleton-text">Team {{ team }} · {{ $t('Victory') }}</span></td>
                </tr>
                <tr v-for="player in 5" :key="player">
                  <td class="py-2 px-3 text-left">
                    <span class="inline-flex items-center gap-2.5 min-w-0">
                      <skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
                      <span class="flex flex-col items-start min-w-0">
                        <span class="font-semibold skeleton-text">Player</span>
                        <span class="text-xs skeleton-text">Samuro</span>
                      </span>
                    </span>
                  </td>
                  <template v-for="kind in 4" :key="kind">
                    <td class="!border-r-0"><span class="skeleton-text">0000 → 0000 +00</span></td>
                    <td class="py-2 px-3"><span class="flex min-h-[2rem]"></span></td>
                  </template>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </panel-box>

      <panel-box title="Experience" subtitle="Team experience over the course of the match." :skeleton="true" :divider="false">
        <!-- The chart's own height: 400px, 260px under 1024px -->
        <skeleton-box class="h-[400px] max-lg:h-[260px] w-full rounded-control"></skeleton-box>
      </panel-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MatchSkeleton',
  props: {
    // An esports league's loader image
    overrideimage: String,
    // Esports matches: one team average and no HP MMR Change
    esport: Boolean,
  },
  data() {
    return {
      // Match Scores' columns and widths, as on the match page
      scoreColumns: [
        { label: 'HP Rating', width: 'w-[180px]', info: true },
        { label: 'Kills', width: 'w-[110px]' },
        { label: 'Takedowns', width: 'w-[130px]' },
        { label: 'Deaths', width: 'w-[110px]' },
        { label: 'Siege Dmg', width: 'w-[160px]' },
        { label: 'Hero Dmg', width: 'w-[160px]' },
        { label: 'Healing', width: 'w-[160px]' },
        { label: 'Dmg Taken', width: 'w-[160px]' },
        { label: 'XP Contrib.', width: 'w-[160px]' },
      ],
    };
  },
};
</script>
