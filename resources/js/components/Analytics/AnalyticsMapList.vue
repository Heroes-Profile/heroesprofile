<template>
  <div>
    <p class="text-center mb-3">{{ prompt }}</p>
    <!-- The map's art as the tile, like the game's custom games list -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <component
        :is="canPick(row.name) ? 'button' : 'div'"
        v-for="row in sorted"
        :key="row.name"
        :type="canPick(row.name) ? 'button' : null"
        class="relative aspect-[16/9] rounded overflow-hidden border-2 border-[transparent] bg-darken bg-cover bg-center text-left"
        :class="{ 'hover:border-teal focus:border-teal': canPick(row.name) }"
        :style="{ backgroundImage: `url('/images/maps/match/match-${slug(row.name)}.jpg')` }"
        :title="canPick(row.name) ? 'Show ' + row.name : null"
        @click="canPick(row.name) && $emit('pick', row.name)"
      >
        <span class="absolute inset-x-0 bottom-0 px-2 pb-1 pt-6" style="background: linear-gradient(to top, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0))">
          <span class="block font-bold text-white leading-tight">{{ row.name }}</span>
          <span class="block text-xs text-white/80">{{ row.n.toLocaleString('en-US') }} {{ unit }}</span>
        </span>
      </component>
    </div>
  </div>
</template>

<script>
// With no single map picked: the player's maps as tiles, alphabetical; picking one sets the page's Map filter
export default {
  name: 'AnalyticsMapList',
  props: {
    rows: { type: Array, default: () => [] },
    prompt: String,
    unit: { type: String, default: '' },
    // Only these can be picked (the public sample's maps); null for all
    pickable: { type: Array, default: null },
  },
  emits: ['pick'],
  computed: {
    sorted() {
      return [...this.rows].sort((a, b) => a.name.localeCompare(b.name));
    },
  },
  methods: {
    canPick(name) {
      return !this.pickable || this.pickable.includes(name);
    },
    // The site's sanitized map name, which its map images are named by
    slug(name) {
      return name.toLowerCase().replace(/'/g, '').replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    },
  },
};
</script>
