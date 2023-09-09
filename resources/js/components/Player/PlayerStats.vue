<template>
  <div class="">
  
  </div>
</template>

<script>
export default {
  name: 'PlayerStats',
  components: {
  },
  props: {
    battletag: String,
    blizzid: String, 
    region: String,
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
        const response = await this.$axios.post("/api/v1/player", {
          blizz_id: this.blizzid,
          region: this.region,
          game_type: "all",
          season: "all",
        });
        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>