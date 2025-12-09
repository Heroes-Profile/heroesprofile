<template>
    <div :class="{ 'grayscale': isRemoved }">
      <round-image v-if="talent" :size="size" popupsize="large" mobileClick="true" :image="getTalentImage()">
        <h2 v-if="isRemoved" class="text-yellow"><b>Talent no longer available</b></h2>
        <h2><b>{{ displayTitle }}</b></h2>
        <p>{{ this.removeNumbers(talent.hotkey) }}</p>
        <p>{{ talent.description }}</p>
      </round-image>
      <round-image v-else-if="talent != 0 && talent" :size="size" :image="'/images/talents/no-image.png'" tooltipdir="right">
        <h2>{{ "Talent removed or changed" }}</h2>
      </round-image>
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
    mobileClick: true
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
    isRemoved() {
      return this.talent && this.talent.status === 'removed';
    },
    displayTitle() {
      if (this.talent && this.talent.title && this.talent.title !== '') {
        return this.talent.title;
      }
      return this.talent && this.talent.talent_name ? this.talent.talent_name : '';
    }
  },
  watch: {
  },
  methods: {
    getTalentImage(){
      if (!this.talent.icon || this.talent.icon === '') {
        return '/images/talents/no-image.png';
      }
      return `/images/talents/${this.talent.icon}`;
    },
    removeNumbers(str) {
      return str.replace(/\d+/g, '');
    }
  }
}
</script>

<style scoped>
.grayscale {
  filter: grayscale(100%);
}
</style>