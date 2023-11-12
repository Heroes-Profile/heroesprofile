<template>
    
    <round-image v-if="talent" :size="size" :image="getTalentImage()">
      <h2>{{ talent.title }}</h2>
      <p>{{ this.removeNumbers(talent.hotkey) }}</p>
      <p>{{ talent.description }}</p>
    </round-image>
    <round-image v-else-if="talent != 0 && talent" :size="size" :image="'/images/talents/no-image.png'">
      <h2>{{ "Talent removed or changed" }}</h2>
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