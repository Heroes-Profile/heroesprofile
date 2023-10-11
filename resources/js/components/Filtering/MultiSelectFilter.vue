<template>
    <div id="filter-label" class="relative" :class="{ 'bg-red': highlighttimesframes }">
      <div @click="showOptions = !showOptions" class="flex flex-col text-sm font-medium text-gray-700 cursor-pointer  p-2    transition-colors">
        <span>{{ this.text }}</span>
        <span class="w-[200px] h-[40px] overflow-hidden hover:bg-teal border-solid border-[1px] border-white bg-blue p-2 flex " > <span  v-for="name in selectedOptionsName" class="uppercase  font-bold  bg-teal rounded px-1 mx-1 flex no-wrap">{{ name }}</span></span>
      </div>
      <!-- I added a z-index here to make sure the dropdown was selectable, in case this breaks something later for you -->
      <div v-if="showOptions" class="absolute left-0 mt-2 w-full bg-white border border-gray-300 rounded shadow-lg expandable-dropdown z-50">
        <div class="space-y-2 p-2">
          <div>
            <input 
              type="checkbox" 
              id="select-all" 
              :checked="selectedOptions.length === values.length"
              @click="toggleAll"
              class="form-checkbox h-5 w-5 text-indigo-600"
            >
            <label for="select-all" class="ml-2 text-sm text-black">Select All</label>
          </div>
          <div class="max-h-80 overflow-y-auto"> 
            <div v-for="value in values" :key="value.code">
              <input 
                type="checkbox" 
                :id="value.code" 
                :value="value.code" 
                v-model="selectedOptions"
                class="form-checkbox h-5 w-5 text-indigo-600"
              >
              <label :for="value.code" class="ml-2 text-sm text-black">{{ value.name }}</label>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  name: 'MultiSelectFilter',
  components: {
  },
  props: {
    values: Array,
    text: String,
    defaultValue: Array,
  },
  data(){
    return {
      showOptions: false,
      selectedOptions: this.defaultValue || [],
    }
  },
  created(){
    if(this.text == "Timeframes" || this.text == "Game Type"){
      this.$emit('input-changed', { field: this.text, value: this.selectedOptions, type: 'multi' });
    }
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener("click", this.handleClickOutside);
  },
  computed: {
    selectedOptionsName() {
      return this.selectedOptions.map(code => {
        const value = this.values.find(v => v.code === code);
        return value ? value.name : '';
      });
    },
    highlighttimesframes(){
      if(this.text === "Timeframes" || this.text === "Game Type"){
        if(this.selectedOptions.length == 0){
          return true
        }
      }
      return false;
    },
  },
  watch: {
    defaultValue: function (newVal) {
      this.selectedOptions = [...newVal];
    },
    selectedOptions: function (newVal) {
      this.$emit('input-changed', { field: this.text, value: newVal, type: 'multi' });
    }
  },
  methods: {
    handleClickOutside(event) {
      const dropdown = this.$el;
      if (dropdown && !dropdown.contains(event.target)) {
        this.showOptions = false;
      }
    },
    toggleAll() {
      if (this.selectedOptions.length === this.values.length) {
        this.selectedOptions = [];
      } else {
        this.selectedOptions = this.values.map(value => value.code);
      }
    },
  }
}
</script>