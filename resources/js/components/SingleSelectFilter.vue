<template>
  <div>
    <div id="filter-label" class="relative">
      <div @click="showOptions = !showOptions" class="block text-sm font-medium text-gray-700 cursor-pointer border p-2 rounded ">
        <span>{{ this.text }}</span>
        <span v-if="selectedOptionsName !== ''">: {{ selectedOptionsName }}</span>      
      </div>
      <!-- I added a z-index here to make sure the dropdown was selectable, in case this breaks something later for you -->
      <div v-if="showOptions" class="absolute left-0 mt-2 w-full bg-white border border-gray-300 rounded shadow-lg expandable-dropdown z-50">
        <div>
          <!-- Search Input -->
          <input v-model="searchQuery" type="text" placeholder="Search" class="w-full p-2"/>
        </div>
        <div class="space-y-2 p-2">
          <div v-for="value in filteredValues" :key="value.code">
            <input 
              type="radio" 
              :id="value.code" 
              :value="value.code" 
              :checked="value.code === selectedOptions"
              @click="toggleSelectedOptions(value.code)"
              class="form-checkbox h-5 w-5 text-indigo-600"
            >
            <label :for="value.code" class="ml-2 text-sm text-gray-900">{{ value.name }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SingleSelectFilter',
  components: {
  },
  props: {
    values: Array,
    text: String,
    defaultValue: String,
    trackclosure: Boolean,
  },
  data(){
    return {
      showOptions: false,
      selectedOptions: this.defaultValue || '',
      searchQuery: ''  // Added this for search
    }
  },
  created(){
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener("click", this.handleClickOutside);
  },
  computed: {
    selectedOptionsName() {
      const selected = this.values.find(value => value.code === this.selectedOptions);
      return selected ? selected.name : '';
    },
    filteredValues() {  // Added this for search
      return this.values.filter(value => 
        value.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  },
  watch: {
    selectedOptions: function (newVal) {
      // Emitting the change back to the parent component
      this.$emit('input-changed', { field: this.text, value: newVal, type: 'single' });
    },
    showOptions: function (newVal) {
      if(this.trackclosure && !newVal){
          this.$emit('dropdown-closed', newVal);
        }
    }
  },
  methods: {
    handleClickOutside(event) {
      const dropdown = this.$el;
      if (dropdown && !dropdown.contains(event.target)) {
        this.showOptions = false;
      }
    },
    toggleSelectedOptions(value) {
      //Change this to be dynamic later
      if (this.text !== "Timeframe Type" && this.text !== "Build Filter" && this.text !== "Stat Filter" && this.text !== "Minimum Games") {                                          
        this.selectedOptions = this.selectedOptions === value ? '' : value;
      } else {
        this.selectedOptions = value;
      }
    },
  }
}
</script>

