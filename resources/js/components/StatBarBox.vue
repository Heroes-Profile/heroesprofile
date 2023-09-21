<template>
  <div :class="['w-auto inline-block m-1 text-center min-w-[5em] ',
  {
      'w-full': size === 'big',
      'w-[10em]': size != 'big',
    }
  ]">
       <h2 class="rounded  px-2 py-1 text-sm text-center uppercase"> {{ title }}</h2>
    
    <div :class=" ['bg-gray-light  rounded rounded-l-lg ',
     {
      'w-full': size === 'big',
      'w-[10em]': size != 'big',

    }
    ]">
      <div :class="[
        'stat-bar bg-blue rounded-l border-r-2 border-black ',
        bgColor,
        
        ]"
          :style="{ width: this.value +'%' }">
        <span :class="[
        ' px-2 flex items-center',
        {
          'py-2' : size === 'big',
        }
        ]">{{ value }}<span class="text-xs">%</span></span>
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
    title: String,
    color: String,
    size: String,
    value: {
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