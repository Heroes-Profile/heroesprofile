<template>
  <div>
    <!-- The heading with the role and search filters beside it; the heroes in rows by role below -->
    <player-page-top>
      <template #heading>
        <page-heading heading="Heroes" subheading="Abilities, talents & stats"></page-heading>
      </template>
      <div class="flex md:justify-end px-4 md:px-8">
        <div class="rounded-panel bg-surface p-4 md:p-6 flex flex-wrap items-end gap-6">
          <div class="flex flex-col gap-2">
            <span class="text-sm text-subtext">{{ $t('Role') }}</span>
            <tab-button :small="true" :tabs="roleTabs" overridedefaultside="All" :ignoreclick="true" @tab-click="role = $event"></tab-button>
          </div>
          <div class="flex flex-col gap-2 w-full sm:w-[20rem]">
            <span class="text-sm text-subtext">{{ $t('Search') }}</span>
            <search-input v-model="search" :placeholder="$t('Search for a hero...')"></search-input>
          </div>
        </div>
      </div>
    </player-page-top>

    <hero-selection class="mt-6" :heroes="heroes" linkbase="/Heroes" :grouped="true" :role-filter="role" :search-filter="search"
      :subtitle="$t('Open a hero to see abilities, talents and stats · :count heroes', { count: heroes.length })"></hero-selection>

    <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false"></dynamic-banner-ad>
  </div>
</template>

<script>
import { ROLE_ORDER } from '../HeroSelection.vue';

export default {
  name: 'HeroesIndex',
  props: {
    heroes: {
      type: Array,
      required: true,
    },
    patreonUser: Boolean,
  },
  data() {
    return {
      role: 'All',
      search: '',
    };
  },
  computed: {
    // All, then the roles in the filters' order
    roleTabs() {
      return [{ key: 'All', text: 'All' }, ...ROLE_ORDER.map(role => ({ key: role, text: role }))];
    },
  },
  methods: {
    // Called by hero-selection when a hero is clicked
    clickedHero(hero) {
      window.location.href = `/Heroes/${encodeURIComponent(hero.name)}`;
    },
  },
}
</script>
