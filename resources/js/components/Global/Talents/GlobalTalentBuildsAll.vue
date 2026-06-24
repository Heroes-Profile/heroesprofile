<template>
  <div class="w-full">
    <table class="w-full text-sm">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Hero</th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Quick Match</th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Storm League</th>
          <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">ARAM</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="hero in heroes" :key="hero.id" class="border-t border-gray-700">
          <td class="py-2 px-3 whitespace-nowrap">
            <div class="flex items-center">
              <hero-image-wrapper class="mr-2" :hero="hero" :includehover="false"></hero-image-wrapper>
              <span class="hidden md:block">{{ hero.name }}</span>
            </div>
          </td>
          <td v-for="gt in ['qm', 'sl', 'ar']" :key="gt" class="py-2 px-3">
            <template v-if="firstBuild(hero.name, gt)">
              <div class="flex flex-wrap gap-4">
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_one"></talent-image-wrapper>
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_four"></talent-image-wrapper>
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_seven"></talent-image-wrapper>
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_ten"></talent-image-wrapper>
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_thirteen"></talent-image-wrapper>
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_sixteen"></talent-image-wrapper>
                <talent-image-wrapper :talent="firstBuild(hero.name, gt).level_twenty"></talent-image-wrapper>
              </div>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-400">{{ getCopyBuildToGame(firstBuild(hero.name, gt)) }}</span>
                <i
                  :class="['fa-solid fa-copy cursor-pointer', copyColor(hero.name, gt) === 'teal' ? 'text-teal-400' : 'text-gray-400', 'hover:text-white']"
                  :title="copyLabel(hero.name, gt)"
                  @click="copyToClipboard(hero.name, gt)"
                ></i>
              </div>
            </template>
            <span v-else class="text-gray-500">—</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
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
      copiedState: {},
    };
  },
  methods: {
    firstBuild(heroName, gameType) {
      const builds = this.talentbuilddataall?.[heroName]?.[gameType];
      return builds && builds.length > 0 ? builds[0] : null;
    },
    getCopyBuildToGame(build) {
      const b = build;
      return "[T" +
        (b.level_one     ? b.level_one.sort     : '0') +
        (b.level_four    ? b.level_four.sort    : '0') +
        (b.level_seven   ? b.level_seven.sort   : '0') +
        (b.level_ten     ? b.level_ten.sort     : '0') +
        (b.level_thirteen ? b.level_thirteen.sort : '0') +
        (b.level_sixteen  ? b.level_sixteen.sort  : '0') +
        (b.level_twenty   ? b.level_twenty.sort   : '0') +
        "," + b.hero.build_copy_name + "]";
    },
    copyKey(heroName, gt) {
      return `${heroName}|${gt}`;
    },
    copyLabel(heroName, gt) {
      return this.copiedState[this.copyKey(heroName, gt)] ? 'COPIED' : 'COPY TO CLIPBOARD';
    },
    copyColor(heroName, gt) {
      return this.copiedState[this.copyKey(heroName, gt)] ? 'teal' : 'blue';
    },
    copyToClipboard(heroName, gt) {
      const build = this.firstBuild(heroName, gt);
      if (!build) return;
      const key = this.copyKey(heroName, gt);
      navigator.clipboard.writeText(this.getCopyBuildToGame(build)).then(() => {
        this.$set(this.copiedState, key, true);
        setTimeout(() => this.$set(this.copiedState, key, false), 2000);
      });
    },
  },
}
</script>
