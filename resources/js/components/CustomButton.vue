<template>
  <div>
    <a 
    :class="[
      staticClasses, 
      disabled ? 'bg-gray-400' : colorClass, 
      { 
        block: size === 'big', 
        'py-4': size === 'big', 
        'text-lg': size === 'big', 
        'inline-block': size !== 'big', 
        'py-2 px-4' : size !== 'small', 
        'py-1 px-4' : size === 'small',
        'text-sm' : size === 'small',
        'cursor-not-allowed': disabled
      }
      ]" 
      :href="disabled ? '#' : href" 
      :alt="alt"
      role="button"
      @click.prevent="handleClick"
      >
      {{text}}
    </a>
  </div>
</template>

<script>
  export default {

    name: 'CustomButton',
    components: {
    },
    props: {
      href: String,
      text: String,
      alt: String,
      size: String,
      color: {
        type: String,
      default: 'blue'
      },
      disabled: Boolean,
      targetblank: Boolean,
    },
    data(){
      return {
        staticClasses: 'transition-colors text-white rounded',

      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
      colorClass() {
        const colorToClassMap = {
          blue: 'bg-blue hover:bg-lblue',
          red: 'bg-red hover:bg-lred',
          teal: 'bg-teal hover:bg-lteal',
          yellow: 'bg-yellow hover:bg-lyellow'
        }
        const defaultClass = 'bg-blue hover:bg-lblue';
        return colorToClassMap[this.color] || defaultClass;
      }
    },
    watch: {
    },
    methods: {
     handleClick() {
      if(!this.disabled) {
        if(this.targetblank){
          window.open(this.href, '_blank');
        }else{
          window.location.href = this.href;
        }
      }
    }
  }
}
</script>
