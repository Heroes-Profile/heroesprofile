<template>
  <!-- A talent build the way the site shows it: seven talent squares, then its in-game copy code with a copy icon.
       show="talents" or show="code" draws one part, for tables that split them into columns. -->
  <div :class="['flex flex-col gap-2', alignClass]">
    <div v-if="show !== 'code'" class="flex flex-nowrap gap-1.5">
      <span v-for="(level, index) in LEVELS" :key="level" class="flex flex-col items-center gap-1">
        <talent-image-wrapper :talent="build[level]" size="talent" :popup-hoverable="popupHoverable"></talent-image-wrapper>
        <span v-if="levels" class="text-[0.625rem] text-subtext">{{ LEVEL_NUMBERS[index] }}</span>
      </span>
    </div>
    <div v-if="show !== 'talents' && code" :class="['flex', alignClass]">
      <button type="button" class="group/copy inline-flex items-center gap-2 rounded-control bg-black/20 hover:bg-black/30 transition-colors px-3 py-1.5 text-sm text-white" :title="$t(copied ? $t('Copied') : $t('Copy build to paste into the in-game chat'))" :aria-label="$t(copied ? $t('Copied') : $t('Copy build'))" @click.stop.prevent="copy">
        <span>{{ code }}</span>
        <i :class="[copied ? 'fas fa-check text-lteal' : 'fas fa-copy group-hover/copy:text-lteal', 'text-xs transition-colors']"></i>
      </button>
    </div>
  </div>
</template>

<script>
const LEVELS = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];
const LEVEL_NUMBERS = [1, 4, 7, 10, 13, 16, 20];

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
    // The talent level under each talent
    levels: Boolean,
    // true: talent popups stay open while the pointer moves onto them
    popupHoverable: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      LEVELS,
      LEVEL_NUMBERS,
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
