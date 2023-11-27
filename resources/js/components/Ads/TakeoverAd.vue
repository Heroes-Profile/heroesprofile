<template v-if="!patreonUser">
  <div id="takeover-ad-container"></div>
</template>

<script>
export default {
  name: 'TakeoverAd',
  components: {
  },
  props: {
    patreonUser: Boolean
  },
  data(){
    return {
    }
  },
  created(){
  },
  mounted() {
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
              placement.setAttribute("data-id", "60f593ac46e4640fd9497d39");
              placement.setAttribute("data-display-type", "hybrid-banner");
          } else {
              //load mobile placement
              placement.setAttribute("data-id", "60f593ac46e4640fd9497d39");
          }
          document.querySelector("#takeover-ad-container").appendChild(placement);
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