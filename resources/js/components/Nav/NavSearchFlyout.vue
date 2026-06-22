<template>
  <div class="nav-flyout-wrapper" @mouseenter="open = true" @mouseleave="open = false">
    <a :href="baseUrl" class="flex justify-between items-center">{{ label }} <span class="nav-flyout-arrow ml-4">›</span></a>

    <div v-show="open" class="nav-flyout">
      <div class="nav-dropdown-inner-wrapper rounded-b-lg rounded-r-lg flex flex-col" style="max-height: 420px;">
        <div class="px-2 pt-2 pb-1 border-b border-darken">
          <input
            v-model="search"
            type="text"
            placeholder="Search"
            class="w-full px-2 py-1 text-xs bg-gray-dark text-white border border-white/20 rounded focus:outline-none focus:border-teal"
            @click.stop
          />
        </div>
        <div class="overflow-y-auto flex-1">
          <a :href="baseUrl" class="block px-4 py-2 border-b border-darken hover:bg-lighten text-sm">{{ allLabel }}</a>
          <a
            v-for="item in filteredItems"
            :key="item.id"
            :href="baseUrl + '/' + encodeURIComponent(item.name)"
            class="block px-4 py-2 border-b border-darken hover:bg-lighten text-sm whitespace-nowrap"
          >{{ item.name }}</a>
          <div v-if="filteredItems.length === 0" class="px-4 py-2 text-xs text-gray-400">No results found</div>
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
    items: { type: Array, required: true },
    label: { type: String, default: '' },
    subpath: { type: String, default: '' },
    allLabel: { type: String, default: '' },
    baseUrlOverride: { type: String, default: '' },
  },
  data() {
    return {
      open: false,
      search: '',
    };
  },
  methods: {
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
    filteredItems() {
      const q = this.normalize(this.search.trim());
      if (!q) return this.items;
      return this.items.filter(item => this.normalize(item.name).includes(q));
    },
  },
};
</script>
