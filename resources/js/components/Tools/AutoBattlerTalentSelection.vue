<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3 min-w-0">
        <img :src="'/images/heroes/' + hero.short_name + '.png'" :alt="hero.name" class="w-12 h-12 shrink-0 rounded-full border-2 border-teal bg-surface-3 text-[0rem]" />
        <div class="flex flex-col min-w-0">
          <h3 class="text-xl font-bold truncate">{{ $heroName(hero) }}</h3>
          <span class="text-xs text-subtext">{{ label }} · {{ $t(':count of 7 talents picked', { count: pickedCount }) }}</span>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <tab-button v-if="!isMobile" :small="true" :tab1text="'Vertical'" :tab2text="'Horizontal'" :ignoreclick="true"
          :overridedefaultside="layout === 'horizontal' ? 'right' : 'left'"
          @tab-click="$emit('layout-change', $event === 'right' ? 'horizontal' : 'vertical')"></tab-button>
        <custom-button class="font-semibold" :ignoreclick="true" :alt="'Change hero'" @click="$emit('change-hero')">{{ $t('Change hero') }}</custom-button>
        <custom-button color="teal" class="font-semibold" :ignoreclick="true" :disabled="!allSelected" :alt="'Lock in talents'" @click="lockIn">{{ $t('Lock in talents') }}</custom-button>
      </div>
    </div>

    <div :class="layout === 'horizontal' ? 'overflow-x-auto' : ''">
      <div :class="layout === 'horizontal' ? 'grid grid-cols-[repeat(7,minmax(7rem,1fr))] gap-2 items-start' : 'flex flex-col gap-2'">
        <talent-builder-column v-for="level in talentLevels" :key="level"
          :data="talents[level]" :level="level" :clickedData="clickedData" :horizontal="layout === 'horizontal'"></talent-builder-column>
      </div>
    </div>

    <p class="flex items-center gap-2 text-xs text-subtext"><basil-icon name="info"></basil-icon>{{ $t('Pick one talent per level. Lock in unlocks when all 7 are picked.') }}</p>
  </div>
</template>

<script>
const TALENT_LEVELS = [1, 4, 7, 10, 13, 16, 20];

export default {
  name: 'AutoBattlerTalentSelection',
  props: {
    hero: { type: Object, required: true },
    talents: { type: Object, required: true },
    initial: { type: Object, default: null },
    layout: { type: String, default: 'vertical' },
    isMobile: { type: Boolean, default: false },
    // e.g. "Team 1 · Player 1"
    label: { type: String, default: '' },
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
    pickedCount() {
      return TALENT_LEVELS.filter(level => this.selected[level]).length;
    },
    allSelected() {
      return this.pickedCount === TALENT_LEVELS.length;
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
