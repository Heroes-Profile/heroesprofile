<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Global Hero Statistics'"></page-heading>

    Need to update some dynamic fields on this to make this fields required.  This page isnt fully complete but im bored of it
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :includecharttype="true"
      :includeregion="true"
      :includeminimumaccountlevel="true"
      :includexaxisincrements="true"
      >
    </filters>
    <dynamic-banner-ad :patreon-user="patreonUser" :index="1"></dynamic-banner-ad>
    <div v-if="data">
      Got Data
      <bar-chart :data="data"></bar-chart>
    </div>
    <div v-else>
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalExtraStats',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    patreonUser: Boolean,
  },
  data(){
    return {
      loading: false,
      infoText: "This page will be used for extraneous stats. This data is for players who have played in the last year.",
      data: null,

      charttype: "Account Level",
      region: null,
      minimumaccountlevel: 100,
      xaxisincrements: 25,
      hero: "Abathur",
      gametype: ["sl"],
      cancelTokenSource: null,
    }
  },
  created(){
  },
  mounted() {
    this.getAccountLevelData();
  },
  computed: {
  },
  watch: {
  },
  methods: {
    async getAccountLevelData(){
      try{
        this.loading = true;

        const response = await this.$axios.post("/api/v1/global/extra/account/level", {
          region: this.region,
          minimumaccountlevel: this.minimumaccountlevel,
          xaxisincrements: this.xaxisincrements,
        });

        this.data = response.data;
        this.loading = false;
      }catch(error){
        //Do something here
      }
    },

    async getHeroLevelData(){
      try{
        this.loading = true;

        const response = await this.$axios.post("/api/v1/global/extra/hero/level", {
          region: this.region,
          hero: this.hero,
          game_type: this.gametype,
        });

        this.data = response.data;
        this.loading = false;
      }catch(error){
        //Do something here
      }
    },


    filterData(filteredData){

      this.charttype = filteredData.single["Chart Type"] ? filteredData.single["Chart Type"] : "Account Level";
      this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];

      if(this.charttype === "Account Level"){
        this.minimumaccountlevel = filteredData.single["Min. Account Level"] ? filteredData.single["Min. Account Level"] : 100;
        this.xaxisincrements = filteredData.single["X Axis Increments"] ? filteredData.single["X Axis Increments"] : 25;

        this.getAccountLevelData();
      }else if(this.charttype === "Hero Level"){
        this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : "Abathur";
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : [];
        this.getHeroLevelData();
      }
    },
  }
}
</script>