<template>
  <!-- The site's search box: magnifying glass inside a field, styled as the redesign's search (surface-3, grey placeholder,
       a teal edge while typing or once filled). Use with v-model.
       collapsible: just the glass until clicked; closes again when left empty. The open box is laid over the
       header, anchored to the glass and reaching left, so a table column keeps its width while searching. -->
  <!-- centered: the open box is placed by the nearest positioned ancestor (the header cell's content), centred over it -->
  <span v-if="collapsible" :class="['inline-flex items-center', { relative: !centered }]">
    <button type="button" :class="['text-teal hover:text-lteal px-1', { invisible: isOpen }]" :aria-label="$t(placeholder)" :title="$t(placeholder)" :tabindex="isOpen ? -1 : null" @click="expand">
      <i class="fas fa-magnifying-glass text-sm"></i>
    </button>
    <div v-if="isOpen" v-bind="$attrs" :class="['absolute top-1/2 -translate-y-1/2 z-20', centered ? 'left-1/2 -translate-x-1/2' : 'right-0']">
      <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-teal text-sm pointer-events-none"></i>
      <input
        ref="input"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="collapse"
        @keydown.esc="clear"
        type="text"
        :placeholder="$t(placeholder)"
        :aria-label="$t(placeholder)"
        :class="['w-full !bg-surface-3 text-white placeholder:text-subtext rounded-control pl-9 pr-3 py-2 text-sm normal-case font-normal shadow-[0_0_0.5rem_rgba(0,0,0,0.4)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal', { 'ring-2 ring-inset ring-teal': modelValue }]"
      />
    </div>
  </span>
  <div v-else v-bind="$attrs" class="relative">
    <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-teal text-sm pointer-events-none"></i>
    <input
      ref="input"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="collapse"
      @keydown.esc="clear"
      type="text"
      :placeholder="$t(placeholder)"
      :aria-label="$t(placeholder)"
      :class="['w-full !bg-surface-3 text-white placeholder:text-subtext rounded-control pl-9 pr-3 py-2 text-sm normal-case font-normal focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal', { 'ring-2 ring-inset ring-teal': modelValue }]"
    />
  </div>
</template>

<script>
export default {
  name: 'SearchInput',
  // Width and spacing classes go on the open box, not the collapsed glass
  inheritAttrs: false,
  props: {
    modelValue: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: 'Search',
    },
    collapsible: Boolean,
    // Collapsible only: open centred over a positioned ancestor rather than reaching left from the glass
    centered: Boolean,
  },
  emits: ['update:modelValue'],
  data() {
    return {
      open: false,
    };
  },
  computed: {
    // The collapsible box stays open while it holds a search
    isOpen() {
      return this.open || !!this.modelValue;
    },
  },
  methods: {
    expand() {
      this.open = true;
      this.$nextTick(() => this.$refs.input.focus());
    },
    collapse() {
      if (this.collapsible && !this.modelValue) {
        this.open = false;
      }
    },
    clear() {
      this.$emit('update:modelValue', '');
      this.$refs.input.blur();
    },
  },
};
</script>
