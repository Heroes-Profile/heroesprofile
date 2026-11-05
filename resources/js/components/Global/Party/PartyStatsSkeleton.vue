<template>
  <!-- party-stats-display in grey while it loads: party-size bars, legend, then the section tables -->
  <div class="flex flex-col gap-6">
    <div class="flex items-start gap-6 mt-2">
      <div class="flex-1 min-w-0 flex flex-col gap-3">
        <skeleton-box class="h-6 w-48"></skeleton-box>
        <div v-for="index in 5" :key="index" class="flex flex-col gap-1">
          <skeleton-box class="h-4 w-56 max-w-full"></skeleton-box>
          <skeleton-box class="h-7"></skeleton-box>
        </div>
      </div>
      <div class="w-48 shrink-0 flex flex-col gap-3 max-sm:hidden">
        <skeleton-box class="h-6 w-24"></skeleton-box>
        <skeleton-box class="h-40"></skeleton-box>
      </div>
    </div>
    <table-skeleton v-for="section in 2" :key="section" :heading="true" :rows="6" :columns="['text', 'stat', 'stat', 'stat']"></table-skeleton>
  </div>
</template>

<script>
export default {
  name: 'PartyStatsSkeleton',
};
</script>
