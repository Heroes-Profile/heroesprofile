<template v-if="!patreonUser">
  <div :id="`dynamic-square-ad-container-${index}`"></div>
</template>

<script>

export default {
  name: 'DynamicSquareAd',
  components: {
  },
  props: {
    patreonUser: Boolean,
    index: Number,
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
    let htmlComponent = `#dynamic-square-ad-container-${this.index}`;

    if(!this.patreonUser){
      window.top.__vm_add = window.top.__vm_add || [];

      //this is a x-browser way to make sure content has loaded.

      (function (success) {
          if (window.document.readyState !== "loading") {
              success();
          } else {
              window.document.addEventListener("DOMContentLoaded", function () {
                  success();
              });
          }
      })(function () {
          var placement = document.createElement("div");
          placement.setAttribute("class", "vm-placement");
          if (window.innerWidth > 1000) {
              //load desktop placement
              placement.setAttribute("data-id", "60f5939d46e4640fd9497d35");
          } else {
              //load mobile placement
              placement.setAttribute("data-id", "60f5939d46e4640fd9497d35");
          }
          document.querySelector(htmlComponent).appendChild(placement);
          window.top.__vm_add.push(placement);
      });
    }
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>