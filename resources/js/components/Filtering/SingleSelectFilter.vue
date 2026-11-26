<template>
  <div id="filter-label" class="relative" :class="compact ? 'min-w-0 w-full' : fit ? 'filter-fit max-md:w-full' : 'filter-fill max-md:w-full md:w-[12.5rem]'">
    <div @click="showOptions = !showOptions" class="flex flex-col gap-1.5 text-sm font-medium text-subtext cursor-pointer transition-colors" :class="compact ? 'p-0' : 'p-2'" @keydown="handleKeyPress" tabindex="0">
      <span v-if="!hidelabel" class="relative">
        <!-- The info icon sits beside the label, so a longer or larger label pushes it along instead of under it -->
        <span class="relative inline-flex items-center gap-1.5">{{ $t(text) }}
          <round-image v-if="showrankinfo" size="small"    icon="fas fa-info"   title="info"  popupsize="large" mobileClick="true">
            <slot>
              <div>
                <p class="max-sm:text-xs">{{ $t(rankInfo) }}</p>
              </div>
            </slot>
          </round-image>
        </span>
      </span> 
      <span :class="['h-[2.5rem] overflow-hidden rounded bg-surface-3 hover:bg-surface-4 text-white p-2 flex items-center gap-2 min-w-0 w-full', { 'ring-1 ring-teal': showOptions }]">
        <!-- fit: every option sits invisibly in the same cell, so the box is as wide as the longest one -->
        <span class="grid min-w-0">
          <span class="font-bold px-1 text-nowrap truncate min-w-0 [grid-area:1/1]" v-if="selectedOptionsName !== ''">{{ selectedOptionsName }}</span>
          <span v-else-if="placeholder" class="px-1 text-nowrap truncate min-w-0 text-subtext [grid-area:1/1]">{{ $t(placeholder) }}</span>
          <template v-if="fit && !compact">
            <span v-for="value in values" :key="value.code" aria-hidden="true" class="font-bold px-1 text-nowrap invisible h-0 [grid-area:1/1]">{{ value.label || $t(value.name) }}</span>
          </template>
        </span>
        <basil-icon name="arrow" class="rotate-90 ml-auto shrink-0 text-xs transition-transform" :class="{ '-rotate-90': showOptions }"></basil-icon>
      </span>
    </div>
    <!-- I added a z-index here to make sure the dropdown was selectable, in case this breaks something later for you -->
    <!-- As wide as its longest option, lined up under the box -->
    <div v-if="showOptions" class="absolute mt-2 w-max max-w-[calc(100vw-2rem)] overflow-hidden expandable-dropdown z-50 flex flex-col gap-2" :class="[compact ? 'left-0' : 'left-2', { '!min-w-0': !nosearch }]">
      <input v-if="!nosearch" v-model="searchQuery" type="text" :placeholder="$t('Search')" class="w-full"/>
      <div class="max-h-80 overflow-y-auto overflow-x-hidden [scrollbar-gutter:stable]">
        <div class="flex flex-col gap-1" :class="{ 'pr-4 whitespace-nowrap': text === 'Stat Filter' }">
          <!-- The whole row picks its option: the label is tied to the radio -->
          <label v-for="value in filteredValues" :key="value.code" :for="value.code" class="select-none group flex items-center gap-3 px-3 py-2 rounded hover:bg-surface-4 transition-colors cursor-pointer" :class="{ 'whitespace-nowrap': hasPatchDates }">
            <input
              type="radio"
              :id="value.code"
              :value="value.code"
              :checked="isChecked(value.code)"
              @click="toggleSelectedOptions(value.code)"
              class="form-checkbox"
              :disabled="disabled"
            >
            <!-- A short code before the name, as in the login's regions -->
            <span v-if="value.badge" class="px-2 py-0.5 rounded-[0.375rem] bg-surface-1 text-sm font-bold text-white">{{ value.badge }}</span>
            <span :class="['text-sm transition-colors', isChecked(value.code) ? 'text-white' : 'text-subtext group-hover:text-white']">{{ value.detail ? $t(value.detail) : (value.label || $t(value.name)) }}</span>
            <span v-if="value.date_added" class="max-md:hidden ml-auto pl-4 pr-1 text-xs variable-text opacity-60" :title="$t('Added :date', { date: patchDate(value.date_added) })">{{ patchAge(value.date_added) }}</span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import { patchAge, patchDate } from '../../utils/patchAge';

// Shown beside the rank filters
const RANK_INFO = "Heroes Profile Rank is based on a custom MMR algorithm developed by Heroes Profile for approximating a player's skill. This rank does not correlate to in-game rank but the distribution of players in the Heroes Profile dataset. Wood rank is players who have played less than 50 games and cannot be accurately ranked.";

export default {
  name: 'SingleSelectFilter',
  components: {
  },
  props: {
    compact: { type: Boolean, default: false },
    // No label above the box, for rows that put it beside the box instead
    hidelabel: Boolean,
    values: Array,
    text: String,
    defaultValue: {
      type: [String, Number, Boolean ]
    },
    trackclosure: Boolean,
    disabled: Boolean,
    showrankinfo: Boolean,
    disabledeselectfilters: Boolean,
    // No search box above the options, for short lists
    nosearch: Boolean,
    // Shown in the box until an option is picked
    placeholder: String,
    // As wide as the longest option instead of filling the row (Season, Group Size)
    fit: Boolean,
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
  beforeUnmount() {
    document.removeEventListener("click", this.handleClickOutside);
  },
  computed: {
    rankInfo() {
      return RANK_INFO;
    },
    selectedOptionsName() {
      const selected = this.values.find(value => value.code === this.selectedOptions);
      return selected ? (selected.label || this.$t(selected.name)) : '';
    },
    filteredValues() {
      return this.values.filter(value => {
        if (value.name && typeof value.name === 'string') {
          // Translated heroes and maps match on either name
          const text = `${value.name} ${value.label || this.$t(value.name)}`;
          let normalizedValue = text;
          // Check if normalize method is available and ICU support is present
          if (String.prototype.normalize && typeof String.prototype.normalize === 'function') {
            try {
              normalizedValue = text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            } catch (error) {
              normalizedValue = text.toLowerCase();
            }
          } else {
            normalizedValue = text.toLowerCase();
          }
          
          return normalizedValue.includes(this.searchQuery.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase());
        }
        return false;
      });
    },
    hasPatchDates() {
      return (this.values || []).some(value => value.date_added);
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
      if(this.trackclosure && newVal){
        this.$emit('dropdown-opened');
      }
    }
  },
  methods: {
    patchAge,
    patchDate,
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
      }else if ((this.text !== "Timeframe Type" && 
        this.text !== "Build Filter" && 
        this.text !== "Minimum Games" && 
        this.text !== "Mirror Matches" && 
        this.text !== "Talent Build Type" && 
        this.text !== "Type" && 
        this.text !== "Leaderboard Type") && !this.disabledeselectfilters
    ) {                                          
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

