<template>
  <div class="flex flex-wrap max-md:justify-center">
    <div id="filter-label" class="relative">
      <div class="flex items-end  text-sm font-medium text-subtext cursor-pointer p-2  transition-colors">
        <div class="flex flex-col gap-1.5"><span>From Date</span>
        <input type="date"
          v-model="start"
          :max="end"
          @change="emitChange('From Date', start)"
          class="w-[200px] h-[40px] overflow-hidden rounded-l !bg-teal hover:!bg-lteal p-2 text-white"
        ></div>
        <button class="h-[40px] rounded-r bg-teal hover:bg-lteal border-l border-darken text-white px-3" @click="start = null; emitChange('From Date', null)">X</button>
      </div>
    </div>
    <div id="filter-label" class="relative">
      <div class="flex items-end  text-sm font-medium text-subtext cursor-pointer p-2  transition-colors">
        <div class="flex flex-col gap-1.5"><span>To Date</span>
        <input type="date"
          v-model="end"
          :min="start"
          @change="emitChange('To Date', end)"
          class="w-[200px] h-[40px] overflow-hidden rounded-l !bg-teal hover:!bg-lteal p-2 text-white"
        ></div>
        <button class="h-[40px] rounded-r bg-teal hover:bg-lteal border-l border-darken text-white px-3" @click="end = null; emitChange('To Date', null)">X</button>
      </div>
    </div>
  </div>
</template>

<script>
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
