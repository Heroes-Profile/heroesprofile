<template>
  
    <round-image :size="size" :image="getTalentImage()" :showTooltip="true" :hovertextstyleoverride="true">
      <div class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
        <h2>{{ talent.title }}</h2>
        <p>{{ this.removeNumbers(talent.hotkey) }}</p>
        <p>{{ talent.description }}</p>
      </div>
    </round-image>
  
</template>

<script>
export default {
  name: 'TalentImageWrapper',
  components: {
  },
  props: {
    talent: Object,
    size: String,
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
  },
  watch: {
  },
  methods: {
    getTalentImage(){
      return `/images/talents/${this.talent.icon}`;
    },
    removeNumbers(str) {
      return str.replace(/\d+/g, '');
    }
  }
}
</script>