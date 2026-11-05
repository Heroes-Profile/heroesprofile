<template>
  <!-- A table's grey placeholder while it loads. Pass columns to mirror the real table's cells. -->
  <component :is="bare ? 'div' : 'panel-box'" :class="{ 'bg-surface rounded overflow-hidden': bare }">
    <skeleton-box v-if="heading" class="h-8 w-80 max-w-full"></skeleton-box>
    <div v-if="columns.length" class="flex flex-col">
      <div class="grid gap-6 px-3 py-5 border-b border-gray-dark" :style="gridStyle">
        <skeleton-box v-for="(column, index) in columns" :key="index" :class="['h-4', column === 'num' ? 'w-2/3 ml-auto' : ['icon', 'avatar', 'stat'].includes(column) ? 'w-2/3 mx-auto' : 'w-3/4']"></skeleton-box>
      </div>
      <div v-for="row in rows" :key="row" :class="['grid gap-6 px-3 py-4 items-center', { 'bg-surface-1': row % 2 === 0 }]" :style="gridStyle">
        <template v-for="(column, index) in columns" :key="index">
          <div v-if="column === 'portrait'" class="flex flex-col gap-2">
            <skeleton-box class="w-12 h-12 !rounded-full"></skeleton-box>
            <skeleton-box class="h-4 w-24 max-w-full"></skeleton-box>
          </div>
          <skeleton-box v-else-if="column === 'icon'" class="w-20 h-20 mx-auto !rounded-full"></skeleton-box>
          <skeleton-box v-else-if="column === 'avatar'" class="w-10 h-10 mx-auto !rounded-full"></skeleton-box>
          <!-- A build: seven talent circles with its copy code underneath -->
          <div v-else-if="column === 'talents'" class="flex flex-col gap-2">
            <div class="flex gap-2">
              <skeleton-box v-for="level in 7" :key="level" class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
            </div>
            <skeleton-box class="h-3 w-40 max-w-full"></skeleton-box>
          </div>
          <div v-else-if="column === 'bar'" class="flex flex-col gap-2">
            <skeleton-box class="h-4 w-14"></skeleton-box>
            <skeleton-box class="h-4 w-full"></skeleton-box>
          </div>
          <div v-else-if="column === 'pair'" class="flex flex-col gap-2">
            <skeleton-box class="h-4 w-12"></skeleton-box>
            <skeleton-box class="h-4 w-28 max-w-full"></skeleton-box>
          </div>
          <skeleton-box v-else-if="column === 'num'" class="h-4 w-16 ml-auto"></skeleton-box>
          <skeleton-box v-else-if="column === 'stat'" class="h-4 w-16 mx-auto"></skeleton-box>
          <skeleton-box v-else class="h-4 w-3/4"></skeleton-box>
        </template>
      </div>
    </div>
    <div v-else class="flex flex-col gap-1">
      <skeleton-box class="h-8"></skeleton-box>
      <skeleton-box v-for="index in rows" :key="index" class="h-10"></skeleton-box>
    </div>
  </component>
</template>

<script>
export default {
  name: 'TableSkeleton',
  props: {
    rows: {
      type: Number,
      default: 15,
    },
    heading: Boolean,
    // Cell shapes per column: portrait, icon (80px), avatar (40px), talents (a build), bar, pair, num (right-aligned), stat (centred), or text.
    columns: {
      type: Array,
      default: () => [],
    },
    // No panel around it, for tables that sit straight on the page.
    bare: Boolean,
  },
  computed: {
    gridStyle() {
      // Icon-only columns are narrow in the real tables
      return { gridTemplateColumns: this.columns.map(column => column === 'avatar' ? 'minmax(0, 0.4fr)' : 'minmax(0, 1fr)').join(' ') };
    },
  },
};
</script>
