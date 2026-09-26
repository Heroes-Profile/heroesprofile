<template>
  <div :class="['inline-block m-1 items-center min-w-[5em]', widthClass,
  {
      'text-right items-right' : align === 'right',
      'text-left' : align === 'left',
      'text-center' : align === 'center'
    }
  ]">
       <h2 class="rounded  px-2 py-1 text-sm  uppercase "> {{ title }}</h2>
    
    <stat-bar :value="value" :display-text="displaytext ? displaytext : value"
      :suffix="displaytext ? '' : '%'" suffix-class="text-xs"
      :color="color" :divider="true"
      :class="[
        'rounded-l-lg',
        widthClass,
        {
          'text-left': align === 'left' || (size === 'full' && align !== 'right' && align !== 'center'),
          'text-right ml-auto': align === 'right',
          'text-center': align === 'center',
          'py-2': size === 'big',
        }
      ]"></stat-bar>
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
      return this.size === 'big' || this.size === 'full' ? 'w-full' : 'w-[10em]';
    },
  },
}
</script>
