<template>
  <div class="bg-lighten p-6 mb-8">
    <div
      @click="toggle"
      class="flex items-center justify-between gap-3 cursor-pointer"
      :class="collapsed ? '' : (tight ? 'mb-1' : 'mb-4')"
    >
      <h2 class="text-lg">{{ title }}</h2>
      <button type="button" class="text-sm text-gray-medium underline" :aria-expanded="!collapsed" @click.stop="toggle">
        {{ collapsed ? 'Show' : 'Hide' }}
      </button>
    </div>

    <div v-show="!collapsed">
      <slot></slot>
    </div>
  </div>
</template>

<script>
const STORAGE_PREFIX = 'hp-admin-card:';

export default {
  name: 'ApiAdminCard',
  props: {
    title: String,
    // Remembers the card's state in this browser. Shared keys share state.
    storageKey: {
      type: String,
      required: true,
    },
    // Heading sits close to a description line rather than straight content.
    tight: Boolean,
  },
  data(){
    return {
      collapsed: false,
    }
  },
  created(){
    try {
      this.collapsed = localStorage.getItem(STORAGE_PREFIX + this.storageKey) === '1';
    } catch (error) {
      // Storage blocked; every card starts open.
    }
  },
  methods: {
    toggle(){
      this.collapsed = !this.collapsed;

      try {
        localStorage.setItem(STORAGE_PREFIX + this.storageKey, this.collapsed ? '1' : '0');
      } catch (error) {
        // As above.
      }
    },
  },
}
</script>
