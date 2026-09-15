<template>
  <div class="flex flex-wrap">
    <div id="filter-label" class="relative">
      <div class="flex items-end  text-sm font-medium text-gray-700 cursor-pointer p-2  transition-colors">
        <div class="flex flex-col"><span>From Date</span>
        <input type="date"
          v-model="start"
          :max="end"
          @change="emitChange('From Date', start)"
          class="w-[200px] h-[40px] overflow-hidden hover:bg-teal border-solid border-[1px] border-white bg-blue p-2 text-white"
        ></div>
        <button class="h-[40px] bg-blue md:m-t-auto  p-2 border-r-[1px] border-t-[1px] border-b-[1px] hover:bg-teal" @click="start = null; emitChange('From Date', null)">X</button>
      </div>
    </div>
    <div id="filter-label" class="relative">
      <div class="flex items-end  text-sm font-medium text-gray-700 cursor-pointer p-2  transition-colors">
        <div class="flex flex-col"><span>To Date</span>
        <input type="date"
          v-model="end"
          :min="start"
          @change="emitChange('To Date', end)"
          class="w-[200px] h-[40px] overflow-hidden hover:bg-teal border-solid border-[1px] border-white bg-blue p-2 text-white"
        ></div>
        <button class="h-[40px] bg-blue md:m-t-auto  p-2 border-r-[1px] border-t-[1px] border-b-[1px] hover:bg-teal" @click="end = null; emitChange('To Date', null)">X</button>
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
