<template>
  <div>
    <div class="flex flex-wrap items-center justify-center gap-4 mb-4">
      <tab-button
        v-if="!isMobile"
        :tab1text="'Vertical'"
        :tab2text="'Horizontal'"
        :ignoreclick="true"
        @tab-click="$emit('layout-change', $event === 'right' ? 'horizontal' : 'vertical')"
        :overridedefaultside="layout === 'horizontal' ? 'right' : 'left'"
      ></tab-button>
      <img :src="'/images/heroes/' + hero.short_name + '.png'" :alt="hero.name" class="w-16 h-16 rounded-full" />
      <h3 class="text-xl font-bold">{{ hero.name }}</h3>
      <button @click="$emit('change-hero')" class="bg-blue hover:bg-lblue text-white px-4 py-2 rounded">
        Change Hero
      </button>
      <button
        @click="lockIn"
        :disabled="!allSelected"
        class="bg-teal hover:bg-lteal text-white font-bold px-6 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Lock in Talents
      </button>
    </div>

    <!-- w-max + mx-auto centers the columns when they fit and scrolls from the left edge when they don't -->
    <div :class="layout === 'horizontal' ? 'overflow-x-auto' : ''">
      <div :class="layout === 'horizontal' ? 'flex gap-1 w-max mx-auto' : 'flex flex-col gap-2'">
        <talent-builder-column
          v-for="level in talentLevels"
          :key="level"
          :data="talents[level]"
          :level="level"
          :clickedData="clickedData"
          :horizontal="layout === 'horizontal'"
          :compact="true"
        ></talent-builder-column>
      </div>
    </div>
  </div>
</template>

<script>
const TALENT_LEVELS = [1, 4, 7, 10, 13, 16, 20];

export default {
  name: 'AutoBattlerTalentSelection',
  props: {
    hero: {
      type: Object,
      required: true,
    },
    talents: {
      type: Object,
      required: true,
    },
    initial: {
      type: Object,
      default: null,
    },
    layout: {
      type: String,
      default: 'vertical',
    },
    isMobile: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['locked', 'change-hero', 'layout-change'],
  data() {
    const selected = {};
    TALENT_LEVELS.forEach(level => {
      selected[level] = this.initial ? this.initial[level] : null;
    });
    return {
      talentLevels: TALENT_LEVELS,
      selected,
    };
  },
  computed: {
    // Shape <talent-builder-column> expects: level => talent_id
    clickedData() {
      const clicked = {};
      TALENT_LEVELS.forEach(level => {
        clicked[level] = this.selected[level] ? this.selected[level].talent_id : null;
      });
      return clicked;
    },
    allSelected() {
      return TALENT_LEVELS.every(level => this.selected[level]);
    },
  },
  methods: {
    // Called by <talent-builder-column> via $parent
    talentClicked(talent, index, level) {
      this.selected[level] = talent;
    },
    removeLevelSelections(level) {
      this.selected[level] = null;
    },
    lockIn() {
      if (!this.allSelected) return;
      this.$emit('locked', { ...this.selected });
    },
  },
};
</script>
