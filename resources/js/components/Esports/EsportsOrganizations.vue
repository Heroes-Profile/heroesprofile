<template>
  <!-- A card per team (or Other's series), styled like the NGS teams and the leagues on /Esports: the logo, then the name and a link -->
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
    <template v-if="esport !== 'Other'">
      <search-input v-model="filterText" :placeholder="$t('Filter by team name')" class="w-full sm:w-[18.125rem]"></search-input>
      <p v-if="!shown.length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: filterText }) }}</p>
    </template>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 md:gap-8">
      <a v-for="item in shown" :key="item.href" :href="item.href" class="group/bt bg-surface rounded p-6 flex flex-col gap-5 min-w-0 hover:bg-surface-1 transition-colors">
        <div class="h-[12.5rem] rounded-control bg-surface-1 group-hover/bt:bg-surface-2 transition-colors flex items-center justify-center">
          <img :src="item.image" :alt="item.name" class="max-w-[10rem] max-h-[10rem] object-contain text-[0rem]" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = fallbackImage; } }"/>
        </div>
        <div class="flex items-end justify-between gap-4">
          <h2 class="text-xl font-bold truncate min-w-0">{{ item.name }}</h2>
          <span class="inline-flex items-center whitespace-nowrap shrink-0"><span class="group-hover/bt:underline">{{ $t('View stats') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
        </div>
      </a>
    </div>
  </div>
</template>

<script>
// Each league's own logo, for a team without one
const LEAGUE_LOGOS = {
  CCL: '/images/CCL/600-600-HHE_CCL_Logo_rectangle.png',
  MastersClash: '/images/MCL/no-image.png',
  hi: '/images/HI/heroes_international.png',
  hi_nc: '/images/HI/heroes_international.png',
  Other: '/images/EsportOther/logo.png',
};

export default {
  name: 'EsportsOrganizations',
  props: {
    data: [Object, Array],
    season: Number,
    esport: String,
  },
  data() {
    return {
      filterText: '',
    };
  },
  computed: {
    fallbackImage() {
      return LEAGUE_LOGOS[this.esport] ?? '/images/logo/heroesprofilelogo.png';
    },
    // Each row's name, link and logo, by league
    items() {
      return Object.values(this.data || {}).map(row => {
        if (this.esport === 'Other') {
          return { name: row.name, href: `/Esports/Other/${row.name}`, image: row.icon ? `/images/EsportOther/${row.icon}` : this.fallbackImage };
        }
        const name = row.team_name;
        const byLeague = {
          CCL: { href: `/Esports/CCL/Organization/${name}?season=${this.season}`, image: `/images/CCL/Organizations/Logos/${row.image}` },
          MastersClash: { href: `/Esports/MastersClash/Team/${name}?season=${this.season}`, image: `/images/MCL/${row.image}` },
          hi: { href: `/Esports/HeroesInternational/Team/${name}?tournament=main&season=${this.season}`, image: `/images/HI/Team/Logos/${row.image}.png` },
          hi_nc: { href: `/Esports/HeroesInternational/Team/${name}?tournament=nationscup&season=${this.season}`, image: `/images/HI/Flags/${row.image}.png` },
        }[this.esport];
        return { name, href: byLeague.href, image: row.image ? byLeague.image : this.fallbackImage };
      });
    },
    shown() {
      const search = this.filterText.trim().toLowerCase();
      return search ? this.items.filter(item => item.name.toLowerCase().includes(search)) : this.items;
    },
  },
};
</script>
