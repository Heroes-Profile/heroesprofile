<template>
  <div>

      <!-- This probably needs to in the round-image component, but putting it here for now -->
      <!--Awards go in the bottom right corner -->
      <div v-if="award">
        {{ award.title }}
        <img :src="getAwardIcon()"/>
      </div>


      <!-- This probably needs to in the round-image component, but putting it here for now -->
      <!--Heroes Profile Owner goes in the top left corner -->
      <div v-if="hpowner">
        {{ "HP Owner" }}
        <i class="fas fa-crown"></i>
      </div>


      <!-- This probably needs to in the round-image component, but putting it here for now -->
      <!--Party Icon go in the bottom right corner -->
      <div v-if="party">
        {{ party }}
        <img :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${party}.png`"/>
      </div>

    <round-image :rectangle="rectangle" :size="size" :image="getHeroImage()" :title="hero.name" :excludehover="excludehover">



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
      let color = "blue";
      if(!this.winner){
        color = "red";
      }
      return `/images/awards/${this.award.icon}_${color}.png`;
    },
   
  }
}
</script>