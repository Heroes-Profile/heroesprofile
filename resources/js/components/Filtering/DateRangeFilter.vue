<template>
  <!-- contents: each date sits in the parent's filter row as its own item -->
  <div class="contents">
    <div id="filter-label" class="relative p-2 flex flex-col gap-1.5 text-sm font-medium text-subtext max-md:flex-[1_1_8rem]">
      <span>{{ $t('From Date') }}</span>
      <date-picker-field v-model="start" :max="end" label="From Date" @change="emitChange('From Date', $event)"></date-picker-field>
    </div>
    <div id="filter-label" class="relative p-2 flex flex-col gap-1.5 text-sm font-medium text-subtext max-md:flex-[1_1_8rem]">
      <span>{{ $t('To Date') }}</span>
      <date-picker-field v-model="end" :min="start" label="To Date" @change="emitChange('To Date', $event)"></date-picker-field>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'DateRangeFilter',
    props: {
      startDate: String,
      endDate: String,
    },
    data(){
      return {
        start: this.startDate || null,
        end: this.endDate || null,
      }
    },
    watch: {
      // Parent clears these when seasons are picked
      startDate(newVal) {
        this.start = newVal || null;
      },
      endDate(newVal) {
        this.end = newVal || null;
      },
    },
    methods: {
      emitChange(field, value) {
        this.$emit('input-changed', { field: field, value: value || null, type: 'single' });
      },
    }
  }
</script>
