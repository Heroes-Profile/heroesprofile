<template>
  <!-- One talent level (Talent Builder, Auto Battler). Horizontal: a column with the level on top and the talents
       stacked under it. Vertical: a row with the level tile on the left and the talents beside it.
       The level turns teal once a talent is picked; Clear unpicks it -->
  <div v-if="horizontal" class="flex flex-col gap-2 min-w-0">
    <button type="button" :class="['rounded-control py-1.5 text-[0.625rem] font-bold uppercase tracking-wider text-center', picked ? 'bg-teal text-white hover:bg-lteal' : 'bg-surface-1 text-subtext']"
      :title="picked ? $t('Clear') : ''" @click="picked && removeAnySelections()">{{ $t('Level :level', { level: level }) }}</button>
    <talent-builder-click-box v-for="(talent, index) in data" :key="talent.title"
      :talent="talent" :isClicked="isSelected(talent)" :dimmed="picked && !isSelected(talent)" :no-games="hasStats && !talent.games_played" :horizontal="true"
      @click="talentClicked(talent, index, level)"></talent-builder-click-box>
  </div>

  <div v-else class="grid grid-cols-[4.25rem_minmax(0,1fr)] md:grid-cols-[5rem_minmax(0,1fr)] gap-2.5">
    <div :class="['rounded-control flex flex-col items-center justify-center py-2', picked ? 'bg-teal/20' : 'bg-surface-1']">
      <span :class="['text-[0.625rem] font-bold uppercase tracking-wider', picked ? 'text-lteal' : 'text-subtext']">{{ $t('Level') }}</span>
      <span class="text-2xl font-bold leading-tight">{{ level }}</span>
      <button v-if="picked" type="button" class="text-xs text-white hover:underline" @click="removeAnySelections">{{ $t('Clear') }}</button>
    </div>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(14rem,1fr))] xl:grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-2.5 content-start min-w-0">
      <talent-builder-click-box v-for="(talent, index) in data" :key="talent.title"
        :talent="talent" :isClicked="isSelected(talent)" :dimmed="picked && !isSelected(talent)" :no-games="hasStats && !talent.games_played"
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
  emits: ['talent-clicked', 'clear'],
  computed: {
    // Some talent on this level has games, so one without any has none in this build
    hasStats() {
      return Object.values(this.data || {}).some(talent => talent.games_played);
    },
    picked() {
      return this.clickedData?.[this.level] != null;
    },
  },
  methods: {
    talentClicked(talent, index, level) {
      this.$emit('talent-clicked', talent, index, level);
    },
    isSelected(talent) {
      return this.clickedData[this.level] === talent.talent_id;
    },
    removeAnySelections() {
      this.$emit('clear', this.level);
    },
  },
};
</script>
