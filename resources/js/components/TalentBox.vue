<template>
  <div  class="relative group flex items-center mt-auto mb-auto " @mouseover="showTooltip = true" @mouseleave="showTooltip = false">
    <img class="card-img-top relative hover:opacity-75 w-10 h-10 rounded-full" :src="getTalentImage()" :alt="talent.name">

     <div v-if="showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      <h2>{{ talent.title }}</h2>
      <p>{{ this.removeNumbers(talent.hotkey) }}</p>
      <p>{{ talent.description }}</p>
    </div>
    
  </div>
</template>

<script>
export default {
  name: 'TalentBox',
  components: {
  },
  props: {

    talent: Object,
  },
  data(){
    return {
       showTooltip: false,
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