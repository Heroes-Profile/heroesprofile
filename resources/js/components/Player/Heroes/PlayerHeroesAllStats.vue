<template>
  <div>
    All Heroes
    as played by
    <span><a :href="`/Player/${battletag}/${blizzid}/${region}`" target="_blank">{{ battletag }}</a></span>

    <infobox :input="infoText"></infobox>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :gametypedefault="gametype"
      :minimumgamesdefault="'0'"
      :includehero="true"
      :includerole="true"
      :includegamemap="true"
      :includegametype="true"
      :includeseason="true"
      :includeminimumgames="true"

      >
    </filters>

    <div>

    </div>


  </div>
</template>

<script>
export default {
  name: 'PlayerHeroesAllStats',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    battletag: String,
    blizzid: String, 
    region: String,
  },
  data(){
    return {
      infoText: "Select a hero below to view detailed stats for that hero. Use the search box above to filter the list of heroes. Or scroll down to the advanced section for table view.",
      gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
    }
  },
  created(){
  },
  mounted() {

    this.getData();
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getData(type){
      try{
        console.log(this.gametype);
        const response = await this.$axios.post("/api/v1/player/heroes/all", {
          blizz_id: this.blizzid,
          region: this.region,
          game_type: this.gametype,
        });
      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
      //this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;

      this.getData();
    },
  }
}
</script>