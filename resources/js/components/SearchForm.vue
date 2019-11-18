<template>
  <div>


<!-- Loop through the primary fields
components for each field type


checkbox:
* icon
* alt-title
{{ primaryfields }}
{{ secondaryfields }}
-->


<div class="primary-filters">
  <div class="filter-group" v-for="field in primaryfields" :key="field.key" >
    <b-form-group :label="field.name" v-if="field.type == 'radio'">
        <b-form-radio v-model="form[field.key]" v-for="radio in field.options" :key="radio.key" name="some-radios" :value="radio.value" @input="onChange(field.key, $event), updateConditional(field.key, $event)">{{ radio.key }}</b-form-radio>
    </b-form-group>

    <b-form-group :label="field.name" v-if="field.type == 'multiselect' && conditionals[field.conditional_field] && conditionals[field.conditional_field] == field.conditional_value">
       <multiselect v-model="form[field.key]"  track-by="value" label="key" placeholder="All" :multiple="true" :options="field.options" :searchable="true" :allow-empty="true" @input="onChange(field.key, $event)" @remove="onChange($event)" ><!--@input="onChange($event)"-->
       </multiselect>
    </b-form-group>
    <b-form-group :label="field.name" v-if="field.type == 'checkbox'">
      <b-form-checkbox-group v-model="form[field.key]"  :name="field.key" @input="onChange(field.key, $event)" buttons >
        <b-form-checkbox v-for="option in field.options" :value="option.value" :key="option.key">{{ option.text }} <div class="checkbox-image" v-if="option.icon.length >0"><img :src="option.icon"/></div> </b-form-checkbox>
      </b-form-checkbox-group>
        <!--<b-form-checkbox v-for="checkbox in field.options" :key="checkbox.key" v-model="form[field.key]" :label="checkbox.name"  :name="field.key" :value="checkbox.value"  unchecked-value="" @input="onChange($event)">{{ checkbox.key }} </b-form-checkbox>-->
    </b-form-group>
  </div>
</div>
<b-button v-b-toggle.filterMenu variant="primary">Filter</b-button>
  <div>
    <b-collapse class="filter-menu" id="filterMenu" >
      <div class="search-form" >
        <div class="filter-group" v-for="field in secondaryfields" :key="field.key" >
          <b-form-group :label="field.name" v-if="field.type == 'radio'">
              <b-form-radio v-model="form[field.key]" v-for="radio in field.options" :key="radio.key" name="some-radios" :value="radio.value" @input="onChange(field.key, $event), updateConditional(field.key, $event)">{{ radio.key }}</b-form-radio>
          </b-form-group>
          <b-form-group :label="field.name" v-if="field.type == 'multiselect' && conditionals[field.conditional_field] && conditionals[field.conditional_field] == field.conditional_value">
             <multiselect v-model="form[field.name]"  track-by="value" label="key" placeholder="All" :multiple="true" :options="field.options" :searchable="true" :allow-empty="true" @input="onChange(field.key, $event)" @remove="onChange($event)" ><!--@input="onChange($event)"-->
             </multiselect>
          </b-form-group>
          <b-form-group :label="field.name" v-if="field.type == 'checkbox'">
            <b-form-checkbox-group v-model="form[field.key]"  :name="field.key" @input="onChange(field.key, $event)" buttons >
              <b-form-checkbox v-for="option in field.options" :value="option.value" :key="option.key"> <div class="checkbox-image" v-if="option.icon"><img :alt="option.name" :src="option.icon"/></div><span v-else>{{ option.text }}</span> </b-form-checkbox>
            </b-form-checkbox-group>
              <!--<b-form-checkbox v-for="checkbox in field.options" :key="checkbox.key" v-model="form[field.key]" :label="checkbox.name"  :name="field.key" :value="checkbox.value"  unchecked-value="" @input="onChange($event)">{{ checkbox.key }} </b-form-checkbox>-->
          </b-form-group>
        </div>
      </div>
    <div>

  </div>
</b-collapse>
</div>
</div>
</template>

<script type="text/ecmascript-6">

export default {
  props: ['rawfields', 'primaryfields', 'secondaryfields'],
    data() {
      return{
        fields: [],
        update: '',
        map: '',
        gametype: '',
        key: '',
        form: {},
        multiselects: {},
        conditionals: {},


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
          updateConditional(field, value){
            if(field != null & value != null){
              if(this.conditionals[field] != null){
            this.conditionals[field] = value;
            console.log('conditionals', this.conditionals);
          }
            }

          },
          onChange(field, event){

            var currentfields= this.form[field];


            if(currentfields == ""){
              this.$router.replace({ query: Object.assign({},this.$route.query, { [field]: undefined }) }).catch(err => {})
            }
            else{
            if(typeof(currentfields) == "object"){
              var multi = [];
              for (var object in currentfields){
                  multi.push(currentfields[object]);


              }

              multi = multi.join(',');
              this.$router.replace({ query: Object.assign({},this.$route.query, { [field]: multi }) }).catch(err => {})
            }
            else if(typeof(currentfields) == "string"){
              this.$router.replace({ query: Object.assign({},this.$route.query, { [field]: currentfields }) }).catch(err => {})
            }
          }

/*
           this.multiselects = Object.assign(this.form);

           if (this.$route.query != ""){this.$router.replace({ query: Object.assign({},this.$route.query, {  }) }).catch(err => {})}
            for (var item in this.fields){

              var multi = [];
              for (var val in this.form[item]){
                multi.push(this.form[item][val]["key"]);
              }
              multi = multi.join(',');
              if(multi.length>0){


                  this.$router.replace({ query: Object.assign({},this.$route.query, { [item]: multi }) }).catch(err => {})

              }
              else{

                this.$router.replace({ query: Object.assign({},this.$route.query, { [item]: undefined }) }).catch(err => {})

              }

            }
*/

          },
    fetchData () {

      for (var field in this.primaryfields){


          this.conditionals[this.primaryfields[field]['key']] = "";
          //this.$router.replace({ query: Object.assign({},this.$route.query, { [this.primaryfields[field]['key']]: undefined }) }).catch(err => {})
      }
      for (var field in this.secondaryfields){
        this.conditionals[this.secondaryfields[field]['key']] = "";

      }

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
  },
  filters: {
        caps: function(value) {
            if (!value) return ''
            return value.split('_').map(function(item) {
                return item.charAt(0).toUpperCase() + item.substring(1);
            }).join(' ');
        }
    }
}
</script>
