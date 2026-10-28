<template>
  <div :class="['inline-block m-1 min-w-[5em]', widthClass,
  {
      'text-right' : align === 'right',
      'text-left' : align === 'left',
      'text-center' : align === 'center'
    }
  ]">
    <div v-if="title" class="text-sm text-header mb-1.5">{{ title }}</div>
    <stat-bar :value="value" :display-text="displaytext ? displaytext : null"
      :suffix="displaytext ? '' : '%'" suffix-class="text-xs"
      :color="color" :size="size === 'big' ? 'big' : null"></stat-bar>
  </div>
</template>

<script>
import StatBar from './StatBar.vue';

export default {
  name: 'StatBarBox',
  components: {
    StatBar,
  },
  props: {
    title: {
      type: [String, Number]
    },
    color: String,
    size: String,
    align: 'center',
    value: {
      type: [String, Number]
    },
    displaytext: {
      type: [String, Number]
    },
  },
  computed: {
    widthClass() {
      return this.size === 'big' || this.size === 'full' ? 'w-full max-md:mx-0' : 'w-[10em]';
    },
  },
}
</script>
