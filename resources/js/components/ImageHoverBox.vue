<template>
  <!-- A hover card: portrait, title and who played it; then the flair bars; then label / value rows -->
  <div class="imageHoverBox flex flex-col gap-3">
    <div v-if="title || playedBy" class="flex items-center gap-3 min-w-0">
      <img v-if="card && card.image" :src="card.image" class="w-12 h-12 rounded-full shrink-0 object-cover" alt="" />
      <div class="flex flex-col min-w-0">
        <h2 v-if="title" class="text-lg font-bold text-white leading-tight">{{ title }}</h2>
        <!-- Plain text only: battletags and team names come from uploaded replays. -->
        <span v-if="playedBy" class="text-sm text-subtext truncate">{{ $t('Played by') }} {{ playedBy }}</span>
      </div>
    </div>
    <div v-if="card && card.badges.length" class="flex flex-col gap-1.5">
      <template v-for="badge in card.badges" :key="badge.key">
        <!-- What the award is for shows on hovering the whole bar -->
        <icon-with-hover v-if="badge.description" class="w-full" popupsize="large">
          <template #trigger>
            <div :class="['w-full flex items-center gap-2.5 rounded-[0.375rem] px-3 py-2 font-medium text-white cursor-default', badge.class]">
              <span class="w-6 h-6 shrink-0 flex items-center justify-center text-lg leading-none">
                <img v-if="badge.icon" :src="badge.icon" class="w-5 h-5 object-contain" alt="" />
              </span>
              {{ badge.label }}
            </div>
          </template>
          <p class="text-sm p-2 font-normal">{{ badge.description }}</p>
        </icon-with-hover>
        <div v-else :class="['flex items-center gap-2.5 rounded-[0.375rem] px-3 py-2 font-medium text-white', badge.class]">
          <!-- The same flair as on the portrait -->
          <span class="w-6 h-6 shrink-0 flex items-center justify-center text-lg leading-none">
            <i v-if="badge.key === 'owner'" class="fas fa-crown owner-crown"></i>
            <i v-else-if="badge.key === 'patreon'" class="fas fa-star text-gold"></i>
            <void-eye-flair v-else-if="badge.key === 'void'" :force="true" :tooltip="false"></void-eye-flair>
            <img v-else-if="badge.key === 'award' && badge.icon" :src="badge.icon" class="w-5 h-5 object-contain" alt="" />
          </span>
          {{ badge.label }}
        </div>
      </template>
    </div>
    <div v-if="rows.length" :class="['flex flex-col gap-1', { 'border-t border-gray-dark pt-3': title || playedBy || (card && card.badges.length) }]">
      <p v-for="(row, index) in rows" :key="index" :class="row.value === null ? '' : 'flex justify-between gap-4'">
        <template v-if="row.value === null">{{ row.label }}</template>
        <template v-else><span class="text-subtext">{{ row.label }}</span><span class="font-semibold text-white text-right">{{ row.value }}</span></template>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImageHoverBox',
  inject: {
    card: { from: 'popupCard', default: null },
    claimPopupCard: { default: null },
  },
  props: {
    title: String,
    playedBy: String,
    paragraphOne: String,
    paragraphTwo: String,
    paragraphThree: String,
    paragraphFour: String,
    paragraphFive: String,
    paragraphSix: String,
    paragraphSeven: String,
    hpOwner: String,
  },
  created() {
    // The round-image around this stops drawing the flair itself
    this.claimPopupCard?.();
  },
  computed: {
    // "Label: value" lines become a label and a value; anything else stays a line of its own
    rows() {
      return [this.paragraphOne, this.hpOwner, this.paragraphTwo, this.paragraphThree, this.paragraphFour, this.paragraphFive, this.paragraphSix, this.paragraphSeven]
        .filter(Boolean)
        .map(text => {
          const split = String(text).indexOf(': ');
          return split > 0 ? { label: text.slice(0, split), value: text.slice(split + 2) } : { label: text, value: null };
        });
    },
  },
}
</script>
