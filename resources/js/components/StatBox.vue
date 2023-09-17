<template>
  <div class="w-auto inline-block m-1 text-center min-w-[10em] flex justify-items-stretch">
    <div class="w-full" > <h2 class="bg-gray-light rounded-t text-blue px-2 py-1 text-sm text-center uppercase"> {{ title }}</h2>

      <div :class="[
      'p-1 rounded-b text-center',  bgColor
      ]" 
      >
        {{ value }}
      </div>
    </div>
     <div v-if="mmrchange" class="border-l-2 border-gray-light w-full"> <h2 class="bg-gray-light rounded-t text-blue px-2 py-1 text-sm text-center uppercase"> MMR Change</h2>

      <div :class="[
      'p-1 rounded-b text-center',  bgColor
      ]" >
     {{ formattedChange }}
      </div>
    </div>

  </div>
</template>

<script>
  export default {
    name: 'StatBox',
    components: {
    },
    props: {
      title: String,
      mmrchange: Number,
      color: String,
      winner: Boolean,
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
       bgColor() {

        if(this.color){
          return `bg-${this.color}`;
        }
        else{
          return 'bg-blue';
        }
        
    },
    formattedChange() {
      return this.mmrchange > 0 ? `+${this.mmrchange}` : this.mmrchange;
    },
    },
    watch: {
    },
    methods: {
    }
  }
</script>