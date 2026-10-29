<template>
  <!-- contents: each date sits in the parent's filter row as its own item -->
  <div class="contents">
    <div id="filter-label" class="relative p-2 flex flex-col gap-1.5 text-sm font-medium text-subtext max-md:flex-[1_1_8rem]">
      <span>From Date</span>
      <div class="relative h-[40px] w-[9.5rem] max-md:w-full rounded bg-teal hover:bg-lteal text-white px-3 flex items-center gap-2">
        <span class="font-bold uppercase whitespace-nowrap">{{ start ? formatDate(start) : placeholder }}</span>
        <input type="date"
          v-model="start"
          :max="end"
          @click="openPicker"
          @change="emitChange('From Date', start)"
          class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        >
        <button v-if="start" type="button" class="relative z-[1] ml-auto" aria-label="Clear From Date" @click="start = null; emitChange('From Date', null)"><i class="fa-solid fa-xmark"></i></button>
        <i v-else class="fa-solid fa-chevron-down ml-auto text-xs"></i>
      </div>
    </div>
    <div id="filter-label" class="relative p-2 flex flex-col gap-1.5 text-sm font-medium text-subtext max-md:flex-[1_1_8rem]">
      <span>To Date</span>
      <div class="relative h-[40px] w-[9.5rem] max-md:w-full rounded bg-teal hover:bg-lteal text-white px-3 flex items-center gap-2">
        <span class="font-bold uppercase whitespace-nowrap">{{ end ? formatDate(end) : placeholder }}</span>
        <input type="date"
          v-model="end"
          :min="start"
          @click="openPicker"
          @change="emitChange('To Date', end)"
          class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        >
        <button v-if="end" type="button" class="relative z-[1] ml-auto" aria-label="Clear To Date" @click="end = null; emitChange('To Date', null)"><i class="fa-solid fa-xmark"></i></button>
        <i v-else class="fa-solid fa-chevron-down ml-auto text-xs"></i>
      </div>
    </div>
  </div>
</template>

<script>
  // Day, month and year in the visitor's own order, e.g. 10/28/26 in the US and 28.10.26 in most of Europe
  const DATE_FORMAT = new Intl.DateTimeFormat(undefined, { day: '2-digit', month: '2-digit', year: '2-digit' });

  export default {
    name: 'DateRangeFilter',
    components: {
    },
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
    created(){
    },
    mounted() {
    },
    computed: {
      placeholder() {
        return DATE_FORMAT.formatToParts(new Date(2000, 0, 2))
          .map(part => ({ day: 'DD', month: 'MM', year: 'YY' }[part.type] || part.value))
          .join('');
      },
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
      // The input is see-through over the box, so open its picker from anywhere on it
      openPicker(event) {
        try {
          event.target.showPicker();
        } catch (error) {
          // Older browsers open it themselves
        }
      },
      // Input values are YYYY-MM-DD; build the date locally so it doesn't shift a day
      formatDate(value) {
        const [year, month, day] = value.split('-');
        return DATE_FORMAT.format(new Date(year, month - 1, day));
      },
    }
  }
</script>
