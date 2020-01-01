<template>
 <div v-b-popover.hover.top.html="popupdata" :title="alttext"  class="image-popup-img" >
   <!--<b-img  v-bind="imgProps" :src="'@/images/heroes/abathur.png'" rounded="circle" :alt="alttext" ></b-img>-->
   <img :src="imgSrc" :alt="alttext" class="rounded-circle circle-image">
 </div>
</template>

<script type="text/ecmascript-6">

export default {
  props: ['imgSrc', 'alttext', 'popupdata'],
  data() {
    return{

      highlighted : false,


      imgProps: { blank: true, blankColor: '#777',  class: '' }
    }
  }

}
</script>

<style scoped>
</style>
