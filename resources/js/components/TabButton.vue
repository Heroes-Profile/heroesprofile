<template>
  <!-- select-none: clicking tabs quickly mustn't highlight their labels -->
  <!-- Wraps onto more rows when the tabs don't fit (a page's section nav on a phone) -->
  <div class="inline-flex flex-wrap justify-center bg-teal rounded p-1 gap-1 select-none">
    <a
      v-for="side in sides"
      :key="side"
      :class="[staticClasses, 'rounded-control text-white', selectedSide === side ? 'bg-surface-1' : disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-lteal']"
      :alt="tabs ? labels[side] : { left: tab1alt, right: tab2alt, third: tab3alt }[side]"
      role="button"
      @click="handleClick(side)"
    >
      {{ $t(tabs ? labels[side] : { left: tab1text, right: tab2text, third: tab3text }[side]) }}
    </a>
  </div>
</template>

<script>
  export default {

    name: 'TabButton',
    components: {
    },
    props: {
     
      tab1text: String,
      tab1alt: String,
      
      tab2text: String,
      tab2alt: String,

      // Optional third tab, after the other two; it reports its side as 'third'
      tab3text: String,
      tab3alt: String,

      // Any number of tabs instead of tab1-3: [{ key, text }]; a click reports the tab's key
      tabs: Array,

      ignoreclick: Boolean,
      loading: false,
      overridedefaultside: String,
      small: Boolean,
      // No tab highlighted until one is picked (a page's section nav, before a section is open)
      noDefault: Boolean,
      // Only the selected tab can be clicked (while its section loads)
      disabled: Boolean,
    },
    data(){
      return {
        selectedSide: 'left'
      }
    },
    created(){
      if(this.overridedefaultside){
        this.selectedSide = this.overridedefaultside;
      } else if (this.noDefault) {
        this.selectedSide = null;
      } else if (this.tabs && this.tabs.length) {
        this.selectedSide = this.tabs[0].key;
      }
    },
    mounted() {
    },
    computed: {
      sides() {
        if (this.tabs) {
          return this.tabs.map(tab => tab.key);
        }
        return this.tab3text ? ['left', 'right', 'third'] : ['left', 'right'];
      },
      labels() {
        return Object.fromEntries((this.tabs || []).map(tab => [tab.key, tab.text]));
      },
      staticClasses() {
        // The full size is the pager's height (pagination-bar), so the two line up beside each other; a wrapped label can grow it
        return 'transition-colors cursor-pointer text-center ' + (this.small ? 'inline-block py-1 px-2 text-sm' : 'inline-flex items-center justify-center min-h-9 px-4');
      },
    },
    watch: {
      // The page can pick the tab itself too (the esports section cards)
      overridedefaultside(side) {
        if (side || this.noDefault) {
          this.selectedSide = side || null;
        }
      },
    },
    methods: {
      handleClick(side) {
        if (this.disabled && side !== this.selectedSide) {
          return;
        }
        this.selectedSide = side;
        this.$emit('tab-click', side);
      }
  }
}
</script>

<style>
.loading-text span {
  display: inline-block;
  opacity: 0;
  transform: translateY(-1em);
  animation: appear 0.5s forwards;
}

@keyframes appear {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.selected{background-color: black;}
</style>