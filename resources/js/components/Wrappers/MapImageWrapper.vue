<template>
  <div>

    <round-image v-if="hovertextstyleoverride" :size="size" :title="map.name" :image="getMapImage()" :tooltiptex="getToolTip()" :showTooltip="true" :hovertextstyleoverride="true">
      <slot>
      </slot>
    </round-image>
    <round-image v-else :size="size" :title="map.name" :image="getMapImage()" :tooltiptex="getToolTip()" :showTooltip="true"></round-image>
  </div>
</template>

<script>
export default {
  name: 'MapImageWrapper',
  components: {
  },
  props: {
    map: Object,
    size: String,
    hovertextstyleoverride: Boolean,
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
    getMapImage(){
      return `/images/maps/icon/bg_${this.map.sanitized_map_name}.jpg`;
    },
    getToolTip(){
      /*
        Need to style the tool tips with this info
        
        this.map.name
        this.hero.win_rate
        this.hero.games_played

      */
      return this.map.name;
    },
  }
}
</script>