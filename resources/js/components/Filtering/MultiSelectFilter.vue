<template>
  <div id="filter-label" class="relative" :class="[compact ? 'min-w-0 w-full' : fit ? 'filter-fit max-md:w-full' : 'filter-fill max-md:w-full md:min-w-[12.5rem]', { 'bg-red': highlighttimesframes }]">
    <div @click="showOptions = !showOptions" class="flex flex-col gap-1.5 text-sm font-medium text-subtext cursor-pointer transition-colors" :class="compact ? 'p-0' : 'p-2'">
      <span v-if="!hidelabel" class="relative">
        <!-- The info icon sits beside the label, so a longer or larger label pushes it along instead of under it -->
        <span class="relative inline-flex items-center gap-1.5">{{ $t(text) }}
          <round-image :popup-hoverable="true" v-if="infolink" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
            <div>
              <p class="max-sm:text-xs">{{ $t('Timeframes correspond to game patches.') }} <a :href="infolink" target="_blank" class="group/bt inline-flex items-center"><span class="group-hover/bt:underline">{{ $t('View the full patch & season history.') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a></p>
            </div>
          </round-image>
          <round-image v-if="showrankinfo" size="small"    icon="fas fa-info"   title="info"  popupsize="large" mobileClick="true">
            <slot>
              <div>
                <p class="max-sm:text-xs">{{ $t(rankInfo) }}</p>
              </div>
            </slot>
          </round-image>
        </span>
      </span>



      
      <span :class="['h-[2.5rem] overflow-hidden rounded bg-surface-3 hover:bg-surface-4 text-white p-2 flex items-center gap-2 relative min-w-0 w-full', { 'ring-1 ring-teal': showOptions }]">
        <span class="flex min-w-0 gap-1">
          <span v-if="toggletabs" class="whitespace-nowrap font-bold px-1 shrink-0">{{ $t(toggleside === 'right' ? toggletabs[1] : toggletabs[0]) }}</span>
          <!-- fit: the options and the "6 of 6 selected" summary sit invisibly in the same cell, so the box is as wide as the longest -->
          <span class="grid min-w-0">
            <span v-if="selectedOptions.length" class="font-bold px-1 truncate min-w-0 [grid-area:1/1]">{{ selectedlabel || (selectedOptions.length <= 3 ? selectedOptionsName.join(', ') : $t(':selected of :total selected', { selected: selectedOptions.length, total: values.length })) }}</span>
            <span v-else-if="placeholder" class="font-bold px-1 truncate min-w-0 [grid-area:1/1]">{{ $t(placeholder) }}</span>
            <template v-if="fit && !compact">
              <span v-for="label in fitLabels" :key="label" aria-hidden="true" class="font-bold px-1 whitespace-nowrap invisible h-0 [grid-area:1/1]">{{ label }}</span>
            </template>
          </span>
        </span>
        <!-- Clears the filter without opening it -->
        <button v-if="clearable" type="button" class="ml-auto shrink-0 w-5 h-5 -my-1 rounded-full flex items-center justify-center text-subtext hover:text-white hover:bg-surface-2 transition-colors" :aria-label="$t('Clear')" :title="$t('Clear')" @click.stop="clearSelection">
          <basil-icon name="cross" class="text-[0.625rem]"></basil-icon>
        </button>
        <basil-icon name="arrow" :class="['rotate-90 shrink-0 text-xs transition-transform', { 'ml-auto': !clearable, '-rotate-90': showOptions }]"></basil-icon>
      </span>
    </div>
    <!-- I added a z-index here to make sure the dropdown was selectable, in case this breaks something later for you -->
    <!-- As wide as its longest option, lined up under the box -->
    <div v-if="showOptions" class="absolute mt-2 w-max max-w-[calc(100vw-2rem)] overflow-hidden expandable-dropdown z-50" :class="compact ? 'left-0 !min-w-0' : 'left-2 !min-w-0'">
      <div class="flex flex-col gap-1">
        <div v-if="toggletabs" class="flex justify-center">
          <tab-button :tab1text="$t(toggletabs[0])" :tab2text="$t(toggletabs[1])" :ignoreclick="true" :small="true"
            :overridedefaultside="toggleside"
            @tab-click="handleToggle"
          ></tab-button>
        </div>
        <!-- Heading checkboxes stand in for Select All -->
        <label v-if="!headings && !groupbyyear" for="select-all" class="select-none whitespace-nowrap group flex items-center gap-3 px-3 py-2 rounded hover:bg-surface-4 transition-colors cursor-pointer">
          <input 
          type="checkbox" 
          id="select-all" 
          :checked="selectedOptions.length === selectableValues.length"
          @click="toggleAll"
          class="form-checkbox"
          >
          <span :class="['text-sm transition-colors', selectedOptions.length === selectableValues.length ? 'text-white' : 'text-subtext group-hover:text-white']">{{ $t('Select All') }}</span>
        </label>
        <!-- pr-3 keeps the longest option clear of the scrollbar -->
        <div class="max-h-80 overflow-y-auto overflow-x-hidden [scrollbar-gutter:stable] pr-1 flex flex-col gap-1">
          <template v-for="row in rows" :key="row.key">
          <label v-if="row.heading" :for="row.key" class="select-none whitespace-nowrap group flex items-center gap-3 px-3 py-2 rounded hover:bg-surface-4 transition-colors cursor-pointer" :class="[indentClass(row.depth), { 'opacity-40': !row.codes.some(code => isSelectable(code)) }]">
            <input
            type="checkbox"
            :id="row.key"
            :checked="row.codes.every(code => selectedOptions.includes(code))"
            :indeterminate="row.codes.some(code => selectedOptions.includes(code)) && !row.codes.every(code => selectedOptions.includes(code))"
            :disabled="!row.codes.some(code => isSelectable(code))"
            @click="toggleHeading(row)"
            class="form-checkbox"
            >
            <span class="text-sm font-bold text-white">{{ $t(row.name) }}</span>
          </label>
          <label v-else :for="row.value.code" class="select-none whitespace-nowrap group flex items-center gap-3 px-3 py-2 rounded hover:bg-surface-4 transition-colors cursor-pointer" :class="[indentClass(row.depth), { 'opacity-40': !isSelectable(row.value.code) }]">
            <input
            type="checkbox"
            :id="row.value.code"
            :value="row.value.code"
            v-model="selectedOptions"
            :disabled="!isSelectable(row.value.code)"
            class="form-checkbox"
            >
            <!-- A short code in a chip, then what it covers (the regions) -->
            <span v-if="row.value.badge" class="px-2 py-0.5 rounded-[0.375rem] bg-surface-1 text-sm font-bold text-white">{{ row.value.badge }}</span>
            <span :class="['text-sm transition-colors', selectedOptions.includes(row.value.code) ? 'text-white' : 'text-subtext group-hover:text-white']">{{ groupbyyear ? seasonLabel(row.value.name) : (row.value.detail ? $t(row.value.detail) : (row.value.label || $t(row.value.name))) }}</span>
            <span v-if="row.value.date_added" class="max-md:hidden ml-auto pl-4 pr-1 text-xs variable-text opacity-60" :title="$t('Added :date', { date: patchDate(row.value.date_added) })">{{ patchAge(row.value.date_added) }}</span>
          </label>
          </template>
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
    name: 'MultiSelectFilter',
    components: {
    },
    props: {
      // As wide as the longest option instead of filling the row (Season)
      fit: Boolean,
      compact: { type: Boolean, default: false },
      // No label above the box, for rows that put it beside the box instead
      hidelabel: Boolean,
      // Shown in the box while nothing is ticked
      placeholder: String,
      values: Array,
      text: String,
      defaultValue: Array,
      trackclosure: Boolean,
      showrankinfo: Boolean,
      infolink: String,
      // Returns an option's group from its code. Once one is picked, the other groups grey out.
      groupby: Function,
      // Returns an option's headings, outermost first: [{ key, name }]. Options are listed indented under them.
      headings: Function,
      // Lists "2026 Season 3"-style options under a heading per year
      groupbyyear: Boolean,
      // Replaces the closed dropdown's list of picked names
      selectedlabel: String,
      // Name sent with input-changed when it differs from the label
      field: String,
      // Two labels for a switch at the top of the dropdown; flipping it clears the selection and emits toggle-click
      toggletabs: Array,
      toggleside: { type: String, default: 'left' },
    },
    data(){
      return {
        showOptions: false,
        selectedOptions: this.defaultValue || [],
      }
    },
    created(){
      if(this.text == "Timeframes" || this.text == "Game Type"){
        this.$emit('input-changed', { field: this.field || this.text, value: this.selectedOptions, type: 'multi' });
      }
    },
    mounted() {
      document.addEventListener("click", this.handleClickOutside);
    },
    beforeUnmount() {
      document.removeEventListener("click", this.handleClickOutside);
    },
    computed: {
      // Something picked (for Game Type, anything short of every type, since clearing it picks them all).
      // Emptying Timeframes is allowed: the box turns red and Filter waits for a pick
      clearable() {
        if (this.text === 'Game Type') {
          return this.selectedOptions.length < this.selectableValues.length;
        }
        return this.selectedOptions.length > 0;
      },
      rankInfo() {
        return RANK_INFO;
      },
      // The widest things the box can show, for fit
      fitLabels() {
        const names = this.values.map(value => (this.groupbyyear ? this.seasonLabel(value.name) : (value.label || this.$t(value.name))));
        return [...new Set([...names, this.$t(':selected of :total selected', { selected: this.values.length, total: this.values.length })])];
      },
      selectedOptionsName() {
        return this.selectedOptions.map(code => {
          const value = this.values.find(v => v.code === code);
          if (!value) return '';
          // Seasons are named "2026 Season 3"
          const season = this.groupbyyear && String(value.name).match(/^(\d{4}) Season (.+)$/);
          if (season) return this.$t(':year Season :season', { year: season[1], season: season[2] });
          return value.label || this.$t(value.name);
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
      // Options with their headings in between, each heading holding the codes under it
      rows() {
        const headingsOf = this.headings
          || (this.groupbyyear ? code => this.yearHeadingOf(code) : null);
        if(!headingsOf){
          return (this.values || []).map(value => ({ key: 'value-' + value.code, value, depth: 0 }));
        }

        const rows = [];
        let open = [];
        (this.values || []).forEach(value => {
          const headings = headingsOf(value.code);
          headings.forEach((heading, depth) => {
            if(open[depth]?.key !== 'heading-' + heading.key){
              const row = { key: 'heading-' + heading.key, heading: true, name: heading.name, depth, codes: [] };
              rows.push(row);
              open = [...open.slice(0, depth), row];
            }
          });
          open.slice(0, headings.length).forEach(row => row.codes.push(value.code));
          rows.push({ key: 'value-' + value.code, value, depth: headings.length });
        });
        return rows;
      },
      hasPatchDates() {
        return (this.values || []).some(value => value.date_added);
      },
      selectableValues() {
        if(!this.groupby || this.selectedOptions.length === 0){
          return this.values;
        }

        return this.valuesInGroup(this.groupby(this.selectedOptions[0]));
      },
      inputWidth() {
        return this.selectedOptions.length > 3 ? 'auto' : '200px';
      },
    },
    watch: {
      defaultValue: function (newVal) {
        this.selectedOptions = [...newVal];
      },
      selectedOptions: function (newVal) {
        this.$emit('input-changed', { field: this.field || this.text, value: newVal, type: 'multi' });
      },
      showOptions: function (newVal) {
      if(this.trackclosure && !newVal){
          this.$emit('dropdown-closed', newVal);
        }
      if(this.trackclosure && newVal){
        this.$emit('dropdown-opened');
      }
      },
    },
    methods: {
      // "2026 Season 3" under its year's heading: "Season 3", in the page's language
      seasonLabel(name) {
        const rest = String(name).split(' ').slice(1).join(' ');
        const numbered = rest.match(/^Season (.+)$/);
        return numbered ? this.$t('Season :season', { season: numbered[1] }) : this.$t(rest);
      },
      patchAge,
      patchDate,
      handleClickOutside(event) {
        const dropdown = this.$el;
        if (dropdown && !dropdown.contains(event.target)) {
          this.showOptions = false;
        }
      },
      handleToggle(side) {
        if(side === this.toggleside){
          return;
        }
        this.selectedOptions = [];
        this.$emit('toggle-click', side);
      },
      valuesInGroup(group) {
        return this.values.filter(value => this.groupby(value.code) === group);
      },
      isSelectable(code) {
        return this.selectableValues.some(value => value.code === code);
      },
      yearHeadingOf(code) {
        const year = String(this.values.find(value => value.code === code)?.name || '').split(' ')[0];
        return year ? [{ key: 'year-' + year, name: year }] : [];
      },
      indentClass(depth) {
        // On top of the row's own px-3
        return ['', 'pl-9', 'pl-[3.75rem]'][depth] || '';
      },
      toggleHeading(row) {
        if(row.codes.every(code => this.selectedOptions.includes(code))){
          this.selectedOptions = this.selectedOptions.filter(code => !row.codes.includes(code));
          return;
        }

        // Nothing picked yet, so any heading's options are allowed
        const allowed = this.selectedOptions.length ? row.codes.filter(code => this.isSelectable(code)) : row.codes;
        this.selectedOptions = [...this.selectedOptions, ...allowed.filter(code => !this.selectedOptions.includes(code))];
      },
      // Game Type has to keep a pick, so clearing it means every game type; the rest empty out
      clearSelection() {
        this.selectedOptions = this.text === 'Game Type' ? this.selectableValues.map(value => value.code) : [];
      },
      toggleAll() {
        let selectable = this.selectableValues;

        // Nothing picked yet, so Select All takes the first option's group
        if(this.groupby && this.selectedOptions.length === 0 && this.values.length > 0){
          selectable = this.valuesInGroup(this.groupby(this.values[0].code));
        }

        if (this.selectedOptions.length === selectable.length) {
          this.selectedOptions = [];
        } else {
          this.selectedOptions = selectable.map(value => value.code);
        }
      },
    }
  }
</script>