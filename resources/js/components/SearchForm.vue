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

<b-button-group role="toolbar" btn-group-toggle class="primary-filter-bar">

  <b-button  id="popover-timeframe"  class="primary-filter-button">Timeframe: {{ timeframetype | caps }} {{ selectedTimeframe | labels }}</b-button>

  <b-button v-b-toggle.filterMenu variant="primary">More</b-button>


</b-button-group>
<b-popover target="popover-timeframe" :show.sync="showTimeframe" triggers="click" placement="bottom">
    <div>
      <b-form-group label="Timeframe Type">
        <b-form-radio v-model="timeframetype" value="major" @change="timeframeChange()">Major</b-form-radio>
        <b-form-radio v-model="timeframetype" value="minor" @change="timeframeChange()">Minor</b-form-radio>
      </b-form-group>
      <b-form-group label="Timeframe" >
         <multiselect :show-labels="false" v-model="selectedTimeframe"  track-by="value" label="key" placeholder="All"  :multiple="true" :options="changetimeframe" :searchable="true" :allow-empty="true" >
         </multiselect>
      </b-form-group>
      <b-button @click="showTimeframe = false" variant="primary" class="menu-close">Close</b-button>
  </div>
  </b-popover>
<div class="filterMenu">




  <b-collapse accordion="primaryFilters" class="filter-menu" id="filterMenu" >

    <div class="search-form" >

      <div class="filter-group" v-for="field in secondaryfields" :key="field.key" >
      <!--  <b-form-group :label="field.name" v-if="field.type == 'radio'">
            <b-form-radio v-model="form[field.key]" v-for="radio in field.options" :key="radio.key" name="some-radios" :value="radio.value" @input="onChange(field.key, $event), updateConditional(field.key, $event)">{{ radio.key }}</b-form-radio>
        </b-form-group>-->
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
        <b-button v-b-toggle.filterMenu variant="primary">Close</b-button>
    </div>


</b-collapse>
</div>


<!--<div class="primary-filters">
  <div class="filter-group" v-for="field in primaryfields" :key="field.key" >
    <b-form-group :label="field.name" v-if="field.type == 'radio'">
        <b-form-radio v-model="form[field.key]" v-for="radio in field.options" :key="radio.key" name="some-radios" :value="radio.value" @input="onChange(field.key, $event), updateConditional(field.key, $event)">{{ radio.key }}</b-form-radio>
    </b-form-group>

    <b-form-group :label="field.name" v-if="field.type == 'multiselect' && conditionals[field.conditional_field] && conditionals[field.conditional_field] == field.conditional_value">
       <multiselect v-model="form[field.key]"  track-by="value" label="key" placeholder="All" :multiple="true" :options="field.options" :searchable="true" :allow-empty="true" @input="onChange(field.key, $event)" @remove="onChange($event)" >
       </multiselect>
    </b-form-group>
    <b-form-group :label="field.name" v-if="field.type == 'checkbox'">
      <b-form-checkbox-group v-model="form[field.key]"  :name="field.key" @input="onChange(field.key, $event)" buttons >
        <b-form-checkbox v-for="option in field.options" :value="option.value" :key="option.key">{{ option.text }} <div class="checkbox-image" v-if="option.icon.length >0"><img :src="option.icon"/></div> </b-form-checkbox>
      </b-form-checkbox-group>
        <b-form-checkbox v-for="checkbox in field.options" :key="checkbox.key" v-model="form[field.key]" :label="checkbox.name"  :name="field.key" :value="checkbox.value"  unchecked-value="" @input="onChange($event)">{{ checkbox.key }} </b-form-checkbox>
    </b-form-group>
  </div>
</div>-->


</div>
</template>

<script type="text/ecmascript-6">

export default {
  props: ['rawfields', 'primaryfields', 'secondaryfields', 'timeframe_type'],
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
        timeframetype: 'major',
        timeframe: undefined,
        selectedTimeframe: null,
        showTimeframe: false





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
    timeframeChange: function ()
            {
            this.selectedTimeframe = undefined;
            this.timeframe = this.timeframe
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
        },
        labels: function(selection){
          if(!selection) return ''
          var values = [];
          var i = 0;
          console.log('value',selection);
          var counter = 0;


          for (const [key, val] of Object.entries(selection)) {
            if(val.text){
              values.push(val.text);
              counter++;
              if(counter > 1){
                values.push ("...");
                break;
              }

            }
          }
          return "| "+values.join(', ')

        }
    },
    computed: {

          changetimeframe: function() {

          let timeframe = ''

               switch(this.timeframetype) {

                   case 'major':
                   timeframe = this.rawfields.major_patch
                   break;

                   case 'minor':
                   timeframe = this.rawfields.minor_patch
                   break;

                   default:
                   timeframe = this.rawfields.major_patch
               }

               return timeframe

          }

      },

}
</script>
<style>
.primary-filter-popup{background-color:white; color:black;}
</style>
