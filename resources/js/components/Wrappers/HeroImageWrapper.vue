<template>
  <div>


    <round-image 
      :rectangle="rectangle" 
      :size="size" 
      :image="getHeroImage()" 
      :title="hero.name" 
      :excludehover="excludehover" 
      :hpowner="hpowner"
      :award="award"
      :awardicon="getAwardIcon()"
      :party="party"
      :ispatreon="ispatreon"
      popupsize="large"
    >
      <slot>
        <div v-if="!hasSlotContent">
          <h2>{{ hero.name }}</h2>
        </div>
      </slot>
    </round-image>
    
  </div>
</template>

<script>
export default {
  name: 'HeroImageWrapper',
  components: {
  },
  props: {
    hero: Object,
    size: String,
    excludehover: Boolean,
    rectangle: Boolean,
    heroImage: String,
    award: Object,
    winner: Boolean,
    hpowner: Boolean,
    party: String,
    ispatreon: Boolean,
    popupsize: String
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
    hasSlotContent() {
      return !!this.$slots.default;
    },
  },
  watch: {
  },
  methods: {
    getHeroImage(){
      if(this.rectangle){
        return `/images/heroes_rectangle/${this.hero.short_name}.jpg`;
      }
      else{
      return `/images/heroes/${this.hero.short_name}.png`;
      }
    },
    getAwardIcon(){
      if(!this.award){
        return null;
      }
      let color = "blue";
      if(!this.winner){
        color = "red";
      }
      return `/images/awards/${this.award.icon}_${color}.png`;
    },
   
  }
}
</script>