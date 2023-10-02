<template>
  <div>

    <round-image :size="size" :image="getMapImage()" :title="map.name">
      <slot>
      </slot>
    </round-image>
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