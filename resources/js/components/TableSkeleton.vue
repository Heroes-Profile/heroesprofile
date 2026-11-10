<template>
  <!-- A table's grey placeholder while it loads. Pass columns to mirror the real table's cells. -->
  <component :is="bare ? 'div' : 'panel-box'" :class="{ 'bg-surface rounded overflow-hidden': bare }">
    <skeleton-box v-if="heading" class="h-8 w-80 max-w-full"></skeleton-box>
    <div v-if="columns.length" class="flex flex-col">
      <div class="grid gap-3 md:gap-6 px-3 py-5 border-b border-gray-dark" :style="gridStyle">
        <skeleton-box v-for="(column, index) in shownColumns" :key="index" :class="['h-4', column === 'num' ? 'w-2/3 ml-auto' : ['icon', 'role', 'avatar', 'stat', 'rank', 'pill', 'talentrow', 'copy', 'code'].includes(column) ? 'w-2/3 mx-auto' : 'w-3/4']"></skeleton-box>
      </div>
      <div v-for="row in rows" :key="row" :class="['grid gap-3 md:gap-6 px-3 py-4 items-center', { 'bg-surface-1': row % 2 === 0 }]" :style="gridStyle">
        <template v-for="(column, index) in shownColumns" :key="index">
          <div v-if="column === 'portrait'" class="flex flex-col gap-2">
            <skeleton-box class="w-12 h-12 max-md:w-10 max-md:h-10 !rounded-full"></skeleton-box>
            <skeleton-box class="h-4 w-24 max-w-full"></skeleton-box>
          </div>
          <!-- A role icon with its name underneath, centred -->
          <div v-else-if="column === 'role'" class="flex flex-col items-center gap-2">
            <skeleton-box class="w-12 h-14 max-md:w-8 max-md:h-9"></skeleton-box>
            <skeleton-box class="h-4 w-20 max-w-full"></skeleton-box>
          </div>
          <skeleton-box v-else-if="column === 'icon'" class="w-20 h-20 max-md:w-12 max-md:h-12 mx-auto !rounded-full"></skeleton-box>
          <skeleton-box v-else-if="column === 'avatar'" class="w-10 h-10 mx-auto !rounded-full"></skeleton-box>
          <!-- A hero portrait with the name beside it -->
          <div v-else-if="column === 'hero'" class="flex items-center gap-2">
            <skeleton-box class="w-10 h-10 shrink-0 !rounded-full"></skeleton-box>
            <skeleton-box class="h-4 w-24 max-w-full"></skeleton-box>
          </div>
          <!-- A build: seven talent squares with its copy code and button underneath -->
          <div v-else-if="column === 'talents'" class="flex flex-col items-center gap-2">
            <div class="flex gap-1.5">
              <skeleton-box v-for="level in 7" :key="level" class="w-[2.625rem] h-[2.625rem] shrink-0"></skeleton-box>
            </div>
            <div class="flex items-center gap-2">
              <skeleton-box class="h-4 w-40 max-w-full"></skeleton-box>
              <skeleton-box class="h-5 w-5"></skeleton-box>
            </div>
          </div>
          <!-- Leaderboard cells: a tier pill, a rank badge with its text, a build without its code, a copy code with its button -->
          <skeleton-box v-else-if="column === 'pill'" class="h-9 w-40 max-w-full mx-auto"></skeleton-box>
          <div v-else-if="column === 'badge'" class="flex items-center gap-3">
            <skeleton-box class="w-9 h-9 shrink-0 !rounded-full"></skeleton-box>
            <skeleton-box class="h-4 w-32 max-w-full"></skeleton-box>
          </div>
          <div v-else-if="column === 'talentrow'" class="flex gap-1.5 justify-center">
            <skeleton-box v-for="level in 7" :key="level" class="w-[2.625rem] h-[2.625rem] shrink-0"></skeleton-box>
          </div>
          <!-- A build's copy code with its copy icon -->
          <div v-else-if="column === 'code'" class="flex items-center justify-center gap-2">
            <skeleton-box class="h-4 w-32 max-w-full"></skeleton-box>
            <skeleton-box class="w-4 h-4 shrink-0"></skeleton-box>
          </div>
          <div v-else-if="column === 'copy'" class="flex flex-col items-center gap-2">
            <skeleton-box class="h-4 w-40 max-w-full"></skeleton-box>
            <skeleton-box class="h-6 w-36 max-w-full"></skeleton-box>
          </div>
          <div v-else-if="column === 'bar'" class="flex flex-col gap-2">
            <skeleton-box class="h-4 w-14 max-w-full"></skeleton-box>
            <skeleton-box class="h-4 w-full"></skeleton-box>
          </div>
          <div v-else-if="column === 'pair'" class="flex flex-col gap-2">
            <skeleton-box class="h-4 w-12"></skeleton-box>
            <skeleton-box class="h-4 w-28 max-w-full"></skeleton-box>
          </div>
          <skeleton-box v-else-if="column === 'num'" class="h-4 w-16 max-w-full ml-auto"></skeleton-box>
          <skeleton-box v-else-if="column === 'stat'" class="h-4 w-16 max-w-full mx-auto"></skeleton-box>
          <skeleton-box v-else-if="column === 'rank'" class="h-4 w-6 mx-auto"></skeleton-box>
          <skeleton-box v-else-if="column === 'name'" class="h-4 w-3/5"></skeleton-box>
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
    // Cell shapes per column: portrait, icon (80px), role (icon over its name, centred), avatar (40px), hero (portrait and name), talents (a build), talentrow, copy, code (copy code and icon), pill, badge, rank, name (wide, left), bar, pair, num (right-aligned), stat (centred), or text.
    columns: {
      type: Array,
      default: () => [],
    },
    // No panel around it, for tables that sit straight on the page.
    bare: Boolean,
    // The columns phones show; without it, the first five
    mobileColumns: Array,
  },
  data() {
    return {
      isMobile: window.matchMedia('(max-width: 1023px)').matches,
    };
  },
  computed: {
    shownColumns() {
      return this.isMobile ? (this.mobileColumns ?? this.columns.slice(0, 5)) : this.columns;
    },
    gridStyle() {
      // Icon-only columns are narrow in the real tables
      const widths = { avatar: 'minmax(0, 0.4fr)', hero: 'minmax(0, 1.6fr)', rank: 'minmax(0, 0.4fr)', name: 'minmax(0, 3fr)', talentrow: 'max-content', badge: 'minmax(0, 1.5fr)' };
      return { gridTemplateColumns: this.shownColumns.map(column => widths[column] ?? 'minmax(0, 1fr)').join(' ') };
    },
  },
};
</script>
