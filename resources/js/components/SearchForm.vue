<template>
  <div class="filter-form">
    {{ }}


    <nav class="navbar">
      <b-button-group role="toolbar" btn-group-toggle class="primary-filter-bar">
        <b-button id="popover-timeframe"  class="primary-filter-button" @click="showFilterMenu = false">Timeframe: {{ timeframetype | caps }} {{ form.timeframe_type | labels }}</b-button>
        <b-button id="popover-filtermenu"  variant="primary" @click="showTimeframe = false">More</b-button>
      </b-button-group>
    </nav>
    <b-popover target="popover-timeframe" :show.sync="showTimeframe" triggers="click" placement="bottom">
          <b-form-group label="Timeframe Type">
            <b-form-radio v-model="timeframetype" value="major" @change="timeframeChange()">Major</b-form-radio>
            <b-form-radio v-model="timeframetype" value="minor" @change="timeframeChange()">Minor</b-form-radio>
          </b-form-group>
          <b-form-group label="Timeframe" class="multiselect-wrapper" >
             <multiselect :show-labels="false" v-model="form.timeframe"  track-by="value" label="key" placeholder="All"  :multiple="true" :options="currentTimeFrameOptions" :searchable="true" :allow-empty="true" >
             </multiselect>
          </b-form-group>
          <b-button @click="showTimeframe = false, showFilterMenu = false, updateFields()" variant="primary" class="menu-close">Apply</b-button>
      </b-popover>
      <b-popover target="popover-filtermenu" :show.sync="showFilterMenu" triggers="click" placement="bottom" >

        <div class="filter-group">
          <b-form-group label="Game Map">
            <multiselect v-model="form.game_map" track-by="value" label="key" placeholder="All" :multiple="true" :options="rawfields.game_map" :searchable="true" :allow-empty="true">
            </multiselect>
          </b-form-group>
        </div>


          <!--  <div class="filter-group " v-for="field in secondaryfields" :key="field.key" >
              <b-form-group :label="field.name" v-if="field.type == 'multiselect' && conditionals[field.conditional_field] && conditionals[field.conditional_field] == field.conditional_value">
                 <multiselect v-model="form[field.name]"  track-by="value" label="key" placeholder="All" :multiple="true" :options="field.options" :searchable="true" :allow-empty="true" >
                 </multiselect>
              </b-form-group>
              <b-form-group :label="field.name" v-if="field.type == 'checkbox'">
                <b-form-checkbox-group v-model="form[field.key]"  :name="field.key"  >
                  <b-form-checkbox v-for="option in field.options" :value="option.value" :key="option.key"> <div class="checkbox-image" v-if="option.icon"><img :alt="option.name" :src="option.icon"/></div><span v-else>{{ option.text }}</span> </b-form-checkbox>
                </b-form-checkbox-group>
                </b-form-group>
            </div>-->
            <b-button @click="showTimeframe = false, showFilterMenu = false, updateFields()" variant="primary" class="menu-close">Apply</b-button>
      </b-popover>
    </div>
</template>

<script type="text/ecmascript-6">

export default {
  props: ['rawfields', 'primaryfields', 'secondaryfields', 'timeframe_type'],
    data() {
      return{
        form: {},
        timeframetype: 'major',
        timeframe: undefined,
        selectedTimeframe: null,
        showTimeframe: false,
        showFilterMenu: false,
        finalFields : {},

      }
    },
created (){


},

  mounted () {
    this.$store.commit('updateAjaxURL', '/get_heroes_stats_table_data');
    this.fetchData()
    this.updateFields()
  },
  watch: {
  },
  methods: {
    onClickOutside (event, el) {
          //  this.showTimeframe = false; this.showFilterMenu = false; this.updateFields();
          },
    fetchData () {



      this.fields = this.rawfields;

      this.loading = true
      if(this.$route.query.map){
        this.map = this.sanitizeParams(this.$route.query.map)
      }

    },

    timeframeChange: function ()
            {
            this.form['timeframe'] = undefined;
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
    },
    updateFields(){
      this.finalFields = this.form;
      /*this.finalFields['timeframe'] = this.form.timeframe
      this.finalFields['game_map'] = this.form.game_map*/

      this.$store.dispatch('updateFormData',  this.form);

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
          return values.join(', ')

        }
    },
    computed: {
          currentTimeFrameOptions: function() {

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
.popover-body{
  display:flex;
  flex-wrap:wrap;

}
.popover-body .menu-close{
  flex-basis:100%;
}
.multiselect-wrapper { width:100%;}

</style>
