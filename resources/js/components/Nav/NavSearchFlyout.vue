<template>
  <div class="nav-flyout-wrapper" @mouseenter="open = true" @mouseleave="open = false">
    <a :href="headingUrl || baseUrl + query" class="flex justify-between items-center">{{ $t(label) }} <span class="nav-flyout-arrow ml-2">›</span></a>

    <div v-show="open" class="nav-flyout">
      <div class="nav-dropdown-inner-wrapper !pt-0 rounded-b rounded-r flex flex-col" style="max-height: 26.25rem;">
        <div class="px-2 pt-2 pb-1 ">
          <input
            v-model="search"
            type="text"
            :placeholder="$t('Search')"
            class="w-full px-2 py-2 text-sm bg-field text-white rounded-control focus:outline-none focus:border-teal text-sm"
            @click.stop
          />
        </div>
        <div class="nav-flyout-scroll overflow-y-auto flex-1">
          <a v-if="allLabel" :href="baseUrl + query" class="block px-4 py-2   hover:bg-lighten text-sm">{{ $t(allLabel) }}</a>
          <a
            v-for="item in filteredItems"
            :key="item.id"
            :href="baseUrl + '/' + encodeURIComponent(item.name) + query"
            class="block px-4 py-2  hover:bg-lighten text-sm whitespace-nowrap"
          >{{ displayName(item) }}</a>
          <div v-if="filteredItems.length === 0" class="px-4 py-2 text-xs text-gray-400">{{ $t('No results found') }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NavSearchFlyout',
  props: {
    battletag: { type: String, default: '' },
    blizzId: { type: Number, default: 0 },
    region: { type: Number, default: 0 },
    // Either a list name from window.navLists (set once in the layout) or the items themselves.
    list: { type: String, default: '' },
    items: { type: Array, default: () => [] },
    label: { type: String, default: '' },
    subpath: { type: String, default: '' },
    allLabel: { type: String, default: '' },
    baseUrlOverride: { type: String, default: '' },
    // Added to every link, e.g. HI's ?tournament=
    query: { type: String, default: '' },
    // For lists with no "all" page: where the heading goes instead (leave allLabel empty to drop that row)
    headingUrl: { type: String, default: '' },
  },
  data() {
    return {
      open: false,
      search: '',
    };
  },
  methods: {
    // Items are heroes or maps; a name is only ever one of the two.
    displayName(item) {
      return this.$mapName(this.$heroName(item.name));
    },
    normalize(str) {
      try {
        return str.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
      } catch {
        return str.toLowerCase();
      }
    },
  },
  computed: {
    baseUrl() {
      if (this.baseUrlOverride) return this.baseUrlOverride;
      return `/Player/${this.battletag}/${this.blizzId}/${this.region}/${this.subpath}`;
    },
    sourceItems() {
      if (this.list) return (window.navLists && window.navLists[this.list]) || [];
      return this.items;
    },
    filteredItems() {
      const q = this.normalize(this.search.trim());
      if (!q) return this.sourceItems;
      return this.sourceItems.filter(item => this.normalize(item.name).includes(q) || this.normalize(this.displayName(item)).includes(q));
    },
  },
};
</script>
