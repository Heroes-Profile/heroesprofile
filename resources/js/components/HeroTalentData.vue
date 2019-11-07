<template>
  <div class="hero-talent">
    <div class="loading" v-if="loading">
      <b-spinner></b-spinner>
    </div>
    <div class="build flex-wrap" v-for="(build, index) in talentData">
      <!--<span class="sub-title">Build 1</span>-->
      <span class="sub-title">Build {{ index+1 }}</span>
      <div class="flex-wrap">
        <div class="talent" v-for="talent in build.talents" :data-talent="talent.level">
          <image-popup :alttext="talent.name" :imgSrc="'/images/talents/'+hero.short_name+'/'+talent.icon" :popupdata="talent.description"></image-popup>
        </div>
      </div>
      <div>
        Win Rate: {{Number((build.win_rate).toFixed(2))}}%
      </div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">

export default {
  props: ['dataurl', 'hero'],
    data() {
      return{
        'talentData' : [],
        'loading' : true
      }
    },

  created () {
    console.log(this.hero);
      this.getTalentData();
  },
  watch: {

  },
  methods: {
    getTalentData () {

      axios.get("/Global/Talents/Builds", {
        params: {
          "hero" : this.hero.hero_name
        }
      }).then(response => {
           this.talentData = response.data;
           console.log(this.talentData);
           this.loading = false;
      });

    }
  }
}
</script>

<style scoped>
</style>
