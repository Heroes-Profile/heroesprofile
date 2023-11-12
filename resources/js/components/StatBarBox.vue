<template>
  <div :class="['w-auto inline-block m-1  items-center   min-w-[5em] ',
  {
      'w-full': size === 'big',
      'w-[10em]': size != 'big',
      'text-right items-right' : align === 'right',
      'text-left' : align === 'left',
      'text-center' : align === 'center'
    }
  ]">
       <h2 class="rounded  px-2 py-1 text-sm  uppercase "> {{ title }}</h2>
    
    <div :class=" ['bg-gray-light  rounded rounded-l-lg ring-inset ring-[1px] ring-gray-medium ',
     {
      'w-full': size === 'big',
      'w-[10em]': size != 'big',
      'w-full text-left': size === 'full',
      'text-right ml-auto' : align === 'right',
      'text-left' : align === 'left',
      'text-center' : align === 'center'


    }
    ]">
      <div :class="[
        'stat-bar bg-blue rounded-l  ',
        bgColor,
        {
          'w-full': size === 'full',
          'border-r-2 border-black': value != 0 && value != 100,
          'rounded-r': value == 100
        }
        
        ]"
          :style="{ width: this.value +'%' }">
        <span :class="[
        ' px-2 flex items-center text-white ',
        {
          'py-2' : size === 'big',
        }
        ]"><span v-if="displaytext">{{displaytext}}</span><span v-else>{{ value }}<span class="text-xs">%</span></span></span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatBarBox',
  components: {
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
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
  },
  computed: {
    barWidth(){
      return "w-[${this.value}%]"

    },
    bgColor() {

        if(this.color){
          return `bg-${this.color}`;
        }
        else{
          return 'bg-blue';
        }
      },
  },
  watch: {
  },
  methods: {
  }
}
</script>