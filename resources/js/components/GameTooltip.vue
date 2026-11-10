<template>
  <div class="space-y-2.5">
    <p v-for="(paragraph, index) in paragraphs" :key="index">
      <template v-for="(segment, segmentIndex) in paragraph" :key="segmentIndex">
        <br v-if="segment.lineBreak">
        <img v-else-if="segment.questIcon" class="inline-block w-4 h-4 mr-1 align-[-0.15em]" src="/images/miscellaneous/quest-icon.png" alt="">
        <span v-else :class="segment.class">{{ segment.text }}</span>
      </template>
    </p>
  </div>
</template>

<script>
import { parseGameTooltip } from '../utils/gameTooltip';

export default {
  name: 'GameTooltip',
  props: {
    text: String,
    level: {
      type: Number,
      default: 1,
    },
  },
  computed: {
    paragraphs() {
      return parseGameTooltip(this.text, this.level);
    },
  },
}
</script>
