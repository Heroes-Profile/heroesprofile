<template>
  <div>
    Im going to work on this later
  </div>
</template>

<script>
export default {
  name: 'GlobalMatchupsTalentStats',
  components: {
  },
  props: {
    heroes: Array,
  },
  data(){
    return {
    }
  },
  created(){
    this.getData();
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/matchups/talents", {
          hero: "Abathur",
          ally_enemy: "Brightwing",
          type: "Enemy",
          talent_view: "hero",
          timeframe: "2.55.3.90670",
          game_type: "qm",
        });
        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>