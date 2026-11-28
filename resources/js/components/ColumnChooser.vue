<template>
  <!-- The "Choose columns" button and its panel: tick columns by group, then Apply -->
  <!-- Raised over its dimmed backdrop while open -->
  <div :class="['relative', { 'z-50': open }]">
    <!-- Dims the page behind the open panel so it stands off the table; a click on it closes the panel -->
    <div v-if="open" class="fixed inset-0 bg-black/50" @click="open = false"></div>
    <custom-button class="relative min-w-[11.75rem]" @click="toggle" :text="open ? 'Hide columns' : 'Choose columns'" :alt="open ? 'Hide columns' : 'Choose columns'" :ignoreclick="true"></custom-button>

    <!-- Phones: centred over the backdrop, since the button can sit anywhere in the header -->
    <div v-if="open" class="md:absolute md:right-0 md:top-full md:mt-[1.125rem] max-md:fixed max-md:inset-x-4 max-md:top-1/2 max-md:-translate-y-1/2 max-md:max-h-[85dvh] max-md:overflow-y-auto z-50 md:w-[min(77.5rem,calc(100vw-2rem))] bg-surface-4 border border-surface-3 rounded shadow-2xl p-4 md:p-[1.5625rem] flex flex-col gap-5 text-sm">
      <div class="flex justify-between gap-4">
        <div>
          <h4 class="text-xl font-bold leading-tight">{{ $t('Table columns') }}</h4>
          <p class="text-sm text-subtext mt-1">{{ $t(':shown of :total columns shown', { shown: draftCount, total: stats.length }) }} · {{ $t(note) }}</p>
        </div>
        <button type="button" class="self-start w-9 h-9 flex items-center justify-center rounded hover:bg-surface-3 transition-colors" :aria-label="$t('Close')" @click="open = false"><basil-icon name="cross" class="text-xl"></basil-icon></button>
      </div>

      <div class="flex flex-wrap items-center gap-4">
        <search-input v-model="search" :placeholder="$t('Search columns...')" class="w-full sm:w-[18.125rem]"></search-input>
        <tab-button :small="true" :tabs="groupTabs" :key="groupTabs.map(tab => tab.text).join()" :ignoreclick="true" :overridedefaultside="group" @tab-click="group = $event"></tab-button>
      </div>

      <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-x-2 gap-y-1">
        <label v-for="stat in shownStats" :key="stat.value"
          :class="['flex items-center gap-3 px-2.5 h-[2.1875rem] rounded cursor-pointer transition-colors', draft[stat.value] ? 'bg-surface-3 font-medium text-white' : 'text-subtext hover:bg-surface-2', { 'opacity-40 cursor-not-allowed': locked(stat) }]">
          <input type="checkbox" class="form-checkbox shrink-0" :checked="draft[stat.value]" :disabled="locked(stat)" @change="pick(stat, $event.target.checked)">
          <span class="truncate">{{ $t(label(stat)) }}</span>
        </label>
        <p v-if="!shownStats.length" class="text-subtext col-span-full">{{ $t('No columns match ":search".', { search: search }) }}</p>
      </div>
      <p v-if="replaceAtCap" class="text-xs text-subtext">{{ $t('Up to :max columns fit on a phone. Turning on another turns off the one you turned on last.', { max: max }) }}</p>
      <p v-else-if="atCap" class="text-xs text-subtext">{{ $t('Up to :max columns can be shown at once.', { max: max }) }}</p>

      <div class="h-px bg-gray-dark"></div>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex gap-5 text-sm">
          <button type="button" class="hover:underline" @click="reset">{{ $t('Reset to default') }}</button>
          <button type="button" class="hover:underline" @click="clear">{{ $t('Clear all') }}</button>
        </div>
        <custom-button class="min-w-[7.5rem]" @click="apply" text="Apply" alt="Apply" color="teal" :ignoreclick="true"></custom-button>
      </div>
    </div>
  </div>
</template>

<script>
// Columns are grouped by name: "Avg ..." under Averages, "Max ..." under Max, the rest under Totals
const GROUPS = [
  { key: 'avg', text: 'Averages', prefix: 'Avg ' },
  { key: 'max', text: 'Max', prefix: 'Max ' },
  { key: 'total', text: 'Totals', prefix: '' },
];

export default {
  name: 'ColumnChooser',
  props: {
    // The page's columns: [{ name, value, selected }]; Apply emits the ticked values
    stats: { type: Array, required: true },
    max: { type: Number, default: 15 },
    // Which columns can't be turned off
    note: { type: String, default: 'Map and Win Rate are always visible' },
    // At the limit, turning a column on turns off the one turned on last, instead of locking the rest (phones)
    replaceAtCap: { type: Boolean, default: false },
    // The shown columns in the order they were turned on, for replaceAtCap; otherwise the stats' own order
    selectedOrder: { type: Array, default: null },
  },
  emits: ['apply'],
  data() {
    return {
      open: false,
      search: '',
      group: 'avg',
      draft: {},
      // Ticked columns, oldest first
      order: [],
      // The columns the page started with, for Reset to default
      defaults: this.stats.filter(stat => stat.selected).map(stat => stat.value),
    };
  },
  computed: {
    draftCount() {
      return Object.values(this.draft).filter(v => v).length;
    },
    atCap() {
      return this.draftCount >= this.max;
    },
    groupTabs() {
      return GROUPS.map(group => ({ key: group.key, text: `${this.$t(group.text)} · ${this.stats.filter(stat => this.groupOf(stat) === group.key && this.draft[stat.value]).length}` }));
    },
    // Searching looks through every group
    shownStats() {
      const search = this.search.trim().toLowerCase();
      if (search) {
        return this.stats.filter(stat => stat.name.toLowerCase().includes(search));
      }
      return this.stats.filter(stat => this.groupOf(stat) === this.group);
    },
  },
  methods: {
    locked(stat) {
      return this.atCap && !this.draft[stat.value] && !this.replaceAtCap;
    },
    pick(stat, checked) {
      if (checked) {
        if (this.replaceAtCap && this.draftCount >= this.max) {
          this.draft[this.order.pop()] = false;
        }
        this.order.push(stat.value);
      } else {
        this.order = this.order.filter(value => value !== stat.value);
      }
      this.draft[stat.value] = checked;
    },
    groupOf(stat) {
      return stat.name.startsWith('Avg ') ? 'avg' : stat.name.startsWith('Max ') ? 'max' : 'total';
    },
    // Inside its own group a column drops the group's word; search results keep it
    label(stat) {
      if (this.search.trim()) {
        return stat.name;
      }
      const prefix = GROUPS.find(group => group.key === this.groupOf(stat)).prefix;
      return prefix ? stat.name.slice(prefix.length) : stat.name;
    },
    toggle() {
      this.open = !this.open;
      if (this.open) {
        this.draft = Object.fromEntries(this.stats.map(stat => [stat.value, stat.selected]));
        this.order = this.selectedOrder ? [...this.selectedOrder] : this.stats.filter(stat => stat.selected).map(stat => stat.value);
        this.search = '';
        // Opens on the group holding the newest pick, so a phone's two columns (e.g. Games, KDA) are in view
        const newest = this.stats.find(stat => stat.value === this.order[this.order.length - 1]);
        if (this.replaceAtCap && newest) {
          this.group = this.groupOf(newest);
        }
      }
    },
    reset() {
      this.draft = Object.fromEntries(this.stats.map(stat => [stat.value, this.defaults.includes(stat.value)]));
      this.order = [...this.defaults];
    },
    clear() {
      this.draft = Object.fromEntries(this.stats.map(stat => [stat.value, false]));
      this.order = [];
    },
    apply() {
      this.$emit('apply', this.replaceAtCap ? [...this.order] : this.stats.filter(stat => this.draft[stat.value]).map(stat => stat.value));
      this.open = false;
    },
  },
};
</script>
