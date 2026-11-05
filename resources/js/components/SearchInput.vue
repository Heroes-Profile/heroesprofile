<template>
  <!-- The site's search box: magnifying glass inside a field. Use with v-model.
       collapsible: just the glass until clicked; closes again when left empty. -->
  <button v-if="collapsible && !open" type="button" class="text-teal hover:text-lteal px-1" :aria-label="placeholder" :title="placeholder" @click="expand">
    <i class="fas fa-magnifying-glass text-sm"></i>
  </button>
  <div v-else v-bind="$attrs" class="relative">
    <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-teal text-sm pointer-events-none"></i>
    <input
      ref="input"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="collapse"
      @keydown.esc="clear"
      type="text"
      :placeholder="placeholder"
      :aria-label="placeholder"
      class="w-full bg-field text-white rounded-control pl-9 pr-3 py-2 text-sm normal-case tracking-normal font-normal"
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
  },
  emits: ['update:modelValue'],
  data() {
    return {
      open: false,
    };
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
