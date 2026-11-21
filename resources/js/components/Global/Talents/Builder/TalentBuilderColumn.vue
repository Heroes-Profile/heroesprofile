<template>
  <!-- One talent level (Talent Builder, Auto Battler). Horizontal: a column with the level on top and the talents
       stacked under it. Vertical: a row with the level tile on the left and the talents beside it.
       The level turns teal once a talent is picked; Clear unpicks it -->
  <div v-if="horizontal" class="flex flex-col gap-2 min-w-0">
    <button type="button" :class="['rounded-control py-1.5 text-[0.625rem] font-bold uppercase tracking-wider text-center', picked ? 'bg-teal text-white hover:bg-lteal' : 'bg-surface-1 text-subtext']"
      :title="picked ? $t('Clear') : ''" @click="picked && removeAnySelections()">{{ $t('Level :level', { level: level }) }}</button>
    <talent-builder-click-box v-for="(talent, index) in data" :key="talent.title"
      :talent="talent" :isClicked="isSelected(talent)" :dimmed="picked && !isSelected(talent)" :horizontal="true"
      @click="talentClicked(talent, index, level)"></talent-builder-click-box>
  </div>

  <div v-else class="grid grid-cols-[3.5rem_minmax(0,1fr)] gap-2">
    <div :class="['rounded-control flex flex-col items-center justify-center py-1.5', picked ? 'bg-teal' : 'bg-surface-1']">
      <span :class="['text-[0.5625rem] font-bold uppercase tracking-wider', picked ? 'text-white/80' : 'text-subtext']">{{ $t('Level') }}</span>
      <span class="text-xl font-bold leading-tight">{{ level }}</span>
      <button type="button" :class="['text-[0.625rem] hover:underline', picked ? 'text-white' : 'text-lteal']" @click="removeAnySelections">{{ $t('Clear') }}</button>
    </div>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-2 content-start min-w-0">
      <talent-builder-click-box v-for="(talent, index) in data" :key="talent.title"
        :talent="talent" :isClicked="isSelected(talent)" :dimmed="picked && !isSelected(talent)"
        @click="talentClicked(talent, index, level)"></talent-builder-click-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TalentBuilderColumn',
  props: {
    level: Number,
    data: [Object, Array],
    clickedData: Object,
    horizontal: { type: Boolean, default: false },
    // Kept for callers that pass it; the layout is the same size everywhere now
    compact: { type: Boolean, default: false },
  },
  computed: {
    picked() {
      return this.clickedData?.[this.level] != null;
    },
  },
  methods: {
    talentClicked(talent, index, level) {
      this.$parent.talentClicked(talent, index, level);
    },
    isSelected(talent) {
      return this.clickedData[this.level] === talent.talent_id;
    },
    removeAnySelections() {
      this.$parent.removeLevelSelections(this.level);
    },
  },
};
</script>
