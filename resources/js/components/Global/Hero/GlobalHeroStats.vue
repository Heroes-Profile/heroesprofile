GlobalHeroStats<template>
  <div>
    Lets rethink how filtering is done.
    <h1>Global Hero Statistics</h1>
    <infobox :input="infoText"></infobox>

    {{ "<filters></filters>" }}


  </div>
    
</template>

<script>
export default {
  name: 'GlobalHeroStats',
  components: {
  },
  props: {
  },
  data(){
    return {
    	infoText: "Hero win rates based on differing increments, stat types, game type, or Rank. Click on a Hero to see detailed information. On the chart, bubble size is a combination of Win Rate, Pick Rate, and Ban Rate",
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
        const response = await this.$axios.post("/api/v1/global/hero/", {
        });

        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
  }
}
</script>