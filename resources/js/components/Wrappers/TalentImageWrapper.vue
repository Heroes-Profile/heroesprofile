<template>
    <div :class="{ 'grayscale': isRemoved }">
      <round-image v-if="talent" :size="size" popupsize="large" mobileClick="true" :image="getTalentImage()">
        <h2 v-if="isRemoved" class="text-yellow font-bold mb-1">Talent no longer available</h2>
        <h2 class="text-base font-bold text-white">{{ displayTitle }}</h2>
        <div class="space-y-2.5 mt-2.5">
          <p v-for="(part, index) in descriptionParts" :key="index">
            <template v-if="part.label">
              <img v-if="part.quest" class="inline-block w-4 h-4 mr-1 align-[-0.15em]" src="/images/miscellaneous/quest-icon.png" alt="">
              <span :class="part.quest ? 'text-quest' : 'font-bold'">{{ part.label }}:</span>
            </template>
            {{ part.text }}
          </p>
        </div>
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
    // Descriptions use double spaces between paragraphs, and quest steps are labelled ("Quest:", "Mythic Reward:")
    descriptionParts() {
      const labels = 'Repeatable Quest|Repeatable Reward|Mythic Quest|Mythic Reward|Quest|Reward|Passive|Gambit';
      return (this.talent.description || '')
        .split(new RegExp(`\\s{2,}|\\s(?<!Mythic |Repeatable )(?=(?:${labels}):)`))
        .map(part => part.trim())
        .filter(part => part)
        .map(part => {
          const match = part.match(new RegExp(`^(${labels}):\\s*(.*)$`));
          if (!match) {
            return { text: part };
          }
          return { label: match[1], text: match[2], quest: match[1] !== 'Passive' && match[1] !== 'Gambit' };
        });
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
  }
}
</script>

<style scoped>
.grayscale {
  filter: grayscale(100%);
}
</style>