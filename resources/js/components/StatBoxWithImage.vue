<template>
  <b-card :title="title" class="statbox">
<div class="stat-bars">
    <div class="individual-section" v-for="item in data">
      <image-popup  :alttext="item.title" :imgSrc="item.imgurl" ></image-popup>
      <b-progress show-value :value="item.value" :max="maxnumber" class="mb-3"></b-progress>
    </div>
  </div>
  </b-card>
</template>
<script type="text/ecmascript-6">
export default {
  props: ['data', 'title'],
data() {
  return {


  }
},
methods:{
},
mounted: function(){

},
computed: {
  maxnumber: function() { // Sets the max value to the highest value in the group, if greater than 100
  let maxvalue = 100
  var dataarray = Object.entries(this.data);
  console.log(dataarray);
    this.data.forEach(function (arrayItem) {
      if(arrayItem.value > maxvalue){
        maxvalue = arrayItem.value;
      }
  });

  if(maxvalue < 100){
    maxvalue = ((100-maxvalue)/2)+ maxvalue;
  }

  return maxvalue;
}

}
}
</script>
