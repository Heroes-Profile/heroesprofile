<template>
  <!-- A talent build the way the site shows it: seven talent squares, then its in-game copy code with a copy icon.
       show="talents" or show="code" draws one part, for tables that split them into columns. -->
  <div :class="['flex flex-col gap-2', alignClass]">
    <div v-if="show !== 'code'" class="flex flex-nowrap gap-1.5">
      <talent-image-wrapper v-for="level in LEVELS" :key="level" :talent="build[level]" size="talent"></talent-image-wrapper>
    </div>
    <div v-if="show !== 'talents' && code" :class="['flex items-center gap-1 text-sm', alignClass]">
      <span>{{ code }}</span>
      <button type="button" class="p-1.5 hover:text-lteal" :title="copied ? 'Copied' : 'Copy build to paste into the in-game chat'" :aria-label="copied ? 'Copied' : 'Copy build'" @click.stop.prevent="copy">
        <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
      </button>
    </div>
  </div>
</template>

<script>
const LEVELS = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

export default {
  name: 'BuildCode',
  props: {
    // An object with level_one ... level_twenty talents
    build: {
      type: Object,
      required: true,
    },
    // The hero the build is for; its build_copy_name goes in the code
    hero: {
      type: Object,
      required: true,
    },
    // all, talents or code
    show: {
      type: String,
      default: 'all',
    },
    // center, start or end
    align: {
      type: String,
      default: 'center',
    },
  },
  data() {
    return {
      LEVELS,
      copied: false,
      timer: null,
    };
  },
  computed: {
    // The in-game chat code, e.g. [T1121113,Abathur]
    code() {
      if (!this.build.level_one || !this.hero) {
        return null;
      }
      return '[T' + LEVELS.map(level => this.build[level] ? this.build[level].sort : '0').join('') + ',' + this.hero.build_copy_name + ']';
    },
    alignClass() {
      return { center: 'items-center justify-center', start: 'items-start justify-start', end: 'items-end justify-end' }[this.align];
    },
  },
  beforeUnmount() {
    clearTimeout(this.timer);
  },
  methods: {
    async copy() {
      try {
        await navigator.clipboard.writeText(this.code);
      } catch (error) {
        // The browser refused the clipboard
        return;
      }
      this.copied = true;
      clearTimeout(this.timer);
      this.timer = setTimeout(() => { this.copied = false; }, 2000);
    },
  },
};
</script>
