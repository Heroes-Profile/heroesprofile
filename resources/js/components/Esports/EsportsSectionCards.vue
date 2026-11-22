<template>
  <!-- A league's sections as cards, styled like the leagues on /Esports: an icon, then the name, what it shows, and a link -->
  <!-- Every league's cards the same width (four across on desktop); a short last row is centred -->
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-wrap justify-center gap-6 md:gap-8">
    <button v-for="section in shown" :key="section.key" type="button" class="group/bt w-full sm:w-[calc((100%-2rem)/2)] lg:w-[calc((100%-4rem)/3)] xl:w-[calc((100%-6rem)/4)] bg-surface rounded p-6 flex flex-col gap-5 min-w-0 text-left hover:bg-surface-1 transition-colors" @click="$emit('select', section.key)">
      <div class="h-[12.5rem] rounded-control bg-surface-1 group-hover/bt:bg-surface-2 transition-colors flex items-center justify-center">
        <i :class="[section.icon, 'text-[5rem]']"></i>
      </div>
      <div class="flex items-end justify-between gap-4">
        <div class="min-w-0">
          <h2 class="text-xl font-bold">{{ $t(section.title) }}</h2>
          <p class="text-sm text-subtext mt-1">{{ $t(section.description) }}</p>
        </div>
        <span class="inline-flex items-center whitespace-nowrap shrink-0"><span class="group-hover/bt:underline">{{ $t('View stats') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
      </div>
    </button>
  </div>
</template>

<script>
const SECTIONS = {
  standings: { title: 'Standings', icon: 'fas fa-list-ol', description: 'Team standings for each season and division' },
  divisions: { title: 'Divisions', icon: 'fas fa-boxes', description: 'Every division with its teams and matches' },
  teams: { title: 'Teams', icon: 'fas fa-users', description: 'Every team, with its players and results' },
  organizations: { title: 'Organizations', icon: 'fas fa-users', description: 'Every organization and its teams' },
  playerSearch: { title: 'Player Search', icon: 'fas fa-address-card', description: 'Find a player and their league stats' },
  players: { title: 'Players', icon: 'fas fa-address-card', description: 'Every player and their stats' },
  recentMatches: { title: 'Recent Matches', icon: 'fas fa-list', description: 'The latest games, with drafts and results' },
  overallHeroStats: { title: 'Overall Hero Stats', icon: 'fa-solid fa-chart-bar', description: 'Win rate, picks and bans for every hero' },
  overallTalentStats: { title: 'Overall Talent Stats', icon: 'fas fa-chart-line', description: 'Talent pick and win rates for every hero' },
};

export default {
  name: 'EsportsSectionCards',
  props: {
    // Section keys in order, the same ones the page's setButtonActive takes
    sections: { type: Array, required: true },
  },
  emits: ['select'],
  computed: {
    shown() {
      return this.sections.map(key => ({ key, ...SECTIONS[key] }));
    },
  },
};
</script>
