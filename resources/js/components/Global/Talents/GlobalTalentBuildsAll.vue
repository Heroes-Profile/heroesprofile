<template>
  <div ref="tablecontainer" class="w-full max-md:overflow-hidden">
    <table ref="responsivetable" class="w-full text-sm">
      <thead>
        <tr>
          <th class="py-2 px-3 text-center text-sm leading-4">
            <div class="flex items-center justify-center gap-3">
              <span>Hero</span>
              <search-input v-model="heroSearch" placeholder="Search hero" :collapsible="true" class="w-40"></search-input>
            </div>
          </th>
          <th class="py-2 px-3 text-center text-sm leading-4">Quick Match</th>
          <th class="py-2 px-3 text-center text-sm leading-4">Storm League</th>
          <th class="py-2 px-3 text-center text-sm leading-4">ARAM</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <tr v-for="hero in shownHeroes" :key="hero.id">
          <td class="py-2 px-3">
            <div class="flex justify-center">
              <hero-image-wrapper :hero="hero" :includehover="false"></hero-image-wrapper>
            </div>
          </td>
          <td v-for="gt in ['qm', 'sl', 'ar']" :key="gt" class="py-2 px-3">
            <build-code v-if="firstBuild(hero.name, gt)" :build="firstBuild(hero.name, gt)" :hero="firstBuild(hero.name, gt).hero"></build-code>
            <span v-else class="text-subtext">—</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';

export default {
  name: 'GlobalTalentBuildsAll',
  props: {
    heroes: {
      type: Array,
      required: true,
    },
    talentbuilddataall: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      heroSearch: '',
    };
  },
  computed: {
    shownHeroes() {
      const search = this.heroSearch.trim().toLowerCase();
      return search ? this.heroes.filter(hero => hero.name.toLowerCase().includes(search)) : this.heroes;
    },
  },
  mounted() {
    this.$nextTick(() => {
      if (window.matchMedia('(max-width: 767px)').matches) {
        scaleTableToFit(this.$refs.responsivetable, this.$refs.tablecontainer);
      }
    });
  },
  methods: {
    firstBuild(heroName, gameType) {
      const builds = this.talentbuilddataall?.[heroName]?.[gameType];
      return builds && builds.length > 0 ? builds[0] : null;
    },
  },
}
</script>
