<template>
  <div :class="[
    'w-auto inline-block m-1 text-center min-w-[10em] flex items-center shadow-lg',
  {
   ' min-w-[15em]' : secondstat
  }
  ]">
    <div class="w-full" > 
      <h2 :class="[
        'bg-gray-light rounded-tl-xl text-blue px-2 py-1 text-xs text-center uppercase',
        {
          'rounded-tr-xl' : !secondstat
        }
        ]"> 
      {{ title }}
    </h2>

      <div :class="[
      'p-1 rounded-bl-xl text-center',  bgColor,
      {
        'rounded-br-xl' : !secondstat
      }
      ]" 
      >
        {{ value }}
      </div>
    </div>
     <div v-if="secondstat" class="border-l-2 border-gray-light w-full"> 
      <h2 class="bg-gray-light rounded-tr-xl text-blue px-2 py-1 text-xs text-center uppercase"> 
        {{secondcaption}}
      </h2>

      <div :class="[
      'p-1 rounded-br-xl text-center',  bgColor
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
      secondstat: {
        type: [String, Number]
      },
      secondtype: String,
      secondcaption: String,
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
      if(this.secondtype == 'mmrchange'){
        return this.secondstat > 0 ? `+${this.secondstat}` : this.secondstat;
      }
      else{
        return this.secondstat;
      }
      
    },
    },
    watch: {
    },
    methods: {
    }
  }
</script>