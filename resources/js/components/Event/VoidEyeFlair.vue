<template>
  <icon-with-hover v-if="visible && tooltip" size="small" icon="void-eye-img" title="info" popupsize="small">
    <slot>
      <div>
        <p class="max-sm:text-xs">Marked by the Void</p>
      </div>
    </slot>
  </icon-with-hover>
  <i v-else-if="visible" class="void-eye-img" title="Marked by the Void"></i>
</template>

<script>
import { hasVoidEye } from '../../voidEye';

// The one place the Mark of the Void flair is drawn. Pass blizz-id/region and it decides whether to show.
export default {
  name: 'VoidEyeFlair',
  props: {
    blizzId: {
      type: [String, Number],
      default: null,
    },
    region: {
      type: [String, Number],
      default: null,
    },
    // Hover tooltip; off where the parent already has one (e.g. hero portraits).
    tooltip: {
      type: Boolean,
      default: true,
    },
    // Parent already knows the player has it.
    force: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    visible() {
      return this.force || hasVoidEye(this.blizzId, this.region);
    },
  },
};
</script>
