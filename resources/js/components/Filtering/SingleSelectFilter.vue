<template>
  <div id="filter-label" class="relative">
    <div @click="showOptions = !showOptions" class="flex flex-col text-sm font-medium text-gray-700 cursor-pointer p-2  transition-colors" @keydown="handleKeyPress" tabindex="0">
      <span class="relative">
        <span class="relative">{{ this.text }}
          <round-image v-if="showrankinfo" class="mt-2"  size="small"    icon="fas fa-info"   title="info"  popupsize="large" style="position:absolute; bottom:0; right:-25px;">
            <slot>
              <div>
                <p class="max-sm:text-xs">Heroes Profile Rank is based on a custom MMR algorithm developed by Heroes Profile for approximating a player's skill. This rank does not correlate to in-game rank but the distribution of players in the Heroes Profile dataset.</p>
              </div>
            </slot>
          </round-image>
        </span>
      </span> 
      <span class="w-[200px] h-[40px] overflow-hidden hover:bg-teal border-solid border-[1px] border-white bg-blue p-2" ><span class="uppercase font-bold bg-teal rounded px-1 text-nowrap" v-if="selectedOptionsName !== ''">{{ selectedOptionsName }}</span></span>      
    </div>
    <!-- I added a z-index here to make sure the dropdown was selectable, in case this breaks something later for you -->
    <div v-if="showOptions" class="absolute left-0 mt-2 w-full bg-white border border-gray-300 rounded shadow-lg expandable-dropdown z-50">
      <div>
        <!-- Search Input -->
        <input v-model="searchQuery" type="text" placeholder="Search" class="w-full p-2 variable-text"/>
      </div>
      <div class="max-h-80 overflow-y-auto"> 
        <div class="space-y-2 p-2">
          <div v-for="value in filteredValues" :key="value.code">
            <input 
              type="radio" 
              :id="value.code" 
              :value="value.code" 
              :checked="isChecked(value.code)"
              @click="toggleSelectedOptions(value.code)"
              class="form-checkbox h-5 w-5 text-indigo-600"
              :disabled="disabled"
            >
            <label :for="value.code" class="ml-2 text-sm variable-text">{{ value.name }}</label>
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
    defaultValue: {
      type: [String, Number, Boolean ]
    },
    trackclosure: Boolean,
    disabled: Boolean,
    showrankinfo: Boolean,
  },
  data(){
    return {
      showOptions: false,
      selectedOptions: this.defaultValue || '',
      searchQuery: '',
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
    filteredValues() {
      return this.values.filter(value => {
        if (value.name && typeof value.name === 'string') {
          let normalizedValue = value.name;
          // Check if normalize method is available and ICU support is present
          if (String.prototype.normalize && typeof String.prototype.normalize === 'function') {
            try {
              normalizedValue = value.name.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            } catch (error) {
              normalizedValue = value.name.toLowerCase();
            }
          } else {
            normalizedValue = value.name.toLowerCase();
          }
          
          return normalizedValue.includes(this.searchQuery.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase());
        }
        return false;
      });
    },

  },
  watch: {
    selectedOptions: function (newVal) {
      this.$emit('input-changed', { field: this.text, value: newVal, type: 'single' });
    },
    showOptions: function (newVal) {
      if(this.trackclosure && !newVal){
          this.$emit('dropdown-closed', newVal);
        }
    }
  },
  methods: {
    isChecked(value){
      if(value === this.selectedOptions){
        return true;
      }
      return false
    },
    handleClickOutside(event) {
      const dropdown = this.$el;
      if (dropdown && !dropdown.contains(event.target)) {
        this.showOptions = false;
      }
    },
    toggleSelectedOptions(value) {
      if(this.text === "Stat Filter"){
        this.selectedOptions = this.selectedOptions === value ? 'win_rate' : value;
      }else if (this.text !== "Timeframe Type" && this.text !== "Build Filter" && this.text !== "Minimum Games" && this.text !== "Mirror Matches") {                                          
        this.selectedOptions = this.selectedOptions === value ? '' : value;
      } else {
        this.selectedOptions = value;
      }
    },
    handleKeyPress(event) {
      const char = event.key;
      if (/^[a-z0-9]$/i.test(char)) {
        this.searchQuery += char;
      }
    },
  }
}
</script>

