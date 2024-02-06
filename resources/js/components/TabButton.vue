<template>
  <div class="flex flex-col md:flex-row">
    <a 
    :class="[
      staticClasses, ' rounded-l ', 
      {
      'bg-blue  hover:bg-lblue ring-inset ring-lblue ring-2' : selectedSide === 'left',
      ' bg-gray-dark hover:bg-gray-light' : selectedSide === 'right'
      }
    
      ]" 
      
      :alt="tab1alt"
      role="button"

      @click="handleClick('left')"
      >
     {{tab1text}}
    </a>
    <a 
    :class="[
      staticClasses, 'rounded-r', 
      {
       'bg-blue  hover:bg-lblue ring-inset ring-lblue ring-2' : selectedSide === 'right',
       ' bg-gray-dark hover:bg-gray-light' : selectedSide === 'left'
      }
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
      overridedefaultside: String,
    },
    data(){
      return {
        staticClasses: 'transition-colors text-white  py-2 px-4 text-lg inline-block',
        selectedSide: 'left'
      }
    },
    created(){
      if(this.overridedefaultside){
        this.selectedSide = this.overridedefaultside;
      }
    },
    mounted() {
    },
    computed: {
    
    },
    watch: {
    },
    methods: {
      handleClick(side) {
        this.selectedSide = side;
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

.selected{background-color: black;}
</style>