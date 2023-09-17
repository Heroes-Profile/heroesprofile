<template>
  <div class="w-auto inline-block m-1 text-center min-w-[5em]">
       <h2 class="rounded  px-2 py-1 text-sm text-center uppercase"> {{ title }}</h2>
    
    <div class=" bg-gray-light  rounded rounded-l-lg w-[10em]">
      <div class="stat-bar bg-blue rounded-l border-r-2 border-black" :style="{ width: this.value +'%' }">
        {{ value }}
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

    }
  },
  watch: {
  },
  methods: {
  }
}
</script>