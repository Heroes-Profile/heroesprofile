<template>
  <!-- One card of logo tiles: a league's teams or organizations, or Other's tournaments -->
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8">
    <panel-box :title="title || (esport === 'Other' ? $t('Tournaments') : $t('Teams'))" :subtitle="subtitle" :divider="false">
      <template v-if="esport !== 'Other'" #right>
        <search-input v-model="filterText" :placeholder="searchPlaceholder || $t('Filter by team name')" class="w-full sm:w-[18.125rem] sm:ml-auto"></search-input>
      </template>
      <p v-if="!shown.length" class="text-subtext">{{ $t('No teams match ":teamsearch".', { teamsearch: filterText }) }}</p>
      <div v-else :class="['grid gap-4', esport === 'Other' ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-7' : 'sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4']">
        <a v-for="item in shown" :key="item.href" :href="item.href" :class="['flex flex-col items-center gap-3 bg-surface-1 border-2 px-4 py-5 min-w-0 text-center link-tile', item.name === highlight ? 'border-teal' : 'border-transparent']">
          <img :src="item.image" :alt="item.name" class="w-24 h-24 object-contain text-[0rem]" @error="e => { if (!e.target.dataset.fallback) { e.target.dataset.fallback = 1; e.target.src = fallbackImage; } }"/>
          <span class="max-w-full truncate font-semibold link-tile-title">{{ item.name }}</span>
          <template v-if="esport === 'Other'">
            <span v-if="item.games != null || item.firstGame" class="flex flex-col text-xs text-subtext -mt-2">
              <span v-if="item.games != null">{{ $t(':count games', { count: item.games.toLocaleString('en-US') }) }}</span>
              <span v-if="item.firstGame">{{ formatMonth(item.firstGame) }} – {{ formatMonth(item.lastGame) }}</span>
            </span>
          </template>
          <span v-else class="-mt-1 inline-flex items-center text-xs font-semibold text-teal">{{ linkText || $t('View stats') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></span>
        </a>
      </div>
    </panel-box>
  </div>
</template>

<script>
import { siteLocale } from '../../utils/translate';

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
    // The card's title and subtitle, the search's placeholder and each tile's link text
    title: String,
    subtitle: String,
    searchPlaceholder: String,
    linkText: String,
    // A tile outlined in teal (HGC on the Other hub)
    highlight: String,
  },
  data() {
    return {
      filterText: '',
    };
  },
  methods: {
    // "Mar 2017" from a game_date
    formatMonth(date) {
      const [year, month] = String(date).split('-');
      return new Date(Date.UTC(year, month - 1, 1)).toLocaleDateString(siteLocale(), { month: 'short', year: 'numeric', timeZone: 'UTC' });
    },
  },
  computed: {
    fallbackImage() {
      return LEAGUE_LOGOS[this.esport] ?? '/images/logo/heroesprofilelogo.png';
    },
    // Each row's name, link and logo, by league
    items() {
      return Object.values(this.data || {}).map(row => {
        if (this.esport === 'Other') {
          return { name: row.name, href: `/Esports/Other/${row.name}`, image: row.icon ? `/images/EsportOther/${row.icon}` : this.fallbackImage, games: row.games ?? null, firstGame: row.first_game, lastGame: row.last_game };
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
