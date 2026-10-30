<template>
  <div class="inline-flex flex-col md:flex-row bg-teal rounded-lg p-1 gap-1">
    <a
      v-for="side in ['left', 'right']"
      :key="side"
      :class="[staticClasses, 'rounded-md text-white', selectedSide === side ? 'bg-surface-1' : 'hover:bg-lteal']"
      :alt="side === 'left' ? tab1alt : tab2alt"
      role="button"
      @click="handleClick(side)"
    >
      {{ side === 'left' ? tab1text : tab2text }}
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
      small: Boolean,
    },
    data(){
      return {
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
      staticClasses() {
        return 'transition-colors inline-block cursor-pointer text-center ' + (this.small ? 'py-1 px-2 text-sm' : 'py-1.5 px-4');
      },
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