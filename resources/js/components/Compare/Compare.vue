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



  <div class="flex flex-center">
    <div :class="'col-start-' + index + ' row-start-1 bg-teal'" v-for="index in range" :key="index">

      <div v-if="playerChosen(index)">
        {{ playerData[index].battletag_short }}({{ playerData[index].region_name }})
      </div>
      <div v-else>
        <hero-or-league-choice-box :index="index" @onDataReturn="handleDataReturn"></hero-or-league-choice-box>
      </div>


    </div>

    <custom-button v-if="this.range.length < 5" @click="newPlayerAddded" text="Change to a plus sign with text 'Add New Player to Compare'" alt="Change to a plus sign with text 'Add New Player to Compare'" size="small" :ignoreclick="true"></custom-button>

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
        isLoading: false,
        range: [0],
        playerData: [],
        data: null,
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
        this.isLoading = true;
        try{
          const response = await this.$axios.post("/api/v1/compare", {
          //season: this.season, 

          });

          this.data = response.data;
        }catch(error){
        //Do something here
        }
        this.isLoading = false;

      },
      filterData(filteredData){
        this.charttype = filteredData.single["Chart Type"] ? filteredData.single["Chart Type"] : "Account Level";
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      },
      handleDataReturn(index, payload) {
        this.playerData[index] = payload;
      },
      newPlayerAddded(){
        this.range.push(1);
      },
      playerChosen(index) {
        if (index in this.playerData) {
          return true;
        }
        return false;
      },

    }
  }
</script>