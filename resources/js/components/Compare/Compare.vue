<template>
  <div>
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :includeherorole="true"
      :includehero="true"
      :includesinglegamemap="true"
      :includesinglegametype="true"
      :includeseason="true"
      >
    </filters>



      <div class="grid grid-cols-4 grid-rows-1 gap-4">
        <!-- Second column, 4 individual items -->
        <div :class="'col-start-' + index + ' row-start-1 bg-green-200'" v-for="index in range" :key="index">
          <hero-or-league-choice-box :index="index" @onDataReturn="handleDataReturn"></hero-or-league-choice-box>
        </div>
      </div>


  </div>
</template>

<script>
export default {
  name: 'Compare',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
  },
  data(){
    return {
      loading: false,
      range: [0, 1, 2 ,3],
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
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/leaderboard", {
          season: this.season, 
          gameType: this.gametype,
          type: this.leaderboardtype.toLowerCase(),
          groupsize: this.groupsize,
        });

        this.data = response.data;
      }catch(error){
        //Do something here
      }
    },

    filterData(filteredData){
      this.charttype = filteredData.single["Chart Type"] ? filteredData.single["Chart Type"] : "Account Level";
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
    },
    handleDataReturn(payload) {
      const { type, result, index } = payload;
    },
  }
}
</script>