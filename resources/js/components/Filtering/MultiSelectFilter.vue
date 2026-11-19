<template>
  <div id="filter-label" class="relative" :class="[compact ? 'min-w-0 w-full' : 'filter-fill max-md:w-full md:min-w-[12.5rem]', { 'bg-red': highlighttimesframes }]">
    <div @click="showOptions = !showOptions" class="flex flex-col gap-1.5 text-sm font-medium text-subtext cursor-pointer transition-colors" :class="compact ? 'p-0' : 'p-2'">
      <span v-if="!hidelabel" class="relative">
        <!-- The info icon sits beside the label, so a longer or larger label pushes it along instead of under it -->
        <span class="relative inline-flex items-center gap-1.5">{{ $t(text) }}
          <round-image v-if="infolink" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
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



      
      <span class="h-[2.5rem] overflow-hidden rounded-control bg-surface-3 hover:bg-surface-4 text-white p-2 flex items-center gap-2 relative min-w-0 w-full">
        <span class="flex min-w-0 gap-1">
          <span v-if="toggletabs" class="whitespace-nowrap font-bold px-1 shrink-0">{{ $t(toggleside === 'right' ? toggletabs[1] : toggletabs[0]) }}</span>
          <span v-if="selectedOptions.length" class="font-bold px-1 truncate min-w-0">{{ selectedlabel || (selectedOptions.length <= 3 ? selectedOptionsName.join(', ') : $t(':selected of :total selected', { selected: selectedOptions.length, total: values.length })) }}</span>
          <span v-else-if="placeholder" class="font-bold px-1 truncate min-w-0">{{ $t(placeholder) }}</span>
        </span>
        <basil-icon name="arrow" class="rotate-90 ml-auto shrink-0 text-xs transition-transform" :class="{ '-rotate-90': showOptions }"></basil-icon>
      </span>
    </div>
    <!-- I added a z-index here to make sure the dropdown was selectable, in case this breaks something later for you -->
    <!-- As wide as its longest option, lined up under the box -->
    <div v-if="showOptions" class="absolute mt-2 w-max max-w-[calc(100vw-2rem)] rounded overflow-hidden shadow-lg expandable-dropdown z-50" :class="compact ? 'left-0 !min-w-0' : 'left-2 !min-w-0'">
      <div class="space-y-2 p-2">
        <div v-if="toggletabs" class="flex justify-center">
          <tab-button :tab1text="$t(toggletabs[0])" :tab2text="$t(toggletabs[1])" :ignoreclick="true" :small="true"
            :overridedefaultside="toggleside"
            @tab-click="handleToggle"
          ></tab-button>
        </div>
        <!-- Heading checkboxes stand in for Select All -->
        <div v-if="!headings && !groupbyyear" class="whitespace-nowrap flex items-center gap-2 py-1">
          <input 
          type="checkbox" 
          id="select-all" 
          :checked="selectedOptions.length === selectableValues.length"
          @click="toggleAll"
          class="form-checkbox"
          >
          <label for="select-all" class="text-sm variable-text">{{ $t('Select All') }}</label>
        </div>
        <!-- pr-3 keeps the longest option clear of the scrollbar -->
        <div class="max-h-80 overflow-y-auto overflow-x-hidden [scrollbar-gutter:stable] !mt-0 pr-3">
          <template v-for="row in rows" :key="row.key">
          <div v-if="row.heading" class="whitespace-nowrap flex items-center gap-2 py-1" :class="[indentClass(row.depth), { 'opacity-40': !row.codes.some(code => isSelectable(code)) }]">
            <input
            type="checkbox"
            :id="row.key"
            :checked="row.codes.every(code => selectedOptions.includes(code))"
            :indeterminate="row.codes.some(code => selectedOptions.includes(code)) && !row.codes.every(code => selectedOptions.includes(code))"
            :disabled="!row.codes.some(code => isSelectable(code))"
            @click="toggleHeading(row)"
            class="form-checkbox"
            >
            <label :for="row.key" class="text-sm font-bold variable-text">{{ $t(row.name) }}</label>
          </div>
          <div v-else class="whitespace-nowrap flex items-center gap-2 py-1" :class="[indentClass(row.depth), { 'opacity-40': !isSelectable(row.value.code) }]">
            <input
            type="checkbox"
            :id="row.value.code"
            :value="row.value.code"
            v-model="selectedOptions"
            :disabled="!isSelectable(row.value.code)"
            class="form-checkbox"
            >
            <label :for="row.value.code" class="text-sm variable-text">{{ groupbyyear ? seasonLabel(row.value.name) : (row.value.label || $t(row.value.name)) }}</label>
            <span v-if="row.value.date_added" class="max-md:hidden ml-auto pl-4 pr-1 text-xs variable-text opacity-60" :title="$t('Added :date', { date: patchDate(row.value.date_added) })">{{ patchAge(row.value.date_added) }}</span>
          </div>
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
      rankInfo() {
        return RANK_INFO;
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
        return ['', 'pl-6', 'pl-12'][depth] || '';
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