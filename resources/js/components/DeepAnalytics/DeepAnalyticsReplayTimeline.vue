<template>
  <div class="mt-2">
    <div class="flex flex-wrap items-center gap-3 mb-2">
      <custom-button
        :text="playing ? 'Pause' : 'Play'"
        :alt="playing ? 'Pause' : 'Play'"
        size="small"
        :ignoreclick="true"
        @click="$emit('toggle')"
      ></custom-button>
      <span class="font-mono">{{ clock(t) }} / {{ clock(length) }}</span>
      <multi-tab-button :options="speedOptions" :modelValue="speed" @update:modelValue="$emit('speed', $event)"></multi-tab-button>
    </div>

    <!-- Event markers above the scrubber; click one to jump a few seconds before it -->
    <div class="relative h-4">
      <span
        v-for="(m, index) in markers"
        :key="index"
        class="absolute top-0 w-[3px] h-4 cursor-pointer -translate-x-1/2"
        :style="{ left: (100 * m.t / length) + '%', background: m.color }"
        :title="clock(m.t) + ' ' + m.label"
        @click="$emit('seek', Math.max(0, m.t - 5))"
      ></span>
    </div>
    <input
      type="range"
      class="w-full accent-teal"
      min="0" :max="length" step="0.1"
      :value="t"
      @input="$emit('seek', Number($event.target.value))"
    >
  </div>
</template>

<script>
import analytics from './deepAnalyticsFormat.js';

export default {
  name: 'DeepAnalyticsReplayTimeline',
  mixins: [analytics],
  props: {
    t: Number,
    length: Number,
    playing: Boolean,
    speed: Number,
    markers: { type: Array, default: () => [] },
  },
  emits: ['toggle', 'seek', 'speed'],
  data() {
    return {
      speedOptions: [1, 2, 4, 8, 16].map(s => ({ key: s, label: s + 'x' })),
    };
  },
};
</script>
