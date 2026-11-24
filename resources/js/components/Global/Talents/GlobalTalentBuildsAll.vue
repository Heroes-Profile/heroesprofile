<template>
  <div class="w-full">
  <!-- Phones: one game mode at a time, picked here; wider screens show them side by side -->
  <tab-button v-if="gameTypes.length > 1" class="md:hidden mb-3" :tabs="gameTypes.map(gt => ({ key: gt.key, text: gt.label }))" :stretch="true" :overridedefaultside="mobileGameType" :ignoreclick="true" @tab-click="mobileGameType = $event"></tab-button>
  <div ref="tablecontainer" class="w-full max-md:overflow-clip">
    <table ref="responsivetable" class="w-full 2xl:min-w-[93.75rem] text-sm rounded-table">
      <colgroup>
        <col class="w-[15%]">
        <col v-for="gt in gameTypes" :key="gt.key">
      </colgroup>
      <thead>
        <tr>
          <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Hero') }}</th>
          <th v-for="gt in gameTypes" :key="gt.key" :class="['py-2 px-4 text-left text-sm leading-4', mobileHidden(gt)]">{{ $t(gt.label) }}</th>
        </tr>
        <!-- The talent level over each column of talent icons -->
        <tr>
          <!-- Narrows the list by hero, English or translated name, like All Maps' search; opens rightward, being the first column -->
          <th class="pb-2 px-4 text-left font-normal"><div class="relative"><search-input v-model="heroSearch" :placeholder="$t('Search hero')" :collapsible="true" align="left" class="w-40"></search-input></div></th>
          <th v-for="gt in gameTypes" :key="gt.key" :class="['pb-2 px-4 font-normal text-xs', mobileHidden(gt)]">
            <div class="flex gap-1.5">
              <span v-for="level in levels" :key="level" class="w-[2.625rem] text-center">{{ level }}</span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="hero in shownHeroes" :key="hero.id">
          <td class="py-3 px-4">
            <div class="flex items-center gap-3">
              <hero-image-wrapper :hero="hero" :includehover="false"></hero-image-wrapper><span class="hidden md:block font-medium">{{ $heroName(hero) }}</span>
            </div>
          </td>
          <td v-for="gt in gameTypes" :key="gt.key" :class="['py-3 px-4', mobileHidden(gt)]">
            <build-code v-if="firstBuild(hero.name, gt.key)" align="start" :build="firstBuild(hero.name, gt.key)" :hero="firstBuild(hero.name, gt.key).hero" :popup-hoverable="false"></build-code>
            <!-- As tall as a build: an invisible build's talent row and copy button hold the height, the message sits over them -->
            <div v-else class="relative">
              <div class="invisible flex flex-col gap-2" aria-hidden="true">
                <div class="flex gap-1.5"><span v-for="level in levels" :key="level" class="block w-[2.625rem] h-[2.625rem]"></span></div>
                <div class="flex"><span class="inline-flex items-center gap-2 px-3 py-1.5 text-sm">[T0000000,Hero] <i class="fas fa-copy text-xs"></i></span></div>
              </div>
              <span class="absolute inset-0 flex items-center text-subtext">{{ $t('No data for this patch') }}</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
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
    // One column per game type; esports player pages have a single league column
    gameTypes: {
      type: Array,
      default: () => [{ key: 'qm', label: 'Quick Match' }, { key: 'sl', label: 'Storm League' }, { key: 'ar', label: 'ARAM' }],
    },
  },
  data() {
    return {
      levels: [1, 4, 7, 10, 13, 16, 20],
      heroSearch: '',
      // The game mode shown on phones
      mobileGameType: this.gameTypes[0]?.key,
    };
  },
  computed: {
    shownHeroes() {
      // Letters in any script, so translated names (Cyrillic, Hangul, CJK) match too
      const strip = text => (text || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^\p{L}\p{N}\s]/gu, '');
      const query = strip(this.heroSearch);
      return query ? this.heroes.filter(hero => strip(hero.name).includes(query) || strip(this.$heroName(hero)).includes(query)) : this.heroes;
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
    // Hidden on phones unless it's the picked game mode
    mobileHidden(gt) {
      return this.gameTypes.length > 1 && gt.key !== this.mobileGameType ? 'max-md:hidden' : '';
    },
    firstBuild(heroName, gameType) {
      const builds = this.talentbuilddataall?.[heroName]?.[gameType];
      return builds && builds.length > 0 ? builds[0] : null;
    },
  },
}
</script>
