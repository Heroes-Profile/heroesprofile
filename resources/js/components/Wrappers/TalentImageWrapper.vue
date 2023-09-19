<template>
  <div>
    <round-image :size="size" :title="talent.title" :image="getTalentImage()" :tooltiptex="getToolTip()" :showTooltip="true"></round-image>
  </div>
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
    getToolTip(){
      /*
     <div v-if="showTooltip" class="absolute hidden bottom-11 -left-24  bg-gray-dark  text-xs p-1  group-hover:block  text-white z-50 drop-shadow-md w-60 rounded-md px-2 text-center">
      <h2>{{ talent.title }}</h2>
      <p>{{ this.removeNumbers(talent.hotkey) }}</p>
      <p>{{ talent.description }}</p>
    </div>
      */
      //I dont know how to send an html object through, maybe lok
      return "Temp";
    },
    removeNumbers(str) {
      return str.replace(/\d+/g, '');
    }
  }
}
</script>