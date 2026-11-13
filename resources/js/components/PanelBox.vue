<template>
  <!-- A filter panel: on mobile it hides behind Show Filters and opens full screen, like Filters.vue -->
  <div v-if="filters">
    <button class="md:hidden p-2 bg-blue w-full" @click="showNav = !showNav">Show Filters</button>
    <div v-show="showNav" :class="[inner ? 'bg-surface-1' : 'bg-surface', 'md:rounded p-4 md:p-6 flex flex-col gap-4 min-w-0 max-md:fixed max-md:inset-0 max-md:z-50 max-md:h-[100dvh] max-md:overflow-y-auto']" @click="closeOnApply">
      <button class="md:hidden ml-auto text-2xl min-w-[2.75rem] min-h-[2.75rem]" aria-label="Close filters" @click="showNav = false">x</button>
      <slot></slot>
    </div>
  </div>
  <div v-else :class="[inner ? 'bg-surface-1' : 'bg-surface', 'rounded p-4 md:p-6 flex flex-col gap-4 min-w-0']">
    <template v-if="title">
      <div class="flex flex-wrap justify-between items-baseline gap-x-4 gap-y-1">
        <h3 class="text-2xl font-bold flex items-center gap-2">{{ $t(title) }}<slot name="title"></slot></h3>
        <a v-if="linkHref" class="text-header whitespace-nowrap hover:underline" :href="linkHref">{{ linkText }}<i class="fas fa-chevron-right text-sm ml-3"></i></a>
        <slot name="right"></slot>
        <img v-if="loading" class="logo-spin h-7 w-auto shrink-0 ml-auto self-center" src="/images/logo/heroesprofilelogo.png" alt="Loading" />
      </div>
      <div v-if="divider" class="h-px bg-gray-dark"></div>
    </template>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: 'PanelBox',
  props: {
    title: String,
    linkText: String,
    linkHref: String,
    // A card inside another panel, one shade lighter
    inner: Boolean,
    // A spinning logo in the header while the panel's data loads
    loading: Boolean,
    divider: {
      type: Boolean,
      default: true,
    },
    // A filter row; its apply button carries data-filter-apply so the mobile panel closes on it
    filters: Boolean,
  },
  data() {
    return {
      showNav: true,
      desktopView: true,
    };
  },
  created() {
    if (this.filters) {
      this.desktopView = !this.isMobileView();
      this.showNav = this.desktopView;
      window.addEventListener('resize', this.checkScreenWidth);
    }
  },
  unmounted() {
    if (this.filters) {
      window.removeEventListener('resize', this.checkScreenWidth);
      if (this.showNav && this.isMobileView()) {
        document.body.style.overflow = '';
      }
    }
  },
  watch: {
    // The mobile panel covers the screen, so the page behind it shouldn't scroll
    showNav(open) {
      if (this.filters && this.isMobileView()) {
        document.body.style.overflow = open ? 'hidden' : '';
      }
    },
  },
  methods: {
    isMobileView() {
      // Same breakpoint as Tailwind's md
      return !window.matchMedia('(min-width: 1024px)').matches;
    },
    checkScreenWidth() {
      const desktop = !this.isMobileView();
      if (desktop) {
        this.showNav = true;
      } else if (this.desktopView) {
        this.showNav = false;
      }
      this.desktopView = desktop;
    },
    closeOnApply(event) {
      if (this.isMobileView() && event.target.closest('[data-filter-apply]')) {
        this.showNav = false;
      }
    },
  },
};
</script>
