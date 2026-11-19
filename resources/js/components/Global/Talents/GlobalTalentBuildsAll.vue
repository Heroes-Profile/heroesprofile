<template>
  <div ref="tablecontainer" class="w-full max-md:overflow-clip">
    <table ref="responsivetable" class="w-full text-sm responsive-table rounded-table">
      <colgroup>
        <col class="w-[15%]">
        <col v-for="gt in gameTypes" :key="gt.key">
      </colgroup>
      <thead>
        <tr>
          <th class="py-2 px-4 text-left text-sm leading-4">{{ $t('Hero') }}</th>
          <th v-for="gt in gameTypes" :key="gt.key" class="py-2 px-4 text-left text-sm leading-4">{{ $t(gt.label) }}</th>
        </tr>
        <!-- The talent level over each column of talent icons -->
        <tr>
          <th></th>
          <th v-for="gt in gameTypes" :key="gt.key" class="pb-2 px-4 font-normal text-xs">
            <div class="flex gap-1.5">
              <span v-for="level in levels" :key="level" class="w-[2.625rem] text-center">{{ level }}</span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="hero in heroes" :key="hero.id">
          <td class="py-3 px-4">
            <div class="flex items-center gap-3">
              <hero-image-wrapper :hero="hero" :includehover="false"></hero-image-wrapper><span class="hidden md:block font-medium">{{ $heroName(hero) }}</span>
            </div>
          </td>
          <td v-for="gt in gameTypes" :key="gt.key" class="py-3 px-4">
            <build-code v-if="firstBuild(hero.name, gt.key)" align="start" :build="firstBuild(hero.name, gt.key)" :hero="firstBuild(hero.name, gt.key).hero"></build-code>
            <span v-else class="text-subtext">{{ $t('No data for this patch') }}</span>
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
    // One column per game type; esports player pages have a single league column
    gameTypes: {
      type: Array,
      default: () => [{ key: 'qm', label: 'Quick Match' }, { key: 'sl', label: 'Storm League' }, { key: 'ar', label: 'ARAM' }],
    },
  },
  data() {
    return {
      levels: [1, 4, 7, 10, 13, 16, 20],
    };
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
