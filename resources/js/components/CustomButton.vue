<template>
    <a 
    :class="[
      staticClasses, 
      disabled ? 'bg-gray' : colorClass, 
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
     <span v-if="!loading">{{ text }}</span>
      <span v-else class="loading-text">
        <span v-for="(letter, index) in loadingText" :key="index" :style="{ animationDelay: index * 0.5 + 's' }">{{ letter }}</span>
      </span>
    </a>
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
      ignoreclick: Boolean,
      loading: false,
    },
    data(){
      return {
        staticClasses: 'transition-colors text-white rounded text-center',

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
      },
      loadingText() {
        return this.loading ? 'Loading...............'.split('') : [];
      },
    },
    watch: {
    },
    methods: {
      handleClick() {
        if (this.ignoreclick || this.disabled) return;

        if (this.targetblank) {
          window.open(this.href, '_blank');
        } else {
          window.location.href = this.href;
        }
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