<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Heroes of the Storm Esports Stats'"></page-heading>
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 -mt-2 mb-4">
      <span class="uppercase text-gray-medium">{{ $t('Leagues & tournaments on Heroes Profile') }}</span>
    </div>

    <!-- One card per league: its logo, then its name, what's on its page, and a link -->
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 grid sm:grid-cols-2 xl:grid-cols-3 gap-6 md:gap-8">
      <a v-for="league in leagues" :key="league.href" :href="league.href" class="group/bt bg-surface rounded-panel p-6 flex flex-col gap-5 min-w-0 hover:bg-surface-1 transition-colors">
        <div class="h-[16.25rem] rounded-control bg-surface-1 group-hover/bt:bg-surface-2 transition-colors overflow-hidden flex items-center justify-center">
          <img :src="league.image" :alt="league.name" :class="league.cover ? 'w-full h-full object-cover' : 'max-w-[12.5rem] max-h-[12.5rem] object-contain'" />
        </div>
        <div class="flex items-end justify-between gap-4">
          <div class="min-w-0">
            <h2 class="text-xl font-bold">{{ $t(league.name) }}</h2>
            <p class="text-sm text-subtext mt-1">{{ $t(league.description) }}</p>
          </div>
          <span class="inline-flex items-center whitespace-nowrap shrink-0"><span class="group-hover/bt:underline">{{ $t('View stats') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
        </div>
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsMain',
  components: {
  },
  props: {
  },
  data(){
    return {
      infoText1: "Click a League below to see Overall Stats, Divisions, Teams, Matches, and Unofficial Rankings",
      // Each description is what that league's page has
      leagues: [
        { name: 'NGS', href: '/Esports/NGS', image: '/images/NGS/600-600-ngs_large.png', description: 'Nexus Gaming Series: standings, divisions, teams and matches' },
        { name: 'CCL', href: '/Esports/CCL', image: '/images/CCL/600-600-HHE_CCL_Logo_rectangle.png', description: 'Community Clash League: organizations, matches and hero stats' },
        { name: 'Nut Cup', href: '/Esports/NutCup', image: '/images/NutCup/IMG_0265.PNG', cover: true, description: 'Hero and talent stats' },
        { name: 'Masters Clash', href: '/Esports/MastersClash', image: '/images/MCL/600-600-MCC_logo_2021.png', description: 'Teams, matches and hero stats' },
        { name: 'Heroes International', href: '/Esports/HeroesInternational', image: '/images/HI/heroes_international.png', description: 'Teams, matches and hero stats' },
        { name: 'Other Esports', href: '/Esports/Other', image: '/images/EsportOther/logo.png', description: 'HGC and other tournaments: teams, players and matches' },
      ],
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>