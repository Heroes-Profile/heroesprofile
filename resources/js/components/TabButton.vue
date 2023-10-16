<template>
  <div class="">
    <a 
    :class="[
      staticClasses, 'bg-blue rounded-l  hover:bg-lblue', 
    
      ]" 
      
      :alt="tab1alt"
      role="button"

      @click="handleClick('left')"
      >
     {{tab1text}}
    </a>
    <a 
    :class="[
      staticClasses, 'bg-teal hover:bg-lteal rounded-r' 
    
      ]" 
     
      :alt="tab2alt"
      role="button"
       @click="handleClick('right')"
     
      >
     {{tab2text}}
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
      
      ignoreclick: Boolean,
      loading: false,
    },
    data(){
      return {
        staticClasses: 'transition-colors text-white  py-2 px-4 text-lg inline-block',

      }
    },
    created(){

    },
    mounted() {
    },
    computed: {
      
      
    },
    watch: {
    },
    methods: {
      handleClick(side) {
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
</style>