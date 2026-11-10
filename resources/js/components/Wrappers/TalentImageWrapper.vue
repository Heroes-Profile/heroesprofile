<template>
    <div :class="{ 'grayscale': isRemoved }">
      <round-image v-if="talent" :size="size" popupsize="large" mobileClick="true" :image="getTalentImage()" :title="displayTitle">
        <h2 v-if="isRemoved" class="text-yellow"><b>Talent no longer available</b></h2>
        <h2><b>{{ displayTitle }}</b></h2>
        <p>{{ this.removeNumbers(talent.hotkey || '') }}</p>
        <game-tooltip v-if="talent.raw_description" :text="talent.raw_description" :level="level"></game-tooltip>
        <p v-else>{{ talent.description }}</p>
        <slot></slot>
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
    mobileClick: true,
    // Hero level for tooltips that scale; only used when the talent has raw_description
    level: {
      type: Number,
      default: 1,
    },
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