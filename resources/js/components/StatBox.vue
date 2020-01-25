<template>
  <b-card :title="title" class="statbox">
<div class="stat-bars">
    <div class="individual-section" v-for="item in data">
      <image-popup v-if="item.imgurl"  :alttext="item.title" :imgSrc="item.imgurl" ></image-popup>
      <span class="stat-bar-title" v-else>{{ item.title}}</span>
      <b-progress :precision="parseInt(decimals)" :label="item.value" :value="item.value" :max="maxnumber" class="mb-3"></b-progress>
    </div>
  </div>
  </b-card>
</template>
<script type="text/ecmascript-6">
export default {
  props: ['data', 'title', 'decimals'],
data() {
  return {


  }
},
methods:{
  
},
mounted: function(){

},
filters: {


},
computed: {
  maxnumber: function() { // Sets the max value to the highest value in the group, if greater than 100
  let maxvalue = 0;
  var dataarray = Object.entries(this.data);
  console.log(dataarray);
    this.data.forEach(function (arrayItem) {
      if(arrayItem.value > maxvalue){
        maxvalue = arrayItem.value;
      }
  });
  /*if(maxvalue < 100){
    maxvalue = ((100-maxvalue)/2)+ maxvalue;
  }*/


  return maxvalue;
},
minnumber: function() { // Sets the max value to the highest value in the group, if greater than 100
let minvalue = 25;
let valuearray = [];

var dataarray = Object.entries(this.data);

console.log(dataarray);
  this.data.forEach(function (arrayItem) {
    valuearray.push(arrayItem.value);

});
minvalue = Math.min(...valuearray);
minvalue = minvalue/2;
console.log(minvalue);
return minvalue;
}

}
}
</script>
