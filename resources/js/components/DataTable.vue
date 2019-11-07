<template>
  <div class="data-table" >
    <div class="loading" v-if="loading">
      <b-spinner></b-spinner>
    </div>
    <div class="error" v-else-if="error.length > 0">
      GET URL incorrect.
    </div>
    <div class="error" v-else-if="tabledata.length === 0">
      No Data Found.
    </div>
    <b-table striped bordered responsive :sticky-header="false" small  :items="tabledata" :fields="tablefields" >
      <template v-slot:cell(name)="data">
        <image-popup  :alttext="data.value.hero_name" :imgSrc="'/images/heroes/'+data.value.short_name+'.png'" :popupdata="'Hero info for '+data.value.hero_name"></image-popup>  <b class="text-info">{{ data.value.hero_name }}</b>
      </template>
      <template v-slot:cell(win_rate)="data">
        {{data.value}}%
      </template>
      <template v-slot:cell(ban_rate)="data">
        {{data.value}}%
      </template>
      <template v-slot:cell(popularity)="data">
        {{data.value}}%
      </template>
      <template v-slot:cell(show_details)="row">
        <b-button size="sm" @click="row.toggleDetails" class="mr-2">
          {{ row.detailsShowing ? 'Hide' : 'Show'}} Talent Builds
        </b-button>
      </template>
      <template v-slot:row-details="row">
        <b-card>
          <b-row class="mb-2">
            <hero-talent-data :hero="row.item.name"></hero-talent-data>
          </b-row>
          <b-button size="sm" @click="row.toggleDetails; ">Hide Builds</b-button>
        </b-card>
      </template>
    </b-table>

  </div>
</template>

<script type="text/ecmascript-6">

export default {
  props: ['dataurl'],
    data() {
      return{
        tabledata: [],
        loading: false,
        tableitems: [{hero_name:"Abathur" }],
        tablefields: [],
        error: ""


      }
    },

  created () {
    this.fetchData()
  },
  watch: {

    '$route': 'fetchData'
  },
  methods: {
    fetchData () {
      this.loading = true
      if(this.dataurl.length > 0){
      axios.get(this.dataurl, {
        params: {
          ...this.$route.query
        }
      }).then(response => {
           this.tabledata = response.data;

           var tf = [];

             this.tabledata.forEach(function(arrayItem){

               $.each(arrayItem, function(key2, value) {
                 tf.push({key: key2, sortable: true});
               });

             })
             // Add "Showdetails" support for the talent info
            tf.push({key: "show_details"});
            this.tablefields = tf;
            this.loading = false;
        })
       }
       else{
         this.loading = false
         this.error = "URL not found"
       }
    },
    sanitizeParams(param){
      param = param.replace(/_/g, ' ')
      param = decodeURI(param)
      return param
    }
  }
}
</script>

<style scoped>
</style>
