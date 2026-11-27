<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText1" -->
    <page-heading :heading="'Heroes International'"></page-heading>
    <!-- The two tournaments as cards, like the leagues on /Esports and the league section cards -->
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-wrap justify-center gap-6 md:gap-8">
      <a v-for="tournament in tournaments" :key="tournament.href" :href="tournament.href" class="group/bt w-full sm:w-[calc((100%-2rem)/2)] md:w-[calc((100%-4rem)/3)] xl:w-[calc((100%-6rem)/4)] bg-surface rounded-panel p-6 flex flex-col gap-5 min-w-0 link-tile">
        <div class="h-[12.5rem] rounded-control bg-surface-1 group-hover/bt:bg-surface-2 transition-colors flex items-center justify-center">
          <img :src="tournament.image" :alt="$t(tournament.name)" class="max-w-[10rem] max-h-[10rem] object-contain"/>
        </div>
        <div class="flex items-end justify-between gap-4">
          <div class="min-w-0">
            <h2 class="text-xl font-bold">{{ $t(tournament.name) }}</h2>
            <p class="text-sm text-subtext mt-1">{{ $t(tournament.description) }}</p>
          </div>
          <span class="inline-flex items-center whitespace-nowrap shrink-0"><span class="link-tile-title">{{ $t('View stats') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
        </div>
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeroesInternationalEntry',
  components: {
  },
  props: {
  },
  data(){
    return {
      infoText1: "Click a League below to see Main Heroes International stats, or Nation Cup stats",
      tournaments: [
        { name: 'Heroes International', href: '/Esports/HeroesInternational?tournament=main', image: '/images/HI/heroes_international.png', description: 'The main tournament: teams, matches and hero stats' },
        { name: 'Nations Cup', href: '/Esports/HeroesInternational?tournament=nationscup', image: '/images/HI/Flags/AllFlags.png', description: 'Country teams: teams, matches and hero stats' },
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