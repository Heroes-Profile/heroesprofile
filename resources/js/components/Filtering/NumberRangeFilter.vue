<template>
  <div class="flex flex-wrap">
    <div id="filter-label" class="relative">
      <div class="flex flex-col text-sm font-medium text-gray-700 p-2">
        <span>{{ text }} Min</span>
        <input type="number"
          v-model="min"
          :min="lowest"
          @change="emitChange(text + ' Min', min)"
          :disabled="disabled"
          :class="['w-[150px] h-[40px] border-solid border-[1px] border-white p-2 text-white focus:outline-none', disabled ? 'bg-gray-md cursor-not-allowed' : 'bg-blue hover:bg-teal']"
        >
      </div>
    </div>
    <div id="filter-label" class="relative">
      <div class="flex flex-col text-sm font-medium text-gray-700 p-2">
        <span>{{ text }} Max</span>
        <input type="number"
          v-model="max"
          :min="min || lowest"
          @change="emitChange(text + ' Max', max)"
          :disabled="disabled"
          :class="['w-[150px] h-[40px] border-solid border-[1px] border-white p-2 text-white focus:outline-none', disabled ? 'bg-gray-md cursor-not-allowed' : 'bg-blue hover:bg-teal']"
        >
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'NumberRangeFilter',
    components: {
    },
    props: {
      text: String,
      lowest: {
        type: Number,
        default: 0,
      },
      disabled: Boolean,
    },
    data(){
      return {
        min: null,
        max: null,
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
    },
    watch: {
      // A disabled range should not keep filtering
      disabled(newVal) {
        if(newVal){
          this.min = null;
          this.max = null;
          this.emitChange(this.text + ' Min', null);
          this.emitChange(this.text + ' Max', null);
        }
      },
    },
    methods: {
      emitChange(field, value) {
        const number = value === '' || value === null ? null : Number(value);
        this.$emit('input-changed', { field: field, value: Number.isNaN(number) ? null : number, type: 'single' });
      },
    }
  }
</script>
