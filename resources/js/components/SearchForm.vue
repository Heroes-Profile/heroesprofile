<template>
  <div class="">
    <b-button id="popover-target-1">
    Filter
  </b-button>
  <!--<b-popover target="popover-target-1" triggers="click" placement="bottom">-->


  <form class="search-form" method="get" @submit.prevent="submitForm">
   <div  style="width: 100%;" v-for="(field, fieldname, index) in rawfields">

     <label class="control-label">
       {{ fieldname }}
    </label>

   <!--<select class="form-control" v-model="form[fieldname]" @change="onChange($event)">
     <option value="">All</option>
     <option v-for="(name, id, index) in field" v-bind:value="name">{{ name.key }}</option>
   </select>-->


     <div>

    <multiselect v-model="form[fieldname]"  track-by="value" label="key" placeholder="All" :multiple="true" :options="field" :searchable="true" :allow-empty="false" @input="onChange($event)" ><!--@input="onChange($event)"-->
    <!--  <template slot="selection" slot-scope="{ option }"><strong>{{ option.key }}</strong> is written in<strong>  {{ option.key }}</strong></template>
      { option }-->
    </multiselect>



  </div>
 </div>

  </form>
  <!--</b-popover>-->
</div>

</template>

<script type="text/ecmascript-6">

export default {
  props: ['rawfields'],
    data() {
      return{
        fields: [],
        update: '',
        map: '',
        gametype: '',
        key: '',
        form: {},
        multiselects: {}


      }
    },
created (){

},
  mounted () {

    this.fetchData()
  },
  watch: {

    '$route': 'fetchData',

    updatePage: function (value) {

            //this.$router.push({ query: { ...this.$route.query, update: value }});

            this.$router.push({ query: Object.assign(this.$route.query, { update: value }) })
    },
    '$route.query.update': function(val) {
        this.update = val;
        console.log('this.update', this.update);
    }
  },
  methods: {
    submitForm() {
              //this.fetchData();
          },
          onChange(event){

           this.multiselects = Object.assign(this.form);

            for (var item in this.fields){

            /*  if(Array.isArray(this.form[item])){

                var formvalues = "";
                for (var val in this.form[item]){

                  formvalues += this.form[item][val].key+",";
                }
                console.log(formvalues);
              }*/
                //this.multiselects[item] = formvalues.substring(0, formvalues.length - 1);

              //console.log("formvalues", this.form[item][0]);
          //  this.$router.push({ query: { ...this.$route.query, : {form[item]} }});

      /*    $.each(fields, function(field, value) {
            this.$router.replace({ query: Object.assign({},this.$route.query, { [field]: this.form }) })
          }*/
          console.log('thequery', this.form[item]);
          var multi = [];
          for (var val in this.form[item]){
            console.log('val', val);
            multi.push(this.form[item][val]["key"]);

          }
          multi = multi.join(',');
          
          this.$router.replace({ query: Object.assign({},this.$route.query, { [item]: multi }) })
        /*  this.$router.replace({
      	         query: {
        	          ...this.$route.query,
        	           [item]: this.sanitizeParams(this.form[item])
                   }
                 })*/
            }

          },
    fetchData () {

      this.fields = this.rawfields;

      this.loading = true
      if(this.$route.query.map){
        this.map = this.sanitizeParams(this.$route.query.map)
      }

    },

    sanitizeParams(param){
    //  param = decodeURI(param)
      param = param.replace(/ _/g, ' ')
      param = param.replace(/ +/g, ' ')
      //param = decodeURI(param)
      return param
    },
    encodeParams(param){
      param = decodeURI(param)
      param = param.replace(/ /g, '_')
      param = encodeURI(param)
      return param
    }
  }
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
